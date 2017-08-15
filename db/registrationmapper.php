<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
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
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\Security\ISecureRandom;

class RegistrationMapper extends Mapper {

	/** @var \OCP\Security\ISecureRandom */
	protected $random;

	public function __construct(IDBConnection $db, ISecureRandom $random) {
		parent::__construct($db, 'registration', Registration::class);
		$this->random = $random;
	}

	/**
	 * @param $token
	 * @return Registration|Entity
	 */
	public function findByToken($token) {
		return $this->findEntity('SELECT * FROM `*PREFIX*registration` WHERE `token` = ? ', [$token]);
	}

	public function findBySecret($secret) {
		return $this->findEntity('SELECT * FROM `*PREFIX*registration` WHERE `client_secret` = ? ', [$secret]);
	}

	public function usernameIsPending($username) {
		try {
			$entity = $this->findEntity(
				'SELECT id FROM `*PREFIX*registration` WHERE `username` = ? ',
				[$username]
			);
		} catch (DoesNotExistException $e) {
			return false;
		}
		return true;
	}

	/**
	 * @param $email
	 * @return Registration|Entity
	 */
	public function find($email) {
		$sql = 'SELECT * FROM `*PREFIX*registration` WHERE `email` = ? ';
		return $this->findEntity($sql, [$email]);
	}

	/**
	 * @param Entity $entity
	 * @return Entity
	 */
	public function insert(Entity $entity) {
		$entity->setRequested(date('Y-m-d H:i:s'));
		return parent::insert($entity);
	}

	/**
	 * @param Registration $registration
	 */
	public function generateNewToken(Registration &$registration) {
		$token = $this->random->generate(6, ISecureRandom::CHAR_UPPER.ISecureRandom::CHAR_DIGITS);
		$registration->setToken($token);
	}

	/**
	 * @param Registration $registration
	 */
	public function generateClientSecret(Registration &$registration) {
		$token = $this->random->generate(32, "abcdefgijkmnopqrstwxyzABCDEFGHJKLMNPQRSTWXYZ23456789");
		//FIXME eqivalent to ISecureRandom::CHAR_HUMAN_READABLE introduced in https://github.com/nextcloud/server/commit/f2a2b34e4639e88f8d948a388a51f010212b42a3 but not supported in ownCloud yet. We'll just use the string for now then switch to constants when supported.
		$registration->setClientSecret($token);
	}

}
