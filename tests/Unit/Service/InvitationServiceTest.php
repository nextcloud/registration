<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\DatabaseTransaction;
use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\Registration\Db\Invitation;
use OCA\Registration\Db\InvitationMapper;
use OCA\Registration\Service\InvitationService;
use OCA\Registration\Service\RegistrationException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IDBConnection;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group DB
 */
class InvitationServiceTest extends TestCase {
	use DatabaseTransaction;

	private InvitationService $service;
	private ITimeFactory&MockObject $timeFactory;

	public function setUp(): void {
		parent::setUp();
		$random = \OC::$server->get(ISecureRandom::class);
		$mapper = new InvitationMapper(
			\OC::$server->get(IDBConnection::class),
			$random
		);
		$urlGenerator = $this->createMock(IURLGenerator::class);
		$urlGenerator->method('linkToRouteAbsolute')
			->willReturn('https://example.com/apps/registration/invite/CODE');
		$l10n = $this->createMock(IL10N::class);
		$l10n->method('t')
			->willReturnCallback(function ($text, $parameters = []) {
				return vsprintf($text, $parameters);
			});
		$this->timeFactory = $this->createMock(ITimeFactory::class);
		$this->timeFactory->method('getTime')
			->willReturn(1000000000);

		$this->service = new InvitationService(
			$mapper,
			$urlGenerator,
			$l10n,
			$this->timeFactory,
		);
	}

	private function createInvitation(array $overrides = []): Invitation {
		$params = array_merge([
			'code' => 'TESTCODE',
			'email' => '',
			'domain' => '',
			'quota' => '5 GB',
			'max_uses' => '',
			'expires' => '',
		], $overrides);

		return $this->service->createInvitation(
			$params['code'],
			$params['email'],
			$params['domain'],
			$params['quota'],
			$params['max_uses'] !== '' ? (int)$params['max_uses'] : null,
			$params['expires'] !== '' ? $params['expires'] : null,
		);
	}

	public function testCreateAndGetByCode(): void {
		$invitation = $this->createInvitation();

		$found = $this->service->getByCode('TESTCODE');

		self::assertSame('TESTCODE', $found->getCode());
		self::assertSame('5 GB', $found->getQuota());
		self::assertSame(0, $found->getUses());
		self::assertNotNull($invitation->getId());
	}

	public function testCreateDuplicateCodeThrows(): void {
		$this->createInvitation();

		$this->expectException(RegistrationException::class);
		$this->createInvitation(['quota' => '']);
	}

	public function testCreateEmptyCodeThrows(): void {
		$this->expectException(RegistrationException::class);
		$this->createInvitation(['code' => '']);
	}

	public function testGetUnknownCodeThrows(): void {
		$this->expectException(DoesNotExistException::class);
		$this->service->getByCode('UNKNOWN');
	}

	public function testDeleteById(): void {
		$invitation = $this->createInvitation();
		$this->service->deleteById($invitation->getId());

		$this->expectException(DoesNotExistException::class);
		$this->service->getByCode('TESTCODE');
	}

	public function testValidateOk(): void {
		$invitation = $this->createInvitation();
		$this->service->validate($invitation, 'user@example.com');
		self::assertTrue(true);
	}

	public function testValidateEmailRestriction(): void {
		$invitation = $this->createInvitation(['email' => 'foo@example.com']);
		$this->service->validate($invitation, 'foo@example.com');

		$this->expectException(RegistrationException::class);
		$this->service->validate($invitation, 'bar@example.com');
	}

	public function testValidateDomainRestriction(): void {
		$invitation = $this->createInvitation(['domain' => 'example.com']);
		$this->service->validate($invitation, 'foo@example.com');

		$this->expectException(RegistrationException::class);
		$this->service->validate($invitation, 'foo@example.tld');
	}

	public function testValidateExpired(): void {
		$invitation = $this->createInvitation(['expires' => '2000-01-01 00:00:00']);

		$this->expectException(RegistrationException::class);
		$this->service->validate($invitation, 'foo@example.com');
	}

	public function testValidateMaxUsesReached(): void {
		$invitation = $this->createInvitation(['max_uses' => '1']);
		$this->service->incrementUses($invitation);

		$this->expectException(RegistrationException::class);
		$this->service->validate($invitation, 'foo@example.com');
	}

	public function testIncrementUses(): void {
		$invitation = $this->createInvitation(['max_uses' => '2']);
		$this->service->incrementUses($invitation);

		$found = $this->service->getById($invitation->getId());
		self::assertSame(1, $found->getUses());
	}

	public function testGenerateLink(): void {
		$invitation = $this->createInvitation();
		self::assertSame('https://example.com/apps/registration/invite/CODE', $this->service->generateLink($invitation));
	}
}
