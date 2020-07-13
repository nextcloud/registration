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
		$this->appName = $appName;
	}

	public function getForm(): TemplateResponse {
		// handle groups
		$groups = $this->groupManager->search('');
		$groupIds = [];
		foreach ($groups as $group) {
			$groupIds[] = $group->getGid();
		}
		$assignedGroups = $this->config->getAppValue($this->appName, 'registered_user_group', 'none');

		// handle domains
		$allowedDomains = $this->config->getAppValue($this->appName, 'allowed_domains', '');

		// handle admin validation
		$adminApprovalRequired = $this->config->getAppValue($this->appName, 'admin_approval_required', "no");

		return new TemplateResponse('registration', 'admin', [
			'groups' => $groupIds,
			'current' => $assignedGroups,
			'allowed' => $allowedDomains,
			'approval_required' => $adminApprovalRequired
		], '');
	}

	public function getSection(): string {
		return 'additional';
	}

	public function getPriority(): int {
		return 50;
	}
}
