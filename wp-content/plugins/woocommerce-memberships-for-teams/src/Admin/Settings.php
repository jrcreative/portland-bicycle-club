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

use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Admin Settings class
 *
 * @since 1.0.0
 */
class Settings {


	/**
	 * Sets up the settings class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add settings & settings section
		add_filter( 'woocommerce_get_sections_memberships', [ $this, 'add_teams_section'  ] );
		add_filter( 'woocommerce_get_settings_memberships', [ $this, 'add_teams_settings' ], 10, 2 );

		// output special fields
		add_action( 'woocommerce_admin_field_team_noun', [ $this, 'output_team_noun_field_html' ] );

		// save and validate special settings
		add_action( 'woocommerce_settings_save_memberships', [ $this, 'update_settings' ] );
	}


	/**
	 * Adds Teams section to Memberships settings screen.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $sections associative array of sections
	 * @return array
	 */
	public function add_teams_section( $sections ) {

		$sections['teams'] = __( 'Teams', 'woocommerce-memberships-for-teams' );

		return $sections;
	}


	/**
	 * Adds Teams settings to Memberships settings screen.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings array of the plugin settings
	 * @param string $current_section the current section being output
	 * @return array
	 */
	public function add_teams_settings( $settings, $current_section ) {

		if ( 'teams' === $current_section ) {

			/**
			 * Filters Memberships for Teams settings.
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings array of teams settings
			 */
			$settings = (array) apply_filters( 'wc_memberships_for_teams_settings', [

				[
					'name'     => __( 'Teams', 'woocommerce-memberships-for-teams' ),
					'type'     => 'title',
				],

				[
					/* @see Settings::output_team_noun_field_html */
					'type'     => 'team_noun',
				],

				[
					'type'     => 'checkbox',
					'id'       => 'wc_memberships_for_teams_allow_removing_members',
					'name'     => __( 'Allow removing members', 'woocommerce-memberships-for-teams' ),
					'desc'     => __( 'If enabled, team owners and managers can remove members from their team.', 'woocommerce-memberships-for-teams' ),
					'default'  => 'yes',
				],

				[
					'type'     => 'checkbox',
					'id'       => 'wc_memberships_for_teams_owners_must_take_seat',
					'name'     => __( 'Owners must be members', 'woocommerce-memberships-for-teams' ),
					'desc'     => __( 'If enabled, team owners must take up a seat in their team.', 'woocommerce-memberships-for-teams' ),
					'default'  => 'no',
				],

				[
					'type'    => 'checkbox',
					'id'      => 'wc_memberships_for_teams_managers_may_manage_managers',
					'name'    => __( 'Allow managers to add or remove other managers', 'woocommerce-memberships-for-teams' ),
					'desc'    => __( 'If enabled, team managers can add/remove other managers. Otherwise, only a team owner may add or remove managers.', 'woocommerce-memberships-for-teams' ),
					'default' => 'yes',
				],

				[
					'type'    => 'sectionend',
				],

			] );
		}

		return $settings;
	}


	/**
	 * Generates and outputs the setting field for the team noun.
	 *
	 * @see Settings::add_teams_settings()
	 *
	 * @internal
	 *
	 * @since 1.3.0
	 *
	 * @param array $data field data
	 */
	public function output_team_noun_field_html( $data ) {

		$default_singular = wc_memberships_for_teams()->get_default_singular_team_noun();
		$default_plural   = wc_memberships_for_teams()->get_default_plural_team_noun();
		$value_singular   = wc_memberships_for_teams()->get_singular_team_noun();
		$value_plural     = wc_memberships_for_teams()->get_plural_team_noun();

		// clear values if these match perfectly with the translatable default
		if ( $value_singular === $default_singular ) {
			$value_singular = '';
		}
		if ( $value_plural === $default_plural ) {
			$value_plural = '';
		}

		?>
		<th scope="row" class="titledesc">
			<?php esc_html_e(  'Teams are called', 'woocommerce-memberships-for-teams' ); ?>
			<?php echo wc_help_tip( __( 'Enter the singular and plural terms you\'d like to use instead of "team" and "teams" (e.g. "family" and "families").', 'woocommerce-memberships-for-teams' ) ); ?>
		</th>
		<td class="forminp">
			<fieldset id="wc_memberships_for_teams_team_noun">
				<table>
					<tr>
						<th><label for="wc_memberships_for_teams_team_noun_singular"><?php esc_html_e(  'Singular', 'woocommerce-memberships-for-teams' ); ?></label></th>
						<th><label for="wc_memberships_for_teams_team_noun_plural"><?php esc_html_e(  'Plural', 'woocommerce-memberships-for-teams' ); ?></label></th>
					</tr>
					<tr>
						<td>
							<input
								type="text"
								name="wc_memberships_for_teams_team_noun_singular"
								id="wc_memberships_for_teams_team_noun_singular"
								placeholder="<?php echo esc_html( $default_singular ); ?>"
								value="<?php echo esc_html( $value_singular ); ?>"
							/>
						</td>
						<td>
							<input
								type="text"
								name="wc_memberships_for_teams_team_noun_plural"
								id="wc_memberships_for_teams_team_noun_plural"
								placeholder="<?php echo esc_html( $default_plural ); ?>"
								value="<?php echo esc_html( $value_plural ); ?>"
							/>
						</td>
					</tr>
				</table>
			</fieldset>
		</td>
		<?php
	}


	/**
	 * Validates and updates specific plugin settings.
	 *
	 * @internal
	 *
	 * @since 1.3.0
	 */
	public function update_settings() {

		if ( ! wc_memberships_for_teams()->is_plugin_settings() ) {
			return;
		}

		$existing_singular  = get_option( 'wc_memberships_for_teams_team_noun_singular', '' );
		$existing_plural    = get_option( 'wc_memberships_for_teams_team_noun_plural', '' );
		$existing_values    = ! empty( $existing_singular ) || ! empty( $existing_plural );
		$default_singular   = wc_memberships_for_teams()->get_default_singular_team_noun();
		$default_plural     = wc_memberships_for_teams()->get_default_plural_team_noun();
		$singular_team_noun = strtolower( wc_clean( trim( Framework\SV_WC_Helper::get_posted_value( 'wc_memberships_for_teams_team_noun_singular', $default_singular ) ) ) );
		$plural_team_noun   = strtolower( wc_clean( trim( Framework\SV_WC_Helper::get_posted_value( 'wc_memberships_for_teams_team_noun_plural',   $default_plural ) ) ) );
		$deleted_values     = $existing_values && empty( $singular_team_noun ) && empty( $plural_team_noun );

		// ensures that, if set to their defaults, the values are translatable via gettext
		if ( empty( $singular_team_noun ) || $default_singular === $singular_team_noun ) {
			delete_option( 'wc_memberships_for_teams_team_noun_singular' );
		} else {
			update_option( 'wc_memberships_for_teams_team_noun_singular', $singular_team_noun );
		}
		if ( empty( $plural_team_noun ) || $default_plural === $plural_team_noun ) {
			delete_option( 'wc_memberships_for_teams_team_noun_plural' );
		} else {
			update_option( 'wc_memberships_for_teams_team_noun_plural', $plural_team_noun );
		}

		// inform the user about the changes
		if ( $deleted_values ) {

			// values have been deleted, hence restoring the defaults
			wc_memberships_for_teams()->get_message_handler()->add_warning( __( 'Heads up! You\'re now using the default "team" and "teams" terminology.', 'woocommerce-memberships-for-teams' ) );

		} elseif ( $singular_team_noun === $plural_team_noun && ! empty( $singular_team_noun ) && ! empty( $plural_team_noun ) ) {

			// admin chose the same word for both singular and plural form (may be correct in some languages)
			wc_memberships_for_teams()->get_message_handler()->add_warning( sprintf(
				/* translators: Placeholder: %s - noun just saved to represent "team" (for both singular and plural) */
				__( 'Heads up! You\'re using the same word - "%s" - to replace both "team" and "teams". Please ensure this term is accurate as a single and plural term.', 'woocommerce-memberships-for-teams' ),
				$singular_team_noun
			) );

		} elseif ( ( $existing_singular !== $singular_team_noun || $existing_plural !== $plural_team_noun ) && ( $singular_team_noun !== $default_singular && $plural_team_noun !== $default_plural ) ) {

			// values have been changed from previous ones
			wc_memberships_for_teams()->get_message_handler()->add_message( sprintf(
				/* translators: Placeholders: %1$s - noun just saved to represent "team" (singular), %2$s - noun just saved to represent "teams" (plural) */
				__( 'You have changed the default terminology for Teams for Memberships to "%1$s" and "%2$s".', 'woocommerce-memberships-for-teams' ),
				$singular_team_noun ?: $default_singular,
				$plural_team_noun ?: $default_plural
			) );
		}
	}


}
