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

use OCA\Registration\Db\Registration;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCS\OCSException;
use OCP\AppFramework\OCS\OCSNotFoundException;
use OCP\Defaults;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUser;
use \Test\TestCase;

class ApiControllerTest extends TestCase {

	/** @var IRequest */
	private $request;
	/** @var RegistrationService|\PHPUnit_Framework_MockObject_MockObject */
	private $registrationService;
	/** @var MailService */
	private $mailService;
	/** @var IL10N */
	private $l10n;
	/** @var Defaults */
	private $defaults;
	/** @var ApiController */
	private $controller;

	public function setUp () {
		parent::setUp();
		$this->request = $this->createMock(IRequest::class);
		$this->registrationService = $this->createMock(RegistrationService::class);
		$this->mailService = $this->createMock(MailService::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->defaults = $this->createMock(Defaults::class);
		$this->controller = new ApiController(
			"registration",
			$this->request,
			$this->registrationService,
			$this->mailService,
			$this->l10n,
			$this->defaults
		);
	}

	public function testValidate() {
		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->with('test@example.com');
		$this->registrationService
			->expects($this->once())
			->method('validateDisplayname')
			->with('user test');
		$this->registrationService
			->expects($this->once())
			->method('validateUsername')
			->with('user1');

		$expected = new DataResponse([
			'username' => 'user1',
			'displayname' => 'user test',
			'email' => 'test@example.com'
		], Http::STATUS_OK);
		$actual = $this->controller->validate('user1', 'user test', 'test@example.com');
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @expectedException \OCP\AppFramework\OCS\OCSException
	 * @expectedExceptionCode 999
	 */
	public function testValidateFailEmail() {
		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->willThrowException(new OCSException('', 999));
		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	/**
	 * @expectedException \OCP\AppFramework\OCS\OCSException
	 * @expectedExceptionCode 999
	 */
	public function testValidateFailDisplayname() {
		$this->registrationService
			->expects($this->once())
			->method('validateDisplayname')
			->willThrowException(new OCSException('', 999));
		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	/**
	 * @expectedException \OCP\AppFramework\OCS\OCSException
	 * @expectedExceptionCode 999
	 */
	public function testValidateFailUsername() {
		$this->registrationService
			->expects($this->once())
			->method('validateUsername')
			->willThrowException(new OCSException('', 999));
		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	/**
	 * @expectedException \OCP\AppFramework\OCS\OCSNotFoundException
	 * @expectedExceptionCode 404
	 */
	public function testStatusNoRegistration() {
		$this->registrationService
			->method('getRegistrationForSecret')
			->with('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp')
			->willThrowException(new DoesNotExistException(''));
		$this->controller->status('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp');
	}

	/**
	 * @expectedException \OCP\AppFramework\OCS\OCSException
	 * @expectedExceptionCode 403
	 */
	public function testStatusPendingRegistration() {
		$registration = new Registration();
		$registration->setEmailConfirmed(false);
		$this->registrationService
			->method('getRegistrationForSecret')
			->with('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp')
			->willReturn($registration);
		$actual = $this->controller->status('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp');
	}

	public function testStatusConfirmedRegistration() {
		$registration = new Registration();
		$registration->setEmailConfirmed(true);
		$registration->setClientSecret('mysecret');
		$user = $this->createMock(IUser::class);
		$this->registrationService
			->method('getRegistrationForSecret')
			->with('mysecret')
			->willReturn($registration);
		$this->registrationService
			->expects($this->once())
			->method('getUserAccount')
			->with($registration)
			->willReturn($user);
		$this->registrationService
			->expects($this->once())
			->method('loginUser');
		$this->registrationService
			->expects($this->once())
			->method('generateAppPassword');
		$actual = $this->controller->status('mysecret');
		$expected = new DataResponse([]);
		$this->assertEquals($expected, $actual);
	}

	public function testStatusConfirmedRegistrationWithSecret() {

	}

}
