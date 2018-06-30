<?php

namespace OCA\Registration\Service;

use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationService;
use OCA\Registration\Util\CoreBridge;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Defaults;
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
	/** @var \OCP\Defaults */
	private $defaults;
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

	public function setUp () {
		parent::setUp();
		$this->mailService = $this->createMock(MailService::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		#$this->userManager = $this->createMock(IUserManager::class);
		$this->userManager = \OC::$server->getUserManager();
		$this->config = $this->createMock(IConfig::class);
		$this->groupManager = \OC::$server->getGroupManager();
		$this->defaults = $this->createMock(Defaults::class);
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
			$this->defaults,
			$this->random,
			$this->usersession,
			$this->request,
			$this->logger,
			$this->session,
			$this->tokenProvider,
			$this->crypto
		);
	}

	public function testValidateNewEmail() {
		$email = 'aaaa@example.com';

		$this->config->expects($this->once())
			->method('getAppValue')
			->with("registration", 'allowed_domains', '')
			->willReturn('');

		$ret = $this->service->validateEmail($email);

		//$this->assertInstanceOf(Registration::class, $ret);
		$this->assertTrue($ret);
	}

	public function testValidateNewEmailWithinAllowedDomain() {
		$email = 'aaaa@example.com';

		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->with("registration", 'allowed_domains', '')
			->willReturn('example.com');

		$ret = $this->service->validateEmail($email);
		$this->assertTrue($ret, print_r($ret, true));
	}
	/**
	 * @depends testValidateNewEmailWithinAllowedDomain
	 * @expectedException OCA\Registration\Service\RegistrationException
	 */
	public function testValidateNewEmailNotWithinAllowedDomain() {
		$email2 = 'bbbb@gmail.com';

		$this->service->validateEmail($email2);
	}

	public function testValidateNewEmailWithinMultipleAllowedDomain() {
		$email = 'aaaa@example.com';
		$email2 = 'bbbb@gmail.com';

		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->with("registration", 'allowed_domains', '')
			->willReturn('example.com;gmail.com');

		$this->assertTrue($this->service->validateEmail($email));
		$this->assertTrue($this->service->validateEmail($email2));
	}
	/**
	 * @depends testValidateNewEmailWithinMultipleAllowedDomain
	 * @expectedException OCA\Registration\Service\RegistrationException
	 */
	public function testValidateNewEmailNotWithinMultipleAllowedDomain() {
		$email2 = 'cccc@yahoo.com';

		$this->service->validateEmail($email2);
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
		$ret = $this->service->validateEmail($email);

		$this->assertInstanceOf(Registration::class, $ret);
		$this->assertEquals($email, $ret->getEmail());
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
			->will($this->returnCallback(array($this, 'settingsCallback1')));


		$form_input_username = 'alice1';
		$resulting_user = $this->service->createAccount($reg, $form_input_username, 'asdf');

		$this->assertInstanceOf(IUser::class, $resulting_user);
		$this->assertEquals($form_input_username, $resulting_user->getUID());
		$this->assertEquals('asd@example.com', $resulting_user->getEmailAddress());
	}

	/**
	 * @depends testCreateAccountWebForm
	 * @expectedException OCA\Registration\Service\RegistrationException
	 */
	public function testDuplicateUsernameWebForm() {
		$reg = new Registration();
		$reg->setEmail("pppp@example.com");
		//$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$resulting_user = $this->service->createAccount($reg, 'alice1', 'asdf');
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
	 * @expectedException OCA\Registration\Service\RegistrationException
	 */
	public function testDuplicateUsernameApi() {
		$reg = new Registration();
		$reg->setEmail("pppp@example.com");
		$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		$resulting_user = $this->service->createAccount($reg);
	}

	public function settingsCallback1($app, $key, $default) {
		$map = [
			'registered_user_group' => 'none',
			'admin_approval_required' => 'no'
		];

		return $map[$key];
	}
}
