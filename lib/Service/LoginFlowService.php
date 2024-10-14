<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Service;

use OC\Core\Controller\ClientFlowLoginController;
use OC\Core\Controller\ClientFlowLoginV2Controller;
use OC\Core\Service\LoginFlowV2Service;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\StandaloneTemplateResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;

class LoginFlowService {

	public function __construct(
		protected IRequest $request,
		protected ISession $session,
		protected LoginFlowV2Service $loginFlowV2Service
	) {
	}

	public function isUsingLoginFlow(?int $version = null): bool {
		if (($version === 1 || $version === null) && $this->session->get(ClientFlowLoginController::STATE_NAME) !== null) {
			return true;
		}

		if (($version === 2 || $version === null) && $this->session->get(ClientFlowLoginV2Controller::TOKEN_NAME) !== null) {
			return true;
		}

		return false;
	}

	public function tryLoginFlowV1(): ?Response {
		/** @var ClientFlowLoginController $controller */
		$container = \OC::$server->getRegisteredAppContainer('core');
		$controller = $container->query(ClientFlowLoginController::class);
		return $controller->generateAppPassword(
			$this->session->get(ClientFlowLoginController::STATE_NAME)
		);
	}

	public function tryLoginFlowV2(IUser $user): ?StandaloneTemplateResponse {
		$result = $this->loginFlowV2Service->flowDone(
			$this->session->get(ClientFlowLoginV2Controller::TOKEN_NAME),
			$this->session->getId(),
			$this->getServerPath(),
			$user->getUID()
		);

		if (!$result) {
			return null;
		}

		return new StandaloneTemplateResponse(
			'core',
			'loginflowv2/done',
			[],
			'guest'
		);
	}

	private function getServerPath(): string {
		$serverPostfix = '';

		if (str_contains($this->request->getRequestUri(), '/index.php')) {
			$serverPostfix = substr($this->request->getRequestUri(), 0, strpos($this->request->getRequestUri(), '/index.php'));
		} elseif (str_contains($this->request->getRequestUri(), '/login/v2')) {
			$serverPostfix = substr($this->request->getRequestUri(), 0, strpos($this->request->getRequestUri(), '/login/v2'));
		}

		$protocol = $this->request->getServerProtocol();
		return $protocol . '://' . $this->request->getServerHost() . $serverPostfix;
	}
}
