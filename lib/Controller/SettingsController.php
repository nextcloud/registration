<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@cnmc.tw>
 * @author Julius Härtl <jus@bitgrid.net>
 * @copyright Pellaeon Lin 2015
 */

namespace OCA\Registration\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Controller;
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;

class SettingsController extends Controller {

	/** @var IL10N */
	private $l10n;
	/** @var IConfig */
	private $config;
	/** @var IGroupManager */
	private $groupmanager;
	/** @var string */
	protected $appName;

	public function __construct($appName, IRequest $request, IL10N $l10n, IConfig $config, IGroupManager $groupmanager) {
		parent::__construct($appName, $request);
		$this->l10n = $l10n;
		$this->config = $config;
		$this->groupmanager = $groupmanager;
		$this->appName = $appName;
	}

	/**
	 * @AdminRequired
	 *
	 * @param string $registered_user_group all newly registered user will be put in this group
	 * @param string $allowed_domains Registrations are only allowed for E-Mailadresses with these domains
	 * @param string $additional_hint show Text at user-creation form
	 * @param string $email_verification_hint if filled embed Text in Verification mail send to user
	 * @param string $username_policy_regex optional regex to check usernames against a pattern
	 * @param bool|null $admin_approval_required newly registered users have to be validated by an admin
	 * @param bool|null $email_is_login email address is forced as user id
	 * @param bool|null $domains_is_blocklist is the domain list an allow or block list
	 * @param bool|null $show_domains should the email list be shown to the user or not
	 * @return DataResponse
	 */
	public function admin(string $registered_user_group,
						  string $allowed_domains,
						  string $additional_hint,
						  string $email_verification_hint,
						  string $username_policy_regex,
						  ?bool $admin_approval_required,
						  ?bool $email_is_login,
						  ?bool $domains_is_blocklist,
						  ?bool $show_domains,
						  ?bool $disable_email_verification) {
		// handle domains
		if (($allowed_domains === '') || ($allowed_domains === null)) {
			$this->config->deleteAppValue($this->appName, 'allowed_domains');
		} else {
			$this->config->setAppValue($this->appName, 'allowed_domains', $allowed_domains);
		}

		// handle hints
		if (($additional_hint === '') || ($additional_hint === null)) {
			$this->config->deleteAppValue($this->appName, 'additional_hint');
		} else {
			$this->config->setAppValue($this->appName, 'additional_hint', $additional_hint);
		}

		if (($email_verification_hint === '') || ($email_verification_hint === null)) {
			$this->config->deleteAppValue($this->appName, 'email_verification_hint');
		} else {
			$this->config->setAppValue($this->appName, 'email_verification_hint', $email_verification_hint);
		}

		//handle regex
		if (($username_policy_regex === '') || ($username_policy_regex === null)) {
			$this->config->deleteAppValue($this->appName, 'username_policy_regex');
		} elseif ((@preg_match($username_policy_regex, null) === false)) {
			// validate regex
			// TODO: Create SettingsException Class, throw it & show it to user
			
		} else {
			$this->config->setAppValue($this->appName, 'username_policy_regex', $username_policy_regex);
		}

		$this->config->setAppValue($this->appName, 'admin_approval_required', $admin_approval_required ? 'yes' : 'no');
		$this->config->setAppValue($this->appName, 'email_is_login', $email_is_login ? 'yes' : 'no');
		$this->config->setAppValue($this->appName, 'domains_is_blocklist', $domains_is_blocklist ? 'yes' : 'no');
		$this->config->setAppValue($this->appName, 'show_domains', $show_domains ? 'yes' : 'no');
		$this->config->setAppValue($this->appName, 'disable_email_verification', $disable_email_verification ? 'yes' : 'no');

		// handle groups
		$groups = $this->groupmanager->search('');
		$group_id_list = [];
		foreach ($groups as $group) {
			$group_id_list[] = $group->getGid();
		}
		if ($registered_user_group === 'none') {
			$this->config->deleteAppValue($this->appName, 'registered_user_group');
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('Saved'),
				],
				'status' => 'success',
			]);
		} elseif (in_array($registered_user_group, $group_id_list)) {
			$this->config->setAppValue($this->appName, 'registered_user_group', $registered_user_group);
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('Saved'),
				],
				'status' => 'success',
			]);
		} else {
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('No such group'),
				],
				'status' => 'error',
			], Http::STATUS_NOT_FOUND);
		}
	}
}
