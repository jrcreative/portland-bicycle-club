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

namespace SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Adapters\JsonSerializers;

use SkyVerge\WooCommerce\Memberships\Teams\Team_Member;

defined( 'ABSPATH' ) or exit;

/**
 * Serializes a team member into an array suitable for JSON output.
 *
 * @since 1.8.0
 */
class TeamMemberSerializer {

	/**
	 * Converts a team member object into an associative array for JSON serialization.
	 *
	 * @since 1.8.0
	 *
	 * @param Team_Member $member
	 * @return array<string, mixed>
	 */
	public static function convert(Team_Member $member) : array
	{
		return [
			'user_id'            => $member->get_id(),
			'team_id'            => $member->get_team_id(),
			'role'               => $member->get_role(),
			'name'               => $member->get_name(),
			'email'              => $member->get_email(),
			'date_added'         => $member->get_added_date( 'c' ),
			'user_membership_id' => $member->get_user_membership_id(),
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
				'user_id'            => [ 'type' => 'integer' ],
				'team_id'            => [ 'type' => 'integer' ],
				'role'               => [ 'type' => 'string' ],
				'name'               => [ 'type' => 'string' ],
				'email'              => [ 'type' => 'string' ],
				'date_added'         => [ 'type' => ['string', 'null'], 'format' => 'date-time' ],
				'user_membership_id' => [ 'type' => ['integer', 'null'] ],
			],
		];
	}
}
