<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\AppInfo;

use OCA\Registration\Capabilities;
use OCA\Registration\Listener\UserEnabledListener;
use OCA\Registration\RegistrationLoginOption;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserChangedEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'registration';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerAlternativeLogin(RegistrationLoginOption::class);
		$context->registerCapability(Capabilities::class);
		$context->registerEventListener(UserChangedEvent::class, UserEnabledListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
