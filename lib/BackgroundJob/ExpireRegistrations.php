<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Registration\BackgroundJob;

use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class ExpireRegistrations extends TimedJob {

	public function __construct(
		ITimeFactory $time,
		protected RegistrationMapper $registrationMapper,
		protected IAppConfig $config,
	) {
		parent::__construct($time);

		// Run once per day
		$this->setInterval(60 * 60 * 24);
		$this->setTimeSensitivity(self::TIME_INSENSITIVE);
	}

	#[\Override]
	public function run($argument): void {
		$expireDays = $this->getDuration();
		$expireDate = $this->time->getDateTime();
		$interval = new \DateInterval('P' . $expireDays . 'D');
		$expireDate->sub($interval);

		$this->registrationMapper->deleteOlderThan($expireDate);
	}

	private function getDuration(): int {
		return max($this->config->getAppValueInt(
			'expire_days',
			30
		),
			1
		);
	}
}
