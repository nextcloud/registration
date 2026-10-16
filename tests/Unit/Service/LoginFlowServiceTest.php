<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Tests\Unit\Service;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OC\Core\Controller\ClientFlowLoginV2Controller;
use OC\Core\Service\LoginFlowV2Service;
use OCA\Registration\Service\LoginFlowService;
use OCP\IInitialStateService;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;

class LoginFlowServiceTest extends TestCase {
	private IRequest&MockObject $request;
	private ISession&MockObject $session;
	private LoginFlowV2Service&MockObject $loginFlowV2Service;
	private IInitialStateService&MockObject $initialStateService;
	private LoginFlowService $service;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->session = $this->createMock(ISession::class);
		$this->loginFlowV2Service = $this->createMock(LoginFlowV2Service::class);
		$this->initialStateService = $this->createMock(IInitialStateService::class);

		$this->request->method('getRequestUri')->willReturn('/index.php/apps/registration/register/secret/token');
		$this->request->method('getServerProtocol')->willReturn('https');
		$this->request->method('getServerHost')->willReturn('cloud.example.com');
		$this->session->method('get')
			->with(ClientFlowLoginV2Controller::TOKEN_NAME)
			->willReturn('login-token');
		$this->session->method('getId')->willReturn('session-id');

		$this->service = new LoginFlowService(
			$this->request,
			$this->session,
			$this->loginFlowV2Service,
			$this->initialStateService,
		);
	}

	public function testTryLoginFlowV2Done(): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');

		$this->loginFlowV2Service->expects($this->once())
			->method('flowDone')
			->with('login-token', 'session-id', 'https://cloud.example.com', 'alice')
			->willReturn(true);
		$this->initialStateService->expects($this->once())
			->method('provideInitialState')
			->with('core', 'loginFlowState', 'done');

		$response = $this->service->tryLoginFlowV2($user);

		$this->assertNotNull($response);
		$this->assertSame('loginflow', $response->getTemplateName());
		$this->assertSame('core', $response->getApp());
		$this->assertSame('guest', $response->getRenderAs());
	}

	public function testTryLoginFlowV2Failed(): void {
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn('alice');

		$this->loginFlowV2Service->method('flowDone')->willReturn(false);
		$this->initialStateService->expects($this->never())
			->method('provideInitialState');

		$this->assertNull($this->service->tryLoginFlowV2($user));
	}
}
