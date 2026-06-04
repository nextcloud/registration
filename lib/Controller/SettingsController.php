<?php

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2015 Johannes Starosta <j.starosta@tu-braunschweig.de>
 * SPDX-FileCopyrightText: 2015 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Registration\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;

class SettingsController extends Controller {

	public function __construct(
		protected $appName,
		IRequest $request,
		private IL10N $l10n,
		private IAppConfig $config,
		private IGroupManager $groupManager,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 *
	 * @param string|null $registered_user_group all newly registered user will be put in this group
	 * @param string $allowed_domains Registrations are only allowed for E-Mailadresses with these domains
	 * @param string $additional_hint show Text at user-creation form
	 * @param string $email_verification_hint if filled embed Text in Verification mail send to user
	 * @param string $username_policy_regex optional regex to check usernames against a pattern
	 * @param bool|null $admin_approval_required newly registered users have to be validated by an admin
	 * @param bool|null $email_is_optional email address is not required
	 * @param bool|null $email_is_login email address is forced as user id
	 * @param bool|null $domains_is_blocklist is the domain list an allow or block list
	 * @param bool|null $show_domains should the email list be shown to the user or not
	 * @return DataResponse
	 */
	public function admin(?string $registered_user_group,
		string $allowed_domains,
		string $additional_hint,
		string $email_verification_hint,
		string $username_policy_regex,
		?bool $admin_approval_required,
		?bool $email_is_optional,
		?bool $email_is_login,
		?bool $show_fullname,
		?bool $enforce_fullname,
		?bool $show_phone,
		?bool $enforce_phone,
		?bool $domains_is_blocklist,
		?bool $show_domains,
		?bool $disable_email_verification): DataResponse {
		// handle domains
		if ($allowed_domains === '') {
			$this->config->deleteAppValue('allowed_domains');
		} else {
			$this->config->setAppValueString('allowed_domains', $allowed_domains);
		}

		// handle hints
		if ($additional_hint === '') {
			$this->config->deleteAppValue('additional_hint');
		} else {
			$this->config->setAppValueString('additional_hint', $additional_hint);
		}

		if ($email_verification_hint === '') {
			$this->config->deleteAppValue('email_verification_hint');
		} else {
			$this->config->setAppValueString('email_verification_hint', $email_verification_hint);
		}

		//handle regex
		if ($username_policy_regex === '') {
			$this->config->deleteAppValue('username_policy_regex');
		} elseif ((@preg_match($username_policy_regex, null) === false)) {
			// validate regex
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('Invalid username policy regex'),
				],
				'status' => 'error',
			], Http::STATUS_BAD_REQUEST);
		} else {
			$this->config->setAppValueString('username_policy_regex', $username_policy_regex);
		}

		$this->config->setAppValueBool('admin_approval_required', $admin_approval_required);
		$this->config->setAppValueBool('email_is_optional', $email_is_optional);
		$this->config->setAppValueBool('email_is_login', !$email_is_optional && $email_is_login);
		$this->config->setAppValueBool('show_fullname', $show_fullname);
		$this->config->setAppValueBool('enforce_fullname', $enforce_fullname);
		$this->config->setAppValueBool('show_phone', $show_phone);
		$this->config->setAppValueBool('enforce_phone', $enforce_phone);
		$this->config->setAppValueBool('domains_is_blocklist', $domains_is_blocklist);
		$this->config->setAppValueBool('show_domains', $show_domains);
		$this->config->setAppValueBool('disable_email_verification', $disable_email_verification);

		if ($registered_user_group === null) {
			$this->config->deleteAppValue('registered_user_group');
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('Saved'),
				],
				'status' => 'success',
			]);
		}

		$group = $this->groupManager->get($registered_user_group);
		if ($group instanceof IGroup) {
			$this->config->setAppValueString('registered_user_group', $registered_user_group);
			return new DataResponse([
				'data' => [
					'message' => $this->l10n->t('Saved'),
				],
				'status' => 'success',
			]);
		}

		return new DataResponse([
			'data' => [
				'message' => $this->l10n->t('No such group'),
			],
			'status' => 'error',
		], Http::STATUS_NOT_FOUND);
	}
}
