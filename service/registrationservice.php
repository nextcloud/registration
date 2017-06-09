<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Registration\Service;

use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\Defaults;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IURLGenerator;
use \OCP\Util;
use \OCP\IUserManager;
use \OCP\IUserSession;
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;
use \OCP\Mail\IMailer;
use \OCP\Security\ISecureRandom;
use \OC_User;
use \OC_Util;

class RegistrationService {

	/** @var IMailer */
	private $mailer;
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
	/** @var string */
	private $appName;
	/** @var IRequest */
	private $request;
	/** @var ILogger */
	private $logger;

	public function __construct($appName, IMailer $mailer, IL10N $l10n, IURLGenerator $urlGenerator,
								RegistrationMapper $registrationMapper, IUserManager $userManager, IConfig $config, IGroupManager $groupManager, Defaults $defaults,
								ISecureRandom $random, IUserSession $us, IRequest $request, ILogger $logger){
		$this->mailer = $mailer;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->registrationMapper = $registrationMapper;
		$this->userManager = $userManager;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->defaults = $defaults;
		$this->random = $random;
		$this->usersession = $us;
		$this->appName = $appName;
		$this->request = $request;
		$this->logger = $logger;
	}


	public function validateEmail($email) {

		if ( !$this->mailer->validateMailAddress($email) ) {
			throw new RegistrationException($this->l10n->t('The email address you entered is not valid'));
		}

		try {
			$registration = $this->registrationMapper->find($email);
		} catch (\Exception $e) {
			$registration = null;
		}
		// check if email already tried to register
		if ( $registration !== null) {
			$this->registrationMapper->delete($registration);
			$this->generateToken($email);
			throw new RegistrationException($this->l10n->t('There is already a pending registration with this email, a new verification email has been sent to the address.'));
		}

		if ( $this->config->getUsersForUserValue('settings', 'email', $email) ) {
			throw new RegistrationException(
				$this->l10n->t('A user has already taken this email, maybe you already have an account?'),
				$this->l10n->t('You can <a href="%s">log in now</a>.', [$this->urlGenerator->getAbsoluteURL('/')])
			);
		}

		// allow only from specific email domain}
		if (!$this->checkAllowedDomains($email)) {
			$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
			$allowed_domains = explode(';', $allowed_domains);
			return new TemplateResponse('registration', 'domains', [
				'domains' => $allowed_domains
			], 'guest');
		}

		$this->generateToken($email);

		return null;

	}

	public function generateToken($email)  {
		try {
			$registration = $this->registrationMapper->find($email);
			$this->registrationMapper->delete($registration);
		} catch (\Exception $exception) {}
		$registration = $this->registrationMapper->save($email);

		try {
			$this->sendValidationEmail($registration->getToken(), $email);
		} catch (\Exception $e) {
			throw new RegistrationException($this->l10n->t('A problem occurred sending email, please contact your administrator.'));
		}
	}

	/**
	 * check if email domain is allowed
	 *
	 * @param $email
	 * @return bool
	 */
	public function checkAllowedDomains($email) {
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
		if ( $allowed_domains !== '' ) {
			$allowed_domains = explode(';', $allowed_domains);
			$allowed = false;
			foreach ($allowed_domains as $domain) {
				$maildomain = explode("@", $email)[1];
				// valid domain, everythings fine
				if ($maildomain === $domain) {
					$allowed = true;
					break;
				}
			}
			return $allowed;
		}
		return true;
	}

	/**
	 * @param $token
	 * @return string
	 * @throws RegistrationException
	 */
	public function verifyToken($token) {
		try {
			return $this->registrationMapper->findByToken($token);
		} catch (DoesNotExistException $exception) {
			throw new RegistrationException($this->l10n->t('Invalid verification URL. No registration request with this verification URL is found.'));
		}
	}


	public function createAccount($token, $username, $password) {
		$email = $this->registrationMapper->findEmailByToken($token);
		if ( $email === false ) {
			throw new RegistrationException($this->l10n->t('Invalid verification URL. No registration request with this verification URL is found.'));
		}

		$user = $this->userManager->createUser($username, $password);
		if ($user === false) {
			throw new RegistrationException($this->l10n->t('Unable to create user, there are problems with the user backend.'));
		}
		$userId = $user->getUID();
		// Set user email
		try {
			$this->config->setUserValue($userId, 'settings', 'email', $email);
		} catch (\Exception $e) {
			throw new RegistrationException($this->l10n->t('Unable to set user email: ' . $e->getMessage()));
		}

		// Add user to group
		$registered_user_group = $this->config->getAppValue($this->appName, 'registered_user_group', 'none');
		if ( $registered_user_group !== 'none' ) {
			try {
				$group = $this->groupManager->get($registered_user_group);
				$group->addUser($user);
			} catch (\Exception $e) {
				throw new RegistrationException($e->getMessage());
			}
		}

		// Delete pending reg request
		$res = $this->registrationMapper->deleteByEmail($email);
		if ($res === false) {
			throw new RegistrationException($this->l10n->t('Failed to delete pending registration request'));
		}

		$this->notifyAdmins($userId);

		$this->loginUser($userId, $username, $password);

	}

	public function loginUser($userId, $username, $password) {
		if ( method_exists($this->usersession, 'createSessionToken') ) {
			$this->usersession->login($username, $password);
			$this->usersession->createSessionToken($this->request, $userId, $username, $password);
			return new RedirectResponse($this->urlGenerator->linkToRoute('files.view.index'));
		} elseif (OC_User::login($username, $password)) {
			$this->cleanupLoginTokens($userId);
			// FIXME unsetMagicInCookie will fail from session already closed, so now we always remember
			$logintoken = $this->random->generate(32);
			$this->config->setUserValue($userId, 'login_token', $logintoken, time());
			OC_User::setMagicInCookie($userId, $logintoken);
			OC_Util::redirectToDefaultPage();
		}
		// Render message in case redirect failed
		return new TemplateResponse('registration', 'message',
			['msg' => $this->l10n->t('Your account has been successfully created, you can <a href="%s">log in now</a>.'), [$this->urlGenerator->getAbsoluteURL('/')]]
			, 'guest'
		);

	}

	public function notifyAdmins($userId) {
		// Notify admin
		$admin_users = $this->groupManager->get('admin')->getUsers();
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
			$this->logger->error('Sending admin notification email failed: '. $e->getMessage());
		}
	}

	/**
	 * Sends validation email
	 * @param string $token
	 * @param string $to
	 * @throws \Exception
	 */
	private function sendValidationEmail($token, $to) {
		$link = $this->urlGenerator->linkToRoute('registration.register.verifyToken', array('token' => $token));
		$link = $this->urlGenerator->getAbsoluteURL($link);
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
			throw new RegistrationException('Failed recipients: '.print_r($failed_recipients, true));
	}

	/**
	 * Sends new user notification email to admin
	 * @param array $to
	 * @param string $username the new user
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
			throw new RegistrationException('Failed recipients: '.print_r($failed_recipients, true));
	}

	/**
	 * Replicates OC::cleanupLoginTokens() since it's protected
	 * @param string $userId
	 */
	public function cleanupLoginTokens($userId) {
		$cutoff = time() - $this->config->getSystemValue('remember_login_cookie_lifetime', 60 * 60 * 24 * 15);
		$tokens = $this->config->getUserKeys($userId, 'login_token');
		foreach ($tokens as $token) {
			$time = $this->config->getUserValue($userId, 'login_token', $token);
			if ($time < $cutoff) {
				$this->config->deleteUserValue($userId, 'login_token', $token);
			}
		}
	}

	public function getRegistrationForToken($token) {
		return $this->registrationMapper->findByToken($token);
	}
}
