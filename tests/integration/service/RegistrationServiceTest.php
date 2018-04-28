<?php

namespace OCA\Registration\Controller;

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
//use \Test\TestCase;

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
		$this->registrationMapper = $this->createMock(RegistrationMapper::class);
		#$this->userManager = $this->createMock(IUserManager::class);
		$this->userManager = \OC::$server->getUserManager();
		$this->config = $this->createMock(IConfig::class);
		$this->groupManager = \OC::$server->getGroupManager();
		$this->defaults = $this->createMock(Defaults::class);
		$this->random = $this->createMock(ISecureRandom::class);
		$this->usersession = $this->createMock(IUserSession::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->session = $this->createMock(ISession::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->crypto = $this->createMock(ICrypto::class);

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

	public function testCreateAccountWebForm() {
		$reg = new Registration();
		$reg->setEmail("asd@example.com");
		//$reg->setUsername("alice1");
		$reg->setDisplayname("Alice");
		//$reg->setPassword("asdf");
		$reg->setEmailConfirmed(true);

		/*
		$map = [
			["registration", 'registered_user_group', 'none'],
			["registration", 'admin_approval_required', 'no']
		];
		 */

		$this->config->expects($this->at(0))
			->method('getAppValue')
			->with("registration", 'registered_user_group', 'none')
			->willReturn('none');
		$this->config->expects($this->at(1))
			->method('getAppValue')
			->with("registration", 'admin_approval_required', 'no')
			->willReturn('no');


		//$regroup = $this->config->getAppValue("registration", 'registered_user_group', 'none');
		//print_r($regroup);
		//$this->assertEquals($this->config->getAppValue('registration', 'registered_user_group', 'none'), "none");

		$form_input_username = 'alice1';
		$resulting_user = $this->service->createAccount($reg, $form_input_username, 'asdf');

		$this->assertInstanceOf(IUser::class, $resulting_user);
		$this->assertEquals($form_input_username, $resulting_user->getUID());
		$this->assertEquals('asd@example.com', $resulting_user->getEmailAddress());


	}
}
