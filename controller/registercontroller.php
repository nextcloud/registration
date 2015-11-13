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
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;

class RegisterController extends Controller {

	private $mail;
	private $l10n;
	private $urlgenerator;
	private $pendingreg;
	private $usermanager;
	private $config;
	private $groupmanager;
	protected $appName;

	public function __construct($appName, IRequest $request, Wrapper\Mail $mail, IL10N $l10n, $urlgenerator,
	$pendingreg, IUserManager $usermanager, IConfig $config, IGroupManager $groupmanager){
		$this->mail = $mail;
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->pendingreg = $pendingreg;
		$this->usermanager = $usermanager;
		$this->config = $config;
		$this->groupmanager = $groupmanager;
		$this->appName = $appName;
		parent::__construct($appName, $request);
	}

	/**
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
	 * @PublicPage
	 */
	public function validateEmail() {
		$email = $this->request->getParam('email');
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('Email address you entered is not valid'),
					'hint' => ''
				))
			), 'error');
		}

		if ( $this->pendingreg->find($email) ) {
			$this->pendingreg->delete($email);
			$token = $this->pendingreg->save($email);
			$link = $this->urlgenerator->linkToRoute('registration.register.verifyToken', array('token' => $token));
			$link = $this->urlgenerator->getAbsoluteURL($link);
			$from = Util::getDefaultEmailAddress('register');
			$res = new TemplateResponse('registration', 'email', array('link' => $link), 'blank');
			$msg = $res->render();
			try {
				$this->mail->sendMail($email, 'ownCloud User', $this->l10n->t('Verify your ownCloud registration request'), $msg, $from, 'ownCloud');
			} catch (\Exception $e) {
				return new TemplateResponse('', 'error', array(
					'errors' => array(array(
						'error' => $this->l10n->t('A problem occurred sending email, please contact your administrator.'),
						'hint' => ''
					))
				), 'error');
			}
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('There is already a pending registration with this email, a new verification email has been sent to the address.'),
					'hint' => ''
				))
			), 'error');
		}

		if ( $this->config->getUsersForUserValue('settings', 'email', $email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('A user has already taken this email, maybe you already have an account?'),
					'hint' => str_replace(
						'{login}', $this->urlgenerator->getAbsoluteURL('/'),
						$this->l10n->t('You can <a href="{login}">log in now</a>.'))
				))
			), 'error');
		}


		// allow only from specific email domain
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains_for_mail_address', '');
		if ( $allowed_domains !== '' ) {
			$allowed_domains = explode(';', $allowed_domains);
			$allowed = false;
			foreach ( $allowed_domains as $domain ) {
				$maildomain=explode("@",$email)[1];
				// valid domain, everythings fine
				if ($maildomain === $domain) {
					$allowed=true;
					break;
				}
			}
			if ( $allowed === false ) {
				return new TemplateResponse('registration', 'domains', ['domains' =>
					$allowed_domains
				], 'guest');
			}
		}

		$token = $this->pendingreg->save($email);
		//TODO: check for error
		$link = $this->urlgenerator->linkToRoute('registration.register.verifyToken', array('token' => $token));
		$link = $this->urlgenerator->getAbsoluteURL($link);
		$from = Util::getDefaultEmailAddress('register');
		$res = new TemplateResponse('registration', 'email', array('link' => $link), 'blank');
		$msg = $res->render();
		try {
			$this->mail->sendMail($email, 'ownCloud User', $this->l10n->t('Verify your ownCloud registration request'), $msg, $from, 'ownCloud');
		} catch (\Exception $e) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('A problem occurred sending email, please contact your administrator.'),
					'hint' => ''
				))
			), 'error');
		}
		return new TemplateResponse('registration', 'message', array('msg' =>
			$this->l10n->t('Verification email successfully sent.')
		), 'guest');
	}

	/**
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
			} catch (\Exception $e) {
				return new TemplateResponse('registration', 'form',
					array('email' => $email,
						'entered_data' => array('username' => $username),
						'errormsgs' => array($e->getMessage()),
						'token' => $token), 'guest');
			}
			if ( $user === false ) {
				return new TemplateResponse('', 'error', array(
					'errors' => array(array(
						'error' => $this->l10n->t('Unable to create user, there are problems with user backend.'),
						'hint' => ''
					))
				), 'error');
			} else {
				// Set user email
				try {
					$this->config->setUserValue($user->getUID(), 'settings', 'email', $email);
				} catch (\Exception $e) {
					return new TemplateResponse('', 'error', array(
						'errors' => array(array(
							'error' => $this->l10n->t('Unable to set user email: '.$e->getMessage()),
							'hint' => ''
						))
					), 'error');
				}

				// Add user to group
				$registered_user_group = $this->config->getAppValue($this->appName, 'registered_user_group', 'none');
				if ( $registered_user_group !== 'none' ) {
					try {
						$group = $this->groupmanager->get($registered_user_group);
						$group->addUser($user);
					} catch (\Exception $e) {
						return new TemplateResponse('', 'error', array(
							'errors' => array(array(
								'error' => $e->message,
							))
						), 'error');
					}
				}

				// Delete pending reg request
				$res = $this->pendingreg->delete($email);
				if ( \OCP\DB::isError($res) ) {
					return new TemplateResponse('', 'error', array(
						'errors' => array(array(
							'error' => $this->l10n->t('Failed to delete pending registration request'),
							'hint' => ''
						))
					), 'error');
				}
			}

			return new TemplateResponse('registration', 'message', array('msg' =>
				str_replace('{link}',
					$this->urlgenerator->getAbsoluteURL('/'),
					$this->l10n->t('Your account has been successfully created, you can <a href="{link}">log in now</a>.'))
				), 'guest');
		}
	}
}
