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

use \OC\AppFramework\Utility\SimpleContainer;

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
		$container->registerService('RegisterController', function(SimpleContainer $c) {
			return new RegisterController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Mailer'),
				$c->query('L10N'),
				$c->query('URLGenerator'),
				$c->query('PendingRegist'),
				$c->query('UserManager'),
				$c->query('Config'),
				$c->query('GroupManager'),
				$c->query('Defaults'),
				$c->query('ServerContainer')->getSecureRandom()->getMediumStrengthGenerator(),
				$c->query('ServerContainer')->getUserSession()
			);
		});

		$container->registerService('SettingsController', function(SimpleContainer $c) {
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
		$container->registerService('UserManager', function(SimpleContainer $c) {
			return $c->query('ServerContainer')->getUserManager();
		});

		$container->registerService('GroupManager', function(SimpleContainer $c) {
			return $c->query('ServerContainer')->getGroupManager();
		});

		$container->registerService('Config', function(SimpleContainer $c) {
			return $c->query('ServerContainer')->getConfig();
		});

		$container->registerService('Mailer', function(SimpleContainer $c) {
			return $c->query('ServerContainer')->getMailer();
		});

		$container->registerService('L10N', function(SimpleContainer $c) {
			return $c->query('ServerContainer')->getL10N($c->query('AppName'));
		});

		$container->registerService('URLGenerator', function(SimpleContainer $c) {
			return $c->getServer()->getURLGenerator();
		});

		$container->registerService('PendingRegist', function(SimpleContainer $c) {
			return new PendingRegist($c->query('ServerContainer')->getDatabaseConnection(),
				$c->query('ServerContainer')->getSecureRandom()->getMediumStrengthGenerator());
		});

		$container->registerService('Defaults', function(SimpleContainer $c) {
			return new \OC_Defaults;
		});
	}


}
