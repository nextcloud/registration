<?php
namespace OCA\Registration\Db;

use \OCP\IDb;

class PendingRegist {

	private $db;

	public function __construct(IDb $db) {
		$this->db = $db;
	}

	public function find($id) {
	}

}
