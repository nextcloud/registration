<?php

namespace OCA\Registration\Tests\Unit\Service;

use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OC\Authentication\Token\IProvider;
use OCP\IRequest;
use OCP\Security\ISecureRandom;
use OCP\Security\ICrypto;
use OCP\ISession;
use OCP\IUser;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\IUserSession;

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;

/**
 * class RegistrationServiceTest
 *
 * @group DB
 */
class RegistrationServiceTest extends TestCase {
	use DatabaseTransaction;

	/** @var MailService */
	private $mailService;
	/** @var IL10N */
	private $l10n;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var RegistrationMapper */
	private $registrationMapper;
	/** @var IUserManager */
	private $userManager;
	/** @var IConfig */
	private $config;
	/** @var IGroupManager */
	private $groupManager;
	/** @var ISecureRandom */
	private $random;
	/** @var IUserSession  */
	private $usersession;
	/** @var IRequest */
	private $request;
	/** @var ILogger */
	private $logger;
	/** @var ISession */
	private $session;
	/** @var IProvider */
	private $tokenProvider;
	/** @var ICrypto */
	private $crypto;
	/** @var RegistrationService */
	private $service;

	public function setUp(): void {
		parent::setUp();
		$this->mailService = $this->createMock(MailService::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->l10n->expects($this->any())
			->method('t')
			->willReturnCallback(function ($text, $parameters = []) {
				return vsprintf($text, $parameters);
			});
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		#$this->userManager = $this->createMock(IUserManager::class);
		$this->userManager = \OC::$server->getUserManager();
		$this->config = $this->createMock(IConfig::class);
		$this->groupManager = \OC::$server->getGroupManager();
		$this->random = \OC::$server->getSecureRandom();
		$this->usersession = $this->createMock(IUserSession::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->session = $this->createMock(ISession::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->crypto = $this->createMock(ICrypto::class);

		$this->registrationMapper = new RegistrationMapper(
			\OC::$server->getDatabaseConnection(),
			$this->random
		);

		$this->service = new RegistrationService(
			'registration',
			$this->mailService,
			$this->l10n,
			$this->urlGenerator,
			$this->registrationMapper,
			$this->userManager,
			$this->config,
			$this->groupManager,
			$this->random,
			$this->usersession,
			$this->request,
			$this->logger,
			$this->session,
			$this->tokenProvider,
			$this->crypto
		);
	}

	public function dataValidateEmail(): array {
		return [
			['aaaa@example.com', '', 'no'],
			['aaaa@example.com', 'example.com', 'no'],
			['aaaa@example.com', 'eXample.com', 'no'],
			['aaaa@eXample.com', 'example.com', 'no'],
			['aaaa@example.com', 'example.com;example.tld', 'no'],
			['aaaa@example.com', 'example.tld;example.com', 'no'],
			['aaaa@cloud.example.com', '*.example.com', 'no'],
			['aaaa@cloud.example.com', 'cloud.example.*', 'no'],

			['aaaa@example.com', '', 'yes'],
			['aaaa@example.com', 'nextcloud.com', 'yes'],
			['aaaa@example.com', 'nextcloud.com;example.tld', 'yes'],
		];
	}

	/**
	 * @dataProvider dataValidateEmail
	 * @param string $email
	 * @param string $allowedDomains
	 * @param string $blocked
	 * @throws RegistrationException
	 */
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

	public function dataValidateEmailThrows(): array {
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
	 * @dataProvider dataValidateEmailThrows
	 * @param string $email
	 * @param string $allowedDomains
	 * @param string $blocked
	 * @throws RegistrationException
	 */
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
		$reg->setEmail("asd@example.com");
		//$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->will($this->returnCallback([$this, 'settingsCallback1']));


		$form_input_username = 'alice1';
		$resulting_user = $this->service->createAccount($reg, $form_input_username, 'asdf');

		$this->assertInstanceOf(IUser::class, $resulting_user);
		$this->assertEquals($form_input_username, $resulting_user->getUID());
		$this->assertEquals('asd@example.com', $resulting_user->getEmailAddress());
	}

	/**
	 * @depends testCreateAccountWebForm
	 */
	public function testDuplicateUsernameWebForm() {
		$reg = new Registration();
		$reg->setEmail("pppp@example.com");
		//$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('The username you have chosen already exists.');
		$this->service->createAccount($reg, 'alice1', 'asdf');
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
		$reg->setEmail("pppp@example.com");
		$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('The username you have chosen already exists.');
		$this->service->createAccount($reg);
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
		$reg->setEmail("pppp@example.com");
		$reg->setUsername("alice23");
		$reg->setDisplayname("Alice");
		$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$this->expectException(RegistrationException::class);
		$this->expectExceptionMessage('Please provide a valid user name.');
		$this->service->createAccount($reg);
	}

	public function settingsCallback1($app, $key, $default) {
		$map = [
			'registered_user_group' => 'none',
			'admin_approval_required' => 'no',
			'username_policy_regex' => '',
		];

		return $map[$key];
	}
}
