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

use SkyVerge\WooCommerce\Memberships\Teams\Abilities\Provider;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\Contracts\AbilitiesProviderContract;

defined( 'ABSPATH' ) or exit;

/**
 * Teams for WooCommerce Memberships Main Plugin Class
 *
 * @since 1.0.0
 */
class Plugin extends Framework\SV_WC_Plugin implements Framework\Abilities\Contracts\HasAbilitiesContract {


	/** plugin version number */
	const VERSION = '1.9.0';

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Plugin single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'memberships-for-teams';

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Admin instance */
	protected $admin;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\AJAX instance */
	protected $ajax;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Cart instance */
	protected $cart;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Frontend instance */
	protected $frontend;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Capabilities instance */
	protected $capabilities;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Teams_Handler instance */
	protected $teams_handler;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Orders instance */
	protected $orders;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Emails instance */
	protected $emails;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Team_Members instance */
	protected $team_members;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Invitations instance */
	protected $invitations;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Membership_Plans instance */
	protected $membership_plans;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Profile_Fields instance */
	protected $profile_fields;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Utilities instance */
	private $utilities;

	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Integrations instance */
	protected $integrations;

	/** @var bool whether profile fields are supported */
	private $supports_profile_fields;


	/**
	 * Sets up the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			[
				'text_domain'   => 'woocommerce-memberships-for-teams',
				'supported_features' => [
					'hpos'   => true,
					'blocks' => [
						'cart'     => true,
						'checkout' => true
					],
				],
				'dependencies'  => [
					'php_extensions' => [
						'mbstring',
					],
				],
			]
		);

		// init post types and rewrite endpoints
		add_action( 'init', array( $this, 'init_post_types' ) );
		// add query vars for rewrite endpoints
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// make sure template files are searched for in our plugin
		add_filter( 'woocommerce_locate_template',      array( $this, 'locate_template' ), 20, 3 );
		add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_template' ), 20, 3 );
	}


	/**
	 * Initializes the plugin.
	 *
	 * @internal
	 *
	 * @since 1.1.2
	 */
	public function init_plugin() {

		$this->includes();
	}


	/**
	 * Initializes post types.
	 *
	 * @internal
	 *
	 * @since 1.1.2
	 */
	public function init_post_types() {

		Post_Types::initialize();

		$this->add_rewrite_endpoints();
	}


	/**
	 * Loads and initializes the plugin's lifecycle handler.
	 *
	 * @internal
	 *
	 * @since 1.11.0
	 */
	protected function init_lifecycle_handler() {

		$this->lifecycle_handler = new Upgrade( $this );
	}


	/**
	 * Loads required handlers.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		$this->capabilities     = new Capabilities;
		$this->teams_handler    = new Teams_Handler;
		$this->cart             = new Cart;
		$this->orders           = new Orders;
		$this->emails           = new Emails;
		$this->invitations      = new Invitations;
		$this->team_members     = new Team_Members;
		$this->membership_plans = new Membership_Plans;
		$this->utilities        = new Utilities;
		$this->integrations     = new Integrations;

		// if supported, load the profile fields handler
		if ( $this->supports_profile_fields() ) {
			$this->profile_fields = new Profile_Fields;
		}

		// frontend includes
		if ( ! is_admin() ) {
			$this->frontend_includes();
		// admin includes
		} else {
			$this->admin_includes();
		}

		// AJAX includes
		if ( wp_doing_ajax() ) {
			$this->ajax_includes();
		}
	}


	/**
	 * Loads required admin classes.
	 *
	 * @since 1.0.0
	 */
	private function admin_includes() {

		$this->admin = new Admin;

		// message handler
		$this->admin->message_handler = $this->get_message_handler();
	}


	/**
	 * Loads required AJAX classes.
	 *
	 * @since 1.0.0
	 */
	private function ajax_includes() {
		$this->ajax = new AJAX;
	}


	/**
	 * Loads required frontend classes.
	 *
	 * @since 1.0.0
	 */
	private function frontend_includes() {

		// load front end
		$this->frontend = new Frontend;
	}


	/**
	 * Returns the Admin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Returns the Ajax instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Returns Cart instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Cart
	 */
	public function get_cart_instance() {
		return $this->cart;
	}


	/**
	 * Returns the Frontend instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Returns the Teams_Handler instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Teams_Handler
	 */
	public function get_teams_handler_instance() {
		return $this->teams_handler;
	}


	/**
	 * Returns the Orders instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Orders
	 */
	public function get_orders_instance() {
		return $this->orders;
	}


	/**
	 * Returns the Emails instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Emails
	 */
	public function get_emails_instance() {
		return $this->emails;
	}


	/**
	 * Returns the Team_Members instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Team_Members
	 */
	public function get_team_members_instance() {
		return $this->team_members;
	}


	/**
	 * Returns the Invitations instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Invitations
	 */
	public function get_invitations_instance() {
		return $this->invitations;
	}


	/**
	 * Returns the Membership Plans instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Membership_Plans
	 */
	public function get_membership_plans_instance() {
		return $this->membership_plans;
	}


	/**
	 * Gets the Profile Fields instance.
	 *
	 * @since 1.4.1
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Profile_Fields|null
	 */
	public function get_profile_fields_instance() {

		return $this->profile_fields;
	}


	/**
	 * Gets the utilities instance.
	 *
	 * @since 1.1.2
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Utilities
	 */
	public function get_utilities_instance() {

		return $this->utilities;
	}


	/**
	 * Returns the Integrations instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Integrations
	 */
	public function get_integrations_instance() {
		return $this->integrations;
	}


	/**
	 * Determines whether the profile fields component is available.
	 *
	 * @since 1.4.3
	 *
	 * @return bool
	 */
	public function supports_profile_fields() {

		if ( null === $this->supports_profile_fields ) {
			$this->supports_profile_fields = version_compare( wc_memberships()->get_version(), '1.19.0', '>=' );
		}

		return $this->supports_profile_fields;
	}


	/**
	 * Locates the WooCommerce template files from our templates directory.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Already found template
	 * @param string $template_name Searchable template name
	 * @param string $template_path Template path
	 * @return string Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// only keep looking if no custom theme template was found
		// or if a default WooCommerce template was found
		if ( ! $template || Framework\SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

			// set the path to our templates directory
			$plugin_path = $this->get_plugin_path() . '/templates/';

			// if a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/**
	 * Generates a unique token for internal uses.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function generate_token() {

		return md5( wp_generate_password() . time() );
	}


	/**
	 * Renders a notice for the user to read the docs before adding add-ons.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$screen = get_current_screen();

		// only render on plugins or settings screen
		if ( ( $screen && 'plugins' === $screen->id ) || $this->is_plugin_settings() ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				sprintf(
					/* translators: the %s placeholders are meant for pairs of opening <a> and closing </a> link tags */
					__( 'Thanks for installing Memberships for Teams! To get started, take a minute to %1$sread the documentation%2$s and then %3$ssetup a membership plan%4$s :)', 'woocommerce-memberships-for-teams' ),
					'<a href="https://docs.woocommerce.com/document/teams-woocommerce-memberships/" target="_blank">', '</a>',
					'<a href="' . admin_url( 'edit.php?post_type=wc_membership_plan' ) . '">', '</a>'
				),
				'get-started-notice',
				[
					'always_show_on_settings' => false,
					'notice_class'            => 'updated',
				]
			);

			if ( 'yes' === get_option( 'wc_memberships_for_teams_show_teams_terminology_notice', 'no' ) ) {

				$this->get_admin_notice_handler()->add_admin_notice(
					sprintf(
						/* translators: %1$s - <strong> HTML opening tag, %2$s - </strong> HTML closing tag, %3$s - <a> HTML opening tag, %4$s - </a> HTML closing tag */
						__( '%1$sHeads up!%2$s In this version, you can change the "team" term from %3$sthe plugin settings%4$s. %1$sThis change may override other methods used for this purpose%2$s, such as the Say What? plugin. Please %3$supdate this setting%4$s to ensure teams are represented properly to your members.', 'woocommerce-memberships-for-teams' ),
						'<strong>', '</strong>',
						'<a href="' . esc_url( $this->get_settings_url() ) . '">', '</a>'
					),
					'update-teams-terminology-feature',
					[
						'always_show_on_settings' => false,
						'notice_class'            => 'updated',
					]
				);
			}
		}
	}


	/**
	 * Adds rewrite rules endpoints.
	 *
	 * TODO when WC 3.3+ is the minimum required version check if we still need this as WC 3.3 adds endpoints dynamically {IT 2018-05-09}
	 * @see \WC_Query::get_query_vars()
	 * @see \WC_Query::add_endpoints()
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_endpoints() {

		// add Teams Area endpoint
		add_rewrite_endpoint( get_option( 'woocommerce_myaccount_teams_area_endpoint', 'teams' ), EP_ROOT | EP_PAGES );

		// add Join Team endpoint
		add_rewrite_endpoint( get_option( 'woocommerce_myaccount_join_team_endpoint', 'join-team' ), EP_ROOT | EP_PAGES );
	}


	/**
	 * Handles query vars for endpoints.
	 *
	 * TODO when WC 3.3+ is the minimum required version check if we still need this as WC 3.3 adds endpoints dynamically {IT 2018-05-09}
	 * @see \WC_Query::get_query_vars()
	 * @see \WC_Query::add_endpoints()
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_vars associative array
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {

		$query_vars[] = get_option( 'woocommerce_myaccount_teams_area_endpoint', 'teams' );
		$query_vars[] = get_option( 'woocommerce_myaccount_join_team_endpoint', 'join-team' );

		return $query_vars;
	}


	/**
	 * Checks whether currently on the Teams settings screen.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean true if on the admin settings page
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'], $_GET['tab'], $_GET['section'] )
		       // WooCommerce core settings Wordpress admin page
		       && 'wc-settings' === $_GET['page']
		       // main Memberships settings sub-page
		       && ( 'memberships' === $_GET['tab']
		            // the Teams settings section
		            && ( 'teams' === $_GET['section'] ) );
	}


	/**
	 * Gets the plugin configuration URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_id the plugin identifier
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {

		return admin_url( 'admin.php?page=wc-settings&tab=memberships&section=teams' );
	}


	/**
	 * Gets the plugin documentation URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_documentation_url() {

		return 'https://docs.woocommerce.com/document/teams-woocommerce-memberships/';
	}


	/**
	 * Gets the plugin support URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Gets the plugin sales page URL
	 *
	 * @since 1.1.2
	 *
	 * @return string
	 */
	public function get_sales_page_url() {

		return 'https://woocommerce.com/products/teams-woocommerce-memberships/';
	}


	/**
	 * Gets the plugin name, localized.
	 *
	 * @since 1.0.0
	 *
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'Teams for WooCommerce Memberships', 'woocommerce-memberships-for-teams' );
	}


	/**
	 * Gets the default singular noun to be used to represent "team".
	 *
	 * @since 1.3.0
	 *
	 * @return string lowercase, default 'team'
	 */
	public function get_default_singular_team_noun() {

		return $this->get_team_noun( 'singular', true );
	}


	/**
	 * Gets the default plural noun to be used to represent "teams".
	 *
	 * @since 1.3.0
	 *
	 * @return string lowercase, default 'teams'
	 */
	public function get_default_plural_team_noun() {

		return $this->get_team_noun( 'plural', true );
	}


	/**
	 * Gets the user-defined singular noun to be used to represent "team".
	 *
	 * @since 1.3.0
	 *
	 * @return string lowercase
	 */
	public function get_singular_team_noun() {

		return $this->get_team_noun( 'singular' );
	}


	/**
	 * Gets the user-defined plural noun to be used to represent "teams".
	 *
	 * @since 1.3.0
	 *
	 * @return string lowercase
	 */
	public function get_plural_team_noun() {

		return $this->get_team_noun( 'plural' );
	}


	/**
	 * Gets the lowercase noun to be used to represent a team across the plugin interface.
	 *
	 * Do not open method to public, prefer using instead:
	 * @see Plugin::get_singular_team_noun()
	 * @see Plugin::get_plural_team_noun()
	 *
	 * @since 1.3.0
	 *
	 * @param string $singular_or_plural either 'singular' or 'plural' (in some languages they may coincide)
	 * @param bool $use_default whether to force return the default value (default false), used as a fallback otherwise
	 * @return string
	 */
	private function get_team_noun( $singular_or_plural, $use_default = false ) {

		$team_noun        = '';
		$default_singular = _x( 'team', 'Used to represent what teams (singular form) are called throughout the plugin', 'woocommerce-memberships-for-teams' );
		$default_plural   = _x( 'teams', 'Used to represent what teams (plural form) are called throughout the plugin', 'woocommerce-memberships-for-teams' );

		if ( $use_default ) {

			if ( 'singular' === $singular_or_plural ) {
				$team_noun = $default_singular;
			} elseif ( 'plural' === $singular_or_plural ) {
				$team_noun = $default_plural;
			}

		} else {

			$singular = trim( get_option( 'wc_memberships_for_teams_team_noun_singular', $default_singular ) );
			$plural   = trim( get_option( 'wc_memberships_for_teams_team_noun_plural', $default_plural ) );

			if ( 'singular' === $singular_or_plural ) {
				$team_noun = empty( $singular ) ? $default_singular : $singular;
			} elseif ( 'plural' === $singular_or_plural ) {
				$team_noun = empty( $plural ) ? $default_plural : $plural;
			}
		}

		return strtolower( $team_noun );
	}


	/**
	 * Returns the full path to the plugin entry script.
	 *
	 * @since 1.0.0
	 *
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return dirname( __DIR__ ) . "/woocommerce-{$this->get_id()}.php";
	}


	/**
	 * Returns the main Memberships for Teams Instance, ensures only one instance is/can be loaded.
	 *
	 * @see wc_memberships_for_teams()
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();

		}

		return self::$instance;
	}


    /** @inheritDoc */
    public function getAbilitiesProvider(): AbilitiesProviderContract
    {
        return new Provider($this);
    }
}
