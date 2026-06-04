<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\Registration\Tests\Unit\BackgroundJob;

use OCA\Registration\BackgroundJob\ExpireRegistrations;
use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ExpireRegistrationsTest extends TestCase {

	private RegistrationMapper&MockObject $registrationMapper;
	private ITimeFactory&MockObject $timeFactory;
	private IAppConfig&MockObject $config;

	private ExpireRegistrations $backgroundJob;

	protected function setUp(): void {
		parent::setUp();

		$this->registrationMapper = $this->createMock(RegistrationMapper::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->config = $this->createMock(IAppConfig::class);

		$this->backgroundJob = new ExpireRegistrations($this->timeFactory, $this->registrationMapper, $this->config);
	}

	public function testRun() {
		$this->config->expects($this->once())
			->method('getAppValueInt')
			->with('expire_days', 30)
			->willReturn(20);

		$expireDate = new \DateTime();
		$this->timeFactory->expects($this->once())
			->method('getDateTime')
			->with()
			->willReturn($expireDate);

		$interval = new \DateInterval('P20D');
		$expireDate->sub($interval);

		$this->registrationMapper->expects($this->once())
			->method('deleteOlderThan')
			->with($expireDate);

		$this->backgroundJob->run([]);
	}
}
