<?php
/**
 * ownCloud
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Pellaeon Lin <pellaeon@cnmc.tw>
 * @copyright Pellaeon Lin, 2014
 */

namespace OCA\Registration\Wrapper;

class Mail {

	/**
	 * send an email
	 * @param string $toaddress
	 * @param string $toname
	 * @param string $subject
	 * @param string $mailtext
	 * @param string $fromaddress
	 * @param string $fromname
	 * @param int $html
	 * @param string $altbody
	 * @param string $ccaddress
	 * @param string $ccname
	 * @param string $bcc
	 */
	public function sendMail( $toaddress, $toname, $subject, $mailtext, $fromaddress, $fromname,
		$html = 0, $altbody = '', $ccaddress = '', $ccname = '', $bcc = '') {
			// call the internal mail class
			\OCP\Util::sendMail($toaddress, $toname, $subject, $mailtext, $fromaddress, $fromname,
				$html, $altbody, $ccaddress, $ccname, $bcc);
		}
}
