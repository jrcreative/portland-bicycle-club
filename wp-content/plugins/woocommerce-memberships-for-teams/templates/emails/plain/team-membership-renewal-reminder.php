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
 * Team membership renewal reminder email.
 *
 * @var string $email_heading email heading
 * @var \SkyVerge\WooCommerce\Memberships\Teams\Emails\Membership_Renewal_Reminder $email email object
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

echo '= ' . esc_html( $email_heading ) . " =\n\n";

printf(
	/* translators: Placeholder: %s - team owner name */
	esc_html__( 'Hey %s', 'woocommerce-memberships-for-teams' ),
	esc_html( $owner->display_name )
);

echo "\r\n\r\n";

echo esc_html( ucfirst( sprintf(
	/* translators: Placeholders: %1$s - the noun used to represent a team (singular), %2$s - site title, %3$s - membership end date */
	__( 'Your %1$s membership access at %2$s expired on %3$s!', 'woocommerce-memberships-for-teams' ),
	wc_memberships_for_teams()->get_singular_team_noun(),
	$site_title,
	$membership_end_date
) ) );

echo "\r\n";

printf(
	/* translators: Placeholder: %s - membership plan name */
	esc_html__( 'If you would like to continue having access to %s, please renew your membership.', 'woocommerce-memberships-for-teams' ),
	esc_html( $plan->get_name() )
);

echo "\r\n\r\n";

echo esc_html( ucfirst( sprintf(
	/* translators: Placeholder: %s - the noun used to represent the team (singular) */
	__( 'Use the link below to log in and renew your %s membership now.', 'woocommerce-memberships-for-teams' ),
	wc_memberships_for_teams()->get_singular_team_noun()
) ) );

echo "\r\n";

echo esc_url( $team->get_renew_membership_url() ) . "\r\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( ! empty( $additional_content ) ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
