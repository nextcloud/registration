<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @author Julius Härtl <jus@bitgrid.net>
 * @copyright Pellaeon Lin 2014
 */

$app = new \OCA\Registration\AppInfo\Application();

return [
	'routes' => [
		['name' => 'settings#admin', 'url' => '/settings', 'verb' => 'POST'],
		['name' => 'register#askEmail', 'url' => '/', 'verb' => 'GET'],
		['name' => 'register#validateEmail', 'url' => '/', 'verb' => 'POST'],
		['name' => 'register#verifyToken', 'url' => '/verify/{token}', 'verb' => 'GET'],
		['name' => 'register#createAccount', 'url' => '/verify/{token}', 'verb' => 'POST']
	],
	'ocs' => [
		['root' => '/registration', 'name' => 'api#validate', 'url' => '/v1/validate', 'verb' => 'POST'],
		['root' => '/registration', 'name' => 'api#status', 'url' => '/v1/status', 'verb' => 'POST'],
		['root' => '/registration', 'name' => 'api#register', 'url' => '/v1/register', 'verb' => 'POST']
	]
];
