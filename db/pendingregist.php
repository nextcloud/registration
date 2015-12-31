<?php
namespace OCA\Registration\Db;

use \OCP\IDb;
use \OCP\Util;
use \OCP\Security\ISecureRandom;

class PendingRegist {

	private $db;

	/** @var \OCP\Security\ISecureRandom */
	protected $random;

	public function __construct(IDb $db, ISecureRandom $random) {
		$this->db = $db;
		$this->random = $random;
	}

	public function save(string $username, string $display_name, string $email, string $password) {
		$query = $this->db->prepareQuery( 'INSERT INTO `*PREFIX*registration`'
			.' ( `username`, `display_name`, `email`, `password`, `token`, `requested` ) VALUES( ?, ?, ?, ?, ?, NOW() )' );
		
		$token = $this->random->generate(6, ISecureRandom::CHAR_UPPER.ISecureRandom::CHAR_DIGITS);
		
		$query->execute(array( $username, $display_name, $email, $password, $token ));
		return $token;
	}
	public function find($email) {
		$query = $this->db->prepareQuery('SELECT `email` FROM `*PREFIX*registration` WHERE `email` = ? ');
		return $query->execute(array($email))->fetchAll();
	}

	public function delete($email) {
		$query = $this->db->prepareQuery('DELETE FROM `*PREFIX*registration` WHERE `email` = ? ');
		return $query->execute(array($email));
	}

	public function findEmailByToken($token) {
		$query = $this->db->prepareQuery('SELECT `email` FROM `*PREFIX*registration` WHERE `token` = ? ');
		return $query->execute(array($token))->fetchOne();
	}

}
