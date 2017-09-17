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
use OCP\AppFramework\Http\TemplateResponse;
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
		if ( !$this->mailer->validateMailAddress($email) ) {
			throw new RegistrationException($this->l10n->t('The email address you entered is not valid'));
		}
	}

	/**
	 * @param Registration $registration
	 * @return bool
	 * @throws RegistrationException
	 */
	public function sendTokenByMail(Registration $registration) {
		$link = $this->urlGenerator->linkToRoute('registration.register.verifyToken', array('token' => $registration->getToken()));
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
		$message->setTo([$registration->getEmail()]);
		$message->setSubject($subject);
		$message->setPlainBody($plaintext_part);
		$message->setHtmlBody($html_part);
		$failed_recipients = $this->mailer->send($message);
		if ( !empty($failed_recipients) ) {
			throw new RegistrationException($this->l10n->t('A problem occurred sending email, please contact your administrator.'));
		}
	}

	/**
	 * @param string $userId
	 */
	public function notifyAdmins($userId) {
		// Notify admin
		$admin_users = $this->groupManager->get('admin')->getUsers();
		$to_arr = array();
		foreach ( $admin_users as $au ) {
			$au_email = $au->getEMailAddress();
			if ( $au_email !== '' && $au->isEnabled()) {
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

}
