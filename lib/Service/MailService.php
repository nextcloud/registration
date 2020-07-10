<?php
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
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\Mail\IMailer;
use OCP\Util;

class MailService {

	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IMailer */
	private $mailer;
	/** @var Defaults */
	private $defaults;
	/** @var IL10N */
	private $l10n;
	/** @var IConfig */
	private $config;
	/** @var IGroupManager */
	private $groupManager;
	/** @var ILogger */
	private $logger;

	public function __construct(IURLGenerator $urlGenerator, IMailer $mailer, Defaults $defaults, IL10N $l10n, IConfig $config, IGroupManager $groupManager, ILogger $logger) {
		$this->urlGenerator = $urlGenerator;
		$this->mailer = $mailer;
		$this->defaults = $defaults;
		$this->l10n = $l10n;
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->logger = $logger;
	}

	/**
	 * @param string $email
	 * @throws RegistrationException
	 */
	public function validateEmail($email) {
		if (!$this->mailer->validateMailAddress($email)) {
			throw new RegistrationException($this->l10n->t('The email address you entered is not valid'));
		}
	}

	/**
	 * @param Registration $registration
	 * @throws RegistrationException
	 */
	public function sendTokenByMail(Registration $registration) {
		$link = $this->urlGenerator->linkToRouteAbsolute('registration.register.verifyToken', ['token' => $registration->getToken()]);
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
		$template->addBodyText(
			htmlspecialchars($body . ' ' . $this->l10n->t('Click the button below to continue.')),
			$body
		);

		$template->addBodyButton(
			$this->l10n->t('Continue registration'),
			$link
		);
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

	/**
	 * @param string $userId
	 * @param string $userGroupId
	 * @param bool $userIsEnabled
	 */
	public function notifyAdmins($userId, $userIsEnabled, $userGroupId) {
		// Notify admin
		$admin_users = $this->groupManager->get('admin')->getUsers();

		// if the user is disabled and belongs to a group
		// add subadmins of this group to notification list
		if (!$userIsEnabled and $userGroupId) {
			$group = $this->groupManager->get($userGroupId);
			$subadmin_users = $this->groupManager->getSubAdmin()->getGroupsSubAdmins($group);
			foreach ($subadmin_users as $user) {
				if (!in_array($user, $admin_users)) {
					$admin_users[] = $user;
				}
			}
		}

		$to_arr = [];
		foreach ($admin_users as $au) {
			$au_email = $au->getEMailAddress();
			if ($au_email && $au->isEnabled()) {
				$to_arr[$au_email] = $au->getDisplayName();
			}
		}
		try {
			$this->sendNewUserNotifEmail($to_arr, $userId, $userIsEnabled);
		} catch (\Exception $e) {
			$this->logger->error('Sending admin notification email failed: '. $e->getMessage());
		}
	}

	/**
	 * Sends new user notification email to admin
	 * @param array $to
	 * @param string $username the new user
	 * @param bool $userIsEnabled the new user account is enabled
	 * @throws \Exception
	 */
	private function sendNewUserNotifEmail(array $to, $username, $userIsEnabled) {
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
				$this->l10n->t('"%1$s" registered a new account on %2$s.', [
					$username,
					$this->defaults->getName(),
				])
			);
		} else {
			$template->addBodyText(
				$this->l10n->t('"%1$s" registered a new account on %2$s and needs to be enabled.', [
					$username,
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
		$failed_recipients = $this->mailer->send($message);
		if (!empty($failed_recipients)) {
			throw new RegistrationException('Failed recipients: '.print_r($failed_recipients, true));
		}
	}
}
