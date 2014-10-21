<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @copyright Pellaeon Lin 2014
 */

namespace OCA\Registration\Controller;


use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;
use \OCP\Util;
use \OCA\Registration\Wrapper;
use \OCP\IUserManager;

class RegisterController extends Controller {

	private $mail;
	private $l10n;
	private $urlgenerator;
	private $pendingreg;

	public function __construct($appName, IRequest $request, Wrapper\Mail $mail, $l10n, $urlgenerator,
	$pendingreg, IUserManager $usermanager){
		$this->mail = $mail;
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->pendingreg = $pendingreg;
		$this->usermanager = $usermanager;
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function askEmail($errormsg, $entered) {
		$params = array(
			'errormsg' => $errormsg ? $errormsg : $this->request->getParam('errormsg'),
			'entered' => $entered ? $entered : $this->request->getParam('entered')
		);
		return new TemplateResponse('registration', 'register', $params, 'guest');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function validateEmail() {
		$email = $this->request->getParam('email');
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			return new TemplateResponse('', 'error', array(array('error' => $this->l10n->t('Email address you entered is not valid'))), 'error');
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('Email address you entered is not valid'),
					'hint' => ''
				))
			), 'error');
		}

		if ( $this->pendingreg->find($email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('There is already a pending registration with this email'),
					'hint' => ''
				))
			), 'error');
		}

		// FEATURE: allow only from specific email domain

		$token = $this->pendingreg->save($email);
		$link = $this->urlgenerator->linkToRoute('registration.register.verifyToken', array('token' => $token));
		$link = $this->urlgenerator->getAbsoluteURL($link);
		$from = Util::getDefaultEmailAddress('register');
		$res = new TemplateResponse('registration', 'email', array('link' => $link), 'blank');
		$msg = $res->render();
		try {
			$this->mail->sendMail($email, 'ownCloud User', $this->l10n->t('Verify your ownCloud registration request'), $msg, $from, 'ownCloud');
		} catch (Exception $e) {
			\OC_Template::printErrorPage( 'A problem occurs during sending the e-mail please contact your administrator.');
			return;
		}
		return new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function verifyToken($token) {
		$email = $this->pendingreg->findEmailByToken($token);
		if ( \OCP\DB::isError($email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('Invalid verification URL. No registration request with this verification URL is found.'),
					'hint' => ''
				))
			), 'error');
		} elseif ( $email ) {
			return new TemplateResponse('registration', 'form', array('email' => $email, 'token' => $token), 'guest');
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function createAccount($token) {
		$email = $this->pendingreg->findEmailByToken($token);
		if ( \OCP\DB::isError($email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('Invalid verification URL. No registration request with this verification URL is found.'),
					'hint' => ''
				))
			), 'error');
		} elseif ( $email ) {
			$username = $this->request->getParam('username');
			$password = $this->request->getParam('password');
			try {
				$user = $this->usermanager->createUser($username, $password);
			} catch (Exception $e) {
				return new TemplateResponse('registration', 'form',
					array('email' => $email,
						'entered_data' => array('username' => $username),
						'errormsgs' => array($e->message, $username, $password)), 'guest');
			}
			if ( $user === false ) {
				return new TemplateResponse('', 'error', array(
					'errors' => array(array(
						'error' => $this->l10n->t('Unable to create user, there are problems with user backend.'),
						'hint' => ''
					))
				), 'error');
			}
			return new TemplateResponse('registration', 'message', array('msgs' =>
					$this->l10n->t('Your account has been successfully created, you can <a href="{link}">log in now</a>.')
				), 'guest');
		}
	}
}
