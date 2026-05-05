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

namespace SkyVerge\WooCommerce\Memberships\Teams\Frontend;

use SkyVerge\WooCommerce\Memberships\Profile_Fields as Profile_Fields_Handler;
use SkyVerge\WooCommerce\Memberships\Teams\Product;
use SkyVerge\WooCommerce\Memberships\Teams\Team;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Profile fields handler.
 *
 * @since 1.4.1
 */
class Profile_Fields {


	/**
	 * Profile_Fields constructor.
	 *
	 * @since 1.4.1
	 */
	public function __construct() {

		// show profile fields on team product pages
		add_filter( 'wc_memberships_get_membership_plans_for_product',                 [ $this, 'add_membership_plans_for_product' ], 10, 2 );
		add_action( 'wc_memberships_before_product_profile_fields',                    [ $this, 'open_product_profile_fields_wrapper' ], 0 );
		add_action( 'wc_memberships_after_product_profile_fields',                     [ $this, 'close_product_profile_fields_wrapper' ], 99 );
		add_filter( 'wc_memberships_should_process_product_profile_fields_submission', [ $this, 'should_process_product_profile_fields_submission' ], 10, 2 );

		// add the profile fields to a team membership registration form
		add_action( 'woocommerce_register_form',                        [ $this, 'add_join_team_membership_profile_fields' ] );
		add_action( 'woocommerce_memberships_for_teams_join_team_form', [ $this, 'add_join_team_membership_profile_fields' ] );

		// validate the profile fields from a team membership registration form
		add_action( 'woocommerce_process_registration_errors',            [ $this, 'validate_register_join_team_profile_fields' ], 9 );
		add_action( 'woocommerce_memberships_for_teams_before_join_team', [ $this, 'validate_join_team_profile_fields' ], 10, 2 );

		// set a member's profile fields after joining a team
		add_action( 'woocommerce_memberships_for_teams_joined_team', [ $this, 'set_member_profile_fields_from_joining_team' ], 10, 2 );
	}


	/**
	 * Adds the membership plan associated with a team product to the list of plans that the product grants access to.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param \WC_Memberships_Membership_Plan[] $membership_plans associative array of membership plan IDs and objects
	 * @param \WC_Product $product product object
	 * @return \WC_Memberships_Membership_Plan[]
	 */
	public function add_membership_plans_for_product( $membership_plans, $product ) {

		if ( $product instanceof \WC_Product && Product::has_team_membership( $product ) && $membership_plan = Product::get_membership_plan( $product ) ) {
			$membership_plans[] = $membership_plan;
		}

		return $membership_plans;
	}


	/**
	 * Adds the opening HTML tag for a container element for the profile field form fields.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 */
	public function open_product_profile_fields_wrapper() {

		if ( ! $this->is_team_product_page() ) {
			return;
		}

		echo '<div class="wc-teams-for-memberships-profile-fields-wrapper" ' . ( ! $this->should_team_owner_take_a_seat() ?  'style="display:none"' : '' ) . '>';
	}


	/**
	 * Determines whether the current page is the page for a team product.
	 *
	 * @since 1.4.1
	 *
	 * @return bool
	 */
	private function is_team_product_page() {

		if ( ! is_product() ) {
			return false;
		}

		$product = wc_get_product( get_queried_object_id() );

		if ( ! $product instanceof \WC_Product ) {
			return false;
		}

		return Product::has_team_membership( $product );
	}


	/**
	 * Determines whether the team owner is going to take a seat after they purchase a team product.
	 *
	 * Team owners take a seat if the choose to on the product page or when the plugin is configured to always reserve a seat for the owner.
	 *
	 * @since 1.4.1
	 *
	 * @return bool
	 */
	private function should_team_owner_take_a_seat() {

		return wc_string_to_bool( Framework\SV_WC_Helper::get_requested_value( 'team_owner_takes_seat' ) ) || 'yes' === get_option( 'wc_memberships_for_teams_owners_must_take_seat' );
	}


	/**
	 * Adds the closing HTML tag for the container element for the profile field form fields.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 */
	public function close_product_profile_fields_wrapper() {

		if ( ! $this->is_team_product_page() ) {
			return;
		}

		echo '</div>';

		$this->enqueue_team_product_profile_fields_js();
	}


	/**
	 * Enqueues a JS snippet to toggle visibility of profile field form fields in the product page.
	 *
	 * @since 1.4.1
	 */
	private function enqueue_team_product_profile_fields_js() {

        Framework\Helpers\ScriptHelper::addInlinejQuery( 'woocommerce-memberships-for-teams-front-end', "
			( function() {

				var \$seatCountLabel = $( '.team-seat-count-label' ),
					\$profileFieldsWrapper = $( '.wc-teams-for-memberships-profile-fields-wrapper' );

				// move profile fields above the Number of Seats label
				if ( \$seatCountLabel.length ) {

					\$profileFieldsWrapper.insertBefore( \$seatCountLabel );

					// re-initialize any profile field enhanced dropdown form field
					if ( $.fn.select2 ) {
						$( '.wc-memberships-member-profile-field select' ).select2( 'destroy' ).select2();
					}
				}

				// toggle profile fields visibility when owner takes a seat
				$( '[name=\"team_owner_takes_seat\"]' ).on( 'change', function() {

					if ( $( this ).prop( 'checked' ) ) {
						\$profileFieldsWrapper.slideDown( 250 );
					} else {
						\$profileFieldsWrapper.slideUp( 250 );
					}
				} ).trigger( 'change' );

			} )();
		" );
	}


	/**
	 * Determines whether product profile fields submission should be processed.
	 *
	 * Disables profile fields processing for team products where the owner doesn't take a seat.
	 *
	 * @internal
	 *
	 * @param bool $process_product_profile_fields whether product profile fields submission should be processed
	 * @param \WC_Product $product product being added to cart
	 */
	public function should_process_product_profile_fields_submission( $process_product_profile_fields, $product ) {

		if ( $product instanceof \WC_Product && Product::has_team_membership( $product ) && ! $this->should_team_owner_take_a_seat() ) {
			return false;
		}

		return $process_product_profile_fields;
	}


	/**
	 * Adds the profile fields to a team membership registration form.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param Team|null $fields_team the team instance
	 */
	public function add_join_team_membership_profile_fields( $fields_team = null ) {
		global $team;

		// if no team is passed, try and get the global
		if ( ! $fields_team ) {
			$fields_team = $team;
		}

		// bail if no team object could be determined
		if ( ! $fields_team instanceof Team ) {
			return;
		}

		if ( ! wc_memberships_for_teams()->get_frontend_instance()->is_join_team_page() ) {
			return;
		}

		// remove Memberships core's register form handling, as we're specifically in the Join Team flow
		remove_action( 'woocommerce_register_form', [ wc_memberships()->get_frontend_instance()->get_profile_fields_instance(), 'add_sign_up_form_profile_fields' ], 999 );

		if ( ! Profile_Fields_Handler::is_using_profile_fields() ) {
			return;
		}

		$profile_fields            = [];
		$profile_field_definitions = $this->get_team_registration_profile_field_definitions( $fields_team );

		$select_enqueued = false;

		foreach ( $profile_field_definitions as $profile_field_definition ) {

			// init select2 (once) for the enhanced select fields
			if ( ! $select_enqueued && $profile_field_definition->is_type( [ Profile_Fields_Handler::TYPE_SELECT, Profile_Fields_Handler::TYPE_MULTISELECT ] ) ) {

                Framework\Helpers\ScriptHelper::addInlinejQuery( 'woocommerce-memberships-for-teams-front-end', " $( '.wc-memberships-member-profile-field select' ).select2(); " );

				$select_enqueued = true;
			}

			try {

				$profile_field = new Profile_Fields_Handler\Profile_Field();
				$profile_field->set_user_id( get_current_user_id() );
				$profile_field->set_slug( $profile_field_definition->get_slug() );

				$profile_fields[] = $profile_field;

			} catch ( Framework\SV_WC_Plugin_Exception $exception ) {}
		}

		foreach ( $profile_fields as $profile_field ) {
			wc_memberships_profile_field_form_field( $profile_field );
		}
	}


	/**
	 * Validates the profile fields when a guest is joining a team.
	 *
	 * @since 1.4.1
	 *
	 * @param \WP_Error $validation_error
	 * @return \WP_Error
	 */
	public function validate_register_join_team_profile_fields( $validation_error ) {

		$token = ! empty( $_POST['wc_memberships_for_teams_token'] ) ? wc_clean( $_POST['wc_memberships_for_teams_token'] ) : '';

		if ( $validation_error instanceof \WP_Error && $token ) {

			$team = wc_memberships_for_teams()->get_frontend_instance()->get_join_page_team( $token );

			if ( $team instanceof Team ) {

				// remove Memberships core's register form handling, as we're specifically in the Join Team flow
				remove_filter( 'woocommerce_process_registration_errors', [ wc_memberships()->get_frontend_instance()->get_profile_fields_instance(), 'validate_sign_up_profile_fields' ] );

				try {

					// we don't have a user ID yet, but it doesn't need to be valid
					$this->validate_join_team_profile_fields( PHP_INT_MAX, $team );

				} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

					$validation_error->add( 'invalid_profile_field', $exception->getMessage() );
				}
			}
		}

		return $validation_error;
	}


	/**
	 * Validates the profile fields from a team membership registration form.
	 *
	 * Note that this method does not store any values. It is fired before a team is joined, so any profile field errors
	 * can be shown and fixed before the membership is created.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param int $user_id WordPress user ID for the user joining the team
	 * @param Team|null $team object for the team being joined
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	public function validate_join_team_profile_fields( $user_id, $team ) {

		if ( ! $team instanceof Team ) {
			return;
		}

		$posted_data = Framework\SV_WC_Helper::get_posted_value( 'member_profile_fields', [] );

		if ( empty( $posted_data ) || ! is_array( $posted_data ) ) {
			return;
		}

		$profile_field_definitions = $this->get_team_registration_profile_field_definitions( $team );

		foreach ( $profile_field_definitions as $profile_field_definition ) {

			$value = isset( $posted_data[ $profile_field_definition->get_slug() ] ) ? wc_clean( $posted_data[ $profile_field_definition->get_slug() ] ) : '';

			$profile_field = new Profile_Fields_Handler\Profile_Field();
			$profile_field->set_user_id( $user_id );
			$profile_field->set_slug( $profile_field_definition->get_slug() );
			$profile_field->set_value( $value );

			$validation = $profile_field->validate();

			foreach ( $validation->get_error_codes() as $error_code ) {

				// don't throw
				if ( Profile_Fields_Handler\Exceptions\Invalid_Field::ERROR_INVALID_PLAN === $error_code ) {
					continue;
				}

				throw new Framework\SV_WC_Plugin_Exception( $validation->get_error_message( $error_code ) );
			}
		}
	}


	/**
	 * Sets a member's profile fields after joining a team.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param int $user_id WordPress user ID for the user joining the team
	 * @param Team|null $team object for the team being joined
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	public function set_member_profile_fields_from_joining_team( $user_id, $team ) {

		if ( ! $team instanceof Team ) {
			throw new Framework\SV_WC_Plugin_Exception( __( 'Invalid team', 'woocommerce-memberships-for-teams' ), 404 );
		}

		$user_membership = wc_memberships_get_user_membership( $user_id, $team->get_plan_id() );

		if ( ! $user_membership instanceof \WC_Memberships_User_Membership ) {

			throw new Framework\SV_WC_Plugin_Exception( sprintf(
				/* translators: Placeholder: %d - user identifier */
				__( 'No user membership exists for user ID %d', 'woocommerce-memberships-for-teams' ),
				(int) $user_id
			), 404 );
		}

		$posted_data = Framework\SV_WC_Helper::get_posted_value( 'member_profile_fields', [] );

		if ( empty( $posted_data ) || ! is_array( $posted_data ) ) {
			return;
		}

		foreach ( $posted_data as $slug => $value ) {

			$definition = Profile_Fields_Handler::get_profile_field_definition( $slug );

			if ( ! $definition ) {
				continue;
			}

			$user_membership->set_profile_field( $slug, $value );
		}
	}


	/**
	 * Gets all of the profile field definitions for the given team.
	 *
	 * @since 1.4.1
	 *
	 * @param Team $team team instance
	 * @return Profile_Fields_Handler\Profile_Field_Definition[]
	 */
	private function get_team_registration_profile_field_definitions( Team $team ) {

		$plan_ids   = [ $team->get_plan_id() ];
		$visibility = [ \SkyVerge\WooCommerce\Memberships\Teams\Admin\Profile_Fields::VISIBILITY_TEAM_REGISTRATION ];

		if ( ! is_user_logged_in() ) {

			$plan_ids     = array_merge( $plan_ids, array_keys( wc_memberships_get_free_membership_plans() ) );
			$visibility[] = Profile_Fields_Handler::VISIBILITY_REGISTRATION_FORM;
		}

		return Profile_Fields_Handler::get_profile_field_definitions( [
			'membership_plan_ids' => $plan_ids,
			'visibility'          => $visibility,
			'editable_by'         => Profile_Fields_Handler\Profile_Field_Definition::EDITABLE_BY_CUSTOMER,
		] );
	}


}
