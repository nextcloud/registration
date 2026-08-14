<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2015 Johannes Starosta <j.starosta@tu-braunschweig.de>
 * SPDX-FileCopyrightText: 2014 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Controller;

use Exception;
use OCA\Registration\Db\Invitation;
use OCA\Registration\Db\Registration;
use OCA\Registration\Events\PassedFormEvent;
use OCA\Registration\Events\ShowFormEvent;
use OCA\Registration\Events\ValidateFormEvent;
use OCA\Registration\Service\InvitationService;
use OCA\Registration\Service\LoginFlowService;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AnonRateLimit;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\StandaloneTemplateResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\HintException;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

class RegisterController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private IL10N $l10n,
		private IURLGenerator $urlGenerator,
		private IAppConfig $config,
		private RegistrationService $registrationService,
		private LoginFlowService $loginFlowService,
		private MailService $mailService,
		private InvitationService $invitationService,
		private IEventDispatcher $eventDispatcher,
		private IInitialState $initialState,
	) {
		parent::__construct($appName, $request);
	}

	#[PublicPage]
	#[NoCSRFRequired]
	public function showEmailForm(string $email = '', string $message = '', string $code = ''): TemplateResponse {
		$emailHint = '';
		$domainList = $this->registrationService->getAllowedDomains();
		if (!empty($domainList) && $this->config->getAppValueBool('show_domains')) {
			$domainList = implode(', ', $domainList);
			if ($this->config->getAppValueBool('domains_is_blocklist')) {
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

		$emailList = $this->registrationService->getAllowedEmails();
		if (!empty($emailList) && $this->config->getAppValueBool('show_domains')) {
			$emailHint = $this->l10n->t(
				'Registration is only allowed with the following email addresses: %s',
				[implode(', ', $emailList)]
			);
		}

		$this->eventDispatcher->dispatchTyped(new ShowFormEvent(ShowFormEvent::STEP_EMAIL));

		$this->initialState->provideInitialState('email', $email);
		$this->initialState->provideInitialState('message', $message ?: $emailHint);
		$this->initialState->provideInitialState('emailIsOptional', $this->config->getAppValueBool('email_is_optional'));
		$this->initialState->provideInitialState('disableEmailVerification', $this->config->getAppValueBool('disable_email_verification'));
		$this->initialState->provideInitialState('isLoginFlow', $this->loginFlowService->isUsingLoginFlow());
		$this->initialState->provideInitialState('loginFormLink', $this->urlGenerator->linkToRoute('core.login.showLoginForm'));
		$this->initialState->provideInitialState('invitationCode', $code);
		$this->initialState->provideInitialState('invitationCodeRequired', $this->config->getAppValueBool('invitation_code_required'));
		$this->initialState->provideInitialState('invitationCodeLocked', $code !== '');
		$this->initialState->provideInitialState('invitationsEnabled', $this->config->getAppValueBool('invitation_code_required') || $code !== '');
		return new TemplateResponse('registration', 'form/email', [], 'guest');
	}

	#[PublicPage]
	#[NoCSRFRequired]
	public function showInviteForm(string $code): Response {
		try {
			$invitation = $this->invitationService->getByCode($code);
			$this->invitationService->assertUsable($invitation);
		} catch (DoesNotExistException $e) {
			return $this->validateSecretAndTokenErrorPage();
		} catch (RegistrationException $e) {
			return $this->showEmailForm('', $e->getMessage(), $code);
		}

		return $this->showEmailForm('', '', $code);
	}

	#[PublicPage]
	#[AnonRateLimit(limit: 5, period: 300)]
	public function submitEmailForm(string $email, string $code = ''): Response {
		$validateFormEvent = new ValidateFormEvent(ValidateFormEvent::STEP_EMAIL);
		$this->eventDispatcher->dispatchTyped($validateFormEvent);

		if (!empty($validateFormEvent->getErrors())) {
			return $this->showEmailForm($email, implode(' ', $validateFormEvent->getErrors()), $code);
		}

		try {
			$invitation = $this->resolveInvitation($email, $code);
		} catch (RegistrationException $e) {
			return $this->showEmailForm($email, $e->getMessage(), $code);
		}

		try {
			// Registration already in progress, update token and continue with verification
			$registration = $this->registrationService->getRegistrationForEmail($email);
			$this->registrationService->generateNewToken($registration);
			if ($invitation !== null && $registration->getInvitationId() === null) {
				$registration->setInvitationId($invitation->getId());
				$this->registrationService->updateInvitation($registration);
			}
		} catch (DoesNotExistException $e) {
			// No registration in progress
			try {
				$email = trim($email);
				$this->registrationService->validateEmail($email, $invitation);
			} catch (RegistrationException $e) {
				return $this->showEmailForm($email, $e->getMessage(), $code);
			}

			$registration = $this->registrationService->createRegistration($email, '', '', '', $invitation?->getId());
		}

		if ($this->config->getAppValueBool('disable_email_verification')) {
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
			return $this->showEmailForm($email, $e->getMessage(), $code);
		} catch (\Exception $e) {
			return $this->showEmailForm($email, $this->l10n->t('A problem occurred sending email, please contact your administrator.'), $code);
		}

		$this->eventDispatcher->dispatchTyped(new PassedFormEvent(PassedFormEvent::STEP_EMAIL, $registration->getClientSecret()));

		return new RedirectResponse(
			$this->urlGenerator->linkToRoute(
				'registration.register.showVerificationForm',
				['secret' => $registration->getClientSecret()]
			)
		);
	}

	#[PublicPage]
	#[AnonRateLimit(limit: 5, period: 300)]
	public function submitInviteForm(string $code, string $email): Response {
		return $this->submitEmailForm($email, $code);
	}

	#[PublicPage]
	#[NoCSRFRequired]
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
	 *
	 * @param string $secret
	 * @param string $token
	 * @return Response
	 */
	#[PublicPage]
	#[AnonRateLimit(limit: 5, period: 300)]
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

	#[PublicPage]
	#[NoCSRFRequired]
	public function showUserForm(string $secret, string $token, string $loginname = '', string $fullname = '', string $phone = '', string $password = '', string $message = ''): TemplateResponse {
		try {
			$registration = $this->validateSecretAndToken($secret, $token);
		} catch (RegistrationException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		$additional_hint = $this->config->getAppValueString('additional_hint');

		$this->eventDispatcher->dispatchTyped(new ShowFormEvent(ShowFormEvent::STEP_USER, $secret));

		$this->initialState->provideInitialState('email', $registration->getEmail());
		$this->initialState->provideInitialState('emailIsLogin', $this->config->getAppValueBool('email_is_login'));
		$this->initialState->provideInitialState('emailIsOptional', $this->config->getAppValueBool('email_is_optional'));
		$this->initialState->provideInitialState('loginname', $loginname);
		$this->initialState->provideInitialState('fullname', $fullname);
		$this->initialState->provideInitialState('showFullname', $this->config->getAppValueBool('show_fullname'));
		$this->initialState->provideInitialState('enforceFullname', $this->config->getAppValueBool('enforce_fullname'));
		$this->initialState->provideInitialState('phone', $phone);
		$this->initialState->provideInitialState('showPhone', $this->config->getAppValueBool('show_phone'));
		$this->initialState->provideInitialState('enforcePhone', $this->config->getAppValueBool('enforce_phone'));
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

	#[PublicPage]
	#[UseSession]
	#[AnonRateLimit(limit: 5, period: 300)]
	public function submitUserForm(string $secret, string $token, string $loginname, string $fullname, string $phone, string $password): Response {
		try {
			$registration = $this->validateSecretAndToken($secret, $token);
		} catch (RegistrationException $e) {
			return $this->validateSecretAndTokenErrorPage();
		}

		if ($this->config->getAppValueBool('email_is_login')) {
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

	/**
	 * Resolve and validate the invitation code, if any is required
	 *
	 * @param string $email
	 * @param string $code
	 * @return Invitation|null
	 * @throws RegistrationException
	 */
	protected function resolveInvitation(string $email, string $code): ?Invitation {
		if ($code !== '') {
			try {
				$invitation = $this->invitationService->getByCode($code);
			} catch (DoesNotExistException $e) {
				throw new RegistrationException($this->l10n->t('This invitation code is not valid.'));
			}

			$this->invitationService->validate($invitation, $email);
			return $invitation;
		}

		if ($this->config->getAppValueBool('invitation_code_required')) {
			throw new RegistrationException($this->l10n->t('Please provide an invitation code.'));
		}

		return null;
	}
}
