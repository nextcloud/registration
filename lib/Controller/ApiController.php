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

namespace OCA\Registration\Controller;

use OCA\Registration\Db\Registration;
use OCA\Registration\Service\MailService;
use OCA\Registration\Service\RegistrationException;
use OCA\Registration\Service\RegistrationService;
use OCA\Registration\Util\CoreBridge;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Http\DataResponse;
use OCP\Defaults;
use OCP\IL10N;
use OCP\IRequest;

class ApiController extends OCSController {

	/** @var RegistrationService */
	private $registrationService;
	/** @var MailService */
	private $mailService;
	/** @var IL10N */
	private $l10n;
	/** @var Defaults */
	private $defaults;

	const REGISTRATION_STATUS_COMPLETE = 0;
	const REGISTRATION_STATUS_PENDING = 1;
	const REGISTRATION_STATUS_EXISTING = 2;

	public function __construct($appName,
								IRequest $request,
								RegistrationService $registrationService,
								MailService $mailService,
								IL10N $l10n,
								Defaults $defaults) {
		parent::__construct($appName, $request);
		$this->registrationService = $registrationService;
		$this->mailService = $mailService;
		$this->l10n = $l10n;
		$this->defaults = $defaults;
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=1)
	 *
	 * @param string $username
	 * @param string $displayname
	 * @param string $email
	 * @throws \Exception
	 * @return DataResponse
	 */
	public function validate($username, $displayname, $email) {
		try {
			$this->registrationService->validateEmail($email);
			$this->registrationService->validateDisplayname($displayname);
			$this->registrationService->validateUsername($username);
		} catch (RegistrationException $e) {
            throw CoreBridge::createException('OCSBadRequestException', $e->getMessage());
		}
		$data = [
			'username' => $username,
			'displayname' => $displayname,
			'email' => $email
		];
		return new DataResponse($data, Http::STATUS_OK);
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=10, period=1)
	 *
	 * @param string $clientSecret
	 * @throws \Exception
	 * @return DataResponse
	 */
	public function status($clientSecret) {
		try {
			/** @var Registration $registration */
			$registration = $this->registrationService->getRegistrationForSecret($clientSecret);
		} catch (DoesNotExistException $e) {
            throw CoreBridge::createException('OCSNotFoundException', 'No pending registration.');
		}

		if (!$registration->getEmailConfirmed()) {
			return new DataResponse(
				[
					'registrationStatus' => self::REGISTRATION_STATUS_PENDING,
					'message' => $this->l10n->t('Your registration is pending. Please confirm your email address.')
				],
				Http::STATUS_OK
			);
		} else {
			// create account if email confirmed and not already created
			$user = $this->registrationService->getUserAccount($registration);
			if ($user === null) {
				$user = $this->registrationService->createAccount($registration);
			}
			$this->registrationService->loginUser($user->getUID(), $registration->getUsername(), $registration->getPassword(), true);
			$appPassword = $this->registrationService->generateAppPassword($user->getUID());
			$data = [
				'appPassword' => $appPassword,
				'cloudUrl' => $this->defaults->getBaseUrl(),
				'registrationStatus' => self::REGISTRATION_STATUS_COMPLETE
			];
			$this->registrationService->deleteRegistration($registration);
			return new DataResponse($data, Http::STATUS_OK);
		}
	}

	/**
	 * @PublicPage
	 * @AnonRateThrottle(limit=5, period=1)
	 *
	 * @param string $username
	 * @param string $displayname
	 * @param string $email
	 * @param string $password
	 * @throws \Exception
	 * @return DataResponse
	 */
	public function register($username, $displayname, $email, $password) {
		$data = [];
		try {
			$secret = null;
			$registration = $this->registrationService->validateEmail($email);
			if($registration === true) {
				$this->registrationService->validateDisplayname($displayname);
				$this->registrationService->validateUsername($username);
				$registration = $this->registrationService->createRegistration($email, $username, $password, $displayname);
				$this->mailService->sendTokenByMail($registration);
				$secret = $registration->getClientSecret();
			} else {
				$this->registrationService->generateNewToken($registration);
				$this->mailService->sendTokenByMail($registration);
				return new DataResponse(
					[
						'registrationStatus' => self::REGISTRATION_STATUS_EXISTING,
						'message' => $this->l10n->t('There is already a pending registration with this email, a new verification email has been sent to the address.')
					],
					Http::STATUS_OK
				);
			}

			$data['message'] = $this->l10n->t('Your registration is pending. Please confirm your email address.');
			$data['registrationStatus'] = self::REGISTRATION_STATUS_PENDING;
			if($secret !== null) {
				$data['secret'] = $secret;
			}
			return new DataResponse($data, Http::STATUS_OK);
		} catch (RegistrationException $exception) {
            throw CoreBridge::createException('OCSException', $exception->getMessage(), $exception->getCode());
		}
	}
}
