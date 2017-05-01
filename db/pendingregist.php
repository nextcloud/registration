<?php
namespace OCA\Registration\Db;

use \OCP\IDbConnection;
use \OCP\Util;
use \OCP\Security\ISecureRandom;

class PendingRegist {

	private $db;

	/** @var \OCP\Security\ISecureRandom */
	protected $random;

	public function __construct(IDbConnection $db, ISecureRandom $random) {
		$this->db = $db;
		$this->random = $random;
	}

	public function save($email) {
		$query = $this->db->prepare( 'INSERT INTO `*PREFIX*registration`'
			.' ( `email`, `token`, `requested` ) VALUES( ?, ?, NOW() )' );
		
		$token = $this->random->generate(6, ISecureRandom::CHAR_UPPER.ISecureRandom::CHAR_DIGITS);
		
		$query->execute(array( $email, $token ));
		return $token;
	}
	public function find($email) {
		$query = $this->db->prepare('SELECT `email` FROM `*PREFIX*registration` WHERE `email` = ? ');
		$query->execute(array($email));
		return $query->fetchAll();
	}

	public function delete($email) {
		$query = $this->db->prepare('DELETE FROM `*PREFIX*registration` WHERE `email` = ? ');
		return $query->execute(array($email));
	}

	/**
	 * @return string|false
	 */
	public function findEmailByToken($token) {
		$query = $this->db->prepare('SELECT `email` FROM `*PREFIX*registration` WHERE `token` = ? ');
		$query->execute(array($token));
		return $query->fetch()['email'];
	}

}
