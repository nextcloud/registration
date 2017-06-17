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

	public function findByToken($token) {
		return $this->findEntity('SELECT * FROM `*PREFIX*registration` WHERE `token` = ? ', [$token]);
	}

	public function findEmailByToken($token) {
		$entity = $this->findByToken($token);
		return $entity->getEmail();
	}

	public function find($email) {
		$sql = 'SELECT `email` FROM `*PREFIX*registration` WHERE `email` = ? ';
		return $this->findEntity($sql, [$email]);
	}

	public function deleteByEmail($email) {
		$entity = $this->findEntity('SELECT * FROM `*PREFIX*registration` WHERE `email` = ?', [$email]);
		return $this->delete($entity);
	}

	public function save($email) {
		$token = $this->random->generate(6, ISecureRandom::CHAR_UPPER.ISecureRandom::CHAR_DIGITS);
		$registration = new Registration();
		$registration->setEmail($email);
		$registration->setToken($token);
		$registration->setRequested(date('Y-m-d H:i:s'));
		return $this->insert($registration);

	}

}