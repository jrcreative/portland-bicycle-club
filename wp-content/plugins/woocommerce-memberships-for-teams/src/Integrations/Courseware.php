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

namespace SkyVerge\WooCommerce\Memberships\Teams\Integrations;

use SkyVerge\WooCommerce\Memberships\Teams\Team;
use SkyVerge\WooCommerce\Memberships\Teams\Team_Member;
use SkyVerge\WooCommerce\Memberships\Teams\Team_Members;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;

defined('ABSPATH') or exit;

/**
 * Abstract class for Courseware/eLearning integrations.
 *
 * @since 1.6.0
 */
abstract class Courseware {


	/** @var string course post type */
	protected $course_post_type;

	/** @var string learner progress section ID */
	private $section_id = 'learner-progress';


	/**
	 * Courseware constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->add_hooks();
	}


	/**
	 * Adds action and filter hooks.
	 *
	 * @since 1.6.0
	 */
	public function add_hooks() {

		add_filter( 'wc_memberships_for_teams_settings', [ $this, 'add_teams_courseware_settings' ] );

		// the following hooks only make sense if the integration is configured
		if ( ! $this->is_configured() ) {
			return;
		}

		add_filter( 'wc_memberships_team_teams_area_sections', [ $this, 'add_learner_progress_section' ] );

		add_filter( 'wc_memberships_for_teams_teams_area_navigation_items', [ $this, 'adjust_navigation_items' ] );

		add_filter( 'wc_memberships_for_teams_my_team_members_column_names', [ $this, 'add_learning_progress_column' ], 10, 2 );

		add_filter( 'wc_memberships_for_teams_my_account_teams_title', [ $this, 'adjust_team_learner_progress_section_title'], 10, 2 );

		add_action( 'wc_memberships_for_teams_my_team_members_column_member-learning-progress', [ $this, 'output_learning_progress_column' ] );

		add_action( 'wc_memberships_for_teams_my_team_learner_progress_content', [ $this, 'output_learning_progress' ], 10, 2 );

		add_filter( 'wc_memberships_for_teams_team_members_table_views', [ $this, 'add_learner_progress_table_view' ], 10, 3 );

		add_filter( 'wc_memberships_for_teams_my_team_members_view_template', [ $this, 'adjust_my_team_members_view_template' ] );
	}


	/**
	 * Adds courseware/eLearning settings to Teams settings.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param array $settings
	 * @return array
	 */
	public function add_teams_courseware_settings( array $settings ) : array {

		$settings[] = [
			'name' => __('eLearning', 'woocommerce-memberships-for-teams'),
			'type' => 'title',
		];

		$settings[] = [
			'id'       => 'wc_memberships_for_teams_learner_progress_reports_viewers',
			'title'    => __( 'Learner progress reports', 'woocommerce-memberships-for-teams' ),
			'desc_tip' => __( 'Determine who can view member progress through plan eLearning content.', 'woocommerce-memberships-for-teams' ),
			'default'  => 'none',
			'type'     => 'multiselect',
			'class'    => 'wc-enhanced-select',
			'css'      => 'min-width:300px;',
			'options'  => [
				'owner'   => __( 'Owners', 'woocommerce-memberships-for-teams' ),
				'manager' => __( 'Managers', 'woocommerce-memberships-for-teams' ),
			],
			'custom_attributes' => [
				'data-placeholder' => __( 'Choose progress viewer...', 'woocommerce-memberships-for-teams' )
			],
		];

		$settings[] = [
			'type' => 'sectionend',
		];

		return $settings;
	}


	/**
	 * Determines whether eLearning progress reports have been configured/enabled.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function is_configured() : bool {

		return ! empty( get_option( 'wc_memberships_for_teams_learner_progress_reports_viewers' ) );
	}


	/**
	 * Adds the Learner progress section to the Teams area.
	 *
	 * @since 1.6.0
	 * @internal
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_learner_progress_section( array $sections ) : array {

		$sections[ $this->section_id ] = __( 'Learner Progress', 'woocommerce-memberships-for-teams' );

		return $sections;
	}


	/**
	 * Adjusts the Teams' area navigation items.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param array $items
	 * @return array
	 */
	public function adjust_navigation_items( array $items ) : array {

		// remove the navigation item for Learner Progress
		unset( $items[ $this->section_id ] );

		// highlight Members section
		if ( $this->get_teams_area_instance()->is_teams_area_section( $this->section_id ) ) {
			$items['members']['class'] = 'is-active';
		}

		return $items;
	}


	/**
	 * Adds the Learning Progress column to Team Members table.
	 *
	 * @since 1.6.0
	 * @internal
	 *
	 * @param array $columns associative array of column ids and names
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
	 */
	public function add_learning_progress_column( array $columns, $team ) : array {

		if ( ! current_user_can( 'wc_memberships_for_teams_view_learner_progress_reports', $team ) ) {
			return $columns;
		}

		return Framework\SV_WC_Helper::array_insert_after( $columns, 'member-last-login', [
			'member-learning-progress' => __( 'Learning Progress', 'woocommerce-memberships-for-teams' )
		] );
	}


	/**
	 * Outputs the Learning Progress column content.
	 *
	 * @since 1.6.0
	 * @internal
	 *
	 * @param Team_Member $member the current team member
	 */
	public function output_learning_progress_column( Team_Member $member ) {

		echo $this->get_member_learning_progress_view_link_html( $member ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Gets the HTML for the member learning progress view link.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member
	 * @return string
	 */
	public function get_member_learning_progress_view_link_html( Team_Member $member ) : string {

		ob_start();
		?>

		<a href="<?php echo esc_url( $this->get_member_learning_progress_url( $member ) ); ?>">
			<?php echo esc_html( _x( 'View', 'verb', 'woocommerce-memberships-for-teams' ) ); ?>
		</a>

		<?php

		return ob_get_clean();
	}


	/**
	 * Gets the Teams' area class instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area
	 */
	private function get_teams_area_instance() {

		return wc_memberships_for_teams()->get_frontend_instance()->get_teams_area_instance();
	}


	/**
	 * Adds "Leaner Progress" to the page title when viewing the learner progress endpoint.
	 *
	 * @since 1.6.0
	 *
	 * @internal
	 *
	 * @param string $title
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the current team
	 * @return string
	 */
	public function adjust_team_learner_progress_section_title( string $title, $team ) : string {

		if ( $team instanceof Team && $this->get_teams_area_instance()->is_teams_area_section( $this->section_id ) ) {
			if ( is_rtl() ) {
				$title  = __( 'Learner Progress', 'woocommerce-memberships-for-teams' ) . ' &laquo; ' . $title;
			} else {
				$title .= ' &raquo; ' . __( 'Learner Progress', 'woocommerce-memberships-for-teams' );
			}
		}

		return $title;
	}


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
	abstract public function output_learning_progress( $team, $member );


	/**
	 * Gets a list of course IDs restricted to the team.
	 *
	 * @since 1.6.0
	 *
	 * @param Team $team the team
	 * @return int[]
	 */
	protected function get_team_restricted_course_ids( Team $team ) : array {

		return $team->get_plan()->get_restricted_content(
			null,
			[
				'post_type' => $this->course_post_type,
				'fields' => 'ids'
			]
		)->posts ?? [];
	}


	/**
	 * Gets IDs of courses available (not enrolled) to the given team member.
	 *
	 * @since 1.6.0
	 *
	 * @param Team $team the team
	 * @param Team_Member $member the member
	 * @return array
	 */
	protected function get_available_course_ids( Team $team, Team_Member $member ) : array {

		return array_diff(
			$this->get_team_restricted_course_ids( $team ),
			$this->get_user_enrolled_course_ids( $member->get_id() )
		);
	}


	/**
	 * Gets the courses available (not enrolled) for the given team member.
	 *
	 * @since 1.6.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the team
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team_Member $member the member
	 * @return int[]|\WP_Post[]
	 */
	public function get_available_courses( $team, $member ) : array {

		$available_course_ids = $this->get_available_course_ids( $team, $member );

		return get_posts( [
			'post_type' => $this->course_post_type,
			'include'   => ! empty( $available_course_ids ) ? $available_course_ids : [ -1 ],
		] );
	}


	/**
	 * Gets IDs of courses the given team member is enrolled in.
	 *
	 * This does not include courses the user is enrolled in outside of their team membership.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @return array
	 */
	protected function get_member_enrolled_course_ids( Team_Member $member ) : array {

		return array_intersect(
			$this->get_team_restricted_course_ids( $member->get_team() ),
			$this->get_user_enrolled_course_ids( $member->get_id() )
		);
	}


	/**
	 * Adds the Learner Progress view to the Team members table.
	 *
	 * Note that this filter is applied both in admin and site frontend.
	 *
	 * @since 1.6.0
	 *
	 * @param array $views the views
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team object
	 * @param string $base_url base URL
	 */
	public function add_learner_progress_table_view( array $views, Team $team, string $base_url ) : array {

		// currently not supported in admin context
		if ( is_admin() ) {
			return $views;
		}

		$classes = 'learner-progress';

		if ( ! empty( $_REQUEST['show_learner_progress'] ) ) {
			$classes .= ' current';

			// deactivate the members view
			$views['members'] = str_replace( 'current', '', $views[ 'members' ] );
		}

		$views['learner-progress'] = Team_Members::get_view_link(
			$base_url,
			array( 'show_learner_progress' => 1 ),
			__( 'Learner Progress', 'woocommerce-memberships-for-teams' ),
			$classes
		);

		return $views;
	}

	/**
	 * Adjusts the my team members table view template.
	 *
	 * @since 1.6.0
	 * @internal
	 *
	 * @param string $template_name template name
	 * @return string
	 */
	public function adjust_my_team_members_view_template( string $template_name ) : string {

		return ! empty( $_REQUEST['show_learner_progress'] ) ? 'myaccount/my-team-learner-progress-table.php' : $template_name;
	}


	/**
	 * Gets the learner progress table columns.
	 *
	 * @since 1.6.0
	 *
	 * @param Team $team the current team
	 * @return array associative array of column ids and names
	 */
	public function get_learner_progress_columns( Team $team ) : array {

		/**
		 * Filters the learner progress table columns.
		 *
		 * @since 1.6.0
		 *
		 * @param array $columns associative array of column ids and names
		 * @param Team $team the current team
		 */
		return apply_filters( 'wc_memberships_for_teams_learner_progress_columns', [
			'learner-name'                => esc_html__( 'Name', 'woocommerce-memberships-for-teams' ),
			'learner-total-courses'       => esc_html__( 'Total courses', 'woocommerce-memberships-for-teams' ),
			'learner-completed-courses'   => esc_html__( 'Completed courses', 'woocommerce-memberships-for-teams' ),
			'learner-courses-in-progress' => esc_html__( 'In progress', 'woocommerce-memberships-for-teams' ),
			'learner-course-progress'     => esc_html__( 'Course progress', 'woocommerce-memberships-for-teams' ),
			'learner-full-report'         => esc_html__( 'Full report', 'woocommerce-memberships-for-teams' ),
		], $team );
	}


	/**
	 * Gets the learner progress data for the given team member.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @param bool $raw optional, whether to return raw or HTML-formatted data - defaults to false
	 * @return array associative array of column ids and data
	 */
	public function get_learner_progress_data( Team_Member $member, bool $raw = false ) : array {

		/**
		 * Filters the learner progress table data for the given member.
		 *
		 * @since 1.6.0
		 *
		 * @param array $columns associative array of column ids and data
		 * @param Team_Member $member the team member
		 * @param Team $team the current team
		 * @param bool $raw whether to return raw or HTML-formatted data
		 */
		return apply_filters( 'wc_memberships_for_teams_learner_progress_data', [
			'learner-name'                => esc_html( $member->get_name() ),
			'learner-total-courses'       => esc_html( $this->get_member_total_courses_count( $member ) ),
			'learner-completed-courses'   => esc_html( $this->get_member_completed_courses_count( $member ) ),
			'learner-courses-in-progress' => esc_html( $this->get_member_in_progress_courses_count( $member ) ),
			'learner-course-progress'     => esc_html( $this->get_member_course_progress_formatted( $member ) ),
			'learner-full-report'         => $raw ? esc_url_raw( $this->get_member_learning_progress_url( $member ) ) : $this->get_member_learning_progress_view_link_html( $member )
		], $member, $member->get_team(), $raw );
	}


	/**
	 * Gets the learning progress url for a team member.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member
	 * @return string
	 */
	public function get_member_learning_progress_url( Team_Member $member ) : string {

		return $this->get_teams_area_instance()->get_teams_area_url(
			$member->get_team(),
			$this->section_id,
			null,
			[ 'user_id' => $member->get_id() ]
		);
	}


	/**
	 * Gets the number of total courses available for the team member.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @return int the number of total courses
	 */
	public function get_member_total_courses_count( Team_Member $member ) : int {

		return count( $this->get_team_restricted_course_ids( $member->get_team() ) );
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
	abstract public function get_member_completed_courses_count( Team_Member $member ) : int;


	/**
	 * Gets the number of courses currently in progress by the team member.
	 *
	 * This includes courses the team member is enrolled in, but has not yet completed.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @return int the number of courses in progress
	 */
	public function get_member_in_progress_courses_count( Team_Member $member ) : int {

		// sanity check: ensure we never return a negative number
		return max( count( $this->get_member_enrolled_course_ids( $member ) ) - $this->get_member_completed_courses_count( $member ), 0 );
	}


	/**
	 * Gets the overall course progress by the team member.
	 *
	 * This method returns a fraction, representing a percentage of courses that have been completed out of all the
	 * courses available to the team member.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member the team member
	 * @return float|int percentage of courses completed
	 */
	public function get_member_course_progress( Team_Member $member ) {

		$completed = $this->get_member_completed_courses_count( $member );
		$total     = $this->get_member_total_courses_count( $member );

		// sanity check: ensure we only calculate the fraction if we have both completed & available courses
		return $completed && $total ? $completed / $total : 0;
	}


	/**
	 * Gets the overall course progress by the team member, formatted as a percentage.
	 *
	 * @since 1.6.0
	 *
	 * @param Team_Member $member
	 * @return string
	 */
	public function get_member_course_progress_formatted( Team_Member $member ) : string {

		return Framework\SV_WC_Helper::format_percentage( $this->get_member_course_progress( $member ) );
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
	abstract public function get_user_enrolled_course_ids( int $user_id ) : array;


}
