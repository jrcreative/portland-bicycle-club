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
 * @type int $paged the current page number
 * @type \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area $teams_area teams area handler instance
 *
 * @version 1.6.0
 * @since 1.0.0
 */

/** @var array $results */
$results = $team->get_members( array( 'number' => 20, 'paged' => $paged ), 'query' );
/** @var \SkyVerge\WooCommerce\Memberships\Teams\Team_Member[] $members */
$members = $results['team_members'];

?>

<?php if ( ! empty( $members ) ) : ?>

	<table class="shop_table shop_table_responsive my_account_orders my_account_teams my_team_members">

		<thead>
			<tr>
				<?php

				$columns = [
					'member-name'       => esc_html__( 'Name', 'woocommerce-memberships-for-teams' ),
					'member-email'      => esc_html__( 'Email', 'woocommerce-memberships-for-teams' ),
					'member-role'       => esc_html__( 'Role', 'woocommerce-memberships-for-teams' ),
					'member-last-login' => esc_html__( 'Last Login', 'woocommerce-memberships-for-teams' ),
					'member-actions'    => $teams_area->get_pagination_links( $team, 'members', $results['total_pages'], $results['current_page'] ),
				];

				// only team owners and manager can see the members last login
				if ( ! current_user_can( 'wc_memberships_for_teams_manage_team_members', $team->get_id() ) ) {
					unset( $columns['member-last-login'] );
				}

				/**
				 * Filters the Team Members table columns in My Account page.
				 *
				 * @since 1.0.0
				 *
				 * @param array $columns associative array of column ids and names
				 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
				 */
				$columns = (array) apply_filters( 'wc_memberships_for_teams_my_team_members_column_names', $columns, $team );

				?>
				<?php foreach ( $columns as $column_id => $column_header ) : ?>
					<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo $column_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php foreach ( $members as $member ) : ?>

				<tr class="member">
					<?php foreach ( $columns as $column_id => $column_name ) : ?>

						<?php if ( 'member-name' === $column_id ) : ?>

							<td class="member-name" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php echo esc_html( $member->get_name() ); ?>
							</td>

						<?php elseif ( 'member-email' === $column_id ) : ?>

							<td class="member-email" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php echo esc_html( $member->get_email() ); ?>
							</td>

						<?php elseif ( 'member-role' === $column_id ) : ?>

							<td class="member-role" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php echo esc_html( $member->get_role( 'label' ) ); ?>
							</td>

						<?php elseif ( 'member-last-login' === $column_id ) : ?>

							<td class="member-last-login" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php

								$last_activity = get_user_meta( $member->get_id() , 'wc_last_active', true );

								echo is_numeric( $last_activity ) ? sprintf(
									/* translators: Placeholder: %s last login since */
									esc_html__( '%s ago', 'woocommerce-memberships-for-teams' ),
									esc_html( human_time_diff( (int) $last_activity ) )
								) : '&mdash;';

								?>
							</td>

						<?php elseif ( 'member-actions' === $column_id ) :

							?>
							<td class="team-actions order-actions" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php echo $teams_area->get_action_links( 'members', $team, $member ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>

						<?php else : ?>

							<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
								<?php

								/**
								 * Fires when populating a Teams Area Members table column for a member.
								 *
								 * @since 1.0.0
								 *
								 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member the current team member
								 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
								 */
								do_action( "wc_memberships_for_teams_my_team_members_column_{$column_id}", $member, $team );

								?>
							</td>

						<?php endif; ?>

					<?php endforeach; ?>
				</tr>

			<?php endforeach; ?>
		</tbody>

		<?php if ( isset( $results['total_pages'] ) && (int) $results['total_pages'] > 1 ) : ?>

			<tfoot>
				<tr>
					<th colspan="<?php echo count( $columns ); ?>">
						<?php echo $teams_area->get_pagination_links( $team, 'members', (int) $results['total_pages'], (int) $results['current_page'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</th>
				</tr>
			</tfoot>

		<?php endif; ?>

	</table>

<?php else : ?>

	<?php wc_get_template( 'myaccount/my-team-no-members.php', [ 'team' => $team ] ); ?>

<?php endif; ?>
