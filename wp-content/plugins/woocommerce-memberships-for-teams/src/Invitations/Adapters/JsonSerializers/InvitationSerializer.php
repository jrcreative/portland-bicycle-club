<?php
/**
 * Teams for WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Teams for WooCommerce Memberships to newer
 * versions in the future. If you wish to customize Teams for WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/teams-woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2017-2026, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Teams\Invitations\Adapters\JsonSerializers;

use SkyVerge\WooCommerce\Memberships\Teams\Invitation;

defined( 'ABSPATH' ) or exit;

/**
 * Serializes an invitation into an array suitable for JSON output.
 *
 * @since 1.8.0
 */
class InvitationSerializer {

	/**
	 * Converts an invitation object into an associative array for JSON serialization.
	 *
	 * @since 1.8.0
	 *
	 * @param Invitation $invitation
	 * @return array<string, mixed>
	 */
	public static function convert(Invitation $invitation) : array
	{
		return [
			'id'           => $invitation->get_id(),
			'team_id'      => $invitation->get_team_id(),
			'email'        => $invitation->get_email(),
			'sender_id'    => $invitation->get_sender_id(),
			'member_role'  => $invitation->get_member_role(),
			'status'       => $invitation->get_status(),
			'date_created' => $invitation->get_date( 'c' ),
		];
	}

	/**
	 * Returns the JSON schema describing the shape of {@see convert()} output.
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, mixed>
	 */
	public static function getJsonSchema() : array
	{
		return [
			'type'       => 'object',
			'properties' => [
				'id'           => [ 'type' => 'integer' ],
				'team_id'      => [ 'type' => 'integer' ],
				'email'        => [ 'type' => 'string', 'format' => 'email' ],
				'sender_id'    => [ 'type' => 'integer' ],
				'member_role'  => [ 'type' => 'string' ],
				'status'       => [ 'type' => 'string' ],
				'date_created' => [ 'type' => 'string', 'format' => 'date-time' ],
			],
		];
	}
}
