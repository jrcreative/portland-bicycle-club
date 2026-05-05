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
 * Team membership ending soon email.
 *
 * @var string $email_heading email heading
 * @var \SkyVerge\WooCommerce\Memberships\Teams\Emails\Membership_Ending_Soon $email email object
 * @var \SkyVerge\WooCommerce\Memberships\Teams\Team $team the team instance
 * @var string $additional_content optional additional user-defined content
 *
 * @version 1.5.4
 * @since 1.0.0
 */

$owner = $team->get_owner();
$plan  = $team->get_plan();

$site_title          = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
$membership_end_date = date_i18n( wc_date_format(), $team->get_local_membership_end_date( 'timestamp' ) );

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php printf(
		/* translators: Placeholder: %s - email recipient's name */
		esc_html__( 'Hey %s,', 'woocommerce-memberships-for-teams' ),
		esc_html( $owner->display_name )
	); ?>
</p>

<p>
	<?php echo esc_html( ucfirst( sprintf(
		/* translators: Placeholders: %1$s - the noun used to represent a team (singular), %2$s - site title, %3$s - membership end date */
		__( 'Heads up: your %1$s membership access at %2$s is ending soon! Your membership access will stop on %3$s.', 'woocommerce-memberships-for-teams' ),
		wc_memberships_for_teams()->get_singular_team_noun(),
		$site_title,
		$membership_end_date
	) ) ); ?>
</p>

<p>
	<?php echo esc_html( ucfirst( sprintf(
		/* translators: Placeholder: %s - membership plan name */
		__( 'If you would like to continue having access to %s, please renew your membership.', 'woocommerce-memberships-for-teams' ),
		$plan->get_name()
	) ) ); ?>
</p>

<p><a href="<?php echo esc_url( $team->get_renew_membership_url() ); ?>"><?php echo esc_html( ucfirst( sprintf(
	/* translators: Placeholder: %s - noun used to represent a team (singular ) */
	__( 'Click here to log in and renew your %s membership now', 'woocommerce-memberships-for-teams' ),
	wc_memberships_for_teams()->get_singular_team_noun()
) ) ); ?></a>.</p>

<?php

if ( ! empty( $additional_content ) ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
