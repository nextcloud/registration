<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Events;

use OCP\IUser;

class PassedFormEvent extends AFormEvent {

	public function __construct(
		string $step,
		string $registrationId = '',
		protected ?IUser $user = null,
	) {
		parent::__construct($step, $registrationId);
	}

	public function getUser(): ?IUser {
		return $this->user;
	}
}
