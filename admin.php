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

namespace OCA\Registration\AppInfo;

use OCA\Registration\Controller\SettingsController;

$controller = \OC::$server->query(SettingsController::class);
return $controller->displayPanel()->render();
