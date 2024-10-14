<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Listener;

use OCA\Registration\AppInfo\Application;
use OCA\Registration\Service\RegistrationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\User\Events\UserChangedEvent;

class UserEnabledListener implements IEventListener {

	public function __construct(private IConfig $config,
		private RegistrationService $registrationService) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserChangedEvent)) {
			// Unrelated
			return;
		}

		if ($event->getFeature() !== 'enabled') {
			// Unrelated
			return;
		}

		$user = $event->getUser();
		$value = $this->config->getUserValue($user->getUID(), Application::APP_ID, 'send_welcome_mail_on_enable', 'no');
		if ($value === 'yes') {
			$this->registrationService->sendWelcomeMail($event->getUser());
		}
	}
}
