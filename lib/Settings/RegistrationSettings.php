<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Settings;

use OCA\Registration\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\Settings\ISettings;
use OCP\Util;

class RegistrationSettings implements ISettings {

	public function __construct(
		protected string $appName,
		private IAppConfig $config,
		private IGroupManager $groupManager,
		private IInitialState $initialState,
	) {
	}

	#[\Override]
	public function getForm(): TemplateResponse {
		$this->initialState->provideInitialState(
			'registered_user_group',
			$this->getGroupDetailArray($this->config->getAppValueString('registered_user_group', 'none'))
		);

		$this->initialState->provideInitialState(
			'admin_approval_required',
			$this->config->getAppValueBool('admin_approval_required')
		);

		$this->initialState->provideInitialState(
			'allowed_domains',
			$this->config->getAppValueString('allowed_domains')
		);
		$this->initialState->provideInitialState(
			'domains_is_blocklist',
			$this->config->getAppValueBool('domains_is_blocklist')
		);
		$this->initialState->provideInitialState(
			'show_domains',
			$this->config->getAppValueBool('show_domains')
		);
		$this->initialState->provideInitialState(
			'disable_email_verification',
			$this->config->getAppValueBool('disable_email_verification')
		);
		$this->initialState->provideInitialState(
			'email_is_optional',
			$this->config->getAppValueBool('email_is_optional')
		);
		$this->initialState->provideInitialState(
			'email_is_login',
			$this->config->getAppValueBool('email_is_login')
		);
		$this->initialState->provideInitialState(
			'username_policy_regex',
			$this->config->getAppValueString('username_policy_regex')
		);
		$this->initialState->provideInitialState(
			'username_policy_regex',
			$this->config->getAppValueString('username_policy_regex')
		);
		$this->initialState->provideInitialState(
			'show_fullname',
			$this->config->getAppValueBool('show_fullname')
		);
		$this->initialState->provideInitialState(
			'enforce_fullname',
			$this->config->getAppValueBool('enforce_fullname')
		);
		$this->initialState->provideInitialState(
			'show_phone',
			$this->config->getAppValueBool('show_phone')
		);
		$this->initialState->provideInitialState(
			'enforce_phone',
			$this->config->getAppValueBool('enforce_phone')
		);

		$this->initialState->provideInitialState(
			'additional_hint',
			$this->config->getAppValueString('additional_hint')
		);
		$this->initialState->provideInitialState(
			'email_verification_hint',
			$this->config->getAppValueString('email_verification_hint')
		);

		Util::addScript('registration', 'registration-settings');
		Util::addStyle('registration', 'registration-settings');

		return new TemplateResponse('registration', 'admin', [], TemplateResponse::RENDER_AS_BLANK);
	}

	#[\Override]
	public function getSection(): string {
		return Application::APP_ID;
	}

	#[\Override]
	public function getPriority(): int {
		return 50;
	}

	protected function getGroupDetailArray(string $gid): array {
		$group = $this->groupManager->get($gid);
		if ($group instanceof IGroup) {
			return [
				'id' => $group->getGID(),
				'displayname' => $group->getDisplayName(),
			];
		}

		return [];
	}
}
