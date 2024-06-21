<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Joas Schilling <coding@schilljs.com>
 *
 * @author Thomas Citharel <nextcloud@tcit.fr>
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

namespace OCA\Registration;

use OCP\Authentication\IAlternativeLogin;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;

class RegistrationLoginOption implements IAlternativeLogin {

	public function __construct(protected IConfig $config, protected IURLGenerator $url, protected IL10N $l, protected \OC_Defaults $theming) {
		$this->config = $config;
	}

	public function getLabel(): string {
		if ($this->config->getAppValue('registration', 'login_button_hide', 'no') === 'no') {
			return $this->l->t('Register');
		}
	}

	public function getLink(): string {
		if ($this->config->getAppValue('registration', 'login_button_hide', 'no') === 'no') {
			return $this->url->linkToRoute('registration.register.showEmailForm');
		}
	}

	public function getClass(): string {
		if ($this->config->getAppValue('registration', 'login_button_hide', 'no') === 'no') {
			return 'register-button';
		}
	}

	public function load(): void {
	}
}
