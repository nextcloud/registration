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
use OCA\Registration\Util\CoreBridge;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
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

	public function testValidateFailEmail() {
        $exception = CoreBridge::createException('OCSException', '', 999);

        $this->expectException(get_class($exception));

		$this->registrationService
			->expects($this->once())
			->method('validateEmail')
			->willThrowException($exception);

		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	public function testValidateFailDisplayname() {
        $exception = CoreBridge::createException('OCSException', '', 999);

        $this->expectException(get_class($exception));

        $this->registrationService
			->expects($this->once())
			->method('validateDisplayname')
			->willThrowException($exception);

		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	public function testValidateFailUsername() {
        $exception = CoreBridge::createException('OCSException', '', 999);

        $this->expectException(get_class($exception));

        $this->registrationService
			->expects($this->once())
			->method('validateUsername')
			->willThrowException($exception);

		$this->controller->validate('user1', 'user test', 'test@example.com');
	}

	public function testStatusNoRegistration() {
        $exception = CoreBridge::createException('OCSNotFoundException', '', 404);

        $this->expectException(get_class($exception));

		$this->registrationService
			->method('getRegistrationForSecret')
			->with('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp')
			->willThrowException($exception);

		$this->controller->status('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp');
	}

	public function testStatusPendingRegistration() {
		$registration = new Registration();
		$registration->setEmailConfirmed(false);
		$this->registrationService
			->method('getRegistrationForSecret')
			->with('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp')
			->willReturn($registration);

		$actual = $this->controller->status('L2qdLAtrJTx499ErjwkwnZqGmLdm3Acp');

		$expected = new DataResponse(
			[
				'registrationStatus' => 1,
				'message'			=> $this->l10n->t('Your registration is pending. Please confirm your email address.'),
			],
			Http::STATUS_OK
		);

		$this->assertEquals($expected, $actual);
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
		$expected = new DataResponse([
			'appPassword'		=> null,
			'cloudUrl'		   => $this->defaults->getBaseUrl(),
			'registrationStatus' => 0,
		]);
		$this->assertEquals($expected, $actual);
	}
}
