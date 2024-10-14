<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
