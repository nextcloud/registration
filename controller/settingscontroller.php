<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@cnmc.tw>
 * @copyright Pellaeon Lin 2015
 */

namespace OCA\Registration\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Controller;
use \OCP\IGroupManager;
use \OCP\IL10N;
use \OCP\IConfig;
use \OCP\IUser;

class SettingsController extends Controller {

	private $l10n;
	private $config;
	private $groupmanager;
	protected $appName;

	public function __construct($appName, IRequest $request, IL10N $l10n, IConfig $config, IGroupManager $groupmanager){
		$this->l10n = $l10n;
		$this->config = $config;
		$this->groupmanager = $groupmanager;
		$this->appName = $appName;
		parent::__construct($appName, $request);
	}

	

	/**
	 * @AdminRequired
	 *
	 * @param string $registered_user_group all newly registered user will be put in this group
	 * @param string $allowed_domains Registrations are only allowed for E-Mailadresses with these domains
	 * @return DataResponse
	 */
	public function admin($registered_user_group, $allowed_domains) {
		if ( ( $allowed_domains==='' ) || ( $allowed_domains === NULL ) ){
			$this->config->deleteAppValue($this->appName, 'allowed_domains');
		}else{
			$this->config->setAppValue($this->appName, 'allowed_domains', $allowed_domains);
		}
		$groups = $this->groupmanager->search('');
		$group_id_list = array();
		foreach ( $groups as $group ) {
			$group_id_list[] = $group->getGid();
		}
		if ( $registered_user_group === 'none' ) {
			$this->config->deleteAppValue($this->appName, 'registered_user_group');
			return new DataResponse(array(
				'data' => array(
					'message' => (string) $this->l10n->t('Your settings have been updated.'),
				),
			));
		} else if ( in_array($registered_user_group, $group_id_list) ) {
			$this->config->setAppValue($this->appName, 'registered_user_group', $registered_user_group);
			return new DataResponse(array(
				'data' => array(
					'message' => (string) $this->l10n->t('Your settings have been updated.'),
				),
			));
		} else {
			return new DataResponse(array(
				'data' => array(
					'message' => (string) $this->l10n->t('No such group'),
				),
			), Http::STATUS_NOT_FOUND);
		}
	}
	/**
	 * @AdminRequired
	 *
	 * @return TemplateResponse
	 */
	public function displayPanel() {
		$groups = $this->groupmanager->search('');
		foreach ( $groups as $group ) {
			$group_id_list[] = $group->getGid();
		}
		// TODO selected
		$current_value = $this->config->getAppValue($this->appName, 'registered_user_group', 'none');
		$allowed_domains = $this->config->getAppValue($this->appName, 'allowed_domains', '');
		return new TemplateResponse('registration', 'admin', [
			'groups' => $group_id_list,
			'current' => $current_value,
			'allowed' => $allowed_domains
		], '');
	}
}
