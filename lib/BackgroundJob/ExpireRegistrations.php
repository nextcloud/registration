<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Registration\BackgroundJob;

use OCA\Registration\AppInfo\Application;
use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;

class ExpireRegistrations extends TimedJob {

	protected RegistrationMapper $registrationMapper;
	protected IConfig $config;

	public function __construct(ITimeFactory $time,
		RegistrationMapper $registrationMapper,
		IConfig $config) {
		parent::__construct($time);

		// Run once per day
		$this->setInterval(60 * 60 * 24);
		$this->setTimeSensitivity(self::TIME_INSENSITIVE);

		$this->config = $config;
		$this->registrationMapper = $registrationMapper;
	}

	public function run($argument): void {
		$expireDays = $this->getDuration();
		$expireDate = $this->time->getDateTime();
		$interval = new \DateInterval('P' . $expireDays . 'D');
		$expireDate->sub($interval);

		$this->registrationMapper->deleteOlderThan($expireDate);
	}

	private function getDuration(): int {
		return max(
			(int)$this->config->getAppValue(
				Application::APP_ID,
				'expire_days',
				'30'
			),
			1
		);
	}
}
