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

/**
 * Renders the team members section on My Account page
 *
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
 * @type int $paged the current page number
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area $teams_area teams area handler instance
 *
 * @version 1.4.2
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or exit;

$user_id = (int) $_GET['user_id'];
$member = wc_memberships_for_teams_get_team_member( $team, $user_id );

?>
<div class="woocommerce-account-my-teams">

	<?php

	/**
	 * Fires before the Learner Progress section in My Account page.
	 *
	 * @since 1.6.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member|false $member current team member
	 */
	do_action( 'wc_memberships_for_teams_before_my_team_learner_progress', $team, $member );

	if ( current_user_can( 'wc_memberships_for_teams_view_learner_progress_reports', $team ) ) :

		if ( $member instanceof \SkyVerge\WooCommerce\Memberships\Teams\Team_Member ) :

			/**
			 * Fires for the Learner Progress section in My Account page.
			 *
			 * @since 1.6.0
			 *
			 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
			 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member current team member
			 */
			do_action( 'wc_memberships_for_teams_my_team_learner_progress_content', $team, $member );

			if ( ! empty( $available_courses = wc_memberships_for_teams_courseware()->get_available_courses( $team, $member) ) ) :

				?>
				<div class="team-member-available-courses">
					<h3><?php esc_html_e( 'Other available courses', 'woocommerce-memberships-for-teams' ); ?></h3>

					<ul>
						<?php foreach ( $available_courses as $course ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $course->ID ) ) ?>"><?php echo esc_html( get_the_title( $course->ID ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>

				</div>
				<?php

			endif;

		else :

			?>
			<div class="woocommerce-error">
				<?php
					/* translators: Placeholder: %s - the noun used to represent a team (singular) */
					echo esc_html( ucfirst( sprintf( __( 'Invalid %s member.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) ) );
				?>
			</div>
			<?php

		endif;

	else :

		?>
		<div class="woocommerce-error">
			<?php
				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				echo esc_html( ucfirst( sprintf( __( 'You cannot view eLearning progress reports for this %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) ) );
			?>
		</div>
		<?php
	endif;

	/**
	 * Fires after the Learner Progress section in My Account page.
	 *
	 * @since 1.6.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member|false $member current team member
	 */
	do_action( 'wc_memberships_for_teams_before_my_team_learner_progress', $team, $member );

	?>

</div>
<?php
