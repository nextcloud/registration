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

namespace OCA\Registration\Service;

class RegistrationException extends \Exception {

	protected $hint;

	/**
	 * RegistrationException constructor.
	 *
	 * @param string $message
	 * @param string $hint
	 * @param int $code
	 */
	public function __construct($message, $hint = "", $code = 400) {
		parent::__construct($message, $code);
		$this->hint = $hint;
	}

	/**
	 * @return string
	 */
	public function getHint() {
		return $this->hint;
	}

}