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

namespace SkyVerge\WooCommerce\Memberships\Teams\Teams\Adapters\JsonSerializers;

use SkyVerge\WooCommerce\Memberships\Teams\Team;

defined( 'ABSPATH' ) or exit;

/**
 * Serializes a team into an array suitable for JSON output.
 *
 * @since 1.8.0
 */
class TeamSerializer {

	/**
	 * Converts a team object into an associative array for JSON serialization.
	 *
	 * @since 1.8.0
	 *
	 * @param Team $team
	 * @return array<string, mixed>
	 */
	public static function convert(Team $team) : array
	{
		return [
			'id'                   => $team->get_id(),
			'name'                 => $team->get_name(),
			'slug'                 => $team->get_slug(),
			'date_created'         => $team->get_date( 'c' ),
			'owner_id'             => $team->get_owner_id(),
			'plan_id'              => $team->get_plan_id(),
			'product_id'           => $team->get_product_id(),
			'order_id'             => $team->get_order_id(),
			'seat_count'           => $team->get_seat_count(),
			'used_seat_count'      => $team->get_used_seat_count(),
			'remaining_seat_count' => $team->get_remaining_seat_count(),
			'member_count'         => $team->get_member_count(),
			'membership_end_date'  => $team->get_membership_end_date( 'c' ),
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
				'id'                   => [ 'type' => 'integer' ],
				'name'                 => [ 'type' => 'string' ],
				'slug'                 => [ 'type' => 'string' ],
				'date_created'         => [ 'type' => 'string', 'format' => 'date-time' ],
				'owner_id'             => [ 'type' => 'integer' ],
				'plan_id'              => [ 'type' => 'integer' ],
				'product_id'           => [ 'type' => ['integer', 'null'] ],
				'order_id'             => [ 'type' => ['integer', 'null'] ],
				'seat_count'           => [ 'type' => ['integer', 'null'] ],
				'used_seat_count'      => [ 'type' => 'integer' ],
				'remaining_seat_count' => [ 'type' => ['integer', 'null'] ],
				'member_count'         => [ 'type' => 'integer' ],
				'membership_end_date'  => [ 'type' => ['string', 'null'] ],
			],
		];
	}
}
