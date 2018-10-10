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
