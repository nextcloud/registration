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
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\AppFramework\Controller;
use \OCP\Defaults;
use \OCP\Util;
use \OCA\Registration\Wrapper;
use \OCP\IUserManager;
use \OCP\IUserSession;
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;
use \OCP\Mail\IMailer;
use \OCP\Security\ISecureRandom;
use \OC_User;
use \OC_Util;

class RegisterController extends Controller {

	private $mailer;
	private $l10n;
	private $urlgenerator;
	private $pendingreg;
	private $usermanager;
	private $config;
	private $groupmanager;
	/** @var \OCP\Defaults */
	private $defaults;
	private $random;
	private $usersession;
	protected $appName;

	public function __construct($appName, IRequest $request, IMailer $mailer, IL10N $l10n, $urlgenerator,
		$pendingreg, IUserManager $usermanager, IConfig $config, IGroupManager $groupmanager, Defaults $defaults,
		ISecureRandom $random, IUserSession $us){
		$this->mailer = $mailer;
		$this->l10n = $l10n;
		$this->urlgenerator = $urlgenerator;
		$this->pendingreg = $pendingreg;
		$this->usermanager = $usermanager;
		$this->config = $config;
		$this->groupmanager = $groupmanager;
		$this->defaults = $defaults;
		$this->appName = $appName;
		$this->random = $random;
		$this->usersession = $us;
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
		if ( !$this->mailer->validateMailAddress($email) ) {
			return new TemplateResponse('', 'error', array(
				'errors' => array(array(
					'error' => $this->l10n->t('The email address you entered is not valid'),
					'hint' => ''
				))
			), 'error');
		}

		if ( $this->pendingreg->find($email) ) {
			$this->pendingreg->delete($email);
			$token = $this->pendingreg->save($email);

			try {
				$this->sendValidationEmail($token, $email);
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
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
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
		try {
			$this->sendValidationEmail($token, $email);
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
		if ( $email === false ) {
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
	 * @UseSession
	 */
	public function createAccount($token) {
		$email = $this->pendingreg->findEmailByToken($token);
		if ( $email === false ) {
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
						'error' => $this->l10n->t('Unable to create user, there are problems with the user backend.'),
						'hint' => ''
					))
				), 'error');
			} else {
				$userId = $user->getUID();
				// Set user email
				try {
					$this->config->setUserValue($userId, 'settings', 'email', $email);
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
				if ( $res === false ) {
					return new TemplateResponse('', 'error', array(
						'errors' => array(array(
							'error' => $this->l10n->t('Failed to delete pending registration request'),
							'hint' => ''
						))
					), 'error');
				}

				// Notify admin
				$admin_users = $this->groupmanager->get('admin')->getUsers();
				$to_arr = array();
				foreach ( $admin_users as $au ) {
					$au_email = $this->config->getUserValue($au->getUID(), 'settings', 'email');
					if ( $au_email !== '' ) {
						$to_arr[$au_email] = $au->getDisplayName();
					}
				}
				try {
					$this->sendNewUserNotifEmail($to_arr, $userId);
				} catch (\Exception $e) {
					\OCP\Util::writeLog('registration', 'Sending admin notification email failed: '. $e->getMessage, \OCP\Util::ERROR);
				}

				// Try to log user in
				if ( method_exists($this->usersession, 'createSessionToken') ) {
					$this->usersession->login($username, $password);
					$this->usersession->createSessionToken($this->request, $userId, $username, $password);
					return new RedirectResponse($this->urlgenerator->linkToRoute('files.view.index'));
				} elseif (OC_User::login($username, $password)) {
					$this->cleanupLoginTokens($userId);
					// FIXME unsetMagicInCookie will fail from session already closed, so now we always remember
					$logintoken = $this->random->generate(32);
					$this->config->setUserValue($userId, 'login_token', $logintoken, time());
					OC_User::setMagicInCookie($userId, $logintoken);
					OC_Util::redirectToDefaultPage();

					// Render message in case redirect failed
					return new TemplateResponse('registration', 'message', array('msg' =>
						str_replace('{link}',
							$this->urlgenerator->getAbsoluteURL('/'),
							$this->l10n->t('Your account has been successfully created, you can <a href="{link}">log in now</a>.')
						)), 'guest');
				}
			}
		}
	}

	/**
	 * Sends validation email
	 * @param string $token
	 * @param string $to
	 * @return null
	 * @throws \Exception
	 */
	private function sendValidationEmail($token, $to) {
		$link = $this->urlgenerator->linkToRoute('registration.register.verifyToken', array('token' => $token));
		$link = $this->urlgenerator->getAbsoluteURL($link);
		$template_var = [
			'link' => $link,
			'sitename' => $this->defaults->getName()
		];
		$html_template = new TemplateResponse('registration', 'email.validate_html', $template_var, 'blank');
		$html_part = $html_template->render();
		$plaintext_template = new TemplateResponse('registration', 'email.validate_plaintext', $template_var, 'blank');
		$plaintext_part = $plaintext_template->render();
		$subject = $this->l10n->t('Verify your %s registration request', [$this->defaults->getName()]);

		$from = Util::getDefaultEmailAddress('register');
		$message = $this->mailer->createMessage();
		$message->setFrom([$from => $this->defaults->getName()]);
		$message->setTo([$to]);
		$message->setSubject($subject);
		$message->setPlainBody($plaintext_part);
		$message->setHtmlBody($html_part);
		$failed_recipients = $this->mailer->send($message);
		if ( !empty($failed_recipients) )
			throw new \Exception('Failed recipients: '.print_r($failed_recipients, true));
	}

	/**
	 * Sends new user notification email to admin
	 * @param array $to
	 * @param string $username the new user
	 * @return null
	 * @throws \Exception
	 */
	private function sendNewUserNotifEmail(array $to, $username) {
		$template_var = [
			'user' => $username,
			'sitename' => $this->defaults->getName()
		];
		$html_template = new TemplateResponse('registration', 'email.newuser_html', $template_var, 'blank');
		$html_part = $html_template->render();
		$plaintext_template = new TemplateResponse('registration', 'email.newuser_plaintext', $template_var, 'blank');
		$plaintext_part = $plaintext_template->render();
		$subject = $this->l10n->t('A new user "%s" has created an account on %s', [$username, $this->defaults->getName()]);

		$from = Util::getDefaultEmailAddress('register');
		$message = $this->mailer->createMessage();
		$message->setFrom([$from => $this->defaults->getName()]);
		$message->setTo($to);
		$message->setSubject($subject);
		$message->setPlainBody($plaintext_part);
		$message->setHtmlBody($html_part);
		$failed_recipients = $this->mailer->send($message);
		if ( !empty($failed_recipients) )
			throw new \Exception('Failed recipients: '.print_r($failed_recipients, true));
	}

	/**
	 * Replicates OC::cleanupLoginTokens() since it's protected
	 * @param string $userId
	 * @return null
	 */
	private function cleanupLoginTokens($userId) {
		$cutoff = time() - $this->config->getSystemValue('remember_login_cookie_lifetime', 60 * 60 * 24 * 15);
		$tokens = $this->config->getUserKeys($userId, 'login_token');
		foreach ($tokens as $token) {
			$time = $this->config->getUserValue($userId, 'login_token', $token);
			if ($time < $cutoff) {
				$this->config->deleteUserValue($userId, 'login_token', $token);
			}
		}
	}
}
