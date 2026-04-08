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
 * Sensei integration class.
 *
 * @since 1.6.0
 */
class Sensei extends Courseware {


	/** @var string course post type */
	protected $course_post_type = 'course';

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Team temporary reference to the current team */
	private $current_team;

	/** @var string course post type */
	private $filtering_get_posts = false;

	/** @var mixed|null original "Public learner profiles" setting value */
	private $original_learner_profile_setting;

	/** @var mixed|null original "Disable Private Messages" setting value */
	private $original_messages_setting;


	/**
	 * This method is based on Sensei's learner-profile.php template.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
	 * @param  \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member
	 */
	public function output_learning_progress( $team, $member ) {

		if ( ! function_exists( 'Sensei' ) || ! is_callable( 'Sensei_Utils::get_setting_as_flag' ) || ! is_callable( 'Sensei_Learner_Profiles::user_info' ) ) {
			return;
		}

		// remove the "coursers ... is taking..." heading
		remove_action( 'sensei_learner_profile_info', [ \Sensei()->learner_profiles, 'learner_profile_courses_heading' ], 30 );

		// ensure sensei JS is enqueued (if enabled so in settings) - this will enable tabs on the profile
		if ( ! \Sensei_Utils::get_setting_as_flag( 'js_disable', 'sensei_settings_js_disable' ) ) {
			wp_enqueue_script( \Sensei()->token . '-user-dashboard' );
		}

		$this->setup_environment_before_learning_progress_output( $team );

		\Sensei_Learner_Profiles::user_info( $member->get_user() );

		\Sensei()->course->load_user_courses_content( $member->get_user() );

		$this->restore_environment_after_learning_progress_output();
	}


	/**
	 * Filters WP_Query args to only allow querying courses on the team plan.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param \WP_Query $wp_query instance of WP_Query
	 */
	public function only_include_team_courses( \WP_Query $wp_query ) {

		// bail if we're already filtering posts
		if ( $this->filtering_get_posts ) {
			return;
		}

		// sanity check: only filter querying courses
		if ( empty( $wp_query->query_vars['post_type'] ) || $wp_query->query_vars['post_type'] !== $this->course_post_type ) {
			return;
		}

		// set up a flag to prevent endless recursive loops due to getting the restricted course IDs below
		$this->filtering_get_posts = true;

		$courses = $this->get_team_restricted_course_ids( $this->current_team );

		if ( ! empty( $wp_query->query_vars['post__in'] ) ) {
			$courses = array_intersect( $wp_query->query_vars['post__in'], $courses );
		}

		// ensure that only team courses are returned and when no intersection between user/team courses
		// is found, nothing will be returned (hence the -1)
		$wp_query->query_vars['post__in'] = ! empty( $courses ) ? $courses : [ -1 ];

		$this->filtering_get_posts = false;
	}


	/**
	 * Sets up the environment for rendering the learner profile.
	 *
	 * @since 1.6.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
	 */
	private function setup_environment_before_learning_progress_output( $team ) {

		$this->current_team = $team;

		add_filter( 'sensei_learner_profile_info_bio', '__return_empty_string', 999 );
		add_filter( 'pre_get_posts', [ $this, 'only_include_team_courses' ] );

		// Force-enable the "Public learner profiles" and "Disable Private Messages" settings for Sensei
		$this->original_learner_profile_setting = \Sensei()->settings->settings['learner_profile_show_courses'];
		$this->original_messages_setting = \Sensei()->settings->settings['messages_disable'];

		\Sensei()->settings->settings['learner_profile_show_courses'] = true;
		\Sensei()->settings->settings['messages_disable'] = true;
	}


	/**
	 * Restores the environment after rendering the learner profile.
	 *
	 * @since 1.6.0
	 */
	private function restore_environment_after_learning_progress_output() {

		$this->current_team = null;

		remove_filter( 'sensei_learner_profile_info_bio', '__return_empty_string', 999 );
		remove_filter( 'pre_get_posts', [ $this, 'only_include_team_courses' ] );

		// Restore the "Public learner profiles" and "Disable Private Messages" settings for Sensei.
		\Sensei()->settings->settings['learner_profile_show_courses'] = $this->original_learner_profile_setting;
		\Sensei()->settings->settings['messages_disable'] = $this->original_messages_setting;
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

		if ( ! is_callable( 'Sensei_Learner::instance' ) ) {
			return [];
		}

		return \Sensei_Learner::instance()->get_enrolled_active_courses_query( $user_id, [ 'fields' => 'ids' ] )->posts ?? [];
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

		$team_courses = $this->get_team_restricted_course_ids( $member->get_team() );

		$course_args = [
			'user_id'  => $member->get_id(),
			'type'     => 'sensei_course_status',
			'status'   => 'complete',
			'post__in' => ! empty( $team_courses ) ? $team_courses : [ -1 ],
		];

		/** this filter originally appears in Sensei_Analysis_Overview_List_Table::get_row_data()  */
		return (int) \Sensei_Utils::sensei_check_for_activity( apply_filters(
			'sensei_analysis_user_courses_ended',
			$course_args,
			$member->get_user()
		) );
	}


}
