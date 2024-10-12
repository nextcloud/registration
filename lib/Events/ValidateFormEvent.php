<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Events;

class ValidateFormEvent extends AFormEvent {

	/** @var string[] */
	protected array $errors;

	public function __construct(string $step, string $registrationId = '') {
		parent::__construct($step, $registrationId);
		$this->errors = [];
	}

	public function addError(string $error): void {
		$this->errors[] = $error;
	}

	public function getErrors(): array {
		return $this->errors;
	}
}
