<?php

declare(strict_types=1);

/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @author Julius Härtl <jus@bitgrid.net>
 * @author 2020 Joas Schilling <coding@schilljs.com>
 * @author 2022 Carl Schwan <carl@carlschwan.eu>
 * @author Thomas Citharel <nextcloud@tcit.fr>
 * @copyright Pellaeon Lin 2014
 */

namespace OCA\Registration\Controller;

use Exception;
use OCA\Registration\AppInfo\Application;
use OCA\Registration\Db\Registration;
use OCA\Registration\Events\PassedFormEvent;
use OCA\Registration\Events\ShowFormEvent;
use OCA\Registration\Events\ValidateFormEvent;
use OCA\Registration\Service\LoginFlowService;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\StandaloneTemplateResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\HintException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

class RegisterController extends Controller {
	private IL10N $l10n;
	private IURLGenerator $urlGenerator;
	private IConfig $config;
	private RegistrationService $registrationService;
	private MailService $mailService;
	private LoginFlowService $loginFlowService;
	private IEventDispatcher $eventDispatcher;
	private IInitialState $initialState;

	public function __construct(
		string $appName,
		IRequest $request,
		IL10N $l10n,
		IURLGenerator $urlGenerator,
		IConfig $config,
		RegistrationService $registrationService,
		LoginFlowService $loginFlowService,
		MailService $mailService,
		IEventDispatcher $eventDispatcher,
		IInitialState $initialState
	) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->config = $config;
		$this->registrationService = $registrationService;
		$this->loginFlowService = $loginFlowService;
		$this->mailService = $mailService;
		$this->eventDispatcher = $eventDispatcher;
		$this->initialState = $initialState;
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function showEmailForm(string $email = '', string $message = ''): TemplateResponse {
		$emailHint = '';
		$domainList = $this->registrationService->getAllowedDomains();
		if (!empty($domainList) && $this->config->getAppValue(Application::APP_ID, 'show_domains', 'no') === 'yes') {
			$domainList = implode(', ', $domainList);
			if ($this->config->getAppValue(Application::APP_ID, 'domains_is_blocklist', 'no') === 'yes') {
				$emailHint = $this->l10n->t(
					'Registration is not allowed with the following domains: %s',
					[$domainList]
				);
			} else {
				$emailHint = $this->l10n->t(
					'Registration is only allowed with the following domains: %s',
					[$domainList]
				);
			}
		}

		$this->eventDispatcher->dispatchTyped(new ShowFormEvent(ShowFormEvent::STEP_EMAIL));

		$this->initialState->provideInitialState('email', $email);
		$this->initialState->provideInitialState('message', $message ?: $emailHint);
		$this->initialState->provideInitialState('emailIsOptional', $this->config->getAppValue($this->appName, 'email_is_optional', 'no') === 'yes');
		$this->initialState->provideInitialState('disableEmailVerification', $this->config->getAppValue($this->appName, 'disable_email_verification', 'no') === 'yes');
		$this->initialState->provideInitialState('isLoginFlow', $this->loginFlowService->isUsingLoginFlow());
		$this->initialState->provideInitialState('loginFormLink', $this->urlGenerator->linkToRoute('core.login.showLoginForm'));
		return new TemplateResponse('registration', 'form/email', [], 'guest');
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=300)
	 */
	public function submitEmailForm(string $email): Response {
		$validateFormEvent = new ValidateFormEvent(ValidateFormEvent::STEP_EMAIL);
		$this->eventDispatcher->dispatchTyped($validateFormEvent);

		if (!empty($validateFormEvent->getErrors())) {
			return $this->showEmailForm($email, implode(' ', $validateFormEvent->getErrors()));
		}

		try {
			// Registration already in progress, update token and continue with verification
			$registration = $this->registrationService->getRegistrationForEmail($email);
			$this->registrationService->generateNewToken($registration);
		} catch (DoesNotExistException $e) {
			// No registration in progress
			try {
				$email = trim($email);
				$this->registrationService->validateEmail($email);
			} catch (RegistrationException $e) {
				return $this->showEmailForm($email, $e->getMessage());
			}

			$registration = $this->registrationService->createRegistration($email);
		}

		if ($this->config->getAppValue($this->appName, 'disable_email_verification', 'no') === 'yes') {
			$this->eventDispatcher->dispatchTyped(new PassedFormEvent(PassedFormEvent::STEP_EMAIL, $registration->getClientSecret()));

			return new RedirectResponse(
				$this->urlGenerator->linkToRoute(
					'registration.register.showUserForm',
					[
						'secret' => $registration->getClientSecret(),
						'token' => $registration->getToken()
					]
				)
			);
		}

		try {
			$this->mailService->sendTokenByMail($registration);
		} catch (RegistrationException $e) {
			return $this->showEmailForm($email, $e->getMessage());
		} catch (\Exception $e) {
			return $this->showEmailForm($email, $this->l10n->t('A problem occurred sending email, please contact your administrator.'));
		}

		$this->eventDispatcher->dispatchTyped(new PassedFormEvent(PassedFormEvent::STEP_EMAIL, $registration->getClientSecret()));

		return new RedirectResponse(
			$this->urlGenerator->linkToRoute(
				'registration.register.showVerificationForm',
				['secret' => $registration->getClientSecret()]
			)
		);
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function showVerificationForm(string $secret, string $message = ''): TemplateResponse {
		try {
			$this->registrationService->getRegistrationForSecret($secret);
		} catch (DoesNotExistException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		$this->eventDispatcher->dispatchTyped(new ShowFormEvent(ShowFormEvent::STEP_VERIFICATION, $secret));
		$this->initialState->provideInitialState('message', $message);
		$this->initialState->provideInitialState('loginFormLink', $this->urlGenerator->linkToRoute('core.login.showLoginForm'));

		return new TemplateResponse('registration', 'form/verification', [], 'guest');
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=300)
	 *
	 * @param string $secret
	 * @param string $token
	 * @return Response
	 */
	public function submitVerificationForm(string $secret, string $token): Response {
		try {
			$registration = $this->registrationService->getRegistrationForSecret($secret);

			if ($registration->getToken() !== $token) {
				return $this->showVerificationForm(
					$secret,
					$this->l10n->t('The entered verification code is wrong')
				);
			}
		} catch (DoesNotExistException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		$validateFormEvent = new ValidateFormEvent(ValidateFormEvent::STEP_VERIFICATION, $secret);
		$this->eventDispatcher->dispatchTyped($validateFormEvent);

		if (!empty($validateFormEvent->getErrors())) {
			return $this->showVerificationForm($secret, implode(' ', $validateFormEvent->getErrors()));
		}

		$this->eventDispatcher->dispatchTyped(new PassedFormEvent(PassedFormEvent::STEP_VERIFICATION, $secret));

		return new RedirectResponse(
			$this->urlGenerator->linkToRoute(
				'registration.register.showUserForm',
				[
					'secret' => $secret,
					'token' => $token,
				]
			)
		);
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function showUserForm(string $secret, string $token, string $loginname = '', string $fullname = '', string $phone = '', string $password = '', string $message = ''): TemplateResponse {
		try {
			$registration = $this->validateSecretAndToken($secret, $token);
		} catch (RegistrationException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		$additional_hint = $this->config->getAppValue('registration', 'additional_hint');

		$this->eventDispatcher->dispatchTyped(new ShowFormEvent(ShowFormEvent::STEP_USER, $secret));

		$this->initialState->provideInitialState('email', $registration->getEmail());
		$this->initialState->provideInitialState('emailIsLogin', $this->config->getAppValue('registration', 'email_is_login', 'no') === 'yes');
		$this->initialState->provideInitialState('emailIsOptional', $this->config->getAppValue('registration', 'email_is_optional', 'no') === 'yes');
		$this->initialState->provideInitialState('loginname', $loginname);
		$this->initialState->provideInitialState('fullname', $fullname);
		$this->initialState->provideInitialState('showFullname', $this->config->getAppValue('registration', 'show_fullname', 'no') === 'yes');
		$this->initialState->provideInitialState('enforceFullname', $this->config->getAppValue('registration', 'enforce_fullname', 'no') === 'yes');
		$this->initialState->provideInitialState('phone', $phone);
		$this->initialState->provideInitialState('showPhone', $this->config->getAppValue('registration', 'show_phone', 'no') === 'yes');
		$this->initialState->provideInitialState('enforcePhone', $this->config->getAppValue('registration', 'enforce_phone', 'no') === 'yes');
		$this->initialState->provideInitialState('message', $message);
		$this->initialState->provideInitialState('password', $password);
		$this->initialState->provideInitialState('additionalHint', $additional_hint);
		$this->initialState->provideInitialState('loginFormLink', $this->urlGenerator->linkToRoute('core.login.showLoginForm'));

		$response = new TemplateResponse('registration', 'form/user', [], 'guest');

		if ($this->loginFlowService->isUsingLoginFlow(1)) {
			$csp = new ContentSecurityPolicy();
			$csp->addAllowedFormActionDomain('nc://*');
			$response->setContentSecurityPolicy($csp);
		}

		return $response;
	}

	/**
	 * @PublicPage
	 * @UseSession
	 * @AnonRateThrottle(limit=5, period=300)
	 *
	 * @return RedirectResponse|TemplateResponse
	 */
	public function submitUserForm(string $secret, string $token, string $loginname, string $fullname, string $phone, string $password): Response {
		try {
			$registration = $this->validateSecretAndToken($secret, $token);
		} catch (RegistrationException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		if ($this->config->getAppValue('registration', 'email_is_login', 'no') === 'yes') {
			$loginname = $registration->getEmail();
		}

		$validateFormEvent = new ValidateFormEvent(ValidateFormEvent::STEP_USER, $secret);
		$this->eventDispatcher->dispatchTyped($validateFormEvent);

		if (!empty($validateFormEvent->getErrors())) {
			return $this->showUserForm($secret, $token, $loginname, $fullname, $phone, $password, implode(' ', $validateFormEvent->getErrors()));
		}

		try {
			$user = $this->registrationService->createAccount($registration, $loginname, $fullname, $phone, $password);
		} catch (HintException $exception) {
			return $this->showUserForm($secret, $token, $loginname, $fullname, $phone, $password, $exception->getHint());
		} catch (Exception $exception) {
			return $this->showUserForm($secret, $token, $loginname, $fullname, $phone, $password, $exception->getMessage());
		}

		// Delete registration
		$this->registrationService->deleteRegistration($registration);

		$this->eventDispatcher->dispatchTyped(new PassedFormEvent(PassedFormEvent::STEP_USER, $secret, $user));

		if ($user->isEnabled()) {
			$this->registrationService->loginUser($user->getUID(), $user->getUID(), $password);

			if ($this->loginFlowService->isUsingLoginFlow(2)) {
				$response = $this->loginFlowService->tryLoginFlowV2($user);
				if ($response instanceof Response) {
					return $response;
				}
			}

			if ($this->loginFlowService->isUsingLoginFlow(1)) {
				$response = $this->loginFlowService->tryLoginFlowV1();
				if ($response instanceof Response && $response->getStatus() === Http::STATUS_SEE_OTHER) {
					return $response;
				}
			}

			return new RedirectResponse($this->urlGenerator->linkToDefaultPageUrl());
		}

		Util::addStyle('registration', 'style');

		// warn the user their account needs admin validation
		return new StandaloneTemplateResponse('registration', 'approval-required', [], 'guest');
	}

	/**
	 * @throws RegistrationException
	 */
	protected function validateSecretAndToken(string $secret, string $token): Registration {
		try {
			$registration = $this->registrationService->getRegistrationForSecret($secret);
		} catch (DoesNotExistException $e) {
			throw new RegistrationException('Invalid secret');
		}

		if ($registration->getToken() !== $token) {
			throw new RegistrationException('Invalid token');
		}

		return $registration;
	}

	protected function validateSecretAndTokenErrorPage(): TemplateResponse {
		return new TemplateResponse('core', 'error', [
			'errors' => [
				['error' => $this->l10n->t('The verification failed.')],
			],
		], 'error');
	}
}
