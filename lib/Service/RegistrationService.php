<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Service;

use InvalidArgumentException;
use libphonenumber\PhoneNumberUtil;
use OC\Authentication\Exceptions\PasswordlessTokenException;
use OC\Authentication\Token\IProvider;
use OCA\Registration\AppInfo\Application;
use OCA\Registration\Db\Registration;
use OCA\Registration\Db\RegistrationMapper;
use OCA\Settings\Mailer\NewUserMailHelper;
use OCP\Accounts\IAccountManager;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Services\IAppConfig;
use OCP\Authentication\Exceptions\InvalidTokenException;
use OCP\Authentication\Token\IToken;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IPhoneNumberUtil;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use OCP\Session\Exceptions\SessionNotAvailableException;
use Psr\Log\LoggerInterface;

class RegistrationService {

	public function __construct(
		private string $appName,
		private MailService $mailService,
		private IL10N $l10n,
		private IURLGenerator $urlGenerator,
		private RegistrationMapper $registrationMapper,
		private IUserManager $userManager,
		private IAccountManager $accountManager,
		private IConfig $config,
		private IAppConfig $appConfig,
		private IGroupManager $groupManager,
		private ISecureRandom $random,
		private IUserSession $userSession,
		private IRequest $request,
		private LoggerInterface $logger,
		private ISession $session,
		private IProvider $tokenProvider,
		private ICrypto $crypto,
		private IPhoneNumberUtil $phoneNumberUtil,
	) {
	}

	public function confirmEmail(Registration $registration): void {
		$registration->setEmailConfirmed(true);
		$this->registrationMapper->update($registration);
	}

	public function generateNewToken(Registration $registration): void {
		$this->registrationMapper->generateNewToken($registration);
		$this->registrationMapper->update($registration);
	}

	/**
	 * Create registration request, used by both the API and form
	 */
	public function createRegistration(string $email, string $username = '', string $password = '', string $displayname = ''): Registration {
		$registration = new Registration();
		$registration->setEmail($email);
		$registration->setUsername($username);
		$registration->setDisplayname($displayname);
		if ($password !== '') {
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
	 * @throws RegistrationException
	 */
	public function validateEmail(string $email): void {
		if ($email === '' && $this->appConfig->getAppValueBool('email_is_optional')) {
			return;
		}

		$this->mailService->validateEmail($email);

		// check for pending registrations
		try {
			$this->registrationMapper->find($email);//if not found DB will throw a exception
			throw new RegistrationException(
				$this->l10n->t('A user has already taken this email, maybe you already have an account?')
			);
		} catch (DoesNotExistException $e) {
		}

		if ($this->userManager->getByEmail($email)) {
			throw new RegistrationException(
				$this->l10n->t('A user has already taken this email, maybe you already have an account?'),
				$this->l10n->t('You can <a href="%s">log in now</a>.', [$this->urlGenerator->getAbsoluteURL('/')])
			);
		}

		$allowedDomains = $this->getAllowedDomains();

		if (empty($allowedDomains)) {
			return;
		}

		$emailIsInDomainList = $this->checkAllowedDomains($email, $allowedDomains);
		$blockDomains = $this->appConfig->getAppValueBool('domains_is_blocklist');
		$showDomains = $this->appConfig->getAppValueBool('show_domains');

		if (!$blockDomains && !$emailIsInDomainList) {
			if ($showDomains) {
				throw new RegistrationException(
					$this->l10n->t(
						'Registration is only allowed with the following domains: %s',
						[implode(', ', $allowedDomains)]
					)
				);
			}
			throw new RegistrationException(
				$this->l10n->t('Registration with this email domain is not allowed.')
			);
		}

		if ($blockDomains && $emailIsInDomainList) {
			if ($showDomains) {
				throw new RegistrationException(
					$this->l10n->t(
						'Registration is not allowed with the following domains: %s',
						[implode(', ', $allowedDomains)]
					)
				);
			}
			throw new RegistrationException(
				$this->l10n->t('Registration with this email domain is not allowed.')
			);
		}
	}

	/**
	 * @param string $displayname
	 * @throws RegistrationException
	 */
	public function validateDisplayname(string $displayname): void {
		if ($displayname === '') {
			throw new RegistrationException($this->l10n->t('Please provide a valid display name.'));
		}
	}

	/**
	 * @param string $username
	 * @throws RegistrationException
	 */
	public function validateUsername(string $username): void {
		if ($username === '') {
			throw new RegistrationException($this->l10n->t('Please provide a valid login name.'));
		}

		$regex = $this->appConfig->getAppValueString('username_policy_regex');
		if ($regex && preg_match($regex, $username) === 0) {
			throw new RegistrationException($this->l10n->t('Please provide a valid login name.'));
		}

		if ($this->registrationMapper->usernameIsPending($username) || $this->userManager->get($username) !== null) {
			throw new RegistrationException($this->l10n->t('The login name you have chosen already exists.'));
		}
	}

	/**
	 * @param string $phone
	 * @throws RegistrationException
	 */
	public function validatePhoneNumber(string $phone): void {
		$defaultRegion = $this->config->getSystemValueString('default_phone_region', '');

		if ($defaultRegion === '') {
			// When no default region is set, only +49… numbers are valid
			if (!str_starts_with($phone, '+')) {
				throw new RegistrationException($this->l10n->t('The phone number needs to contain the country code.'));
			}

			$defaultRegion = 'EN';
		}

		if ($this->phoneNumberUtil->convertToStandardFormat($phone, $defaultRegion) === null) {
			throw new RegistrationException($this->l10n->t('The phone number is invalid.'));
		}
	}

	/**
	 * check if email domain is allowed
	 */
	public function checkAllowedDomains(string $email, array $allowedDomains): bool {
		if (!empty($allowedDomains)) {
			[,$mailDomain] = explode('@', strtolower($email), 2);

			foreach ($allowedDomains as $domain) {
				// valid domain, everything's fine

				// Wildcards
				if (str_contains($domain, '*')) {
					// *.example.com
					// Make save for regex:
					// \*\.example\.com
					$regexDomain = preg_quote($domain, '\\');
					// Replace "\*" with an actual regex wildcard and set start and end:
					// /^.+\.example\.com$/
					$regexDomain = '/^' . str_replace('\\*', '.+', $regexDomain) . '$/';

					if (preg_match($regexDomain, $mailDomain)) {
						return true;
					}
				} elseif ($mailDomain === $domain) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * @return string[]
	 */
	public function getAllowedDomains(): array {
		$allowedDomains = $this->appConfig->getAppValueString('allowed_domains');
		$allowedDomains = explode(';', $allowedDomains);
		$allowedDomains = array_map('trim', $allowedDomains);
		$allowedDomains = array_filter($allowedDomains);
		return array_map('strtolower', $allowedDomains);
	}

	/**
	 * @param Registration $registration
	 * @param string|null $loginName
	 * @param string|null $fullName
	 * @param string|null $phone
	 * @param string|null $password
	 * @return IUser
	 * @throws RegistrationException|InvalidArgumentException
	 */
	public function createAccount(Registration $registration, ?string $loginName = null, ?string $fullName = null, ?string $phone = null, ?string $password = null): IUser {
		if ($loginName === null) {
			$loginName = $registration->getUsername();
		}

		if ($registration->getPassword() !== null) {
			$password = $this->crypto->decrypt($registration->getPassword());
		}

		if (!$password) {
			throw new RegistrationException($this->l10n->t('Please provide a password.'));
		}

		$this->validateUsername($loginName);

		if ($this->appConfig->getAppValueBool('show_fullname')
			&& $this->appConfig->getAppValueBool('enforce_fullname')) {
			$this->validateDisplayname($fullName);
		}

		if (class_exists(PhoneNumberUtil::class)
			&& $this->appConfig->getAppValueBool('show_phone')) {
			if ($phone) {
				$this->validatePhoneNumber($phone);
			} elseif ($this->appConfig->getAppValueBool('enforce_phone')) {
				throw new RegistrationException($this->l10n->t('Please provide a valid phone number.'));
			}
		}

		/* TODO
		 * createUser tests username validity once, but validateUsername already checked it,
		 * but createUser doesn't check if there is a pending registration with that name
		 *
		 * And validateUsername will throw RegistrationException while
		 * createUser throws \InvalidArgumentException
		 */
		$user = $this->userManager->createUser($loginName, $password);
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

		// Set display name
		if ($fullName && $this->appConfig->getAppValueBool('show_fullname')) {
			$user->setDisplayName($fullName);
		}

		// Set phone number in account data
		if (method_exists($this->accountManager, 'updateAccount')
			&& $phone
			&& $this->appConfig->getAppValueBool('show_phone')) {
			$account = $this->accountManager->getAccount($user);
			$property = $account->getProperty(IAccountManager::PROPERTY_PHONE);
			$account->setProperty(
				IAccountManager::PROPERTY_PHONE,
				$phone,
				$property->getScope(),
				IAccountManager::NOT_VERIFIED
			);
			$this->accountManager->updateAccount($account);
		}

		// Add user to group
		$registeredUserGroup = $this->appConfig->getAppValueString('registered_user_group', 'none');
		if ($registeredUserGroup !== 'none') {
			$group = $this->groupManager->get($registeredUserGroup);
			if ($group === null) {
				// This might happen if $registered_user_group is deleted after setting the value
				// Here I choose to log error instead of stopping the user to register
				$this->logger->error("You specified newly registered users be added to '$registeredUserGroup' group, but it does not exist.");
				$groupId = '';
			} else {
				$group->addUser($user);
				$groupId = $group->getGID();
			}
		} else {
			$groupId = '';
		}

		// disable user if this is requested by config
		$adminApprovalRequired = $this->appConfig->getAppValueBool('admin_approval_required');
		if ($adminApprovalRequired) {
			$user->setEnabled(false);
			$this->config->setUserValue($userId, Application::APP_ID, 'send_welcome_mail_on_enable', 'yes');
		} else {
			$this->sendWelcomeMail($user);
		}

		$this->mailService->notifyAdmins($userId, $user->getEMailAddress(), $user->isEnabled(), $groupId);
		return $user;
	}

	public function sendWelcomeMail(IUser $user): void {
		$this->config->deleteUserValue($user->getUID(), Application::APP_ID, 'send_welcome_mail_on_enable');

		if ($this->config->getAppValue('core', 'newUser.sendEmail', 'yes') === 'yes') {
			/** @var NewUserMailHelper $helper */
			$helper = \OC::$server->get(NewUserMailHelper::class);

			try {
				$emailTemplate = $helper->generateTemplate($user);
				$helper->sendMail($user, $emailTemplate);
			} catch (\Exception $e) {
				// Catching this so at least admins are notified
				$this->logger->error(
					'Unable to send the invitation mail to {user}',
					[
						'user' => $user->getUID(),
						'exception' => $e,
					]
				);
			}
		}
	}

	/**
	 * @param string $email
	 * @return Registration
	 * @throws DoesNotExistException
	 */
	public function getRegistrationForEmail(string $email): Registration {
		return $this->registrationMapper->find($email);
	}

	/**
	 * @param string $secret
	 * @return Registration
	 * @throws DoesNotExistException
	 */
	public function getRegistrationForSecret(string $secret): Registration {
		return $this->registrationMapper->findBySecret($secret);
	}

	public function deleteRegistration(Registration $registration): void {
		$this->registrationMapper->delete($registration);
	}

	/**
	 * Return a 25 digit device password
	 *
	 * Example: AbCdE-fGhIj-KlMnO-pQrSt-12345
	 *
	 * @return string
	 */
	private function generateRandomDeviceToken(): string {
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
	public function generateAppPassword(string $uid): string {
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
	 * @param string $userId
	 * @param string $username
	 * @param string $password
	 * @param bool $decrypt
	 */
	public function loginUser(string $userId, string $username, string $password, bool $decrypt = false): void {
		if ($decrypt) {
			$password = $this->crypto->decrypt($password);
		}

		$this->userSession->login($username, $password);
		$this->userSession->createSessionToken($this->request, $userId, $username, $password);
	}
}
