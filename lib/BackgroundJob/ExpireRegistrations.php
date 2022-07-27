<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Thomas Citharel <nextcloud@tcit.fr>
 *
 * @author Thomas Citharel <nextcloud@tcit.fr>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Registration\BackgroundJob;

use OCA\Registration\AppInfo\Application;
use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;

class ExpireRegistrations extends TimedJob {

	/** @var RegistrationMapper */
	protected $registrationMapper;

	/** @var IConfig */
	protected $config;

	public function __construct(ITimeFactory $time,
		RegistrationMapper $registrationMapper,
		IConfig $config) {
		parent::__construct($time);

		// Run once per day
		$this->setInterval(60 * 60 * 24);
		/**
		 * @TODO Remove check with 24+
		 */
		if (method_exists($this, 'setTimeSensitivity')) {
			$this->setTimeSensitivity(self::TIME_INSENSITIVE);
		}

		$this->config = $config;
		$this->registrationMapper = $registrationMapper;
	}

	public function run($argument): void {
		$expireDays = $this->getDuration();
		$expireDate = $this->time->getDateTime();
		$interval = new \DateInterval("P" . $expireDays . "D");
		$expireDate->sub($interval);

		$this->registrationMapper->deleteOlderThan($expireDate);
	}

	private function getDuration(): int {
		return max(
			(int) $this->config->getAppValue(
				Application::APP_ID,
				'expire_days',
				'30'
			),
			1
		);
	}
}
