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

namespace SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware;

use SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware;
use SkyVerge\WooCommerce\Memberships\Teams\Team_Member;

defined( 'ABSPATH' ) or exit;

/**
 * LearnDash integration class.
 *
 * @since 1.6.0
 */
class LearnDash extends Courseware {


	/** @var string course post type */
	protected $course_post_type = 'sfwd-courses';


	/**
	 * Outputs the learning progress view for a particular team member.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
	 * @param  \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member
	 */
	public function output_learning_progress( $team, $member ) {

		$team_courses = $this->get_team_restricted_course_ids( $team );

		if ( ! function_exists( 'learndash_profile' ) ) {
			return;
		}

		echo learndash_profile( [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'user_id'  => $member->get_id(),
			// if the team plan does not grant access to any courses, ensure none are shown
			'post__in' => ! empty( $team_courses ) ? $team_courses : [ -1 ],
		] );
	}


	/**
	 * Gets a list of all course IDs the user is already enrolled in.
	 *
	 * This includes all courses, regardless of whether the user has access to them via a team or otherwise.
	 *
	 * @since 1.6.0
	 *
	 * @param int $user_id the user ID
	 * @return int[]
	 */
	public function get_user_enrolled_course_ids( int $user_id ) : array {

		return function_exists( 'ld_get_mycourses' ) ? ld_get_mycourses( $user_id, [ 'fields' => 'ids' ] ) : [];
	}


	/**
	 * Gets the number of courses completed by the team member.
	 *
	 * This does not include courses completed outside the user's team membership.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @return int the number of completed courses
	 */
	public function get_member_completed_courses_count( Team_Member $member ) : int {

		if ( ! function_exists( 'learndash_reports_get_activity' ) ) {
			return 0;
		}

		$team_courses = $this->get_team_restricted_course_ids( $member->get_team() );

		// implementation based on Learndash_Admin_Data_Reports_Courses::process_report_action()
		$result = learndash_reports_get_activity( [
			'course_ids'      => ! empty( $team_courses ) ? $team_courses : [ -1 ],
			'activity_types'  => 'course',
			'activity_status' => 'COMPLETED',
			'user_ids'        => [ $member->get_id() ],
			'per_page'        => 1, // only get the first full result, we can get the total count from the pager (see below)
			'include_meta'    => false,
		], $member->get_id() );

		return isset( $result['pager']['total_items'] ) ? (int) $result['pager']['total_items'] : 0;
	}


}
