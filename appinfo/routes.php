<?php
/**
 * ownCloud - registration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
 * @copyright Pellaeon Lin 2014
 */

return ['routes' => [
	array('name' => 'settings#admin', 'url' => '/settings', 'verb' => 'POST'),
	array('name' => 'register#askEmail', 'url' => '/', 'verb' => 'GET'),
	array('name' => 'register#validateEmail', 'url' => '/', 'verb' => 'POST'),
	array('name' => 'register#verifyToken', 'url' => '/verify/{token}', 'verb' => 'GET'),
	array('name' => 'register#createAccount', 'url' => '/verify/{token}', 'verb' => 'POST')
]];
