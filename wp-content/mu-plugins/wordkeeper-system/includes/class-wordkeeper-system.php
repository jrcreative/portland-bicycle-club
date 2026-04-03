<?php

namespace WordKeeper_System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use add_filter;
use current_user_can;
use dirname;
use function_exists;
use plugin_dir_path;

/**
 * The core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin
 *
 */
class WordKeeper_System {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin
	 *
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin
	 */
	public function __construct() {

		$this->plugin_name = 'wordkeeper-system';
		$this->version = '1.3.6';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_universal_hooks();

	}

	/**
	 * Load the required dependencies for this plugin
	 *
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'front/class-front.php';

		/**
		 * The utility class that provides useful functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-utilities.php';

		/**
		 * The purge class provides useful cache purging functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-purge.php';

		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress
	 *
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new i18n();
		$plugin_i18n->set_domain($this->get_plugin_name());

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Admin();
		$purge_class = new Purge();

		$isajax = ((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined('DOING_AJAX') && DOING_AJAX)) ? true : false;
		$iscron = (function_exists('wp_doing_cron') && wp_doing_cron()) ? true : false;
		$iscli = ((defined('WP_CLI') && WP_CLI)) ? true : false;
		$isrest = (strpos($_SERVER['REQUEST_URI'], '/wp-json') === 0 || !empty($_GET['rest_route '])) ? true : false;

		$page = (empty($_GET['page'])) ? '' : $_GET['page'];
		if($page == 'wordkeeper-system') {
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		}

		$this->loader->add_action('admin_init', $purge_class, 'handle_bulk_operations');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_settings_page');

		$this->loader->add_action('publish_future_post', $purge_class, 'queue_purge_post');
		$this->loader->add_action('transition_post_status', $purge_class, 'handle_status_transition', 10, 3);

		$this->loader->add_action('customize_save_after', $purge_class, '_purge_theme');
		$this->loader->add_action('switch_theme', $purge_class, '_purge_theme');

		$this->loader->add_action('wp_before_admin_bar_render', $plugin_admin, 'register_purge_menu', 10);
		$this->loader->add_action('wp_loaded', $plugin_admin, 'handle_purge_request');

		// Add theme/plugin specific hooks
		$this->loader->add_action('fl_builder_cache_cleared', $purge_class, 'purge_cache');
		$this->loader->add_action('fl_builder_after_save_layout', $purge_class, 'purge_cache');
		$this->loader->add_action('fl_builder_after_save_user_template', $purge_class, 'purge_cache');

		// Add hooks for problematic theme/plugin updates
		add_action('upgrader_process_complete', function($upgrader, $hook_extra){
			if(!empty($hook_extra) && $hook_extra['type'] == 'plugin' && !empty($hook_extra['plugins'])){
				$purgeable = array(
					'bb-plugin/fl-builder.php',
					'bbpowerpack/bb-powerpack.php',
					'bb-ultimate-addon/bb-ultimate-addon.php',
					'bb-theme-builder/bb-theme-builder.php',
				);

				if(!empty(array_intersect($purgeable, $hook_extra['plugins']))){
					Purge::purge_cache();
				}
			}
			elseif(!empty($hook_extra) && $hook_extra['type'] == 'theme' && !empty($hook_extra['themes'])){
				$purgeable = array(
					'bb-theme',
					'Divi'
				);

				if(!empty(array_intersect($purgeable, $hook_extra['themes']))){
					Purge::purge_cache();
				}
			}
		}, 10 , 2);

		if($isajax && isset($_POST['action']) && $_POST['action'] == 'wordkeeper_admin_ajax') {
			$this->loader->add_action('wp_ajax_wordkeeper_admin_ajax', $plugin_admin, '_ajax');
			//return;
		}
		else{

			// Load Woo stock status change purge hooks
			if(!is_admin() || $isajax || $iscron || $iscli){
				$this->loader->add_action('woocommerce_variation_set_stock_status', $purge_class, 'purge_post', 10, 1);
				$this->loader->add_action('woocommerce_product_set_stock_status', $purge_class, 'purge_post', 10, 1);
			}

			// User is in the admin or using AJAX, register associated hooks
			if(is_admin() || $isajax || $iscron || $iscli || $isrest){
				// Post changes, hooks pass ID of post
				$this->loader->add_action('save_post', $purge_class, 'queue_purge_post');
				$this->loader->add_action('woocommerce_update_product_variation', $purge_class, 'purge_post');
				$this->loader->add_action('pre_post_update', $purge_class, 'purge_post');
				$this->loader->add_action('wp_trash_post', $purge_class, 'purge_post', 0);

				// Comment changes, hooks pass ID of comment
				$this->loader->add_action('transition_comment_status', $purge_class, '_purge_comment_transition', 10, 3);
				$this->loader->add_action('edit_comment', $purge_class, '_purge_comment', 10, 1);
				$this->loader->add_action('untrashed_comment', $purge_class, '_purge_comment', 10, 1);
				$this->loader->add_action('delete_comment', $purge_class, '_purge_comment', 10, 1);

				// Term/taxonomy changes, hooks pass ID of terms
				$this->loader->add_action('edit_terms', $purge_class, '_purge_term_processor', 10, 2);
				$this->loader->add_action('delete_term', $purge_class, '_purge_term_processor', 10, 5);
			}
		}

		// Remove security headers for GiveWP gateway auth flow (where these block gateway verification)
		if(
			strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false &&
			isset($_GET['page']) && $_GET['page'] == 'give-settings' &&
			isset($_GET['tab']) && $_GET['tab'] == 'gateways'
		){
			header('Content-Security-Policy: ');
			header('Cross-Origin-Opener-Policy: ');
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin
	 *
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Front();
		$purge_class = new Purge();

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		$this->loader->add_action('admin_enqueue_scripts', $plugin_public, 'heartbeat_control', 100);
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'heartbeat_control', 100);
		$this->loader->add_filter('heartbeat_settings', $plugin_public, 'heartbeat_frequency');

		$this->loader->add_filter('rest_post_dispatch', $plugin_public, 'rest_cache_control', 99, 3);
		$this->loader->add_filter('rest_authentication_errors', $plugin_public, 'rest_auth_cache_control', 99, 1);
		$this->loader->add_filter('robots_txt', $plugin_public, 'robots', 99, 2);
		$this->loader->add_filter('wp', $plugin_public, 'cache_control', 99);
		$this->loader->add_filter('wp_redirect', $plugin_public, 'redirect_cache_control', 100, 2);

		// Disable WP Rocket page caching
		if(defined('WP_ROCKET_VERSION')){
			add_filter('do_rocket_generate_caching_files', '__return_false');
		}

		// Get a list of all active plugins (both single and multisite)
		$plugins = get_option('active_plugins');
		$plugins = (is_array($plugins)) ? $plugins : array();
		if(defined('MULTISITE') && MULTISITE === true){
			$networkplugins = get_site_option('active_sitewide_plugins', array());
			$networkplugins = (is_array($networkplugins)) ? array_keys($networkplugins) : array();
			$plugins = array_unique(array_merge($plugins, $networkplugins));
		}

		// For WooCommerce sites, purge the cache for product pages if stock changes
		if(in_array('woocommerce/woocommerce.php', $plugins) && 'yes' === get_option('woocommerce_manage_stock')){
			$this->loader->add_action('woocommerce_updated_product_stock', $purge_class, 'handle_stock_update');
		}

		// Massage attachment data before saving
		add_filter('wp_handle_upload_prefilter', function($file){
			// Remove problem chars from file name
			if(isset($file['name'])){
				$file['name'] = sanitize_filename($file['name']);
			}

			// Remove problem chars from file path
			if(isset($file['full_path'])){
				$file['full_path'] = sanitize_filename($file['full_path']);
			}

			return $file;
		}, 1000, 1);

		// Add submitting IP and URL headers for email security logging
		add_filter('wp_mail', function($atts){

			// Set the URL header to either the active URL or the referring URL if the active URL is a REST API request
			$url = (strpos($_SERVER['REQUEST_URI'], '/wp-json') !== false && !empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'];

			if(is_array($atts['headers'])){
				$atts['headers']['X-Originating-IP'] = "X-Originating-IP: " . $_SERVER['REMOTE_ADDR'];
				$atts['headers']['X-Originating-URL'] = "X-Originating-URL: " . $url;
			}
			else{
				$atts['headers'] .= "X-Originating-IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
				$atts['headers'] .= "X-Originating-URL: " . $url . "\n";
			}

			return $atts;
		}, 10, 1);

		// Allow WP cron to set itself
		add_filter('schedule_event', function($event){
			if(!defined('\SETTING_CRON')){
				define('SETTING_CRON', true);
			}

			return $event;
		}, PHP_INT_MIN, 1);

		// Allow WP cron to set itself
		add_filter('pre_unschedule_event', function($pre, $timestamp, $hook, $args, $wp_error ){
			if(!defined('\SETTING_CRON')){
				define('SETTING_CRON', true);
			}

			return $pre;
		}, PHP_INT_MIN, 5);

		// Allow WP cron to set itself
		add_filter('pre_unschedule_hook', function($pre, $hook, $wp_error){
			if(!defined('\SETTING_CRON')){
				define('SETTING_CRON', true);
			}

			return $pre;
		}, PHP_INT_MIN, 3);

		// User audit
		if(!empty($_SERVER['SERVER_MODE']) && $_SERVER['SERVER_MODE'] == '1'){
			add_filter('init', function(){
				if(is_user_logged_in()){
					// Make sure that get current user is defined
					if(!function_exists('wp_get_current_user')){
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
					}

					// Log active user for malware/abuse detection
					$user = wp_get_current_user();
					header('X-Auth-User: ' . $user->user_login);
				}
			}, 10);
		}

		// If the site is a dev/staging site, disable problem features
		if(preg_replace('#(?:dev|staging)_#', '', ABSPATH) != ABSPATH){
			// Disable WooCommerce subscriptions to prevent duplicate transactions
			add_filter('woocommerce_subscriptions_is_duplicate_site', '__return_true');
		}
	}

	/**
	 * Register all of the univeral hooks
	 *
	 * @access   private
	 */
	private function define_universal_hooks(){

		$purge_class = new Purge();

		// Cache purge on post comments
		// Only purges the cache if the user is logged in or the comment is approved
		$this->loader->add_action('comment_post', $purge_class, '_purge_comment', 10, 2);

		// Register a global PHP callback to remove caching headers if the final status code of the request isn't cacheable
		// Not ideal since you can only register one header callback, but it's not a common function and this is the perfect
		// and intended use case for that function.  So it will work for our purposes
		header_register_callback(function(){
			$cache = array(
				200,
				301,
				302,
				404
			);

			// If the response code signifies an uncacheable request, remove caching headers
			$code = (int) http_response_code();
			if(!in_array($code, $cache)){
				header_remove('X-Accel-Expires');
			}
		});

		// Submit the final purge request to the purger before the PHP process wraps
		$this->loader->add_action('shutdown', $purge_class, 'purge', 10);

		if(!empty($_SERVER['SERVER_MODE']) && $_SERVER['SERVER_MODE'] == '1'){
			// Send back a failed login header for Nginx scripting to use
			add_action('wp_login_failed', function($username, $error){
				header('X-Auth-Status: Failed');
			}, 10, 2);

			// Send back a failed login header for Nginx scripting to use
			add_filter('xmlrpc_login_error', function($error, $user){
				header('X-XmlRpc-Auth-Status: Failed');
				return $error;
			}, 10, 2);
		}

		// Reject changes to core WP options when the change wasn't submitted by an approved admin
		if((!defined('WP_CLI') || !\WP_CLI) && (!defined('DOING_CRON') || !\DOING_CRON)){
			add_filter('pre_update_option', function ($new, $name, $old){
				// If permalinks change, flush the whole site cache
				if($name == 'permalink_structure'){
					if($new != $old){
						Purge::purge_cache();
					}
				}

				$filter = array(
					'siteurl' => true,
					'home' => true,
					'active_plugins' => true,
					'users_can_register' => true,
					'admin_email' => true,
					'comments_notify' => true,
					'comment_moderation' => true,
					'comment_registration' => true,
					'mailserver_url' => true,
					'mailserver_login' => true,
					'mailserver_pass' => true,
					'mailserver_port' => true,
					'default_comment_status' => true,
					'default_ping_status' => true,
					'default_role' => true,
					'blog_public' => true,
					'use_trackback' => true,
					'upload_path' => true,
					'upload_url_path' => true,
					'blog_public' => true,
					'wp_user_roles' => true,
					'cron' => true,
					'current_theme' => true,
				);

				// Allow WP to manage its crons in special cases
				if(defined('\SETTING_CRON') && \SETTING_CRON === true){
					unset($filter['cron']);
				}

				if(isset($filter[$name])){
					if(!function_exists('current_user_can')){
						require_once(\ABSPATH . '/wp-includes/user.php');
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
						require_once(\ABSPATH . '/wp-includes/capabiliies.php');
					}
					elseif(!function_exists('wp_get_current_user')){
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
					}
					return (current_user_can('manage_options')) ? $new : $old;
				}
				else{
					return $new;
				}
			}, 100, 3);
		}

		// Reject changes to user access rights unless the logged in user has rights to edit that
		if((!defined('WP_CLI') || !\WP_CLI)){
			add_filter('update_user_metadata', function ($check, $object_id, $meta_key, $meta_value, $old_value){
				$filter = array(
					'wp_capabilities' => true,
					'wp_user_level' => true,
				);

				// If we're dealing with a permissions change setting, evaluate
				if(isset($filter[$meta_key])){
					// If the value hasn't changed, exit early
					if($old_value === $meta_value){
						return $check;
					}

					// Otherwise we need to scrutinize the change
					if(!function_exists('current_user_can')){
						require_once(\ABSPATH . '/wp-includes/user.php');
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
						require_once(\ABSPATH . '/wp-includes/capabiliies.php');
					}
					elseif(!function_exists('wp_get_current_user')){
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
					}

					// If the current user has promote_users privileges, allow the change
					if(current_user_can('promote_users')){
						return $check;
					}
					// If the current user doesn't have promote_users privileges, determine if the user is new or pre-existing
					else{
						// For pre-existing users, reject the change
						if($old_value !== ''){
							return false;
						}
						// For new users, only allow non-admin capable roles
						else{
							// For the capabilities attribute, filter by capability
							if($meta_key == 'wp_capabilities'){
								$unsafe = array(
									'activate_plugins',
									'add_users',
									'create_sites',
									'create_users',
									'customize',
									'delete_others_pages',
									'delete_others_posts',
									'delete_plugins',
									'delete_private_pages',
									'delete_private_posts',
									'delete_site',
									'delete_sites',
									'delete_themes',
									'delete_users',
									'edit_dashboard',
									'edit_files',
									'edit_others_pages',
									'edit_others_posts',
									'edit_plugins',
									'edit_private_pages',
									'edit_private_posts',
									'edit_theme_options',
									'edit_themes',
									'edit_users',
									'export',
									'import',
									'install_plugins',
									'install_themes',
									'list_users',
									'manage_categories',
									'manage_links',
									'manage_network',
									'manage_network_options',
									'manage_network_plugins',
									'manage_network_themes',
									'manage_network_users',
									'manage_options',
									'manage_sites',
									'promote_users',
									'read_private_pages',
									'read_private_posts',
									'remove_users',
									'setup_network',
									'switch_themes',
									'update_core',
									'update_plugins',
									'update_themes',
									'upgrade_network',
								);

								$reject = false;
								if(is_array($meta_value)){
									foreach($meta_value as $role => $value){
										if($value === true){
											$role = get_role($role);

											// If the named role doesn't exist, reject
											if(empty($role)){
												$reject = true;
											}
											// If the named role does exist and the user level contains dangerous capabilities, reject
											else{
												if(!empty(array_intersect($unsafe, array_keys($role->capabilities)))){
													$reject = true;
												}
											}
										}
									}
								}
								else{
									return false;
								}

								// Return the value or reject the value depending on the caps assigned to the user
								return ($reject === true) ? false : $check;
							}
							// For user level, filter by user levels that have user access and up
							elseif($meta_key == 'wp_user_level'){
								if((int) $meta_value >= 5){
									return false;
								}
							}
						}
					}
				}
				// Otherwise allow the change
				else{
					return $check;
				}
			}, 10, 5);
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality
	 *
	 * @return    string    The name of the plugin
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin
	 *
	 * @return    Loader    Orchestrates the hooks of the plugin
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin
	 *
	 * @return    string    The version number of the plugin
	 */
	public function get_version() {
		return $this->version;
	}
}