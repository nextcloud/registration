<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 * @copyright Copyright (c) 2017 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @copyright Copyright (c) 2017 Lukas Reschke <lukas@statuscode.ch>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @author Lukas Reschke <lukas@statuscode.ch>
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

use OC\Authentication\Exceptions\InvalidTokenException;
use OC\Authentication\Exceptions\PasswordlessTokenException;
use OC\Authentication\Token\IProvider;
use OC\Authentication\Token\IToken;
use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCP\Defaults;
use OCP\ILogger;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\Security\ICrypto;
use OCP\Session\Exceptions\SessionNotAvailableException;
use \OCP\IUserManager;
use \OCP\IUserSession;
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;
use \OCP\Security\ISecureRandom;
use \OC_User;
use \OC_Util;

class RegistrationService {

	/** @var string */
	private $appName;
	/** @var MailService */
	private $mailService;
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
	/** @var IRequest */
	private $request;
	/** @var ILogger */
	private $logger;
	/** @var ISession */
	private $session;
	/** @var IProvider */
	private $tokenProvider;
	/** @var ICrypto */
	private $crypto;

	public function __construct($appName, MailService $mailService, IL10N $l10n, IURLGenerator $urlGenerator,
								RegistrationMapper $registrationMapper, IUserManager $userManager, IConfig $config, IGroupManager $groupManager, Defaults $defaults,
								ISecureRandom $random, IUserSession $us, IRequest $request, ILogger $logger, ISession $session, IProvider $tokenProvider, ICrypto $crypto){
		$this->appName = $appName;
		$this->mailService = $mailService;
		$this->l10n = $l10n;
		$this->urlGenerator = $urlGenerator;
		$this->registrationMapper = $registrationMapper;
		$this->userManager = $userManager;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->defaults = $defaults;
		$this->random = $random;
		$this->usersession = $us;
		$this->request = $request;
		$this->logger = $logger;
		$this->session = $session;
		$this->tokenProvider = $tokenProvider;
		$this->crypto = $crypto;
	}

	/**
	 * @param Registration $registration
	 */
	public function confirmEmail(Registration &$registration) {
		$registration->setEmailConfirmed(true);
		$this->registrationMapper->update($registration);
	}

	/**
	 * @param Registration $registration
	 */
	public function generateNewToken(Registration &$registration) {
		$this->registrationMapper->generateNewToken($registration);
		$this->registrationMapper->update($registration);
	}
	/**
	 * @param string $email
	 * @param string $username
	 * @param string $password
	 * @param string $displayname
	 * @return Registration
	 */
	public function createRegistration($email, $username="", $password="", $displayname="") {
		$registration = new Registration();
		$registration->setEmail($email);
		$registration->setUsername($username);
		$registration->setDisplayname();
		if($password !== "") {
			$password = $this->crypto->encrypt($password);
			$registration->setPassword($password);
		}
		$this->registrationMapper->generateNewToken($registration);
		$this->registrationMapper->generateClientSecret($registration);
		$this->registrationMapper->insert($registration);
		return $registration;
	}

	/**
	 * @param string $email
	 * @return Registration
	 * @throws RegistrationException
	 */
	public function validateEmail($email) {

		$this->mailService->validateEmail($email);

		// check for pending registrations
		try {
			return $this->registrationMapper->find($email);
		} catch (\Exception $e) {}

		if ( $this->config->getUsersForUserValue('settings', 'email', $email) ) {
			throw new RegistrationException(
				$this->l10n->t('A user has already taken this email, maybe you already have an account?'),
				$this->l10n->t('You can <a href="%s">log in now</a>.', [$this->urlGenerator->getAbsoluteURL('/')])
			);
		}

		if (!$this->checkAllowedDomains($email)) {
			throw new RegistrationException(
				$this->l10n->t(
					'Registration is only allowed for the following domains: ' .
					$this->config->getAppValue($this->appName, 'allowed_domains', '')
				)
			);
		}
		return null;
	}

	/**
	 * @param string $displayname
	 * @throws RegistrationException
	 */
	public function validateDisplayname($displayname) {
		if($displayname === "") {
			throw new RegistrationException($this->l10n->t('Please provide a valid display name.'));
		}
	}

	/**
	 * @param string $username
	 * @throws RegistrationException
	 */
	public function validateUsername($username) {
		if($username === "") {
			throw new RegistrationException($this->l10n->t('Please provide a valid user name.'));
		}

		if($this->registrationMapper->usernameIsPending($username) || $this->userManager->get($username) !== null) {
			throw new RegistrationException($this->l10n->t('The username you have chosen already exists.'));
		}
	}

	/**
	 * check if email domain is allowed
	 *
	 * @param string $email
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
	 * @return array
	 */
	public function getAllowedDomains() {
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
		$allowed_domains = explode(';', $allowed_domains);
		return $allowed_domains;
	}

	/**
	 * Find registration entity for token
	 *
	 * @param string $token
	 * @return string
	 * @throws RegistrationException
	 */
	public function verifyToken($token) {
		try {
			return $this->registrationMapper->findByToken($token);
		} catch (DoesNotExistException $exception) {
			throw new RegistrationException($this->l10n->t('Invalid verification URL. No registration request with this verification URL is found.', 404));
		}
	}

	/**
	 * @param $registration
	 * @param string $username
	 * @param string $password
	 * @return \OCP\IUser
	 * @throws RegistrationException
	 */
	public function createAccount(Registration &$registration, $username = null, $password = null) {
		if($password === null && $registration->getPassword() === null) {
			$generatedPassword = $this->generateRandomDeviceToken();
			$registration->setPassword($this->crypto->encrypt($generatedPassword));
		}

		if ($username === null) {
			$username = $registration->getUsername();
		}

		if($registration->getPassword() !== null) {
			$password = $this->crypto->decrypt($registration->getPassword());
		}

		$user = $this->userManager->createUser($username, $password);
		if ($user === false) {
			throw new RegistrationException($this->l10n->t('Unable to create user, there are problems with the user backend.'));
		}
		$userId = $user->getUID();
		// Set user email
		try {
			$user->setEMailAddress($registration->getEmail());
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

		// Delete pending registration if no client secret is stored
		if($registration->getClientSecret() === null) {
			$res = $this->registrationMapper->delete($registration);
			if ($res === false) {
				throw new RegistrationException($this->l10n->t('Failed to delete pending registration request'));
			}
		}

		$this->mailService->notifyAdmins($userId);
		return $user;
	}

	/**
	 * @param $token
	 * @return Registration
	 */
	public function getRegistrationForToken($token) {
		return $this->registrationMapper->findByToken($token);
	}

	/**
	 * @param $secret
	 * @return Registration
	 */
	public function getRegistrationForSecret($secret) {
		return $this->registrationMapper->findBySecret($secret);
	}

	/**
	 * @param Registration $registation
	 * @return null|\OCP\IUser
	 */
	public function getUserAccount(Registration $registation) {
		$user = $this->userManager->get($registation->getUsername());
		return $user;
	}

	/**
	 * @param Registration $registration
	 */
	public function deleteRegistration(Registration $registration) {
		$this->registrationMapper->delete($registration);
	}

	/**
	 * Return a 25 digit device password
	 *
	 * Example: AbCdE-fGhIj-KlMnO-pQrSt-12345
	 *
	 * @return string
	 */
	private function generateRandomDeviceToken() {
		$groups = [];
		for ($i = 0; $i < 5; $i++) {
			$groups[] = $this->random->generate(5, ISecureRandom::CHAR_HUMAN_READABLE);
		}
		return implode('-', $groups);
	}

	/**
	 * @param string $uid
	 * @return string
	 * @throws RegistrationException
	 */
	public function generateAppPassword($uid) {
		$name = $this->l10n->t('Registration app auto setup');
		try {
			$sessionId = $this->session->getId();
		} catch (SessionNotAvailableException $ex) {
			throw new RegistrationException('Failed to generate an app token.');
		}

		try {
			$sessionToken = $this->tokenProvider->getToken($sessionId);
			$loginName = $sessionToken->getLoginName();
			try {
				$password = $this->tokenProvider->getPassword($sessionToken, $sessionId);
			} catch (PasswordlessTokenException $ex) {
				$password = null;
			}
		} catch (InvalidTokenException $ex) {
			throw new RegistrationException('Failed to generate an app token.');
		}

		$token = $this->generateRandomDeviceToken();
		$this->tokenProvider->generateToken($token, $uid, $loginName, $password, $name, IToken::PERMANENT_TOKEN);
		return $token;
	}

	/**
	 * @param $userId
	 * @param $username
	 * @param $password
	 * @param $decrypt
	 * @return RedirectResponse|TemplateResponse
	 */
	public function loginUser($userId, $username, $password, $decrypt = false) {
		if ($decrypt) {
			$password = $this->crypto->decrypt($password);
		}
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

}
