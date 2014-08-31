<?php
namespace OCA\Registration\Db;

use \OCP\IDb;
use \OCP\Util;
use \OCP\Config;

class PendingRegist {

	private $db;

	public function __construct(IDb $db) {
		$this->db = $db;
	}

	public function save($email) {
		$query = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*registration`'
			.' ( `email`, `token`, `requested` ) VALUES( ?, ?, ? )' );
		$token = hash('sha256', Util::generateRandomBytes(30).Config::getSystemValue('passwordsalt', ''));
		$query->execute(array( $email, $token, time() ));
		return $token;
	}
	public function find($email) {
		$query = $this->db->prepareQuery('SELECT `email` FROM `*PREFIX*registration` WHERE `email` = ? ');
		return $query->execute(array($email))->fetchAll();
	}

}
