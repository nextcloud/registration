<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Controller;

use OCA\Registration\Db\Invitation;
use OCA\Registration\Service\InvitationService;
use OCA\Registration\Service\RegistrationException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\AdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IRequest;

class InvitationController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private InvitationService $invitationService,
		private IL10N $l10n,
	) {
		parent::__construct($appName, $request);
	}

	#[AdminRequired]
	public function index(): DataResponse {
		$invitations = array_map(
			fn(Invitation $invitation) => $this->serialize($invitation),
			$this->invitationService->getAll()
		);

		return new DataResponse($invitations);
	}

	#[AdminRequired]
	public function create(string $code = '', string $email = '', string $domain = '', string $quota = '', string $max_uses = '', string $expires = '', string $skip_email_verification = ''): DataResponse {
		if ($code === '') {
			$code = $this->invitationService->generateCode();
		}

		try {
			$invitation = $this->invitationService->createInvitation(
				$code,
				$email,
				$domain,
				$quota,
				$max_uses !== '' ? (int)$max_uses : null,
				$expires !== '' ? $expires : null,
				$skip_email_verification === 'true' || $skip_email_verification === '1'
			);
		} catch (RegistrationException $e) {
			return new DataResponse(
				[
					'status' => 'error',
					'message' => $e->getMessage(),
				],
				Http::STATUS_BAD_REQUEST
			);
		}

		return new DataResponse($this->serialize($invitation));
	}

	#[AdminRequired]
	public function destroy(int $id): DataResponse {
		$this->invitationService->deleteById($id);

		return new DataResponse([
			'status' => 'success',
		]);
	}

	private function serialize(Invitation $invitation): array {
		return [
			'id' => $invitation->getId(),
			'code' => $invitation->getCode(),
			'email' => $invitation->getEmail(),
			'domain' => $invitation->getDomain(),
			'quota' => $invitation->getQuota(),
			'max_uses' => $invitation->getMaxUses(),
			'uses' => $invitation->getUses(),
			'expires' => $invitation->getExpires(),
			'created_at' => $invitation->getCreatedAt(),
			'skip_email_verification' => $invitation->getSkipEmailVerification(),
			'link' => $this->invitationService->generateLink($invitation),
		];
	}
}