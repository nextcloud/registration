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

use \OCP\AppFramework\App;

use \OCA\Registration\Controller\RegisterController;
use \OCA\Registration\Controller\SettingsController;
use \OCA\Registration\Wrapper;
use \OCA\Registration\Db\PendingRegist;


class Application extends App {

	public function __construct (array $urlParams=array()) {
		parent::__construct('registration', $urlParams);

		$container = $this->getContainer();

		/**
		 * Controllers
		 */
		$container->registerService('RegisterController', function($c) {
			return new RegisterController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Mail'),
				$c->query('L10N'),
				$c->query('URLGenerator'),
				$c->query('PendingRegist'),
				$c->query('UserManager'),
				$c->query('Config'),
				$c->query('GroupManager')
			);
		});

		$container->registerService('SettingsController', function($c) {
			return new SettingsController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('L10N'),
				$c->query('Config'),
				$c->query('GroupManager')
			);
		});


		/**
		 * Core
		 */
		$container->registerService('UserId', function($c) {
			return \OCP\User::getUser();
		});		

		$container->registerService('UserManager', function($c) {
			return $c->query('ServerContainer')->getUserManager();
		});

		$container->registerService('GroupManager', function($c) {
			return $c->query('ServerContainer')->getGroupManager();
		});

		$container->registerService('Config', function($c) {
			return $c->query('ServerContainer')->getConfig();
		});

		$container->registerService('Mail', function($c) {
			return new Wrapper\Mail;
		});

		$container->registerService('L10N', function($c) {
			return $c->query('ServerContainer')->getL10N($c->query('AppName'));
		});

		$container->registerService('URLGenerator', function($c) {
			return $c->getServer()->getURLGenerator();
		});

		$container->registerService('PendingRegist', function($c) {
			return new PendingRegist($c->query('ServerContainer')->getDb(),
				$c->query('ServerContainer')->getSecureRandom()->getMediumStrengthGenerator());
		});
	}


}
