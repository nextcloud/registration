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

\OC_App::registerLogIn([
	'name' => \OC::$server->getL10N('registration')->t('Register'),
	'href' => \OC::$server->getURLGenerator()->linkToRoute('registration.register.askEmail')
]);

\OCP\App::registerAdmin('registration', 'admin');

if(interface_exists('\OCP\Capabilities\IPublicCapability')) {
	$app = new \OCP\AppFramework\App('registration');
	$app->getContainer()->registerCapability(\OCA\Registration\Capabilities::class);
}