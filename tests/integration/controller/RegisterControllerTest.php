<?php

namespace OCA\Registration\Controller;

use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationService;
use OCA\Registration\Util\CoreBridge;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Defaults;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OC\Authentication\Token\IProvider;
use OC\User\Session as UserSession;
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
 * class RegistrationControllerTest
 *
 * @group DB
 */
class RegistrationControllerTest extends TestCase {
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
		$this->usersession = $this->createMock(UserSession::class);
		$this->request = $this->createMock(IRequest::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->session = $this->createMock(ISession::class);
		$this->tokenProvider = $this->createMock(IProvider::class);
		$this->crypto = $this->createMock(ICrypto::class);

		$this->registrationMapper = new RegistrationMapper(
			\OC::$server->getDatabaseConnection(),
			$this->random
		);

		$this->registrationService = new RegistrationService(
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

		$this->controller = new RegisterController(
			'registration',
			$this->request,
			$this->l10n,
			$this->urlGenerator,
			$this->registrationService,
			$this->mailService
		);
	}

	public function testValidateEmailNormal() {
		$email = 'aaaa@example.com';

		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->with("registration", 'allowed_domains', '')
			->willReturn('');
		$this->mailService->expects($this->once())
			->method('sendTokenByMail')
			->willReturn(true);

		$this->assertEquals($this->registrationService->validateEmail($email), true);

		$ret = $this->controller->validateEmail($email);

		$expected = new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');


		$this->assertEquals($expected, $ret, print_r($ret, true));
	}

	public function testRedirectUrl() {
		$email = 'aaaa@example.com';
		$redirect_url = 'https://example.com';

		$config_map = array(
			['registration', 'allowed_domains', '', ''],
			['registration', 'registered_user_group', 'none', 'none'],
		);
		$this->config->expects($this->atLeastOnce())
			->method('getAppValue')
			->will($this->returnValueMap($config_map));
		$this->mailService->expects($this->once())
			->method('sendTokenByMail')
			->willReturn(true);
		$this->request->expects($this->atLeastOnce())
			->method('getParam')
			->withConsecutive(['username'], ['password'])
			->willReturnOnConsecutiveCalls('sampleuser', 'secret');

		$this->assertEquals($this->registrationService->validateEmail($email), true);

		$ret = $this->controller->validateEmail($email, $redirect_url);

		$expected = new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');


		$this->assertEquals($expected, $ret, print_r($ret, true));

		$reg = $this->registrationService->validateEmail($email);
		$this->assertInstanceOf(Registration::class, $reg);
		$this->assertEquals($redirect_url, $reg->getRedirectUrl());

		$ret = $this->controller->createAccount($reg->getToken());
		$expected = new RedirectResponse($redirect_url);
		$this->assertEquals($expected, $ret, print_r($ret, true));
	}
}
