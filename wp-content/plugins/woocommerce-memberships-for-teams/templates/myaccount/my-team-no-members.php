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
 * Renders the team members table on My Account page
 *
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
 *
 * @version 1.6.0
 * @since 1.6.0
 */

?>

<p>
	<?php

	$no_members_text = ucfirst( sprintf(
	/* translators: Placeholder: %s - noun used to represent a team (singular) */
		esc_html__( 'Looks like your %s has no members!', 'woocommerce-memberships-for-teams' ),
		esc_html( wc_memberships_for_teams()->get_singular_team_noun() )
	) );

	/**
	 * Filters the text for no team members in My Account area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $no_members_text the text displayed for teams with no members
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
	 */
	echo wp_kses_post( (string) apply_filters( 'wc_memberships_for_teams_my_team_members_no_members_text', $no_members_text, $team ) );

	?>
</p>
