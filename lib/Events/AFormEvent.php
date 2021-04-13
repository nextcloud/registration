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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Registration\Events;

use OCP\EventDispatcher\Event;

abstract class AFormEvent extends Event {
	public const STEP_EMAIL = 'email';
	public const STEP_VERIFICATION = 'verification';
	public const STEP_USER = 'user';

	/** @var string */
	protected $step;

	/** @var string */
	protected $registrationId;

	public function __construct(string $step, string $registrationId = '') {
		parent::__construct();
		$this->step = $step;
		$this->registrationId = $registrationId;
	}

	public function getStep(): string {
		return $this->step;
	}

	public function getRegistrationIdentifier(): string {
		return $this->registrationId;
	}
}
