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
use \OCA\Registration\Wrapper;

class RegisterController extends Controller {

	private $mail;
	private $l10n;
	private $db;
	private $urlgenerator;

	public function __construct($appName, IRequest $request, Mail $mail, $l10n, $db, $urlgenerator){
		$this->mail = $mail;
		$this->l10n = $l10n;
		$this->db = $db;
		$this->urlgenerator = $urlgenerator;
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
		return new TemplateResponse('registration', 'register', $params, 'blank');
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function validateEmail() {
		$email = $this->request->getParam('email');
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			$this->displayRegisterPage($this->l10n->t('Email address you entered is not valid'), true);
			return;
		}

		if ( $this->db->find($email) ) {
			$this->displayRegisterPage($this->l10n->t('There is already a pending registration with this email'), true);
			return;
		}

		// FEATURE: allow only from specific email domain

		$token = $this->db->savePendingRegistration($email);
		$link = $this->urlgenerator->linkToRoute('registration.verify.token', array('token' => $token));
		$link = OC_Helper::makeURLAbsolute($link);
		$from = OCP\Util::getDefaultEmailAddress('register');
		$tmpl = new OC_Template('core/registration', 'email');
		$tmpl->assign('link', $link, false);
		$msg = $tmpl->fetchPage();
		try {
			OC_Mail::send($_POST['email'], 'ownCloud User', $l->t('Verify your ownCloud registration request'), $msg, $from, 'ownCloud');
		} catch (Exception $e) {
			OC_Template::printErrorPage( 'A problem occurs during sending the e-mail please contact your administrator.');
			return;
		}
		$this->displayRegisterPage('', true);
	}
}
