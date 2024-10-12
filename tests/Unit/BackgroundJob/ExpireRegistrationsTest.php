<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\Registration\Tests\Unit\BackgroundJob;

use OCA\Registration\AppInfo\Application;
use OCA\Registration\BackgroundJob\ExpireRegistrations;
use OCA\Registration\Db\RegistrationMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class ExpireRegistrationsTest extends TestCase {

	/** @var RegistrationMapper | MockObject */
	private $registrationMapper;

	/** @var ITimeFactory | MockObject */
	private $timeFactory;

	/** @var IConfig|MockObject */
	private $config;

	private ExpireRegistrations $backgroundJob;

	protected function setUp(): void {
		parent::setUp();

		$this->registrationMapper = $this->createMock(RegistrationMapper::class);
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->config = $this->createMock(IConfig::class);

		$this->backgroundJob = new ExpireRegistrations($this->timeFactory, $this->registrationMapper, $this->config);
	}

	public function testRun() {
		$this->config->expects($this->once())
			->method('getAppValue')
			->with(Application::APP_ID, 'expire_days', '30')
			->willReturn('20');

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
