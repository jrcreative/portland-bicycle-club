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

namespace SkyVerge\WooCommerce\Memberships\Teams\Admin;

use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Helpers\ScriptHelper;

defined( 'ABSPATH' ) or exit;

/**
 * Profile fields handler.
 *
 * @since 1.4.1
 */
class Profile_Fields {


	/** @var string ID of the team registration page visibility option */
	const VISIBILITY_TEAM_REGISTRATION = 'team-member-registration';


	/**
	 * Profile_Fields constructor.
	 *
	 * @since 1.4.1
	 */
	public function __construct() {

		// add a "Team member registration" option to the field visibility settings
		add_filter( 'wc_memberships_profile_fields_visibility_options', [ $this, 'add_teams_visibility_option' ] );

		// add the team registration option to the field visibility options
		add_action( 'wc_memberships_after_profile_fields_data', [ $this, 'handle_profile_fields_data' ] );
	}


	/**
	 * Adds a "Team member registration" option to the field visibility settings.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 *
	 * @param array $options existing visibility options
	 * @return array
	 */
	public function add_teams_visibility_option( $options ) {

		$options[ self::VISIBILITY_TEAM_REGISTRATION ] = $this->get_visibility_option_label();

		return $options;
	}


	/**
	 * Adds the team registration option to the field visibility options.
	 *
	 * @internal
	 *
	 * @since 1.4.1
	 */
	public function handle_profile_fields_data() {

		$team_plan_ids      = array_filter( wc_memberships()->get_plans_instance()->get_available_membership_plans( 'ids' ), [ wc_memberships_for_teams()->get_membership_plans_instance(), 'has_membership_plan_team_products' ] );
		$team_plan_ids_json = json_encode( array_values( $team_plan_ids ) );

		$option_label = $this->get_visibility_option_label();
		$option_value = self::VISIBILITY_TEAM_REGISTRATION;

        ScriptHelper::addInlinejQuery( 'woocommerce-memberships-for-teams-admin', "
			( function( $ ) {

				$( '#membership_plan_ids' ).on( 'change', function() {

					var selectedPlanIDs = $( this ).val();
					var teamsOption     = new Option( '{$option_label}', '{$option_value}', false, false );

					if ( selectedPlanIDs !== null ) {

						var teamPlanIDs = {$team_plan_ids_json};

						if ( teamPlanIDs.length ) {

							teamPlanIDs = Object.values( teamPlanIDs ).filter( function( id ) {
								return ( selectedPlanIDs.indexOf( id.toString() ) !== -1 )
							} );
						}
					}

					if ( selectedPlanIDs === null || teamPlanIDs.length !== 0 ) {

						if ( $( '#visibility option[value=\"{$option_value}\"]' ).length === 0 ) {
							$( '#visibility' ).append( teamsOption ).trigger( 'change' );
						}

					} else {

						$( '#visibility option[value=\"{$option_value}\"]' ).remove().trigger( 'change' );
					}

				} ).trigger( 'change' );

			} ) ( jQuery );
		" );
	}


	/**
	 * Gets the label for the teams visibility option.
	 *
	 * @since 1.4.1
	 *
	 * @return string
	 */
	private function get_visibility_option_label() {

		return __( 'Team member registration', 'woocommerce-memberships-for-teams' );
	}


}
