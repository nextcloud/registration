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

$app = new Application();
$controller = $app->getContainer()->query('SettingsController');
return $controller->displayPanel()->render();
