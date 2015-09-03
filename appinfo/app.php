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

namespace OCA\Registration\AppInfo;

$app = new Application();
$c = $app->getContainer();

\OC_App::registerLogIn(array('name' => $c->query('L10N')->t('Register'), 'href' => $c->query('URLGenerator')->linkToRoute('registration.register.askEmail')));

\OCP\App::registerAdmin($c->getAppName(), 'admin');

// Witchcraft!
$request = \OC::$server->getRequest();
if (isset($request->server['REQUEST_URI'])) {
	$url = $request->server['REQUEST_URI'];
	if (preg_match('%index.php/settings/users(/.*)?%', $url)) {
		\OCP\Util::addScript('registration', 'settings-users-inject');
		\OCP\Util::addStyle('registration', 'settings-users-inject');
	}
}
