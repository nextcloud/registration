<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 * @copyright Copyright (c) 2017 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
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

use OCA\Registration\Db\Registration;
use OCP\Defaults;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Mail\IMailer;
use OCP\Util;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class MailService {

	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IMailer */
	private $mailer;
	/** @var Defaults */
	private $defaults;
	/** @var IL10N */
	private $l10n;
	/** @var IGroupManager */
	private $groupManager;
	/** @var LoginFlowService */
	private $loginFlowService;
	/** @var LoggerInterface */
	private $logger;
	/** @var IConfig */
	private $config;

	public function __construct(IURLGenerator $urlGenerator,
								IMailer $mailer,
								Defaults $defaults,
								IL10N $l10n,
								IGroupManager $groupManager,
								IConfig $config,
								LoginFlowService $loginFlowService,
								LoggerInterface $logger) {
		$this->urlGenerator = $urlGenerator;
		$this->mailer = $mailer;
		$this->config = $config;
		$this->defaults = $defaults;
		$this->l10n = $l10n;
		$this->groupManager = $groupManager;
		$this->loginFlowService = $loginFlowService;
		$this->logger = $logger;
	}

	/**
	 * @param string $email
	 * @throws RegistrationException
	 */
	public function validateEmail(string $email): void {
		if (!$this->mailer->validateMailAddress($email)) {
			throw new RegistrationException($this->l10n->t('The email address you entered is not valid'));
		}
	}

	/**
	 * @param Registration $registration
	 * @throws RegistrationException
	 */
	public function sendTokenByMail(Registration $registration): void {
		$link = $this->urlGenerator->linkToRouteAbsolute('registration.register.showUserForm', [
			'secret' => $registration->getClientSecret(),
			'token' => $registration->getToken(),
		]);
		$subject = $this->l10n->t('Verify your %s registration request', [$this->defaults->getName()]);

		$template = $this->mailer->createEMailTemplate('registration_verify', [
			'link' => $link,
			'token' => $registration->getToken(),
			'sitename' => $this->defaults->getName(),
		]);

		$template->setSubject($subject);
		$template->addHeader();
		$template->addHeading($this->l10n->t('Registration'));

		$body = $this->l10n->t('Email address verified, you can now complete your registration.');
		if (!$this->loginFlowService->isUsingLoginFlow()) {
			$template->addBodyText(
				htmlspecialchars($body . ' ' . $this->l10n->t('Click the button below to continue.')),
				$body
			);
		} else {
			$template->addBodyText(
				$body
			);
		}

		// if the parameter is set through the settings panel add to body text
		$email_verification_hint = $this->config->getAppValue('registration', 'email_verification_hint');
		if (!empty($email_verification_hint)) {
			$template->addBodyText($email_verification_hint);
		};

		$template->addBodyText(
			$this->l10n->t('Verification code: %s', $registration->getToken())
		);

		if (!$this->loginFlowService->isUsingLoginFlow()) {
			$template->addBodyButton(
				$this->l10n->t('Continue registration'),
				$link
			);
		}
		$template->addFooter();

		$from = Util::getDefaultEmailAddress('register');
		$message = $this->mailer->createMessage();
		$message->setFrom([$from => $this->defaults->getName()]);
		$message->setTo([$registration->getEmail()]);
		$message->useTemplate($template);
		$failed_recipients = $this->mailer->send($message);
		if (!empty($failed_recipients)) {
			throw new RegistrationException($this->l10n->t('A problem occurred sending email, please contact your administrator.'));
		}
	}

	public function notifyAdmins(string $userId, string $userEMailAddress, bool $userIsEnabled, string $userGroupId): void {
		// Notify admin
		$adminUsers = $this->groupManager->get('admin')->getUsers();

		// if the user is disabled and belongs to a group
		// add subadmins of this group to notification list
		if (!$userIsEnabled && $userGroupId) {
			$group = $this->groupManager->get($userGroupId);
			$subAdmins = $this->groupManager->getSubAdmin()->getGroupsSubAdmins($group);
			foreach ($subAdmins as $subAdmin) {
				if (!in_array($subAdmin, $adminUsers, true)) {
					$adminUsers[] = $subAdmin;
				}
			}
		}

		$toArr = [];
		foreach ($adminUsers as $adminUser) {
			$email = $adminUser->getEMailAddress();
			if ($email && $adminUser->isEnabled()) {
				$toArr[$email] = $adminUser->getDisplayName();
			}
		}

		try {
			$this->sendNewUserNotifyEmail($toArr, $userId, $userEMailAddress, $userIsEnabled);
		} catch (\Exception $e) {
			$this->logger->error('Sending admin notification email failed: '. $e->getMessage());
		}
	}

	/**
	 * Sends new user notification email to given user list
	 *
	 * @param array $to
	 * @param string $username the new user
	 * @param bool $userIsEnabled the new user account is enabled
	 * @throws \Exception
	 */
	private function sendNewUserNotifyEmail(array $to, string $username, string $userEMailAddress, bool $userIsEnabled): void {
		$link = $this->urlGenerator->linkToRouteAbsolute('settings.Users.usersListByGroup', [
			'group' => 'disabled',
		]);
		$template = $this->mailer->createEMailTemplate('registration_admin', [
			'link' => $link,
			'user' => $username,
			'sitename' => $this->defaults->getName(),
		]);

		$subject = $this->l10n->t('New user "%s" has created an account on %s', [$username, $this->defaults->getName()]);

		$template->setSubject($subject);
		$template->addHeader();
		$template->addHeading($this->l10n->t('New user registered'));

		if ($userIsEnabled) {
			$template->addBodyText(
				$this->l10n->t('"%1$s" (%2$s) registered a new account on %3$s.', [
					$username,
					$userEMailAddress,
					$this->defaults->getName(),
				])
			);
		} else {
			$template->addBodyText(
				$this->l10n->t('"%1$s" (%2$s) registered a new account on %3$s and needs to be enabled.', [
					$username,
					$userEMailAddress,
					$this->defaults->getName(),
				])
			);

			$template->addBodyButton(
				$this->l10n->t('Enable now'),
				$link
			);
		}
		$template->addFooter();

		$from = Util::getDefaultEmailAddress('register');
		$message = $this->mailer->createMessage();
		$message->setFrom([$from => $this->defaults->getName()]);
		$message->setTo([]);
		$message->setBcc($to);
		$message->useTemplate($template);
		$failedRecipients = $this->mailer->send($message);
		if (!empty($failedRecipients)) {
			throw new RegistrationException('Failed recipients: ' . print_r($failedRecipients, true));
		}
	}
}
