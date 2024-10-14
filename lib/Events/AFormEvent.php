<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Events;

use OCP\EventDispatcher\Event;

abstract class AFormEvent extends Event {
	public const STEP_EMAIL = 'email';
	public const STEP_VERIFICATION = 'verification';
	public const STEP_USER = 'user';

	public function __construct(protected string $step, protected string $registrationId = '') {
		parent::__construct();
	}

	public function getStep(): string {
		return $this->step;
	}

	public function getRegistrationIdentifier(): string {
		return $this->registrationId;
	}
}
