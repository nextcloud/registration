<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Service;

use OCA\Registration\Db\Invitation;
use OCA\Registration\Db\InvitationMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IL10N;
use OCP\IURLGenerator;

class InvitationService {

	public function __construct(
		private InvitationMapper $invitationMapper,
		private IURLGenerator $urlGenerator,
		private IL10N $l10n,
		private ITimeFactory $timeFactory,
	) {
	}

	/**
	 * @param string $code
	 * @param string|null $email
	 * @param string|null $domain
	 * @param string|null $quota
	 * @param int|null $maxUses
	 * @param string|null $expires (Y-m-d H:i:s or null)
	 * @param bool $skipEmailVerification
	 * @param bool $skipAdminApproval
	 * @return Invitation
	 * @throws RegistrationException
	 */
	public function createInvitation(string $code, ?string $email, ?string $domain, ?string $quota, ?int $maxUses, ?string $expires, bool $skipEmailVerification = false, bool $skipAdminApproval = false): Invitation {
		$code = trim($code);
		if ($code === '') {
			throw new RegistrationException($this->l10n->t('Please provide an invitation code.'));
		}

		try {
			$this->invitationMapper->findByCode($code);
			throw new RegistrationException($this->l10n->t('An invitation with this code already exists.'));
		} catch (DoesNotExistException $e) {
		}

		if ($maxUses !== null && $maxUses < 1) {
			throw new RegistrationException($this->l10n->t('The maximum number of uses needs to be at least one.'));
		}

		$invitation = new Invitation();
		$invitation->setCode($code);
		$invitation->setEmail($email !== null && $email !== '' ? strtolower($email) : null);
		$invitation->setDomain($domain !== null && $domain !== '' ? strtolower($domain) : null);
		$invitation->setQuota($quota !== null && $quota !== '' ? $quota : null);
		$invitation->setMaxUses($maxUses);
		$invitation->setUses(0);
		$invitation->setExpires($expires);
		$invitation->setSkipEmailVerification($skipEmailVerification);
		$invitation->setSkipAdminApproval($skipAdminApproval);

		return $this->invitationMapper->insert($invitation);
	}

	/**
	 * @param string $code
	 * @return Invitation
	 * @throws DoesNotExistException
	 */
	public function getByCode(string $code): Invitation {
		return $this->invitationMapper->findByCode($code);
	}

	/**
	 * @param int $id
	 * @return Invitation
	 * @throws DoesNotExistException
	 */
	public function getById(int $id): Invitation {
		return $this->invitationMapper->findById($id);
	}

	/**
	 * @return Invitation[]
	 */
	public function getAll(): array {
		return $this->invitationMapper->findAllInvitations();
	}

	public function deleteById(int $id): void {
		$this->invitationMapper->deleteById($id);
	}

	public function generateCode(): string {
		return $this->invitationMapper->generateCode();
	}

	public function generateLink(Invitation $invitation): string {
		return $this->urlGenerator->linkToRouteAbsolute('registration.register.showInviteForm', [
			'code' => $invitation->getCode(),
		]);
	}

	public function isExpired(Invitation $invitation): bool {
		$expires = $invitation->getExpires();
		if ($expires === null) {
			return false;
		}

		if ($expires instanceof \DateTimeInterface) {
			$expireTimestamp = $expires->getTimestamp();
		} else {
			$expireTimestamp = strtotime((string)$expires);
		}

		return $expireTimestamp !== false && $expireTimestamp < $this->timeFactory->getTime();
	}

	public function isMaxUsesReached(Invitation $invitation): bool {
		$maxUses = $invitation->getMaxUses();
		if ($maxUses === null) {
			return false;
		}

		return $invitation->getUses() >= $maxUses;
	}

	/**
	 * @param Invitation $invitation
	 * @throws RegistrationException
	 */
	public function assertUsable(Invitation $invitation): void {
		if ($this->isExpired($invitation)) {
			throw new RegistrationException($this->l10n->t('This invitation is no longer valid.'));
		}

		if ($this->isMaxUsesReached($invitation)) {
			throw new RegistrationException($this->l10n->t('This invitation has already been used up.'));
		}
	}

	/**
	 * @param Invitation $invitation
	 * @param string $email
	 * @throws RegistrationException
	 */
	public function validate(Invitation $invitation, string $email): void {
		$this->assertUsable($invitation);

		$allowedEmail = $invitation->getEmail();
		if ($allowedEmail !== null && strtolower($email) !== strtolower($allowedEmail)) {
			throw new RegistrationException($this->l10n->t('This invitation is not valid for this email address.'));
		}

		$allowedDomain = $invitation->getDomain();
		if ($allowedDomain !== null && !$this->domainMatches($email, $allowedDomain)) {
			throw new RegistrationException($this->l10n->t('This invitation is not valid for this email domain.'));
		}
	}

	public function incrementUses(Invitation $invitation): void {
		$this->invitationMapper->incrementUses($invitation);
	}

	private function domainMatches(string $email, string $allowedDomain): bool {
		[,$mailDomain] = explode('@', strtolower($email), 2);

		if (str_contains($allowedDomain, '*')) {
			$regexDomain = preg_quote($allowedDomain, '\\');
			$regexDomain = '/^' . str_replace('\\*', '.+', $regexDomain) . '$/';
			return (bool)preg_match($regexDomain, $mailDomain);
		}

		return $mailDomain === $allowedDomain;
	}
}