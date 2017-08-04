<?php

namespace OCA\Registration\TokenHasher;

class TokenHasher {
	private static $hashFuncName = 'sha256';

	private function __construct() {

	}

	public static function hash($token) {
		return hash(self::$hashFuncName, $token);
	}
}
