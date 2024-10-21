<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Security\ISecureRandom;

class RegistrationMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
		protected ISecureRandom $random,
	) {
		parent::__construct($db, 'registration', Registration::class);
	}

	/**
	 * @param string $token
	 * @return Registration
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByToken(string $token): Entity {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('token', $query->createNamedParameter($token)));

		return $this->findEntity($query);
	}

	/**
	 * @param string $secret
	 * @return Registration
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findBySecret(string $secret): Entity {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('client_secret', $query->createNamedParameter($secret)));

		return $this->findEntity($query);
	}

	public function usernameIsPending(string $username): bool {
		try {
			$query = $this->db->getQueryBuilder();
			$query->select('*')
				->from($this->getTableName())
				->where($query->expr()->eq('username', $query->createNamedParameter($username)));

			$this->findEntity($query);
		} catch (DoesNotExistException $e) {
			return false;
		}
		return true;
	}

	/**
	 * @param string $email
	 * @return Registration
	 */
	public function find(string $email): Entity {
		$query = $this->db->getQueryBuilder();
		$query->select('*')
			->from($this->getTableName())
			->where($query->expr()->eq('email', $query->createNamedParameter($email)));

		return $this->findEntity($query);
	}

	/**
	 * @param Entity $entity
	 * @return Registration
	 * @throws Exception
	 */
	public function insert(Entity $entity): Entity {
		$entity->setRequested(date('Y-m-d H:i:s'));
		return parent::insert($entity);
	}

	/**
	 * @param Registration $registration
	 */
	public function generateNewToken(Registration $registration): void {
		$token = $this->random->generate(10, ISecureRandom::CHAR_HUMAN_READABLE);
		$registration->setToken($token);
	}

	/**
	 * @param Registration $registration
	 */
	public function generateClientSecret(Registration $registration): void {
		$token = $this->random->generate(32, ISecureRandom::CHAR_HUMAN_READABLE);
		$registration->setClientSecret($token);
	}

	public function deleteOlderThan(\DateTime $date): void {
		$query = $this->db->getQueryBuilder();
		$query->delete($this->getTableName())
			->where($query->expr()->lt('requested', $query->createNamedParameter($date, IQueryBuilder::PARAM_DATE)))
			->executeStatement();
	}
}
