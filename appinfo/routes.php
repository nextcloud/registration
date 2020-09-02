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
