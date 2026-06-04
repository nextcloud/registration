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
use OCP\AppFramework\Services\IAppConfig;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IPhoneNumberUtil;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use OCP\Server;
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

	private RegistrationMapper $registrationMapper;
	private IConfig&MockObject $config;
	private IAppConfig&MockObject $appConfig;
	private ICrypto&MockObject $crypto;
	private IPhoneNumberUtil $phoneNumberUtil;
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
		$this->appConfig = $this->createMock(IAppConfig::class);
		$groupManager = \OC::$server->get(IGroupManager::class);
		$random = \OC::$server->get(ISecureRandom::class);
		$userSession = $this->createMock(IUserSession::class);
		$request = $this->createMock(IRequest::class);
		$logger = $this->createMock(LoggerInterface::class);
		$session = $this->createMock(ISession::class);
		$tokenProvider = $this->createMock(IProvider::class);
		$this->crypto = $this->createMock(ICrypto::class);
		$this->phoneNumberUtil = Server::get(IPhoneNumberUtil::class);

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
			$this->appConfig,
			$groupManager,
			$random,
			$userSession,
			$request,
			$logger,
			$session,
			$tokenProvider,
			$this->crypto,
			$this->phoneNumberUtil
		);
	}

	public static function dataValidateEmail(): array {
		return [
			['aaaa@example.com', '', false],
			['aaaa@example.com', 'example.com', false],
			['aaaa@example.com', 'eXample.com', false],
			['aaaa@eXample.com', 'example.com', false],
			['aaaa@example.com', 'example.com;example.tld', false],
			['aaaa@example.com', 'example.tld;example.com', false],
			['aaaa@example.tld', 'example.tld ; example.com', false],
			['aaaa@example.com', 'example.tld ; example.com', false],
			['aaaa@cloud.example.com', '*.example.com', false],
			['aaaa@cloud.example.com', 'cloud.example.*', false],

			['aaaa@example.com', '', true],
			['aaaa@example.com', 'nextcloud.com', true],
			['aaaa@example.com', 'nextcloud.com;example.tld', true],
		];
	}

	/**
	 * @throws RegistrationException
	 */
	#[DataProvider('dataValidateEmail')]
	public function testValidateEmail(string $email, string $allowedDomains, bool $blocked) {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('allowed_domains')
			->willReturn($allowedDomains);

		$this->appConfig->expects($this->exactly($allowedDomains === '' ? 0 : 2))
			->method('getAppValueBool')
			->willReturnMap([
				['domains_is_blocklist', $blocked],
				['show_domains', false],
			]);

		$this->service->validateEmail($email);
	}

	public static function dataValidateEmailThrows(): array {
		return [
			['aaaa@example.com', 'nextcloud.com;example.tld', false],
			['aaaa@example.com', 'nextcloud.com', false],

			['aaaa@example.com', 'example.com', true],
			['aaaa@example.com', 'eXample.com', true],
			['aaaa@eXample.com', 'example.com', true],
			['aaaa@example.com', 'example.com;example.tld', true],
			['aaaa@example.com', 'example.tld;example.com', true],
			['aaaa@cloud.example.com', '*.example.com', true],
			['aaaa@cloud.example.com', 'cloud.example.*', true],
		];
	}

	/**
	 * @throws RegistrationException
	 */
	#[DataProvider('dataValidateEmailThrows')]
	public function testValidateEmailThrows(string $email, string $allowedDomains, bool $blocked) {
		$this->appConfig->expects($this->once())
			->method('getAppValueString')
			->with('allowed_domains')
			->willReturn($allowedDomains);

		$this->appConfig->expects($this->exactly(2))
			->method('getAppValueBool')
			->willReturnMap([
				['domains_is_blocklist', $blocked],
				['show_domains', false],
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

		$this->appConfig->expects($this->exactly(2))
			->method('getAppValueString')
			->willReturnMap([
				['registered_user_group', 'none', 'none'],
				['username_policy_regex', ''],
			]);

		$this->appConfig->expects($this->exactly(6))
			->method('getAppValueBool')
			->willReturnMap([
				['show_fullname', true],
				['enforce_fullname', false],
				['show_phone', true],
				['enforce_phone', false],
				['admin_approval_required', false],
			]);

		$this->config->expects($this->once())
			->method('getAppValue')
			->with('core', 'newUser.sendEmail');

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
		$this->appConfig->expects($this->atLeastOnce())
			->method('getAppValueString')
			->willReturnMap([
				['username_policy_regex', '', '/^[a-z]\.[a-z]+$/'],
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
}
