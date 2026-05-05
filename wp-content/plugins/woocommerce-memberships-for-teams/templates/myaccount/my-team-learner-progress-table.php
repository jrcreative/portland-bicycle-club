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
 * Renders the team learner progress table on My Account page
 *
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Team $team current team instance
 * @type int $paged the current page number
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area $teams_area teams area handler instance
 *
 * @version 1.6.0
 * @since 1.6.0
 */

/** @var array $results */
$results = $team->get_members( [ 'number' => 20, 'paged' => $paged ], 'query' );
/** @var \SkyVerge\WooCommerce\Memberships\Teams\Team_Member[] $members */
$members = $results['team_members'];

?>

<?php if ( ! empty( $members ) ) : ?>

	<table class="shop_table shop_table_responsive my_account_orders my_account_teams my_team_learner_progress">

		<thead>
			<tr>
				<?php foreach ( $columns = wc_memberships_for_teams_courseware()->get_learner_progress_columns( $team ) as $column_id => $column_header ) : ?>
					<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_header ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php foreach ( $members as $member ) : ?>

				<tr class="member">
					<?php $learner_progress = wc_memberships_for_teams_courseware()->get_learner_progress_data( $member ); ?>

					<?php foreach ( $columns as $column_id => $column_name ) : ?>

						<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php

							// output must be escaped in Courseware::get_learner_progress_data()
							echo wp_kses_post( $learner_progress[ $column_id ] );

							/**
							 * Fires when populating a Teams Area Learner Progress table column for a member.
							 *
							 * @since 1.6.0
							 *
							 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member the current team member
							 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
							 * @param array $learner_progress learner progress data
							 */
							do_action( "wc_memberships_for_teams_my_team_members_column_{$column_id}", $member, $team, $learner_progress );

							?>
						</td>

					<?php endforeach; ?>
				</tr>

			<?php endforeach; ?>
		</tbody>

		<?php if ( isset( $results['total_pages'] ) && (int) $results['total_pages'] > 1 ) : ?>

			<tfoot>
				<tr>
					<th colspan="<?php echo count( $columns ); ?>">
						<?php echo $teams_area->get_pagination_links( $team, 'members', (int) $results['total_pages'], (int) $results['current_page'], [ 'show_learner_progress' => 1 ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</th>
				</tr>
			</tfoot>

		<?php endif; ?>

	</table>

<?php else : ?>

	<?php wc_get_template( 'myaccount/my-team-no-members', [ 'team' => $team ] ); ?>

<?php endif; ?>
