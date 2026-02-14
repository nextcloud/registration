<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Tests\Unit\Controller;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Registration\Controller\RegisterController;
use OCA\Registration\Db\Registration;
use OCA\Registration\Service\LoginFlowService;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\StandaloneTemplateResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

class RegisterControllerTest extends TestCase {

	/** @var IRequest */
	private $request;
	/** @var IL10N|MockObject */
	private $l10n;
	/** @var IURLGenerator|MockObject */
	private $urlGenerator;
	/** @var IConfig|MockObject */
	private $config;
	/** @var RegistrationService|MockObject */
	private $registrationService;
	/** @var LoginFlowService|MockObject */
	private $loginFlowService;
	/** @var MailService|MockObject */
	private $mailService;
	/** @var IEventDispatcher|MockObject */
	private $eventDispatcher;
	/** @var IInitialState|MockObject */
	private $initialState;

	public function setUp(): void {
		parent::setUp();
		$this->request = $this->createMock(IRequest::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->config = $this->createMock(IConfig::class);
		$this->registrationService = $this->createMock(RegistrationService::class);
		$this->loginFlowService = $this->createMock(LoginFlowService::class);
		$this->mailService = $this->createMock(MailService::class);
		$this->eventDispatcher = $this->createMock(IEventDispatcher::class);
		$this->initialState = $this->createMock(IInitialState::class);

		$this->l10n->expects($this->any())
			->method('t')
			->willReturnCallback(function ($text, $parameters = []): string {
				return vsprintf($text, $parameters);
			});
	}

	/**
	 * @param string[] $methods
	 * @return RegisterController|MockObject
	 */
	protected function getController(array $methods = []) {
		if (empty($methods)) {
			return new RegisterController(
				'registration',
				$this->request,
				$this->l10n,
				$this->urlGenerator,
				$this->config,
				$this->registrationService,
				$this->loginFlowService,
				$this->mailService,
				$this->eventDispatcher,
				$this->initialState
			);
		}

		return $this->getMockBuilder(RegisterController::class)
			->onlyMethods($methods)
			->setConstructorArgs([
				'registration',
				$this->request,
				$this->l10n,
				$this->urlGenerator,
				$this->config,
				$this->registrationService,
				$this->loginFlowService,
				$this->mailService,
				$this->eventDispatcher,
				$this->initialState,
			])
			->getMock();
	}

	public static function dataShowEmailForm(): array {
		return [
			['', ''],
			['test@example.tld', 'Registration is only allowed for the following domains: nextcloud.com'],
		];
	}

	#[DataProvider('dataShowEmailForm')]
	public function testShowEmailForm(string $email, string $message): void {
		$this->registrationService->method('getAllowedDomains')
			->willReturn([]);

		$controller = $this->getController();
		$response = $controller->showEmailForm($email, $message);
		$disable_email_verification = $this->config->getAppValue('registration', 'disable_email_verification', 'no');
		$email_is_optional = $this->config->getAppValue('registration', 'email_is_optional', 'no');

		$this->loginFlowService->method('isUsingLoginFlow')
			->willReturn(false);

		self::assertSame(TemplateResponse::RENDER_AS_GUEST, $response->getRenderAs());
		self::assertSame('form/email', $response->getTemplateName());

		self::assertSame([], $response->getParams());

		$expectedCalls = [
			['email', $email],
			['message', $message],
			['email_is_optional', $email_is_optional],
			['disable_email_verification', $disable_email_verification],
			['is_login_flow', false]
		];
		$i = 0;

		$this->initialState->method('provideInitialState')
			->willReturnCallback(function () use ($expectedCalls, &$i) {
				$this->assertEquals($expectedCalls[$i], func_get_args());
				$i++;
			});
	}

	public function testSubmitEmailForm(): void {
		$email = 'nextcloud@example.tld';

		$this->registrationService
			->method('getRegistrationForEmail')
			->with($email)
			->willThrowException(new DoesNotExistException($email));

		$registration = Registration::fromParams([
			'clientSecret' => 'clientSecret',
		]);

		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->with($email);
		$this->registrationService
			->expects($this->once())
			->method('createRegistration')
			->with($email)
			->willReturn($registration);

		$this->mailService
			->expects($this->once())
			->method('sendTokenByMail')
			->with($registration);

		$this->urlGenerator
			->method('linkToRoute')
			->willReturnCallback(function () {
				return json_encode(func_get_args());
			});

		$controller = $this->getController();
		$response = $controller->submitEmailForm($email);

		self::assertInstanceOf(RedirectResponse::class, $response);
		/** @var RedirectResponse $response */
		self::assertSame('["registration.register.showVerificationForm",{"secret":"clientSecret"}]', $response->getRedirectURL());
	}

	public function testSubmitEmailFormInvalidEmail(): void {
		$email = 'nextcloud@example.tld';

		$this->registrationService
			->method('getRegistrationForEmail')
			->with($email)
			->willThrowException(new DoesNotExistException($email));

		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->with($email)
			->willThrowException(new RegistrationException('Invalid email'));
		$this->registrationService
			->expects($this->never())
			->method('createRegistration');

		$controller = $this->getController([
			'showEmailForm',
		]);

		$response = $this->createMock(TemplateResponse::class);
		$controller->expects($this->once())
			->method('showEmailForm')
			->with($email, 'Invalid email')
			->willReturn($response);

		self::assertSame($response, $controller->submitEmailForm($email));
	}

	public function testSubmitEmailFormErrorSendingEmail(): void {
		$email = 'nextcloud@example.tld';

		$this->registrationService
			->method('getRegistrationForEmail')
			->with($email)
			->willThrowException(new DoesNotExistException($email));

		$registration = Registration::fromParams([
			'clientSecret' => 'clientSecret',
		]);

		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->with($email);
		$this->registrationService
			->expects($this->once())
			->method('createRegistration')
			->with($email)
			->willReturn($registration);

		$this->mailService
			->expects($this->once())
			->method('sendTokenByMail')
			->with($registration)
			->willThrowException(new RegistrationException('Error sending email'));

		$controller = $this->getController([
			'showEmailForm',
		]);

		$response = $this->createMock(TemplateResponse::class);
		$controller->expects($this->once())
			->method('showEmailForm')
			->with($email, 'Error sending email')
			->willReturn($response);

		self::assertSame($response, $controller->submitEmailForm($email));
	}

	public function testSubmitEmailFormResendPendingRequest(): void {
		$email = 'nextcloud@example.tld';

		$registration = Registration::fromParams([
			'clientSecret' => 'clientSecret',
		]);

		$this->registrationService
			->method('getRegistrationForEmail')
			->with($email)
			->willReturn($registration);

		$this->registrationService
			->expects($this->once())
			->method('generateNewToken')
			->with($registration);

		$this->mailService
			->expects($this->once())
			->method('sendTokenByMail')
			->with($registration);

		$this->urlGenerator
			->method('linkToRoute')
			->willReturnCallback(function () {
				return json_encode(func_get_args());
			});

		$controller = $this->getController();
		$response = $controller->submitEmailForm($email);
		self::assertInstanceOf(RedirectResponse::class, $response);
		/** @var RedirectResponse $response */
		self::assertSame('["registration.register.showVerificationForm",{"secret":"clientSecret"}]', $response->getRedirectURL());
	}

	public static function dataShowVerificationForm(): array {
		return [
			[''],
			['The entered verification code is wrong'],
		];
	}

	#[DataProvider('dataShowVerificationForm')]
	public function testShowVerificationForm(string $message): void {
		$secret = '123456789';

		$this->registrationService
			->expects($this->once())
			->method('getRegistrationForSecret')
			->with($secret);

		$controller = $this->getController();
		$response = $controller->showVerificationForm($secret, $message);

		self::assertSame(TemplateResponse::RENDER_AS_GUEST, $response->getRenderAs());
		self::assertSame('form/verification', $response->getTemplateName());
		$this->initialState->method('provideInitialState')
			->with('message', $message);

		self::assertSame([], $response->getParams());
	}

	public function testShowVerificationFormInvalidSecret(): void {
		$secret = '123456789';
		$message = '';

		$this->registrationService
			->expects($this->once())
			->method('getRegistrationForSecret')
			->with($secret)
			->willThrowException(new DoesNotExistException('Not found'));

		$response = $this->createMock(TemplateResponse::class);
		$controller = $this->getController([
			'validateSecretAndTokenErrorPage'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndTokenErrorPage')
			->willReturn($response);

		self::assertSame($response, $controller->showVerificationForm($secret, $message));
	}

	public function testSubmitVerificationForm(): void {
		$secret = '123456789';
		$token = 'abcdefghi';

		$registration = Registration::fromParams([
			'clientSecret' => $secret,
			'token' => $token,
		]);

		$this->registrationService
			->expects($this->once())
			->method('getRegistrationForSecret')
			->with($secret)
			->willReturn($registration);

		$this->urlGenerator
			->method('linkToRoute')
			->willReturnCallback(function () {
				return json_encode(func_get_args());
			});

		$controller = $this->getController();
		$response = $controller->submitVerificationForm($secret, $token);
		self::assertInstanceOf(RedirectResponse::class, $response);
		/** @var RedirectResponse $response */
		self::assertSame('["registration.register.showUserForm",{"secret":"123456789","token":"abcdefghi"}]', $response->getRedirectURL());
	}

	public function testSubmitVerificationFormInvalidToken(): void {
		$secret = '123456789';
		$token = 'abcdefghi';

		$registration = Registration::fromParams([
			'clientSecret' => $secret,
			'token' => 'zyxwvu',
		]);

		$this->registrationService
			->expects($this->once())
			->method('getRegistrationForSecret')
			->with($secret)
			->willReturn($registration);

		$response = $this->createMock(TemplateResponse::class);
		$controller = $this->getController([
			'showVerificationForm',
		]);
		$controller->expects($this->once())
			->method('showVerificationForm')
			->with($secret, 'The entered verification code is wrong')
			->willReturn($response);

		self::assertSame($response, $controller->submitVerificationForm($secret, $token));
	}

	public function testSubmitVerificationFormInvalidSecret(): void {
		$secret = '123456789';
		$token = 'abcdefghi';

		$registration = Registration::fromParams([
			'clientSecret' => $secret,
			'token' => $token,
		]);

		$this->registrationService
			->expects($this->once())
			->method('getRegistrationForSecret')
			->with($secret)
			->willThrowException(new DoesNotExistException('Invalid secret'));

		$response = $this->createMock(TemplateResponse::class);
		$controller = $this->getController([
			'validateSecretAndTokenErrorPage',
		]);
		$controller->expects($this->once())
			->method('validateSecretAndTokenErrorPage')
			->willReturn($response);

		self::assertSame($response, $controller->submitVerificationForm($secret, $token));
	}

	public static function dataShowUserForm(): array {
		return [
			['', ''],
			['tester', ''],
			['', 'Unable to create user, there are problems with the user backend.'],
		];
	}

	#[DataProvider('dataShowUserForm')]
	public function testShowUserForm(string $username, string $message): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$email = 'nextcloud@example.tld';
		$fullname = 'Full name';
		$phone = '0123 / 456789';
		$password = '123456';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken'
		]);

		$this->config->method('getAppValue')
			->willReturnMap([
				['registration', 'show_fullname', 'no', 'yes'],
				['registration', 'show_phone', 'no', 'yes'],
			]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$response = $controller->showUserForm($secret, $token, $username, $fullname, $phone, $password, $message);

		self::assertSame(TemplateResponse::RENDER_AS_GUEST, $response->getRenderAs());
		self::assertSame('form/user', $response->getTemplateName());

		self::assertSame([], $response->getParams());

		$expectedCalls = [
			['email', $email],
			['email_is_login', false],
			['email_is_optional', false],
			['loginname', $username],
			['fullname', $fullname],
			['show_fullname', true],
			['enforce_fullname', false],
			['phone', $phone],
			['show_phone', true],
			['enforce_phone', false],
			['message', $message],
			['password', $password],
			['additional_hint', null],
		];
		$i = 0;

		$this->initialState->method('provideInitialState')
			->willReturnCallback(function () use ($expectedCalls, &$i) {
				$this->assertEquals($expectedCalls[$i], func_get_args());
				$i++;
			});
	}

	public function testShowUserFormInvalidSecretAndToken(): void {
		$secret = '123456789';
		$token = 'abcdefghi';

		$controller = $this->getController([
			'validateSecretAndToken',
			'validateSecretAndTokenErrorPage',
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willThrowException(new RegistrationException('Invalid secret or token'));

		$response = $this->createMock(TemplateResponse::class);
		$controller->expects($this->once())
			->method('validateSecretAndTokenErrorPage')
			->willReturn($response);

		self::assertSame($response, $controller->showUserForm($secret, $token));
	}

	public function testSubmitUserFormInvalidSecretAndToken(): void {
		$secret = '123456789';
		$token = 'abcdefghi';

		$controller = $this->getController([
			'validateSecretAndToken',
			'validateSecretAndTokenErrorPage',
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willThrowException(new RegistrationException('Invalid secret or token'));

		$response = $this->createMock(TemplateResponse::class);
		$controller->expects($this->once())
			->method('validateSecretAndTokenErrorPage')
			->willReturn($response);

		self::assertSame($response, $controller->submitUserForm($secret, $token, '', '', '', ''));
	}

	public function testSubmitUserFormCreateAccountException(): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$username = 'user';
		$password = 'password';
		$fullname = 'Full name';
		$phone = '0123 / 456789';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken',
			'showUserForm'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$response = $this->createMock(TemplateResponse::class);
		$controller->expects($this->once())
			->method('showUserForm')
			->willReturn($response);

		$this->registrationService->expects($this->once())
			->method('createAccount')
			->with($registration, $username, $fullname, $phone, $password)
			->willThrowException(new RegistrationException('Invalid account data'));

		self::assertSame($response, $controller->submitUserForm($secret, $token, $username, $fullname, $phone, $password));
	}

	public function testSubmitUserFormRequiresAdminApproval(): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$username = 'user';
		$password = 'password';
		$fullname = 'Full name';
		$phone = '0123 / 456789';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken',
			'showUserForm'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$user = $this->createMock(IUser::class);
		$user->method('isEnabled')
			->willReturn(false);

		$this->registrationService->expects($this->once())
			->method('createAccount')
			->with($registration, $username, $fullname, $phone, $password)
			->willReturn($user);

		$this->registrationService->expects($this->once())
			->method('deleteRegistration')
			->with($registration);

		$response = $controller->submitUserForm($secret, $token, $username, $fullname, $phone, $password);

		self::assertInstanceOf(StandaloneTemplateResponse::class, $response);
		self::assertSame(TemplateResponse::RENDER_AS_GUEST, $response->getRenderAs());
		self::assertSame('approval-required', $response->getTemplateName());
	}

	public function testSubmitUserFormSuccessful(): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$username = 'user';
		$password = 'password';
		$fullname = 'Full name';
		$phone = '0123 / 456789';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken',
			'showUserForm'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$user = $this->createMock(IUser::class);
		$user->method('isEnabled')
			->willReturn(true);
		$user->method('getUID')
			->willReturn($username);

		$this->registrationService->expects($this->once())
			->method('createAccount')
			->with($registration, $username, $fullname, $phone, $password)
			->willReturn($user);

		$this->registrationService->expects($this->once())
			->method('deleteRegistration')
			->with($registration);

		$this->registrationService->expects($this->once())
			->method('loginUser')
			->with($username, $username, $password);

		$response = $controller->submitUserForm($secret, $token, $username, $fullname, $phone, $password);

		self::assertInstanceOf(RedirectResponse::class, $response);
		self::assertSame('', $response->getRedirectURL());
	}

	public function testSubmitUserFormSuccessfulLoginFlow2(): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$username = 'user';
		$password = 'password';
		$fullname = 'Full name';
		$phone = '0123 / 456789';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken',
			'showUserForm'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$user = $this->createMock(IUser::class);
		$user->method('isEnabled')
			->willReturn(true);
		$user->method('getUID')
			->willReturn($username);

		$this->registrationService->expects($this->once())
			->method('createAccount')
			->with($registration, $username, $fullname, $phone, $password)
			->willReturn($user);

		$this->registrationService->expects($this->once())
			->method('deleteRegistration')
			->with($registration);

		$this->registrationService->expects($this->once())
			->method('loginUser')
			->with($username, $username, $password);

		$this->loginFlowService->method('isUsingLoginFlow')
			->with(2)
			->willReturn(true);

		$response = $this->createMock(StandaloneTemplateResponse::class);
		$this->loginFlowService->method('tryLoginFlowV2')
			->with($user)
			->willReturn($response);

		self::assertSame($response, $controller->submitUserForm($secret, $token, $username, $fullname, $phone, $password));
	}

	public function testSubmitUserFormSuccessfulLoginFlow1(): void {
		$secret = '123456789';
		$token = 'abcdefghi';
		$username = 'user';
		$password = 'password';
		$fullname = 'Full name';
		$phone = '0123 / 456789';

		$registration = Registration::fromParams([
			'email' => 'nextcloud@example.tld',
		]);

		$controller = $this->getController([
			'validateSecretAndToken',
			'showUserForm'
		]);

		$controller->expects($this->once())
			->method('validateSecretAndToken')
			->willReturn($registration);

		$user = $this->createMock(IUser::class);
		$user->method('isEnabled')
			->willReturn(true);
		$user->method('getUID')
			->willReturn($username);

		$this->registrationService->expects($this->once())
			->method('createAccount')
			->with($registration, $username, $fullname, $phone, $password)
			->willReturn($user);

		$this->registrationService->expects($this->once())
			->method('deleteRegistration')
			->with($registration);

		$this->registrationService->expects($this->once())
			->method('loginUser')
			->with($username, $username, $password);

		$this->loginFlowService->method('isUsingLoginFlow')
			->willReturnMap([
				[2, false],
				[1, true]
			]);

		$response = $this->createMock(RedirectResponse::class);
		$response->method('getStatus')
			->willReturn(Http::STATUS_SEE_OTHER);
		$this->loginFlowService->method('tryLoginFlowV1')
			->willReturn($response);

		self::assertSame($response, $controller->submitUserForm($secret, $token, $username, $fullname, $phone, $password));
	}
}
