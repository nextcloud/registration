<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2017 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
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

namespace OCA\Registration\Settings;

use OCA\Registration\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\Settings\ISettings;

class RegistrationSettings implements ISettings {
	/** @var IConfig */
	private $config;
	/** @var IGroupManager */
	private $groupManager;
	/** @var string */
	protected $appName;

	public function __construct(string $appName,
								IConfig $config,
								IGroupManager $groupManager) {
		$this->appName = $appName;
		$this->config = $config;
		$this->groupManager = $groupManager;
	}

	public function getForm(): TemplateResponse {
		// handle groups
		$groups = $this->groupManager->search('');
		$groupIds = [];
		foreach ($groups as $group) {
			$groupIds[] = $group->getGid();
		}
		$assignedGroups = $this->config->getAppValue($this->appName, 'registered_user_group', 'none');

		// handle additional hint
		$additional_hint = $this->config->getAppValue($this->appName, 'additional_hint', '');
		$email_verification_hint = $this->config->getAppValue($this->appName, 'email_verification_hint', '');

		// handle domains
		$allowedDomains = $this->config->getAppValue($this->appName, 'allowed_domains', '');

		$username_policy_regex = $this->config->getAppValue($this->appName, 'username_policy_regex', '');
		$adminApprovalRequired = $this->config->getAppValue($this->appName, 'admin_approval_required', 'no');
		$emailIsLogin = $this->config->getAppValue($this->appName, 'email_is_login', 'no');
		$domainsIsBlocklist = $this->config->getAppValue($this->appName, 'domains_is_blocklist', 'no');
		$showDomains = $this->config->getAppValue($this->appName, 'show_domains', 'no');
		$disableEmailVerification = $this->config->getAppValue($this->appName, 'disable_email_verification', 'no');

		return new TemplateResponse('registration', 'admin', [
			'groups' => $groupIds,
			'current' => $assignedGroups,
			'additional_hint' => $additional_hint,
			'email_verification_hint' => $email_verification_hint,
			'username_policy_regex' => $username_policy_regex,
			'allowed' => $allowedDomains,
			'approval_required' => $adminApprovalRequired,
			'email_is_login' => $emailIsLogin,
			'domains_is_blocklist' => $domainsIsBlocklist,
			'show_domains' => $showDomains,
			'disable_email_verification' => $disableEmailVerification,
		], '');
	}

	public function getSection(): string {
		return Application::APP_ID;
	}

	public function getPriority(): int {
		return 50;
	}
}
