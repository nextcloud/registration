<?php
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Service;

class RegistrationException extends \Exception {
	public function __construct(
		string $message,
		protected string $hint = '',
		int $code = 400,
	) {
		parent::__construct($message, $code);
	}

	public function getHint(): string {
		return $this->hint;
	}
}
