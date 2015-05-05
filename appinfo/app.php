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

$app = new Registration();
$c = $app->getContainer();

\OC_App::registerLogIn(array('name' => $c->query('L10N')->t('Register'), 'href' => $c->query('URLGenerator')->linkToRoute('registration.register.askEmail')));
