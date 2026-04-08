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

namespace SkyVerge\WooCommerce\Memberships\Teams;

use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;

defined( 'ABSPATH' ) or exit;

/**
 * Frontend class
 *
 * @since 1.0.0
 */
class Frontend {


	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Products instance */
	protected $products;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Checkout instance */
	protected $checkout;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area instance */
	protected $teams_area;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Profile_Fields instance */
	private $profile_fields;

	/** @var string the endpoint / query var used by the join team page */
	private $join_team_endpoint;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Team join team page team instance */
	private $join_page_team;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Invitation join team page invitation instance */
	private $join_page_invitation;

	/** @var bool whether the join team page template is already loaded or not */
	private $join_team_page_template_loaded = false;


	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->products   = new Frontend\Products;
		$this->checkout   = new Frontend\Checkout;
		$this->teams_area = new Frontend\Teams_Area;

		// if supported, load the profile fields handler
		if ( wc_memberships_for_teams()->supports_profile_fields() ) {
			$this->profile_fields = new Frontend\Profile_Fields();
		}

		$this->join_team_endpoint = get_option( 'woocommerce_myaccount_join_team_endpoint', 'join-team' );

		add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_scripts' ) );

		// handle team settings actions
		add_action( 'template_redirect', array( $this, 'update_team_name' ) );
		add_action( 'template_redirect', array( $this, 'regenerate_team_registration_link' ) );
		add_action( 'template_redirect', array( $this, 'add_team_member' ) );
		add_action( 'template_redirect', array( $this, 'add_owner_as_team_member' ) );
		add_action( 'template_redirect', array( $this, 'join_team' ) );
		add_action( 'template_redirect', array( $this, 'leave_team' ) );
		add_action( 'template_redirect', array( $this, 'renew_team_membership' ) );
		add_action( 'template_redirect', array( $this, 'update_team_seats' ) );

		// joins a team member upon successful registration by following a registration link
		add_filter( 'woocommerce_registration_redirect', array( $this, 'join_team_upon_registration' ) );

		// show join team form
		add_filter( 'wc_get_template',            array( $this, 'get_join_team_template' ), 1, 2 );
		add_filter( 'the_title',                  array( $this, 'adjust_account_page_title' ), 40 );
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'adjust_account_page_breadcrumbs' ), 100 );

		// save additional team data
		add_action( 'woocommerce_created_customer', array( $this, 'save_team_member_name' ) );

		// show team name on membership details page on members area
		add_filter( 'wc_memberships_members_area_my_membership_details', array( $this, 'add_my_membership_team_details' ), 10, 2 );

		// add "leave team" action to my membership actions and remove billing actions for non-owners
		add_filter( 'wc_memberships_members_area_my-memberships_actions', array( $this, 'maybe_change_my_membership_actions' ), 99, 2 );
		add_filter( 'wc_memberships_members_area_my-membership-details_actions', array( $this, 'maybe_change_my_membership_actions' ), 99, 2 );
	}


	/**
	 * Enqueues frontend scripts and styles.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		if ( is_account_page() ) {
			wp_enqueue_style( 'woocommerce-memberships-for-teams-frontend', wc_memberships_for_teams()->get_plugin_url() . '/assets/css/frontend/wc-memberships-for-teams.min.css', array( 'wc-memberships-frontend', 'dashicons' ), Plugin::VERSION );
		}

		if ( $this->get_teams_area_instance()->is_teams_area_section( 'settings' ) ) {
			wp_enqueue_script(  'woocommerce-memberships-for-teams-team-settings', wc_memberships_for_teams()->get_plugin_url() . '/assets/js/frontend/wc-memberships-for-teams-team-settings.min.js', array( 'jquery' ), Plugin::VERSION );
			wp_localize_script( 'woocommerce-memberships-for-teams-team-settings', 'wc_memberships_teams_area_team_settings', $this->get_teams_area_instance()->get_team_settings_l10n() );
		}
	}


	/**
	 * Updates the name for a team.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function update_team_name() {

		if ( ! isset( $_POST['update_team_name'] ) ) {
			return;
		}

		$team_name = sanitize_text_field( filter_input( INPUT_POST, 'team_name' ) ?? '' );
		$team_id   = absint( filter_input( INPUT_POST, 'update_team_name', FILTER_SANITIZE_NUMBER_INT ) ?? 0 );
		$team      = wc_memberships_for_teams_get_team( $team_id );

		if ( ! $team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif ( ! $team_name ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Please provide a name for this %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif (    isset( $_POST['_team_settings_nonce'] )
			       && current_user_can( 'wc_memberships_for_teams_manage_team_settings', $team_id )
			       && wp_verify_nonce( wc_clean( $_POST['_team_settings_nonce'] ), 'update-team-name-' . $team_id ) ) {

			$team->set_name( $team_name, true );

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( '%s name was updated.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'notice';

		} else {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Cannot update name for this %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';
		}

		if ( isset( $notice_message, $notice_type ) ) {
			wc_add_notice( $notice_message, $notice_type );
		}

		wp_safe_redirect( $this->teams_area->get_teams_area_url( $team, 'settings' ) );
		exit;
	}


	/**
	 * Regenerates the registration link for a team.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function regenerate_team_registration_link() {

		if ( ! isset( $_POST['regenerate_team_registration_link'] ) ) {
			return;
		}

		$team_id = (int) $_POST['regenerate_team_registration_link'];
		$team    = wc_memberships_for_teams_get_team( $team_id );

		if ( ! $team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} else {

			if (    isset( $_POST['_team_link_nonce'] )
			     && current_user_can( 'wc_memberships_for_teams_manage_team_settings', $team_id )
			     && wp_verify_nonce( wc_clean( $_POST['_team_link_nonce'] ), 'regenerate-team-registration-link-' . $team_id ) ) {

				$team->generate_registration_key();

				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				$notice_message = ucfirst( sprintf( __( '%s registration link was regenerated.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
				$notice_type    = 'notice';

			} else {

				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				$notice_message = ucfirst( sprintf( __( 'Cannot regenerate registration link for this %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
				$notice_type    = 'error';
			}

		}

		if ( isset( $notice_message, $notice_type ) ) {
			wc_add_notice( $notice_message, $notice_type );
		}

		wp_safe_redirect( $this->teams_area->get_teams_area_url( $team, 'add-member' ) );
		exit;
	}


	/**
	 * Adds (invites) a member to the team.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function add_team_member() {

		if ( ! isset( $_POST['add_team_member'] ) ) {
			return;
		}

		$email   = ! empty( $_POST['email'] ) ? wc_clean( $_POST['email'] ) : null;
		$role    = ! empty( $_POST['role'] )  ? wc_clean( $_POST['role'] )  : null;
		$team_id = (int) $_POST['add_team_member'];
		$team    = wc_memberships_for_teams_get_team( $team_id );

		if ( ! $team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif (    isset( $_POST['_team_add_member_nonce'] )
		           && current_user_can( 'wc_memberships_for_teams_manage_team_members', $team_id )
		           && wp_verify_nonce( wc_clean( $_POST['_team_add_member_nonce'] ), 'add-team-member-' . $team_id ) ) {

			try {

				$current_user = wc_memberships_for_teams_get_team_member( $team, get_current_user_id() );

				if (    'manager' === $role
				     && $current_user
				     && $current_user->has_role( 'manager' )
				     && 'yes' !== get_option( 'wc_memberships_for_teams_managers_may_manage_managers', 'yes' ) ) {

					throw new Framework\SV_WC_Plugin_Exception( __( 'Invalid role.', 'woocommerce-memberships-for-teams' ) );
				}

				$send_invite = true;

				// if skipping invitations, join the member directly
				if ( wc_memberships_for_teams()->get_invitations_instance()->should_skip_invitations( $team, $email ) ) {

					// first though we need to verify that an user with this email address exists
					$user = is_email( $email ) ? get_user_by( 'email', $email ) : false;

					if ( $user ) {

						$team->add_member( $user, $role );

						/* translators: Placeholder: %1$s - user email, %2$s - the noun used to represent a team (singular)  */
						$notice_message = ucfirst( sprintf( __( '%1$s was added to the %2$s.', 'woocommerce-memberships-for-teams' ), $user->display_name, wc_memberships_for_teams()->get_singular_team_noun() ) );
						$notice_type    = 'success';
						$send_invite    = false;
					}
				}

				// otherwise invite users to join (default behavior)
				if ( $send_invite ) {

					// invite member to join the team
					$team->invite( $email, $role );

					/* translators: Placeholder: %1$s - user email, %2$s - the noun used to represent a team (singular) */
					$notice_message = ucfirst( sprintf( __( '%1$s was invited to join the %2$s.', 'woocommerce-memberships-for-teams' ), $email, wc_memberships_for_teams()->get_singular_team_noun() ) );
					$notice_type    = 'notice';
				}

			} catch ( \Exception $e ) {

				// already invited - offer to re-send invitation
				if ( 3 === $e->getCode() && $invitation	= $team->get_invitation( $email ) ) {

					$resend_url = add_query_arg( array(
						'action'     => 'invitation_resend',
						'invitation' => $invitation->get_id(),
						'_wpnonce'   => wp_create_nonce( 'team-invitation-resend-' . $invitation->get_id() ),
					), $this->get_teams_area_instance()->get_teams_area_url( $team->get_id() ) );

					/* translators: Placeholders: %1$s - user email, %2$s - opening <a> HTML link tag, %3$s - closing </a> HTML link tag */
					$notice_message = sprintf( __( '%1$s is already invited. Do you want to %2$sre-send the invitation%3$s?', 'woocommerce-memberships-for-teams' ), $email, '<a href="' . $resend_url . '">', '</a>' );
					$notice_type    = 'notice';

				} else {

					/* translators: Placeholders: %1$s - user email, %2$s - the noun used to represent a team (singular ), %3$s - error message */
					$notice_message = ucfirst( sprintf( __( 'Cannot invite %1$s to this %2$s: %3$s', 'woocommerce-memberships-for-teams' ), $email, wc_memberships_for_teams()->get_singular_team_noun(), $e->getMessage() ) );
					$notice_type    = 'notice';
				}
			}

		} else {

			/* translators: Placeholder: %1$s - user email, %2$s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Cannot invite %1$s to this %2$s.', 'woocommerce-memberships-for-teams' ), $email, wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'notice';
		}

		if ( isset( $notice_message, $notice_type ) ) {

			wc_add_notice( $notice_message, $notice_type );

			if ( 'error' !== $notice_type ) {
				wp_safe_redirect( $this->teams_area->get_teams_area_url( $team, 'add-member' ) );
				exit;
			}
		}
	}


	/**
	 * Adds the owner of the team as team member.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function add_owner_as_team_member() {

		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'add_owner_as_team_member' ) {
			return;
		}

		$team    = $this->get_teams_area_instance()->get_teams_area_team();
		$user_id = get_current_user_id();

		if ( ! $team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif (    isset( $_GET['_wpnonce'] )
			       && $team->is_user_owner( $user_id )
			       && wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'add-owner-as-team-member-' . $team->get_id() ) ) {

			try {

				$team->add_member( $user_id ); // will default to 'owner'

				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				$notice_message = ucfirst( sprintf( __( 'You are now a member of the %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
				$notice_type    = 'notice';

			} catch ( \Exception $e ) {

				/* translators: Placeholder: %s - error message */
				$notice_message = sprintf( __( 'Cannot add yourself as a member: %s', 'woocommerce-memberships-for-teams' ), $e->getMessage() );
				$notice_type    = 'error';
			}

		} else {

			$notice_message = __( 'Cannot add yourself as a member.', 'woocommerce-memberships-for-teams' );
			$notice_type    = 'error';
		}

		if ( isset( $notice_message, $notice_type ) ) {

			wc_add_notice( $notice_message, $notice_type );

			if ( 'notice' === $notice_type ) {
				wp_safe_redirect( $this->teams_area->get_teams_area_url( $team, 'add-member' ) );
				exit;
			}
		}
	}


	/**
	 * Joins the current user to a team by accepting an invite or after registering via link.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function join_team() {

		/* @see Frontend::join_team_upon_registration() user has joined upon registration */
		if (    ! empty( $_GET['joined_team'] )
		     &&   is_numeric( $_GET['joined_team'] )
		     &&   wc_memberships_is_members_area() ) {

			$team = wc_memberships_for_teams_get_team( absint( $_GET['joined_team'] ) );

			if ( $team ) {

				/* translators: Placeholder: %s - team name */
				$notice_message = sprintf( __( 'Success! You are now a member of %s.', 'woocommerce-memberships-for-teams' ), $team->get_name() );
				$notice_type    = 'success';

				wc_add_notice( $notice_message, $notice_type );
			}

		// normal behavior: user is joining a team by invitation
		} elseif ( ! empty( $_POST['join_team'] ) && $this->is_join_team_page() ) {

			$team         = $this->get_join_page_team();
			$invitation   = $this->get_join_page_invitation();
			$current_user = get_userdata( get_current_user_id() );

			if ( ! $team ) {

				$notice_message = __( 'Invalid token.', 'woocommerce-memberships-for-teams' );
				$notice_type    = 'error';

			} elseif ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( wc_clean( $_POST['_wpnonce'] ), 'join-team-' . $team->get_id() ) ) {

				try {

					/**
					 * Fires before a user has joined a team via the join team page.
					 *
					 * @since 1.4.1
					 *
					 * @param int $user_id id of the user who joined
					 * @param Team $team instance of the team that was joined
					 */
					do_action( 'woocommerce_memberships_for_teams_before_join_team', get_current_user_id(), $team );

					// if user has been invited, but they join via the registration link, simply accept the existing pending invitation
					if ( ! $invitation ) {
						$invitation = $team->get_invitation( $current_user->user_email );
						$invitation = $invitation && $invitation->has_status( 'pending' ) ? $invitation : false;
					}

					if ( $invitation ) {
						$invitation->accept( get_current_user_id() );
					} else {
						$team->add_member( get_current_user_id() );
					}

					/**
					 * Fires right after a user has joined a team via the join team page.
					 *
					 * @since 1.0.2
					 *
					 * @param int $user_id id of the user who joined
					 * @param Team $team instance of the team that was joined
					 */
					do_action( 'woocommerce_memberships_for_teams_joined_team', get_current_user_id(), $team );

					/* translators: Placeholder: %s - team name */
					$notice_message =  sprintf( __( 'Success! You are now a member of %s.', 'woocommerce-memberships-for-teams' ), $team->get_name() );
					$notice_type    = 'success';

				} catch ( \Exception $e ) {

					/* translators: Placeholders: %1$s - team name %2$s - error message */
					$notice_message = sprintf( __( 'Cannot join %1$s: %2$s', 'woocommerce-memberships-for-teams' ), $team->get_name(), $e->getMessage() );
					$notice_type    = 'error';
				}

			} else {

				/* translators: Placeholder: %s - team name */
				$notice_message = sprintf( __( 'Cannot join %s.', 'woocommerce-memberships-for-teams' ), $team->get_name() );
				$notice_type    = 'error';
			}

			if ( isset( $notice_message, $notice_type ) ) {

				wc_add_notice( $notice_message, $notice_type );

				if ( 'success' === $notice_type ) {

					wp_safe_redirect( $this->get_team_member_joined_redirect_url( $team, $invitation ) );
					exit;
				}
			}
		}
	}


	/**
	 * Joins a member to a team automatically if they followed a registration link.
	 *
	 * Adjusts the registration redirect URL on the join team page.
	 *
	 * @internal
	 *
	 * @since 1.1.2
	 *
	 * @param string $redirect_to the url to redirect to
	 * @return string
	 */
	public function join_team_upon_registration( $redirect_to ) {

		// note that since WooCommerce processes registrations at `wp_loaded`, no global $wp_query is available, so we cannot use $this->is_join_team_page()
		if ( ! empty( $_POST['wc_memberships_for_teams_token'] ) ) {

			$token    = wc_clean( $_POST['wc_memberships_for_teams_token'] );
			$new_user = wp_get_current_user();
			$team     = $this->get_join_page_team( $token );

			if ( ! $team ) {

				if ( $this->is_invitation_token( $token ) && ( $invitation = $this->get_join_page_invitation( $token ) ) ) {
					$redirect_to = $invitation->get_accept_url();
				}

			} else {

				try {

					// if the new user was invited and given a different role, consider that
					if ( $this->is_invitation_token( $token ) && ( $invitation = $this->get_join_page_invitation( $token ) ) ) {
						$role = $invitation->get_member_role();
					} elseif ( $invitation = wc_memberships_for_teams_get_invitation( $new_user->user_email ) ) {
						$role = $invitation->get_member_role();
					}

					$team->add_member( $new_user->ID, ! empty( $role ) ? $role : 'member' );

					/** @see Frontend::join_team() */
					do_action( 'woocommerce_memberships_for_teams_joined_team', $new_user->ID, $team );

					$redirect_to = add_query_arg(
						array( 'joined_team' => $team->get_id() ),
						$this->get_team_member_joined_redirect_url( $team, $invitation )
					);

				} catch ( \Exception $e ) {

					if ( $this->is_invitation_token( $token ) && ( $invitation = $this->get_join_page_invitation( $token ) ) ) {

						$redirect_to = $invitation->get_accept_url();

					} else {

						$registration_url = $team->get_registration_url();

						if ( is_string( $registration_url ) ) {
							$redirect_to = $registration_url;
						}
					}
				}
			}
		}

		return $redirect_to;
	}


	/**
	 * Gets a URL to redirect a team member that just joined by invitation.
	 *
	 * Note: if opening this method to public, consider other placement than the current frontend handler.
	 *
	 * @since 1.1.2
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team that a member has just joined
	 * @param false|\SkyVerge\WooCommerce\Memberships\Teams\Invitation $invitation invitation object (or false if not set)
	 * @return string URL
	 */
	private function get_team_member_joined_redirect_url( $team, $invitation ) {

		// redirect user to the membership plan area
		$redirect_to = wc_memberships_get_members_area_url( $team->get_plan() );
		$sections    = $team->get_plan()->get_members_area_sections();

		// if no sections are available for the plan, redirect to my account instead
		if ( empty( $sections ) ) {
			$endpoint    = get_option( 'permalink_structure' ) ? get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' ) : 'members_area';
			$redirect_to = wc_get_account_endpoint_url( $endpoint );
		}

		/**
		 * Filters the URL to redirect to when a user joins a team by invitation or via link.
		 *
		 * @since 1.0.4
		 *
		 * @param string $redirect_to URL to redirect to
		 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team the team object
		 * @param false|\SkyVerge\WooCommerce\Memberships\Teams\Invitation $invitation the invitation object
		 */
		return (string) apply_filters( 'wc_memberships_for_teams_join_team_redirect_to', $redirect_to, $team, $invitation );
	}


	/**
	 * Saves the team member first and last name if set.
	 *
	 * @since 1.0.0
	 *
	 * @param int $customer_id the newly created customer's user ID
	 */
	public function save_team_member_name( $customer_id ) {

		$user      = get_userdata( $customer_id );
		$user_data = array( 'ID' => $customer_id );

		if ( isset( $_POST['first_name'] ) && ! empty( $_POST['first_name'] ) ) {

			$user_data['first_name'] = sanitize_text_field( $_POST['first_name'] );

			// WC billing first name
			update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['first_name'] ) );
		}

		if ( isset( $_POST['last_name'] ) && ! empty( $_POST['last_name'] ) ) {

			$user_data['last_name'] = sanitize_text_field( $_POST['last_name'] );

			// WC billing last name
			update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['last_name'] ) );
		}

		// set display name to first name to start
		$user_data['display_name'] = isset( $user_data['first_name'] ) ? $user_data['first_name'] : $user->user_login;

		// if we have a full name, set that as display name, and let translators adjust the name
		/* translators: Placeholders: %1$s - first or given name, %2$s - surname or last name */
		$user_data['display_name'] = isset( $user_data['first_name'], $user_data['last_name'] ) ? sprintf( _x( '%1$s %2$s', 'User full name', 'woocommerce-memberships-for-teams' ), $user_data['first_name'], $user_data['last_name'] ) : $user_data['display_name'];

		wp_update_user( $user_data );
	}


	/**
	 * Removes the current user from the team provided.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function leave_team() {

		if ( empty( $_GET['leave_team'] ) || ! ( $user_id = get_current_user_id() ) ) {
			return;
		}

		$team = wc_memberships_for_teams_get_team( wc_clean( $_GET['leave_team'] ) );

		if ( ! $team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wc_clean( $_GET['_wpnonce'] ), 'leave-team-' . $team->get_id() ) ) {

			try {

				$team->remove_member( $user_id );

				/* translators: Placeholders: %1$s - team name, %2$s - the noun used to represent a team (singular) */
				$notice_message = ucfirst( sprintf( __( 'You have left the %1$s %2$s.', 'woocommerce-memberships-for-teams' ), $team->get_name(), wc_memberships_for_teams()->get_singular_team_noun() ) );
				$notice_type    = 'notice';

			} catch ( Framework\SV_WC_Plugin_Exception $e ) {

				/* translators: Placeholders: %1$s - team name %2$s - error message */
				$notice_message = sprintf( __( 'Cannot leave %1$s: %2$s', 'woocommerce-memberships-for-teams' ), $team->get_name(), $e->getMessage() );
				$notice_type    = 'error';
			}

		} else {

			/* translators: Placeholder: %s - team name */
			$notice_message = sprintf( __( 'Cannot leave %s.', 'woocommerce-memberships-for-teams' ), $team->get_name() );
			$notice_type    = 'error';
		}

		if ( isset( $notice_message, $notice_type ) ) {
			wc_add_notice( $notice_message, $notice_type );

			wp_safe_redirect( 'notice' === $notice_type ? wc_get_page_permalink( 'myaccount' ) : wp_get_referer() );
			exit;
		}
	}


	/**
	 * Returns the join team template for guest visitors.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $located the located template
	 * @param string $template_name the template name
	 * @return string template to load
	 */
	public function get_join_team_template( $located, $template_name ) {
		global $token, $team, $invitation, $is_invitation_page;

		if ( ! $this->join_team_page_template_loaded && in_array( $template_name, array( 'myaccount/form-login.php', 'myaccount/my-account.php' ), true ) && $this->is_join_team_page() ) {

			// set a few globals for the template
			$token              = $this->get_join_team_token_from_query_vars();
			$team               = $this->get_join_page_team();
			$invitation         = $this->get_join_page_invitation();
			$is_invitation_page = $this->is_invitation_page();

			// a necessary evil to prevent the "Your theme version of my-account.php template is deprecated" notice - note that this only
			// happens with the myaccount/my-account.php template (when user is logged in)
			if ( 'myaccount/my-account.php' === $template_name ) {

				// prevent WooCommerce from actually outputting my account content
				remove_action( 'woocommerce_account_content', 'woocommerce_account_content' );
				ob_start();
				// ... but do fire the woocommerce_account_content action, so that it does not throw a notice
				do_action( 'woocommerce_account_content' );
				ob_end_clean();
			}

			$located = wc_locate_template( 'myaccount/join-team.php' );

			// prevent an endless loop when including the login form on join team page
			$this->join_team_page_template_loaded = true;
		}

		return $located;
	}


	/**
	 * Checks whether we are currently on the join team page/endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_join_team_page() {
		global $wp_query;

		if ( $wp_query && get_option( 'permalink_structure' ) ) {
			$is_endpoint_url = ! empty( $wp_query->query_vars[ $this->join_team_endpoint ] );
		} else {
			$is_endpoint_url = isset( $_GET[ $this->join_team_endpoint ] );
		}

		return ! empty( $is_endpoint_url );
	}



	/**
	 * Checks whether we are currently on the join team by invitation page/endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_invitation_page() {
		return $this->is_join_team_page() && $this->is_invitation_token( $this->get_join_team_token_from_query_vars() );
	}


	/**
	 * Returns the join team token (either team registration key or invitation token) from the query vars.
	 *
	 * @since 1.0.0
	 *
	 * @return string token
	 */
	public function get_join_team_token_from_query_vars() {
		global $wp;

		if ( ! get_option( 'permalink_structure' ) ) {

			if ( ! empty( $_GET[ $this->join_team_endpoint ] ) ) {
				$key = wc_clean( $_GET[ $this->join_team_endpoint ] );
			}

		} else {

			$key = ! empty( $wp->query_vars[ $this->join_team_endpoint ] ) ? $wp->query_vars[ $this->join_team_endpoint ] : null;
		}

		return ! empty( $key ) ? $key : '';
	}


	/**
	 * Returns the current team for the join team endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key (optional) the registration key - defaults to the key found in the URL
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Team current team instance
	 */
	public function get_join_page_team( $key = null ) {

		if ( ! $this->join_page_team ) {

			$this->join_page_team = false;

			if ( ! $key ) {
				$key = $this->get_join_team_token_from_query_vars();
			}

			if ( $this->is_invitation_token( $key ) ) {

				if ( $invitation = $this->get_join_page_invitation( $key ) ) {
					$this->join_page_team = $invitation->get_team();
				}

			} else {

				$this->join_page_team = wc_memberships_for_teams_get_team( $key );
			}
		}

		return $this->join_page_team;
	}


	/**
	 * Returns the invitation instance for the join team page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token (optional) the invitation token - defaults to the token found in the URL
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Invitation|false team invitation instance or false if no invitation
	 */
	public function get_join_page_invitation( $token = null ) {

		if ( ! $this->join_page_invitation ) {

			if ( ! $token ) {
				$token = $this->get_join_team_token_from_query_vars();
			}

			$token = str_replace( 'i_', '', $token );

			$invitation = wc_memberships_for_teams_get_invitation( $token );

			$this->join_page_invitation = $invitation && $invitation->has_status( 'pending' ) ? $invitation : false;
		}

		return $this->join_page_invitation;
	}


	/**
	 * Checks whether a token is possible an invitation token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token the token to check
	 * @return bool true if likely an invitation token, false otherwise
	 */
	private function is_invitation_token( $token ) {

		return Framework\SV_WC_Helper::str_starts_with( $token, 'i_' );
	}


	/**
	 * Sets the My Account page title when viewing the Join Team endpoint.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $title the page title
	 * @return string
	 */
	public function adjust_account_page_title( $title ) {

		if ( $this->is_join_team_page() && ( ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) ) {

			$team = $this->get_join_page_team();

			if ( $team ) {

				$title = sprintf(
					/* translators: Placeholder: %s - team name */
					__( 'Join %s', 'woocommerce-memberships-for-teams' ),
					$team->get_name()
				);

			} else {

				$title = ucfirst( sprintf(
					/* translators: Placeholder: %s - the noun used to represent a team (singular) */
					__( 'Join %s', 'woocommerce-memberships-for-teams' ),
					ucfirst( wc_memberships_for_teams()->get_singular_team_noun() )
				) );
			}

			// remember: the removal priority must match the priority when the filter was added in constructor
			remove_filter( 'the_title', array( $this, 'adjust_account_page_title' ), 40 );
		}

		return $title;
	}


	/**
	 * Adjusts WooCommerce My Account area breadcrumbs.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $crumbs WooCommerce My Account breadcrumbs
	 * @return array
	 */
	public function adjust_account_page_breadcrumbs( $crumbs ) {
		global $wp;

		// sanity check to see if we're at the right endpoint:
		if (    isset( $wp->query_vars[ $this->join_team_endpoint ] )
		     && is_account_page()
		     && ( count( $crumbs ) > 0 ) ) {

			$team     = $this->get_join_page_team();
			$crumb    = $team ? sprintf( esc_html__( 'Join %s', 'woocommerce-memberships-for-teams' ), $team->get_name() ) : __( 'Join Team', 'woocommerce-memberships-for-teams' );
			$crumbs[] = array( $crumb, $team ? $team->get_registration_url() : '#' );
		}

		return $crumbs;
	}


	/**
	 * Returns the fields for the add team member form.
	 *
	 * @since 1.0.0
	 *
	 * @return array associative array of form fields
	 */
	public function get_add_team_member_form_fields() {

		$fields = array(
			'email' => array(
				'type'     => 'email',
				'label'    => __( 'Email', 'woocommerce-memberships-for-teams' ),
				'required' => true,
				'class' => array( 'form-row-first' ),
			),
			'role' => array(
				'type'     => 'select',
				'label'    => __( 'Role', 'woocommerce-memberships-for-teams' ),
				'required' => true,
				'class'    => array( 'form-row-last' ),
				'options'  => wc_memberships_for_teams_get_team_member_roles()
			),
		);

		/**
		 * Filters form fields for the add team member form on frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields associative array of form fields
		 */
		return apply_filters( 'wc_memberships_for_teams_add_team_member_form_fields', $fields );
	}


	/**
	 * Logs in a team owner.
	 *
	 * @since 1.0.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team instance
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	private function log_owner_in( $team ) {

		// we're not really concerned with roles since membership / subscription sites probably use custom roles
		// instead, just be sure we don't log anyone in with high permissions
		$log_in_user_id = $team->get_owner_id();
		$user_is_admin  = user_can( $log_in_user_id, 'edit_others_posts' ) || user_can( $log_in_user_id, 'edit_users' );

		/**
		 * Filters whether a team owner can be logged in automatically.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $allow true if the user should be automatically logged in (default false, do not allow)
		 * @param int $log_in_user_id the user ID of the user to log in
		 */
		$allow_login = (bool) apply_filters( 'wc_memberships_for_teams_allow_renewal_auto_user_login', false, $log_in_user_id );

		/**
		 * Fires right before logging a team member in.
		 *
		 * Can throw Framework\SV_WC_Plugin_Exception to halt the login completely.
		 *
		 * @since 1.0.0
		 *
		 * @param int $log_in_user_id the user ID of the member to log in
		 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team instance
		 * @param bool $allow_login whether automatic log in is allowed
		 */
		do_action( 'wc_memberships_for_teams_before_renewal_auto_login', $log_in_user_id, $team, $allow_login );

		// phpcs:disable Generic.PHP.ForbiddenFunctions.Discouraged

		// maybe log in the team owner
		if ( is_user_logged_in() ) {

			// another user is logged in
			if ( $log_in_user_id !== get_current_user_id() ) {

				// log out existing user
				wp_logout();

				// do not log in a user with high privileges
				if ( ! $user_is_admin || $allow_login ) {

					wp_set_current_user( $log_in_user_id );
					wp_set_auth_cookie( $log_in_user_id ); // nosemgrep
				}
			}

		} elseif ( ! $user_is_admin || $allow_login ) {

			// log the member in automatically if has low privileges
			wp_set_current_user( $log_in_user_id );
			wp_set_auth_cookie( $log_in_user_id ); // nosemgrep

		} else {

			throw new Framework\SV_WC_Plugin_Exception( __( 'Cannot automatically log in. Please log into your account and renew this membership manually.' , 'woocommerce-memberships-for-teams' ) );
		}

		// phpcs:enable Generic.PHP.ForbiddenFunctions.Discouraged

		/**
		 * Fires after logging in a team owner.
		 *
		 * @since 1.0.0
		 *
		 * @param int $log_in_user_id the user ID of the member to log in
		 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team instance
		 * @param bool $allow_login whether automatic log in is allowed
		 */
		do_action( 'wc_memberships_for_teams_after_renewal_auto_login', $log_in_user_id, $team, $allow_login );
	}


	/**
	 * Renews a team membership.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function renew_team_membership() {

		if ( ! isset( $_GET['renew_team_membership'] ) ) {
			return;
		}

		if ( ! isset( $_GET['user_token'] ) ) {
			wc_add_notice( __( 'Invalid renewal URL. Please log in to your account to renew.', 'woocommerce-memberships-for-teams' ), 'error' );
			return;
		}

		$team_id    = (int) $_GET['renew_team_membership'];
		$team       = wc_memberships_for_teams_get_team( $team_id );
		$user_token = wc_clean( $_GET['user_token'] );

		// we only need to redirect upon success; we should already be on the account page
		// based on how we generate this renewal URL so no need to redirect there
		try {

			$result       = $this->process_team_membership_renewal( $team, $user_token );
			$redirect_url = $result['redirect'];

			if ( ! empty( $result['message'] ) ) {
				wc_add_notice( $result['message'], 'success' );
			}

			wp_safe_redirect( $redirect_url );
			exit;

		} catch ( Framework\SV_WC_Plugin_Exception $e ) {

			wc_add_notice( $e->getMessage(), 'error' );
			return;
		}
	}


	/**
	 * Updates the seat count on a team.
	 *
	 * @internal
	 *
	 * @since 1.1.0
	 */
	public function update_team_seats() {

		if ( ! isset( $_POST['update_team_seats'] ) ) {
			return;
		}

		$change_value = ! empty( $_POST['team_seats'] )       ? (int) trim( $_POST['team_seats'] ) : 0; // phpcs:ignore: WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$change_mode  = ! empty( $_POST['seat_change_mode'] ) ? wc_clean( $_POST['seat_change_mode'] ) : null;
		$team_id      = (int) $_POST['update_team_seats'];
		$team         = wc_memberships_for_teams_get_team( $team_id );

		if ( ! $team || ! $team instanceof Team ) {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Invalid %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';

		} elseif ( 1 > $change_value || ! $change_mode || $change_mode !== $team->get_seat_change_mode() ) {

			$notice_message = __( 'Invalid seat value.', 'woocommerce-memberships-for-teams' );
			$notice_type    = 'error';

		} elseif (    isset( $_POST['_team_seats_nonce'] )
		           && current_user_can( 'wc_memberships_for_teams_manage_team_settings', $team_id )
		           && wp_verify_nonce( wc_clean( $_POST['_team_seats_nonce'] ), 'update-team-seats-' . $team_id ) ) {

			try {

				Seat_Manager::handle_team_seat_change( $team, $change_value );

			} catch ( Framework\SV_WC_Plugin_Exception $exception ) {

				$notice_message = $exception->getMessage();
				$notice_type    = 'error';
			}

		} else {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			$notice_message = ucfirst( sprintf( __( 'Cannot update seats for this %s.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );
			$notice_type    = 'error';
		}

		if ( isset( $notice_message, $notice_type ) ) {
			wc_add_notice( $notice_message, $notice_type );
		}

		wp_safe_redirect( $this->teams_area->get_teams_area_url( $team, 'settings' ) );
		exit;
	}


	/**
	 * Processes team membership renewals with a valid renewal link.
	 *
	 * @since 1.0.0
	 *
	 * @param \SkyVerge\WooCommerce\Memberships\Teams\Team $team team instance
	 * @param string $token team membership renewal token
	 * @return string[] updated redirect URL with a success message
	 * @throws Framework\SV_WC_Plugin_Exception
	 */
	protected function process_team_membership_renewal( $team, $token ) {

		if ( ! $team instanceof Team ) {

			throw new Framework\SV_WC_Plugin_Exception( ucfirst( sprintf(
				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				__( 'Invalid %s.', 'woocommerce-memberships-for-teams' ),
				wc_memberships_for_teams()->get_singular_team_noun()
			) ) );
		}

		if ( $team->can_be_renewed() ) {

			$renewal_token = $team->get_renewal_login_token();

			// check the token in the URL with the user membership's stored token
			if ( ! isset( $renewal_token['token'] ) || $token !== $renewal_token['token'] ) {

				throw new Framework\SV_WC_Plugin_Exception( ucfirst( sprintf(
					/* translators: Placeholder: %s - the noun used to represent a team (singular) */
					__( 'Invalid renewal token. Please log in to renew this %s membership.', 'woocommerce-memberships-for-teams' ),
					wc_memberships_for_teams()->get_singular_team_noun()
				) ) );
			}

			if ( ! isset( $renewal_token['expires'] ) || (int) $renewal_token['expires'] < time() ) {

				// wipe expired renewal token meta
				$team->delete_renewal_login_token();

				throw new Framework\SV_WC_Plugin_Exception( ucfirst( sprintf(
					/* translators: Placeholder: %s - the noun used to represent a team (singular) */
					__( 'Cannot log in as your renewal token has expired. Please log in to renew this %s membership from your account.', 'woocommerce-memberships-for-teams' ),
					wc_memberships_for_teams()->get_singular_team_noun()
				) ) );
			}

			// makes sure the member is logged in
			$this->log_owner_in( $team );

			// get the renewal product to be added to cart
			$product = $team->get_product();

			if ( current_user_can( 'wc_memberships_for_teams_renew_team_membership', $team->get_id() ) ) {

				/**
				 * Filters whether to add to cart the renewal product and redirect to checkout, or redirect to the product page without adding it to cart.
				 *
				 * @since 1.0.0
				 *
				 * @param bool $add_to_cart whether to add to cart the product and redirect to checkout (true, default) or redirect to product page instead (false).
				 * @param \WC_Product $product the product that would renew access if purchased again.
				 * @param int $team_id id of the team for which the membership is being renewed upon purchase.
				 */
				if ( true === (bool) apply_filters( 'wc_memberships_for_teams_add_to_cart_renewal_product', true, $product, $team->get_id() ) ) {

					// empty the cart and add the one product to renew this membership
					wc_empty_cart();

					// set up variation data (if needed) before adding to the cart
					$product_id           = $product->is_type( 'variation' ) ? $product->get_parent_id( 'edit' ) : $product->get_id();
					$variation_id         = $product->is_type( 'variation' ) ? $product->get_id() : 0;
					$variation_attributes = $product->is_type( 'variation' ) ? wc_get_product_variation_attributes( $variation_id ) : array();

					$cart_item_data = array(
						'team_meta_data' => array(
							'_wc_memberships_for_teams_team_renewal' => true,
							'_wc_memberships_for_teams_team_id'      => $team->get_id(),
							'team_name'                              => $team->get_name(),
						),
					);

					$seat_count  = $team->get_seat_count();
					$max_members = Product::get_max_member_count( $product );

					// quantity is determined by the number of seats or members currently in team
					if ( Product::has_per_member_pricing( $product ) ) {

						if ( $seat_count ) {
							$quantity = $seat_count;
						} else {
							// if the team has 0 members, then charge for 1 member
							$quantity = max( $team->get_member_count(), 1 );
						}

					} elseif ( $seat_count && $max_members ) {

						// for per-team pricing, we ensure that if required, an appropriate number of
						// "blocks" is purchased
						$quantity = ceil( $seat_count / $max_members );

					} else {

						// quantity should be 1 for per-team pricing with unlimited seats (max member count not set in product or team seat count not set)
						$quantity = 1;
					}

					// add the product to the cart (check for WC errors, like product not purchasable etc.)
					try {
						WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_attributes, $cart_item_data );
					} catch ( \Exception $e ) {
						/* translators: Placeholders: %1$s - the noun used to represent a team (singular), %2$s - error message */
						throw new Framework\SV_WC_Plugin_Exception( sprintf( __( 'Cannot renew this %1$s membership. %2$s', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun(), $e->getMessage() ) );
					}

					// then redirect to checkout instead of my account page
					$redirect_url = wc_get_checkout_url();

				} else {

					$redirect_url = get_permalink( $product->is_type( 'variation' ) ? $product->get_parent_id( 'edit' ) : $product->get_id() );
				}

				/* translators: Placeholder: %1$s - the noun used to represent a team (singular), %2$s - a product to purchase to renew a membership */
				$message = ucfirst( sprintf( __( 'Renew your %1$s membership by purchasing %2$s.', 'woocommerce-memberships-for-teams' ) . ' ', wc_memberships_for_teams()->get_singular_team_noun(), $product->get_title() ) );
				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				$message .= is_user_logged_in() ? ' ' : ucfirst( sprintf( __( 'You must be logged to renew your %s membership.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) );

			} else {

				/* translators: Placeholder: %s - the noun used to represent a team (singular) */
				throw new Framework\SV_WC_Plugin_Exception( ucfirst( sprintf( __( 'Cannot renew this %s membership. Please contact us if you need assistance.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) ) );
			}

		} else {

			/* translators: Placeholder: %s - the noun used to represent a team (singular) */
			throw new Framework\SV_WC_Plugin_Exception( ucfirst( sprintf( __( 'This %s membership cannot be renewed. Please contact us if you need assistance.', 'woocommerce-memberships-for-teams' ), wc_memberships_for_teams()->get_singular_team_noun() ) ) );
		}

		return [ 'redirect' => $redirect_url, 'message' => $message ];
	}


	/**
	 * Adds team details to membership details on Members Area.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $details user membership details
	 * @param \WC_Memberships_User_Membership $user_membership the user membership instance
	 * @return array
	 */
	public function add_my_membership_team_details( $details, $user_membership ) {

		if ( $team_id = wc_memberships_for_teams_get_user_membership_team_id( $user_membership->get_id() ) ) {

			$team    = wc_memberships_for_teams_get_team( $team_id );
			$content = $team->get_name();

			if ( current_user_can( 'wc_memberships_for_teams_manage_team', $team ) ) {
				$content = '<a href="' .  $this->get_teams_area_instance()->get_teams_area_url( $team, 'members' ) . '">' . $content . '</a>';
			}

			$team_details = [
				'team' => [
					'label'   => ucfirst( wc_memberships_for_teams()->get_singular_team_noun() ),
					'content' => $content,
					'class'   => 'my-membership-detail-user-membership-team'
				],
			];

			foreach ( array( 'next-payment-date', 'expires' ) as $key ) {

				if ( array_key_exists( $key, $details ) ) {

					$details = Framework\SV_WC_Helper::array_insert_after( $details, $key, $team_details );
					break;
				}
			}

			if ( ! array_key_exists( 'team', $details ) ) {

				$details['team'] = $team_details['team'];
			}
		}

		return $details;
	}


	/**
	 * Adds leave team action to membership actions on Members Area.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $actions user membership actions
	 * @param \WC_Memberships_User_Membership $user_membership the user membership instance
	 * @return array
	 */
	public function maybe_change_my_membership_actions( $actions, $user_membership ) {

		$team_id = wc_memberships_for_teams_get_user_membership_team_id( $user_membership->get_id() );

		if ( ! $team_id ) {
			return $actions;
		}

		$team = wc_memberships_for_teams_get_team( $team_id );

		if ( ! $team ) {
			return $actions;
		}

		$is_owner = $team->is_user_owner( $user_membership->get_user_id() );

		// add action to leave team unless the current member is the owner and they must occupy a seat
		if ( ! $is_owner || 'yes' !== get_option( 'wc_memberships_for_teams_owners_must_take_seat', 'yes' ) ) {

			$actions['leave_team'] = [
				'url'  => add_query_arg(
					[
						'leave_team' => $team_id,
						'_wpnonce'   => wp_create_nonce( 'leave-team-' . $team_id ),
					],
					wc_get_page_permalink( 'myaccount' )
				),
				'name' => ucfirst( sprintf(
					/* translators: Placeholder: %s - the noun used to represent a team (singular) */
					__( 'Leave %s', 'woocommerce-memberships-for-teams' ),
					ucfirst( wc_memberships_for_teams()->get_singular_team_noun() )
				) ),
			];
		}

		// remove billing-related actions for non-owners
		if ( ! $is_owner ) {
			unset( $actions['view-subscription'], $actions['renew'], $actions['cancel'] );
		}

		return $actions;
	}


	/**
	 * Returns Products instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Products
	 */
	public function get_products_instance() {
		return $this->products;
	}


	/**
	 * Returns Teams_Area instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Frontend\Teams_Area
	 */
	public function get_teams_area_instance() {
		return $this->teams_area;
	}


	/**
	 * Gets the profile fields handler instance.
	 *
	 * @since 1.4.1
	 *
	 * @return Frontend\Profile_Fields|null
	 */
	public function get_profile_fields_instance() {

		return $this->profile_fields;
	}


}
