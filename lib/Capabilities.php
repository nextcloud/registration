<?php

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration;

use OCP\Capabilities\IPublicCapability;
use OCP\IURLGenerator;

class Capabilities implements IPublicCapability {

	public function __construct(
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getCapabilities(): array {
		return [
			'registration' =>
			[
				'enabled' => true,
				'apiRoot' => $this->urlGenerator->linkTo(
					'', 'ocs/v2.php/apps/registration/api/v1/'),
				'apiLevel' => 'v1'
			]
		];
	}
}
