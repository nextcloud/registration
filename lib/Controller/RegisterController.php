<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @author Julius Härtl <jus@bitgrid.net>
 * @copyright Pellaeon Lin 2014
 */

namespace OCA\Registration\Controller;

use OCA\Registration\Db\Registration;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\Response;
use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use \OCP\IConfig;
use \OCP\IL10N;

class RegisterController extends Controller {

	/** @var IL10N */
	private $l10n;
	/** @var IURLGenerator */
	private $urlgenerator;
	/** @var IConfig */
	private $config;
	/** @var RegistrationService */
	private $registrationService;
	/** @var MailService */
	private $mailService;


	public function __construct(
		$appName,
		IRequest $request,
		IL10N $l10n,
		IURLGenerator $urlgenerator,
		IConfig $config,
		RegistrationService $registrationService,
		MailService $mailService
	) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->config = $config;
		$this->registrationService = $registrationService;
		$this->mailService = $mailService;
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param string $email
	 * @param string $message
	 * @return TemplateResponse
	 */
	public function showEmailForm(string $email = '', string $message = ''): TemplateResponse {
		$params = [
			'email' => $email,
			'message' => $message,
		];
		return new TemplateResponse('registration', 'form/email', $params, 'guest');
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=1)
	 *
	 * @param string $email
	 * @return TemplateResponse
	 */
	public function submitEmailForm(string $email): Response {
		try {
			// Registration already in progress, update token and continue with verification
			$registration = $this->registrationService->getRegistrationForEmail($email);
			$this->registrationService->generateNewToken($registration);
		} catch (DoesNotExistException $e) {
			// No registration in progress
			try {
				$this->registrationService->validateEmail($email);
				$registration = $this->registrationService->createRegistration($email);
			} catch (RegistrationException $e) {
				return $this->showEmailForm($email, $e->getMessage());
			}
		}

		try {
			$this->mailService->sendTokenByMail($registration);
		} catch (RegistrationException $e) {
			return $this->showEmailForm($email, $e->getMessage());
		}

		return new RedirectResponse(
			$this->urlgenerator->linkToRoute(
				'registration.register.showVerificationForm',
				['secret' => $registration->getClientSecret()]
			)
		);
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param string $secret
	 * @param string $message
	 * @return TemplateResponse
	 */
	public function showVerificationForm(string $secret, string $message = ''): TemplateResponse {
		try {
			$this->registrationService->getRegistrationForSecret($secret);
		} catch (RegistrationException $e) {
			return new TemplateResponse('core', 'error', [
				'errors' => [
					$this->l10n->t('The verification secret does not exist anymore'),
				],
			], 'error');
		}

		return new TemplateResponse('registration', 'form/verification', [
			'message' => $message,
		], 'guest');
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=1)
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
		} catch (RegistrationException $e) {
			return new TemplateResponse('core', 'error', [
				'errors' => [
					$this->l10n->t('The verification secret does not exist anymore'),
				],
			], 'error');
		}

		return new RedirectResponse(
			$this->urlgenerator->linkToRoute(
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
	 *
	 * @param string $secret
	 * @param string $token
	 * @return TemplateResponse
	 */
	public function showUserForm(string $secret, string $token): TemplateResponse {
		try {
			$registration = $this->registrationService->getRegistrationForSecret($secret);

			if ($registration->getToken() !== $token) {
				throw new RegistrationException('Invalid verification token');
			}
		} catch (RegistrationException $e) {
			return new TemplateResponse('core', 'error', [
				'errors' => [
					$this->l10n->t('The verification secret does not exist anymore or the verification token is invalid'),
				],
			], 'error');
		}

		try {
			/** @var Registration $registration */
			$registration = $this->registrationService->verifyToken($token);
			$this->registrationService->confirmEmail($registration);

			// create account without form if username/password are already stored
			if ($registration->getUsername() !== "" && $registration->getPassword() !== "") {
				$this->registrationService->createAccount($registration);
				return new TemplateResponse('registration', 'message',
					['msg' => $this->l10n->t('Your account has been successfully created, you can <a href="%s">log in now</a>.', [$this->urlgenerator->getAbsoluteURL('/')])],
					'guest'
				);
			}

			return new TemplateResponse('registration', 'form/user', [
				'email' => $registration->getEmail(),
				'email_is_login' => $this->config->getAppValue('registration', 'email_is_login', '0') === '1',
				'token' => $registration->getToken(),
			], 'guest');
		} catch (RegistrationException $exception) {
			return $this->renderError($exception->getMessage(), $exception->getHint());
		}
	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * @param $token
	 * @return RedirectResponse|TemplateResponse
	 */
	public function submitUserForm($token) {
		$registration = $this->registrationService->getRegistrationForToken($token);
		if ($this->config->getAppValue('registration', 'email_is_login', '0') === '1') {
			$username = $registration->getEmail();
		} else {
			$username = $this->request->getParam('username');
		}
		$password = $this->request->getParam('password');

		try {
			$user = $this->registrationService->createAccount($registration, $username, $password);
		} catch (\Exception $exception) {
			// Render form with previously sent values
			return new TemplateResponse('registration', 'form',
				[
					'email' => $registration->getEmail(),
					'entered_data' => ['user' => $username],
					'errormsgs' => [$exception->getMessage()],
					'token' => $token
				], 'guest');
		}

		if ($user->isEnabled()) {
			// log the user
			return $this->registrationService->loginUser($user->getUID(), $username, $password, false);
		} else {
			// warn the user their account needs admin validation
			return new TemplateResponse(
				'registration',
				'message',
				['msg' => $this->l10n->t("Your account has been successfully created, but it still needs approval from an administrator.")],
				'guest');
		}
	}

	private function renderError($error, $hint="") {
		return new TemplateResponse('', 'error', [
			'errors' => [[
				'error' => $error,
				'hint' => $hint
			]]
		], 'error');
	}
}
