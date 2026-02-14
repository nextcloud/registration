<?php

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;
use OC\Authentication\Token\IProvider;
use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\Accounts\IAccountManager;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;

use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * class RegistrationServiceTest
 *
 * @group DB
 */
class RegistrationServiceTest extends TestCase {
	use DatabaseTransaction;

	/** @var RegistrationMapper */
	private RegistrationMapper $registrationMapper;
	/** @var IConfig */
	private $config;
	/** @var ICrypto | MockObject */
	private $crypto;
	private RegistrationService $service;

	public function setUp(): void {
		parent::setUp();
		$mailService = $this->createMock(MailService::class);
		$l10n = $this->createMock(IL10N::class);
		$l10n->expects($this->any())
			->method('t')
			->willReturnCallback(function ($text, $parameters = []) {
				return vsprintf($text, $parameters);
			});
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$userManager = \OC::$server->get(IUserManager::class);
		$accountManager = $this->createMock(IAccountManager::class);
		$this->config = $this->createMock(IConfig::class);
		$groupManager = \OC::$server->get(IGroupManager::class);
		$random = \OC::$server->get(ISecureRandom::class);
		$userSession = $this->createMock(IUserSession::class);
		$request = $this->createMock(IRequest::class);
		$logger = $this->createMock(LoggerInterface::class);
		$session = $this->createMock(ISession::class);
		$tokenProvider = $this->createMock(IProvider::class);
		$this->crypto = $this->createMock(ICrypto::class);

		$this->registrationMapper = new RegistrationMapper(
			\OC::$server->get(IDBConnection::class),
			$random
		);

		$this->service = new RegistrationService(
			'registration',
			$mailService,
			$l10n,
			$urlGenerator,
			$this->registrationMapper,
			$userManager,
			$accountManager,
			$this->config,
			$groupManager,
			$random,
			$userSession,
			$request,
			$logger,
			$session,
			$tokenProvider,
			$this->crypto
		);
	}

	public static function dataValidateEmail(): array {
		return [
			['aaaa@example.com', '', 'no'],
			['aaaa@example.com', 'example.com', 'no'],
			['aaaa@example.com', 'eXample.com', 'no'],
			['aaaa@eXample.com', 'example.com', 'no'],
			['aaaa@example.com', 'example.com;example.tld', 'no'],
			['aaaa@example.com', 'example.tld;example.com', 'no'],
			['aaaa@example.tld', 'example.tld ; example.com', 'no'],
			['aaaa@example.com', 'example.tld ; example.com', 'no'],
			['aaaa@cloud.example.com', '*.example.com', 'no'],
			['aaaa@cloud.example.com', 'cloud.example.*', 'no'],

			['aaaa@example.com', '', 'yes'],
			['aaaa@example.com', 'nextcloud.com', 'yes'],
			['aaaa@example.com', 'nextcloud.com;example.tld', 'yes'],
		];
	}

	/**
	 * @throws RegistrationException
	 */
	#[DataProvider('dataValidateEmail')]
	public function testValidateEmail(string $email, string $allowedDomains, string $blocked) {
		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->willReturnMap([
				['registration', 'allowed_domains', '', $allowedDomains],
				['registration', 'domains_is_blocklist', 'no', $blocked],
				['registration', 'show_domains', 'no', 'no'],
			]);

		$this->service->validateEmail($email);
	}

	public static function dataValidateEmailThrows(): array {
		return [
			['aaaa@example.com', 'nextcloud.com;example.tld', 'no'],
			['aaaa@example.com', 'nextcloud.com', 'no'],

			['aaaa@example.com', 'example.com', 'yes'],
			['aaaa@example.com', 'eXample.com', 'yes'],
			['aaaa@eXample.com', 'example.com', 'yes'],
			['aaaa@example.com', 'example.com;example.tld', 'yes'],
			['aaaa@example.com', 'example.tld;example.com', 'yes'],
			['aaaa@cloud.example.com', '*.example.com', 'yes'],
			['aaaa@cloud.example.com', 'cloud.example.*', 'yes'],
		];
	}

	/**
	 * @throws RegistrationException
	 */
	#[DataProvider('dataValidateEmailThrows')]
	public function testValidateEmailThrows(string $email, string $allowedDomains, string $blocked) {
		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->willReturnMap([
				['registration', 'allowed_domains', '', $allowedDomains],
				['registration', 'domains_is_blocklist', 'no', $blocked],
				['registration', 'show_domains', 'no', 'no'],
			]);

		$this->expectException(RegistrationException::class);
		$this->service->validateEmail($email);
	}

	public function testCreatePendingReg() {
		$email = 'aaaa@example.com';

		$actual = $this->service->createRegistration($email);

		$expected = new Registration();
		$expected->setEmail($email);
		$expected->setUsername('');
		$expected->setDisplayname('');
		$this->registrationMapper->generateNewToken($expected);

		$this->assertEquals($expected->getEmail(), $actual->getEmail());

		$indb = $this->registrationMapper->find($email);
		$this->assertEquals($expected->getEmail(), $indb->getEmail());
	}

	public function testValidatePendingReg() {
		$email = 'aaaa@example.com';

		$this->service->createRegistration($email, 'alice');
		$this->expectException(RegistrationException::class);
		$this->service->validateEmail($email);
	}

	public function testCreateAccountWebForm() {
		$reg = new Registration();
		$reg->setEmail('asd@example.com');
		//$reg->setUsername("alice1");
		$reg->setDisplayname('Alice');
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->will($this->returnCallback([$this, 'settingsCallback1']));


		$form_input_username = 'alice1';
		$resulting_user = $this->service->createAccount($reg, $form_input_username, 'Full name', '+49 800 / 1110111', 'asdf');

		$this->assertInstanceOf(IUser::class, $resulting_user);
		$this->assertEquals($form_input_username, $resulting_user->getUID());
		$this->assertEquals('asd@example.com', $resulting_user->getEmailAddress());
	}

	/**
	 * @depends testCreateAccountWebForm
	 */
	public function testDuplicateUsernameWebForm() {
		$reg = new Registration();
		$reg->setEmail('pppp@example.com');
		//$reg->setUsername("alice1");
		$reg->setDisplayname('Alice');
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('The login name you have chosen already exists.');
		$this->service->createAccount($reg, 'alice1', 'Full name', '+49 800 / 1110111', 'asdf');
	}

	/*
	 * NOTE
	 * We don't need to test for duplicate emails here, because:
	 * In Webform, emails are validated not to be duplicate in validateEmail(),
	 * that is, when users first fill in their email
	 * In API, they are also validated in ApiControllerTest::validate()
	 */

	/**
	 * @depends testCreateAccountWebForm
	 */
	public function testDuplicateUsernameApi() {
		$reg = new Registration();
		$reg->setEmail('pppp@example.com');
		$reg->setUsername('alice1');
		$reg->setDisplayname('Alice');
		$reg->setPassword('crypto(asdf)');
		$reg->setEmailConfirmed(true);

		$this->crypto->method('decrypt')
			->with('crypto(asdf)')
			->willReturn('asdf');

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('The login name you have chosen already exists.');
		$this->service->createAccount($reg, null, 'Full name', '+49 800 / 1110111');
	}

	/**
	 * @depends testDuplicateUsernameApi
	 */
	public function testUsernameDoesntMatchPattern() {
		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->willReturnMap([
				['registration', 'username_policy_regex', '', '/^[a-z]\.[a-z]+$/'],
			]);

		$reg = new Registration();
		$reg->setEmail('pppp@example.com');
		$reg->setUsername('alice23');
		$reg->setDisplayname('Alice');
		$reg->setPassword('crypto(asdf)');
		$reg->setEmailConfirmed(true);

		$this->crypto->method('decrypt')
			->with('crypto(asdf)')
			->willReturn('asdf');

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('Please provide a valid login name.');
		$this->service->createAccount($reg, null, 'Full name', '+49 800 / 1110111');
	}

	public function settingsCallback1(string $app, string $key, string $default): string {
		$map = [
			'registered_user_group' => 'none',
			'admin_approval_required' => 'no',
			'username_policy_regex' => '',
			'show_fullname' => 'yes',
			'enforce_fullname' => 'no',
			'show_phone' => 'yes',
			'enforce_phone' => 'no',
			'newUser.sendEmail' => 'no',
		];

		return $map[$key];
	}
}
