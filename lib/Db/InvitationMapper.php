<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Security\ISecureRandom;

/**
 * @template-extends QBMapper<Invitation>
 */
class InvitationMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
		protected ISecureRandom $random,
	) {
		parent::__construct($db, 'registration_invitation', Invitation::class);
	}

	/**
	 * @param string $code
	 * @return Invitation
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByCode(string $code): Entity {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('code', $query->createNamedParameter($code)));

		return $this->findEntity($query);
	}

	/**
	 * @param int $id
	 * @return Invitation
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findById(int $id): Entity {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('id', $query->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($query);
	}

	/**
	 * @return Invitation[]
	 */
	public function findAllInvitations(): array {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->orderBy('created_at', 'DESC');

		return $this->findEntities($query);
	}

	/**
	 * @param int $id
	 */
	public function deleteById(int $id): void {
		$query = $this->db->getQueryBuilder();
		$query->delete($this->getTableName())
			->where($query->expr()->eq('id', $query->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->executeStatement();
	}

	#[\Override]
	public function insert(Entity $entity): Entity {
		$entity->setCreatedAt(date('Y-m-d H:i:s'));
		return parent::insert($entity);
	}

	/**
	 * @param Invitation $invitation
	 */
	public function incrementUses(Invitation $invitation): void {
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('uses', $query->createFunction('`uses` + 1'))
			->where($query->expr()->eq('id', $query->createNamedParameter($invitation->getId(), IQueryBuilder::PARAM_INT)))
			->executeStatement();
	}

	public function generateCode(): string {
		return $this->random->generate(8, ISecureRandom::CHAR_HUMAN_READABLE);
	}
}