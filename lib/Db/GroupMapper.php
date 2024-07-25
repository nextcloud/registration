<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 * @author Thomas Citharel <nextcloud@tcit.fr>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
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

class GroupMapper extends QBMapper {
	public function __construct(IDBConnection $db, protected ISecureRandom $random) {
		parent::__construct($db, 'registration_group', Group::class);
	}

	/**
	 * @return Group[]
	 */
	public function getGroupMappings(): array {
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName);

		return $this->findEntities($qb);
	}

	/**
	 * @return Group|null
	 */
	public function getGroupMappingByEmailDomain($emailDomain): ?Group {
		try {
			$qb = $this->db->getQueryBuilder();
			$qb
				->select('*')
				->from($this->tableName)
				->where($qb->expr()->like('email_domains', $qb->createNamedParameter("%".$emailDomain."%", IQueryBuilder::PARAM_STR)));

			return $this->findEntity($qb);
		} catch( \Exception ) {
			return null;
		}
	}

	/**
	 * @return Group
	 */
	public function getById($id): Group {
		$qb = $this->db->getQueryBuilder();
		$qb
			->select('*')
			->from($this->tableName)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}
}
