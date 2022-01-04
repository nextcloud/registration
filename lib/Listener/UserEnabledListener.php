<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Registration\Listener;

use OCA\Registration\AppInfo\Application;
use OCA\Registration\Service\RegistrationService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\User\Events\UserChangedEvent;

class UserEnabledListener implements IEventListener {
	/** @var IConfig */
	private $config;
	/** @var RegistrationService */
	private $registrationService;

	public function __construct(IConfig $config,
								RegistrationService $registrationService) {
		$this->config = $config;
		$this->registrationService = $registrationService;
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
