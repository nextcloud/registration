<?php
/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2014 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'routes' => [
		['name' => 'settings#admin', 'url' => '/settings', 'verb' => 'POST'],
		['name' => 'register#showEmailForm', 'url' => '/', 'verb' => 'GET'],
		['name' => 'register#submitEmailForm', 'url' => '/', 'verb' => 'POST'],
		['name' => 'register#showVerificationForm', 'url' => '/verify/{secret}', 'verb' => 'GET'],
		['name' => 'register#submitVerificationForm', 'url' => '/verify/{secret}', 'verb' => 'POST'],
		['name' => 'register#showUserForm', 'url' => '/register/{secret}/{token}', 'verb' => 'GET'],
		['name' => 'register#submitUserForm', 'url' => '/register/{secret}/{token}', 'verb' => 'POST'],
	],
];
