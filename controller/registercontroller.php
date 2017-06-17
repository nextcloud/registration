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

use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\AppFramework\Controller;
use OCP\IURLGenerator;
use \OCP\IL10N;

class RegisterController extends Controller {

	private $l10n;
	private $urlgenerator;
	/** @var RegistrationService */
	private $registrationService;


	public function __construct(
		$appName,
		IRequest $request,
		IL10N $l10n,
		IURLGenerator $urlgenerator,
		RegistrationService $registrationService
	){
		parent::__construct($appName, $request);
		$this->request = $request;
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->registrationService = $registrationService;
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param $errormsg
	 * @param $entered
	 * @return TemplateResponse
	 */
	public function askEmail($errormsg, $entered) {
		$params = array(
			'errormsg' => $errormsg ? $errormsg : $this->request->getParam('errormsg'),
			'entered' => $entered ? $entered : $this->request->getParam('entered')
		);
		return new TemplateResponse('registration', 'register', $params, 'guest');
	}

	/**
	 * @PublicPage
	 * @return TemplateResponse
	 */
	public function validateEmail() {
		$email = $this->request->getParam('email');
		try {
			$validation = $this->registrationService->validateEmail($email);
			if($validation instanceof TemplateResponse) {
				return $validation;
			}
		} catch (RegistrationException $e) {
			return $this->renderError($e->getMessage(), $e->getHint());
		}

		return new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 *
	 * @param $token
	 * @return TemplateResponse
	 */
	public function verifyToken($token) {
		try {
			$registration = $this->registrationService->verifyToken($token);
			return new TemplateResponse('registration', 'form', ['email' => $registration->getEmail(), 'token' => $registration->getToken()], 'guest');
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
	public function createAccount($token) {
		$username = $this->request->getParam('username');
		$password = $this->request->getParam('password');
		$registration = $this->registrationService->getRegistrationForToken($token);

		try {
			$this->registrationService->createAccount($token, $username, $password);
		} catch (RegistrationException $exception) {
			return $this->renderError($exception->getMessage(), $exception->getHint());
		} catch (\InvalidArgumentException $exception) {
			// Render form with previously sent values
			return new TemplateResponse('registration', 'form',
				[
					'email' => $registration->getEmail(),
					'entered_data' => array('user' => $username),
					'errormsgs' => array($exception->getMessage()),
					'token' => $token
				], 'guest');
		}

		return new TemplateResponse('registration', 'message',
			['msg' => $this->l10n->t('Your account has been successfully created, you can <a href="%s">log in now</a>.', [$this->urlgenerator->getAbsoluteURL('/')])],
			'guest'
		);
	}

	private function renderError($error, $hint="") {
		return new TemplateResponse('', 'error', array(
			'errors' => array(array(
				'error' => $error,
				'hint' => $hint
			))
		), 'error');
	}

}
