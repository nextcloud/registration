<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Jonas Rittershofer <jotoeri@users.noreply.github.com>
 *
 * @author Jonas Rittershofer <jotoeri@users.noreply.github.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Registration\Settings;

use OCA\Registration\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class RegistrationSettingsSection implements IIconSection {
	public function __construct(private IL10N $l10n, private IURLGenerator $urlGenerator) {
	}

	/**
	 * Section ID to be set in Settings
	 * @return string
	 */
	public function getID(): string {
		return Application::APP_ID;
	}

	/**
	 * Section Name to be displayed
	 * @return string
	 */
	public function getName(): string {
		return $this->l10n->t('Registration');
	}

	/**
	 * Return Priority of section 0-100
	 * @return int
	 */
	public function getPriority(): int {
		return 80;
	}

	/**
	 * Pass the relative path to the icon
	 * @return string
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}
}
