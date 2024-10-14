<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getEmail()
 * @method void setEmail(string $email)
 * @method string getUsername()
 * @method void setUsername(string $username)
 * @method string getPassword()
 * @method void setPassword(string $password)
 * @method string getDisplayname()
 * @method void setDisplayname(string $displayname)
 * @method bool getEmailConfirmed()
 * @method void setEmailConfirmed(bool $emailConfirmed)
 * @method string getToken()
 * @method void setToken(string $token)
 * @method string getClientSecret()
 * @method void setClientSecret(string $clientSecret)
 * @method string getRequested()
 * @method void setRequested(string $requested)
 */
class Registration extends Entity {
	public $id;
	protected $email;
	protected $username;
	protected $displayname;
	protected $password;
	protected $token;
	protected $requested;
	protected $emailConfirmed;
	protected $clientSecret;

	public function __construct() {
		$this->addType('email', 'string');
		$this->addType('username', 'string');
		$this->addType('password', 'string');
		$this->addType('displayname', 'string');
		$this->addType('emailConfirmed', 'boolean');
		$this->addType('token', 'string');
		$this->addType('clientSecret', 'string');
		$this->addType('requested', 'datetime');
	}
}
