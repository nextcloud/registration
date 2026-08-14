<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getCode()
 * @method void setCode(string $code)
 * @method string|null getEmail()
 * @method void setEmail(?string $email)
 * @method string|null getDomain()
 * @method void setDomain(?string $domain)
 * @method string|null getQuota()
 * @method void setQuota(?string $quota)
 * @method int|null getMaxUses()
 * @method void setMaxUses(?int $maxUses)
 * @method int getUses()
 * @method void setUses(int $uses)
 * @method string|null getExpires()
 * @method void setExpires(?string $expires)
 * @method string|null getCreatedBy()
 * @method void setCreatedBy(?string $createdBy)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method bool getSkipEmailVerification()
 * @method void setSkipEmailVerification(bool $skipEmailVerification)
 */
class Invitation extends Entity {
	public $id;
	protected $code;
	protected $email;
	protected $domain;
	protected $quota;
	protected $maxUses;
	protected $uses;
	protected $expires;
	protected $createdBy;
	protected $createdAt;
	protected $skipEmailVerification;

	public function __construct() {
		$this->addType('code', 'string');
		$this->addType('email', 'string');
		$this->addType('domain', 'string');
		$this->addType('quota', 'string');
		$this->addType('maxUses', 'integer');
		$this->addType('uses', 'integer');
		$this->addType('expires', 'datetime');
		$this->addType('createdBy', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('skipEmailVerification', 'boolean');
	}
}