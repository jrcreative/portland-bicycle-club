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

namespace SkyVerge\WooCommerce\Memberships\Teams;

use SkyVerge\WooCommerce\Memberships\Profile_Fields as Profile_Fields_Handler;
use SkyVerge\WooCommerce\Memberships\Teams\Team;
use SkyVerge\WooCommerce\Memberships\Teams\Team_Member;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Profile Fields handler class.
 *
 * @since 1.4.1
 */
class Profile_Fields {


	/**
	 * Sets up the Profile Fields handler.
	 *
	 * @since 1.4.1
	 */
	public function __construct() {

		add_action( 'wc_memberships_for_teams_create_team_from_order', [ $this, 'copy_team_product_profile_fields_data_from_order_item' ], 10, 2 );
		add_action( 'wc_memberships_for_teams_add_team_member',        [ $this, 'set_member_profile_fields_from_purchase' ], 10, 3 );
	}


	/**
	 * Copies profile field values from order item meta to user meta.
	 *
	 * The metadata will be used later to set profile field values on a membership when the owner of the team takes a seat.
	 *
	 * @see Profile_Fields::set_member_profile_fields_from_purchase()
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param Team $team the team that was created
	 * @param array $args contextual arguments including the ID of the associated order item and user
	 */
	public function copy_team_product_profile_fields_data_from_order_item( $team, $args ) {

		if ( ! isset( $args['item_id'], $args['user_id'] ) ) {
			return;
		}

		try {

			$profile_fields_data = wc_get_order_item_meta( $args['item_id'], Profile_Fields_Handler::ORDER_ITEM_PROFILE_FIELDS_META );

			update_user_meta( $args['user_id'], Profile_Fields_Handler::ORDER_ITEM_PROFILE_FIELDS_META, $profile_fields_data );

		} catch ( \Exception $e ) {

			// order item couldn't be loaded
			return;
		}
	}


	/**
	 * Adds profile fields to the membership created after a product team purchase where the owner takes a seat.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param Team_Member $member the team member instance
	 * @param Team $team the team instance
	 * @param \WC_Memberships_User_Membership $user_membership the related user membership instance
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	public function set_member_profile_fields_from_purchase( $member, $team, $user_membership ) {

		if ( ! $user_membership instanceof \WC_Memberships_User_Membership ) {
			return;
		}

		if ( ! $profile_fields_data = get_user_meta( $user_membership->get_user_id(), Profile_Fields_Handler::ORDER_ITEM_PROFILE_FIELDS_META, true ) ) {
			return;
		}

		if ( ! is_array( $profile_fields_data ) ) {
			return;
		}

		$this->set_user_membership_profile_fields( $user_membership, $profile_fields_data );

		delete_user_meta( $user_membership->get_user_id(), Profile_Fields_Handler::ORDER_ITEM_PROFILE_FIELDS_META );
	}


	/**
	 * Adds profile fields to a user membership.
	 *
	 * @since 1.4.1
	 *
	 * @param \WC_Memberships_User_Membership $user_membership the related user membership instance
	 * @param array $profile_fields_data profile fields data indexed by field slug
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	private function set_user_membership_profile_fields( $user_membership, $profile_fields_data ) {

		foreach ( $profile_fields_data as $slug => $value ) {

			$definition = Profile_Fields_Handler::get_profile_field_definition( $slug );

			if ( ! $definition ) {
				continue;
			}

			$user_membership->set_profile_field( $slug, $value );
		}
	}


}
