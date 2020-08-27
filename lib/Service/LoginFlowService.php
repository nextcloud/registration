<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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

	/** @var IRequest */
	protected $request;
	/** @var ISession */
	protected $session;
	/** @var LoginFlowV2Service */
	protected $loginFlowV2Service;

	public function __construct(
		IRequest $request,
		ISession $session,
		LoginFlowV2Service $loginFlowV2Service
	) {
		$this->request = $request;
		$this->session = $session;
		$this->loginFlowV2Service = $loginFlowV2Service;
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

		if (strpos($this->request->getRequestUri(), '/index.php') !== false) {
			$serverPostfix = substr($this->request->getRequestUri(), 0, strpos($this->request->getRequestUri(), '/index.php'));
		} elseif (strpos($this->request->getRequestUri(), '/login/v2') !== false) {
			$serverPostfix = substr($this->request->getRequestUri(), 0, strpos($this->request->getRequestUri(), '/login/v2'));
		}

		$protocol = $this->request->getServerProtocol();
		return $protocol . '://' . $this->request->getServerHost() . $serverPostfix;
	}
}
