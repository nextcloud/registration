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
use \OCA\Registration\Wrapper;


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
				$c->query('Request')
			);
		});


		/**
		 * Core
		 */
		$container->registerService('UserId', function($c) {
			return \OCP\User::getUser();
		});		

		$container->registerService('Mail', function($c) {
			return Mail();
		});

		$container->registerService('L10N', function($c) {
			return $c->query('ServerContainer')->getL10N($c->query('AppName'));
		});

		$container->registerService('URLGenerator', function($c) {
			return $c->getServer()->getURLGenerator();
		});

		$container->registerService('PendingRegist', function($c) {
			return new PendingRegist($c->query('ServerContainer')->getDb());
		});
	}


}
