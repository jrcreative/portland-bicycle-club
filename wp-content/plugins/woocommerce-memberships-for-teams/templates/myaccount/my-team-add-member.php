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

defined( 'ABSPATH' ) or exit;

/**
 * Renders the team members table on My Account page.
 *
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area $teams_area teams area handler instance
 *
 * @version 1.5.2
 * @since 1.0.0
 */

$seat_count      = $team->get_seat_count();
$remaining_seats = $team->get_remaining_seat_count();
$fields          = wc_memberships_for_teams()->get_frontend_instance()->get_add_team_member_form_fields();
$current_member  = wc_memberships_for_teams_get_team_member( $team, get_current_user_id() );

if ( $current_member && $current_member->has_role( 'manager' ) && 'yes' !== get_option( 'wc_memberships_for_teams_managers_may_manage_managers', 'yes' ) ) {
	unset( $fields['role']['options']['manager'] );
}

?>
<div class="woocommerce-account-my-teams">
	<?php

	/**
	 * Fires before the Add Member section in My Account page.
	 *
	 * @since 1.0.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
	 */
	do_action( 'wc_memberships_for_teams_before_my_team_add_member', $team );

	?>
	<p>
		<?php

		if ( $seat_count > 0 ) {

			$seats_message = sprintf(
				/* translators: Placeholders: %1$s - the noun used to represent a team (singular), %2$s - opening <strong> HTML tag, %3$s - seat count, %4$s - closing </strong> HTML tag */
				_n( 'This %1$s has %2$s%3$s seat remaining%4$s.', 'This %1$s has %2$s%3$s seats remaining%4$s.', $remaining_seats, 'woocommerce-memberships-for-teams'  ),
				esc_html( wc_memberships_for_teams()->get_singular_team_noun() ),
				'<strong>',
				esc_html( (string) $remaining_seats ),
				'</strong>'
			);

		} else {

			$seats_message = ucfirst( sprintf(
				/* translators: Placeholders: %1$s - the noun used to represent a team (singular), %2$s - opening <strong> HTML tag, %3$s - closing </strong> HTML tag */
				esc_html__( 'This %1$s has %2$sunlimited seats%3$s.', 'woocommerce-memberships-for-teams' ),
				esc_html( wc_memberships_for_teams()->get_singular_team_noun() ),
				'<strong>',
				'</strong>'
			) );
		}

		$is_team_owner = $team->is_user_owner( get_current_user_id() );

		if ( $is_team_owner && ! $team->is_user_member( get_current_user_id() ) ) {

			$action_url = add_query_arg( array(
				'action' => 'add_owner_as_team_member',
			), wp_nonce_url( $teams_area->get_teams_area_url( $team, 'add-member' ), 'add-owner-as-team-member-' . $team->get_id() ) );

			$owner_message = ucfirst( sprintf(
				/* translators: Placeholders: %1$s - HTML opening tag, %2$s - HTML closing tag, %3$s - the noun used to represent a team (singular) */
				esc_html__( 'You can %1$sadd yourself as a member%2$s, share your %3$s registration link, or manually add new members below.', 'woocommerce-memberships-for-teams' ),
				'<a href="' . esc_url( $action_url ) . '"><strong>',
				'</strong></a>',
				esc_html( wc_memberships_for_teams()->get_singular_team_noun() )
			) );

		} elseif ( $remaining_seats > 0 ) {

			$owner_message = ucfirst( sprintf(
				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				esc_html__( 'You can share your %s registration link or manually add new members below.', 'woocommerce-memberships-for-teams' ),
				esc_html( wc_memberships_for_teams()->get_singular_team_noun() )
			) );

		} else {

			$owner_message = '';

			if ( $is_team_owner ) {

				$can_add_seats    = $team->can_add_seats();
				$can_remove_seats = $team->can_remove_seats();

				if ( $can_add_seats && $can_remove_seats ) {

					$owner_message = sprintf(
						/* translators: Placeholders; %1$s - opening HTML <a> link tag, %2$s - the noun used to represent a team (singular), %3$s - closing HTML </a> link tag, %4$s - opening HTML <a> link tag, %5$s - closing HTML </a> link tag */
						esc_html__( 'You can purchase more seats on the %1$s%2$s Settings page%3$s or remove members from the %4$sMembers page%5$s.', 'woocommerce-memberships-for_teams' ),
						'<a href="' . esc_url( $teams_area->get_teams_area_url( $team, 'settings' ) ) . '">',
						esc_html( ucfirst( wc_memberships_for_teams()->get_singular_team_noun() ) ),
						'</a>',
						'<a href="' . esc_url( $teams_area->get_teams_area_url( $team, 'members' ) ) . '">',
						'</a>'
					);

				} elseif ( $can_add_seats ) {

					$owner_message = sprintf(
						/* translators: Placeholders; %1$s - opening HTML <a> link tag, %2$s - the noun used to represent a team (singular), %3$s - closing HTML </a> link tag */
						esc_html__( 'You can purchase more seats on the %1$s%2$s Settings page%3$s.', 'woocommerce-memberships-for_teams' ),
						'<a href="' . esc_url( $teams_area->get_teams_area_url( $team, 'settings' ) ) . '">',
						esc_html( ucfirst( wc_memberships_for_teams()->get_singular_team_noun() ) ),
						'</a>'
					);
				}
			}
		}

		// print messages together with a space between them
		printf( '%1$s %2$s', $seats_message, $owner_message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		?>
	</p>

	<?php if ( $remaining_seats > 0 || null === $team->get_seat_count() ) : ?>

		<h3><?php esc_html_e( 'Registration Link', 'woocommerce-memberships-for-teams' ); ?></h3>

		<p><?php echo esc_html( ucfirst( sprintf(
			/* translators: Placeholders: %1$s - the noun used to represent a team (singular) */
			__( 'This registration link will allow members to register themselves for your %1$s. Please use caution when sharing this, as it allows any visitor to add themselves to your %1$s.', 'woocommerce-memberships-for-teams' ),
			wc_memberships_for_teams()->get_singular_team_noun()
		) ) ); ?></p>

		<form id="registration-link-form" method="post">

			<?php wp_nonce_field( 'regenerate-team-registration-link-' . $team->get_id(), '_team_link_nonce' ); ?>

			<input
				type="hidden"
				name="regenerate_team_registration_link"
				value="<?php echo esc_attr( $team->get_id() ); ?>"
			/>

			<p class="form-row" id="registration-link_field">
				<input
					type="text"
					class="input-text"
					name="registration_link"
					id="registration-link"
					value="<?php echo esc_url( $team->get_registration_url() ); ?>"
				/>
				<?php if ( current_user_can( 'wc_memberships_for_teams_manage_team_settings', $team ) ) : ?>
					<button class="woocommerce-Button button regenerate-link" type="submit"><?php esc_html_e( 'Regenerate link', 'woocommerce-memberships-for-teams' ); ?></button>
				<?php endif; ?>
			</p>

		</form>

	<?php endif; ?>

	<h3><?php esc_html_e( 'Add Member', 'woocommerce-memberships-for-teams' ); ?></h3>

	<?php if ( $seat_count > 0 && ! $remaining_seats ) : ?>

		<p><?php echo esc_html( ucfirst( sprintf(
			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			__( 'You can\'t add more members because your %s has no more seats left.', 'woocommerce-memberships-for-teams' ),
			wc_memberships_for_teams()->get_singular_team_noun()
		) ) ); ?></p>

	<?php elseif ( ! $team->can_be_managed() ) : ?>

		<p><?php echo esc_html( $team->get_management_decline_reason( 'add_member' ) ); ?></p>

	<?php else : ?>

		<?php

		if ( wc_memberships_for_teams()->get_invitations_instance()->should_skip_invitations() ) {
			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$additional_information = ucfirst( sprintf( esc_html__( 'Your %s member will be added automatically if they are registered, or receive an invitation via email.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
		} else {
			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$additional_information = ucfirst( sprintf( esc_html__( 'Your %s member will receive an invitation via email.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
		}

		?>
		<p>
			<?php printf(
				/* translators: Placeholder: %s - additional information */
				esc_html__( 'Enter member details - %s', 'woocommerce-memberships-for-teams' ),
				esc_html( lcfirst( $additional_information ) )
			); ?>
		</p>

		<form id="add-member-form" method="POST">

			<?php wp_nonce_field( 'add-team-member-' . $team->get_id(), '_team_add_member_nonce' ); ?>

			<input
				type="hidden"
				name="add_team_member"
				value="<?php echo esc_attr( $team->get_id() ); ?>"
			/>

			<div class="form-fields">
				<?php foreach ( $fields as $key => $field ) : ?>
					<?php
						$value = isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ? $_POST[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						if ( ! is_null( $value ) ) {
							if ( 'textarea' === ( $field['type'] ?? null ) ) {
								$value = sanitize_textarea_field( $value );
							} else {
								$value = wc_clean( $value );
							}
						}
						?>
					<?php woocommerce_form_field( $key, $field, $value ); ?>
				<?php endforeach; ?>
			</div>

			<input
				type="submit"
				class="woocommerce-Button button"
				value="<?php esc_attr_e( 'Add member', 'woocommerce-memberships-for-teams' ); ?>"
			/>

		</form>

	<?php endif; ?>

	<?php

	/**
	 * Fires after the Add Member section in My Account page.
	 *
	 * @since 1.0.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
	 */
	do_action( 'wc_memberships_for_teams_after_my_team_add_member', $team );

	?>
</div>
