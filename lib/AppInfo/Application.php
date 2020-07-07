<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Registration\AppInfo;

use OCP\AppFramework\App;

class Application extends App {

	public function __construct(array $urlParams = array()) {
		parent::__construct('registration', $urlParams);

		$container = $this->getContainer();
		$container->registerService('OC\Authentication\Token\IProvider', function ($c) {
			return \OC::$server->query('OC\Authentication\Token\IProvider');
		});

		if (interface_exists('\OCP\Capabilities\IPublicCapability')) {
			$container->registerCapability(\OCA\Registration\Capabilities::class);
		}
	}

}
