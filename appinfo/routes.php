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

namespace OCA\Registration\App;

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
$application = new Registration();

$application->registerRoutes($this, array('routes' => array(
	array('name' => 'register#askEmail', 'url' => '/', 'verb' => 'GET'),
	array('name' => 'register#validateEmail', 'url' => '/', 'verb' => 'POST'),
	array('name' => 'register#verifyToken', 'url' => '/verify/{token}', 'verb' => 'GET'),
	array('name' => 'register#createAccount', 'url' => '/verify/{token}', 'verb' => 'POST')
)));
