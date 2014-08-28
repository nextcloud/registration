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

namespace OCA\Registration\Controller;


use \OCP\IRequest;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Controller;

class RegistrationController extends Controller {

	public function __construct($appName, IRequest $request){
		parent::__construct($appName, $request);
	}

	/**
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function displayRegisterPage() {
		$params = array(
			'errormsg' => $this->request->getParam('errormsg'),
			'entered' => $this->request->getParam('entered')
		);
		return new TemplateResponse('registration', 'register', $params);
	}
}
