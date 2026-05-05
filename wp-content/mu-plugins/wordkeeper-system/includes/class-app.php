<?php

namespace WordKeeper\System;

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
class App {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin
	 *
	 * @access   	protected
	 * @var			Loader		$loader				Maintains and registers all hooks for the plugin
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin
	 *
	 * @access		protected
	 * @var			string		$plugin_name		The string used to uniquely identify this plugin
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin
	 *
	 * @access		protected
	 * @var			string		$version			The current version of the plugin
	 */
	protected $version;

	/**
	 * The current version of the plugin
	 *
	 * @access		protected
	 * @var			array		$settings			The current settings of the plugin
	 */
	protected $settings;

	/**
	 * Define the core functionality of the plugin
	 */
	public function __construct(){

		$this->plugin_name = 'wordkeeper-system';
		$this->version = '2.1.1';
		$this->settings = (defined('MULTISITE') && MULTISITE === true) ? get_site_option('wordkeeper/system') : get_option('wordkeeper/system');

		if(!defined('WORDKEEPER_SYSTEM_VERSION')){
			define('WORDKEEPER_SYSTEM_VERSION', $this->version);
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_front_hooks();
		$this->define_purge_hooks();
		$this->define_universal_hooks();

		// Add an "every minute" schedule option
		add_filter('cron_schedules', function($schedules){
			$schedules['every_minute'] = array(
				'interval' => 60, // 60 seconds
				'display'  => __('Every Minute')
			);
			return $schedules;
		});

		// Add a purge process to clear any queued purges
		add_action('wordkeeper/purge', function(){
			if(class_exists('\WordKeeper\System\Purge')){
				// Get any URLs that weren't successfully purged in
				// a previous purge due to rate limiting
				$addpurges = get_transient('wordkeeper/purge/next');
				if(!empty($addpurges)){
					\WordKeeper\System\Purge::purge_by_url();
				}
			}
		});

		// Schedule a purge to run once every minute (or as close as possible) to check for pending purges
		add_action('init', function(){
			if(!wp_next_scheduled('wordkeeper/purge')){
				// If there isn't a cron for this yet, schedule a cron to run every minute
				wp_schedule_event(time(), 'every_minute', 'wordkeeper/purge');
			}
		});

		// Skip REST setup if we're in WP CLI
		if(defined('WP_CLI') && WP_CLI){
			return;
		}

		$rest = new Rest();
		$rest->register_routes();
	}

	/**
	 * Load the required dependencies for this plugin
	 *
	 * @access		private
	 */
	private function load_dependencies(){

		// Plugin loader
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-loader.php';

		// Internationalization
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-i18n.php';

		// Settings class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-settings.php';

		// Limits class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-limits.php';

		// Admin class
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-admin.php';

		// Front/public facing class
		require_once plugin_dir_path(dirname(__FILE__)) . 'front/class-front.php';

		// Countries class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-countries.php';

		// Bots class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-bots.php';

		// Dispatch class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-dispatch.php';

		// REST class
		require_once plugin_dir_path(dirname(__FILE__)) . 'rest/class-rest.php';

		// Common utilities
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-utilities.php';

		// Purge class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-purge.php';

		// Robots class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-robots.php';

		// Heartbeat class
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-heartbeat.php';

		// Lifetimes class for cache liftime management
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lifetimes.php';

		$this->loader = new Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress
	 *
	 * @access		private
	 */
	private function set_locale(){

		$plugin_i18n = new i18n();
		$plugin_i18n->set_domain($this->get_plugin_name());

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin
	 *
	 * @access		private
	 */
	private function define_admin_hooks(){
		// Skip admin setup if we're in WP CLI
		if(defined('WP_CLI') && WP_CLI){
			return;
		}

		$admin = new Admin();

		// WordKeeper admin adjustments
		if(preg_match('#/wp-admin/(?:network/)?admin\.php$#', $_SERVER['SCRIPT_NAME']) === 1){
			$page = (empty($_GET['page'])) ? '' : $_GET['page'];
			if(strpos($page, 'wordkeeper-system') !== false){
				// Limit admin JS enqueue to just our own admin pages
				$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_scripts');
				$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_styles');

				// Prevent plugins that generate excessive background HTML from running in the WordKeeper admin
				add_filter('option_active_plugins', function($plugins){
					// List of plugins to disable
					$disable = array(
						'query-monitor/query-monitor.php',			// Injects ALL of the HTML into our admin pages
						'wp-sentry-integration/wp-sentry.php',		// Modifies our AJAX requests to include tracing headers
						'imagify/imagify.php',						// Injects their Swal into our admin pages
						'geodirectory/geodirectory.php',			// Injects ALL of the HTML into our admin pages
						'userswp/userswp.php',						// Injects ALL of the HTML into our admin pages
						'userswp-recaptcha/uwp-recaptcha.php',		// Injects ALL of the HTML into our admin pages
					);

					// Remove plugins from load
					foreach($plugins as $index => $plugin){
						if(in_array($plugin, $disable)){
							unset($plugins[$index]);
						}
					}

					return $plugins;
				}, 5, 1);

				// Prevent sitewide network active plugins that generate excessive background HTML from running in the WordKeeper admin
				if(defined('MULTISITE') && MULTISITE === true){
					add_filter('site_option_active_sitewide_plugins', function($plugins){
						// List of plugins to disable
						$disable = array(
							'query-monitor/query-monitor.php',			// Injects ALL of the HTML into our admin pages
							'wp-sentry-integration/wp-sentry.php',		// Modifies our AJAX requests to include tracing headers
							'imagify/imagify.php',						// Injects their Swal into our admin pages
							'geodirectory/geodirectory.php',			// Injects ALL of the HTML into our admin pages
							'userswp/userswp.php',						// Injects ALL of the HTML into our admin pages
							'userswp-recaptcha/uwp-recaptcha.php',		// Injects ALL of the HTML into our admin pages
						);

						// Remove plugins from load
						foreach($plugins as $plugin => $value){
							if(in_array($plugin, $disable)){
								unset($plugins[$plugin]);
							}
						}

						return $plugins;
					}, 5, 1);
				}
			}
		}

		$this->loader->add_action('admin_enqueue_scripts', $admin, 'enqueue_global_styles');
		$this->loader->add_action('admin_enqueue_scripts', $admin, 'dequeue_conflicts', PHP_INT_MAX);
		$this->loader->add_action('admin_menu', $admin, 'add_menu');
		$this->loader->add_action('network_admin_menu', $admin, 'add_menu');

		// Add drop down purge menu to toolbar and handle any issues purge requests
		$this->loader->add_action('wp_before_admin_bar_render', $admin, 'add_purge_menu', 10);
		$this->loader->add_action('wp_loaded', $admin, 'handle_purge_request');

		// Fix status tests to remove failing/wrong/junk tests and better direct customers how to do things that we automate
		add_filter('site_status_tests', function($tests){
			if(!empty($tests['direct'])){
				// Remove disk space check since it always fails and doesn't apply in our case
				unset($tests['direct']['available_updates_disk_space']);
			}

			if(!empty($tests['async'])){
				// Remove page cache check since it's always wrong.  We are caching pages
				unset($tests['async']['page_cache']);
			}

			return $tests;
		}, 10 , 1);

		add_filter('site_status_test_result', function($result){
			// Edit the PHP version test to direct people to the WordKeeper area in hosting to change their PHP version or sync to staging to test there first
			if($result['test'] == 'php_version'){
				$result['description'] = sprintf(
					'<p>%s</p>',
					sprintf(
						__( 'PHP is the programming language that powers WordPress.  Newer versions of PHP often increase security and improve performance but may introduce new compatibility problems so testing new versions in staging may be appropriate before deploying a version change on live.')
				));

				$result['actions'] = '<ul style="list-style-type:disc;padding: 0 0 0 20px;"><li>' . __('To sync to staging to test your site go to the') . ' <a href="' . get_admin_url() . 'admin.php?page=wordkeeper-system-general' . '">' . __('General') . '</a> ' . __('area in WordKeeper.') . '</li><li>' . __('To change your PHP version, go to the') . ' <a href="' . get_admin_url() . 'admin.php?page=wordkeeper-system-hosting&tab=php-settings' . '">' . __('PHP Settings') . '</a> ' . __('area in WordKeeper\'s') . ' <a href="' . get_admin_url() . 'admin.php?page=wordkeeper-system-hosting' . '">' . __('Hosting') . '</a> ' . __('settings.') . '</li></ul>';
			}
			return $result;
		}, 10, 1);

		// Remove wordkeeper cookie when switching users or otherwise clearing auth cookies
		add_action('clear_auth_cookie', function(){
			if(isset($_COOKIE['wordkeeper'])){
				setcookie('wordkeeper', '', time() - 3600);
			}
		}, 10);

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
	 * @access		private
	 */
	private function define_front_hooks(){
		// Skip frontend setup if we're in WP CLI
		if(defined('WP_CLI') && WP_CLI){
			return;
		}

		$front = new Front();

		$this->loader->add_action('wp_enqueue_scripts', $front, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $front, 'enqueue_scripts');

		// Robots.txt management
		$robots = new Robots();
		$this->loader->add_filter('robots_txt', $robots, 'render', 99, 2);

		// Cache lifetime management
		$lifetimes = new Lifetimes();
		if(!is_admin()){
			$this->loader->add_filter('wp', $lifetimes, 'cache', 99);
			$this->loader->add_filter('wp_redirect', $lifetimes, 'redirects', 100, 2);
			$this->loader->add_filter('rest_post_dispatch', $lifetimes, 'rest', 99, 3);
		}
		$this->loader->add_filter('rest_authentication_errors', $lifetimes, 'rest_auth', 99, 1);
		$this->loader->add_action('login_init', $lifetimes, 'login', 99);

		// Redirect source tracking
		add_filter('x_redirect_by', function($source){
			// Exit early If something already named its redirect
			if($source !== 'WordPress'){
				return $source;
			}

			// Exit early for non-HTTP contexts (WP-CLI, cron, or CLI executions)
			if(defined('WP_CLI') && WP_CLI || defined('DOING_CRON') && DOING_CRON ||
				PHP_SAPI === 'cli' || !isset($_SERVER['REQUEST_URI'])){
				return $source;
			}

			// Ignore admin and other core file redirects
			if(
				(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-admin/') !== false) ||
				(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) ||
				(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-comments-post.php') !== false) ||
				(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-trackback.php') !== false) ||
				(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'wp-cron.php') !== false)){
				return 'WordPress';
			}

			// Evaluate the backtrace to find the original cause of the redirect
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($backtrace as $frame){
				if(!isset($frame['file'])){
					continue;
				}

				$file = $frame['file'];

				// Skip references to core WordPress files
				if(strpos($file, ABSPATH . 'wp-includes') !== false || strpos($file, ABSPATH . 'wp-admin') !== false){ continue; }

				// An MU plugin caused the redirect
				if(strpos($file, WPMU_PLUGIN_DIR) !== false){
					$source = 'MU Plugin';
					break;
				}
				// A plugin caused the redirect.  Name the plugin
				elseif(strpos($file, WP_PLUGIN_DIR) !== false){
					// Include plugin.php if not yet loaded
					if(!function_exists('get_plugins')){
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}

					$plugins = get_plugins();
					$plugindir = dirname($file);

					// Top level plugin
					if($plugindir === WP_PLUGIN_DIR){
						$key = basename($file);
					}
					// Normal plugin w/ subfolder
					else{
						$slug = basename($plugindir);
						$key = null;
						foreach($plugins as $path => $data){
							if(strpos($path, $slug . '/') === 0){
								$key = $path;
								break;
							}
						}
					}

					// Name the plugin that caused the redirect
					if(isset($key) && isset($plugins[$key])){
						$source = $plugins[$key]['Name'];
					}
					// If something goes wrong, just indicate that it's a plugin that redirected
					else{
						$source = 'Plugin';
					}
					break;
				}
				// The active theme (or child theme) caused the redirect
				elseif(strpos($file, WP_CONTENT_DIR . '/themes') !== false){
					$source = 'Theme';
					break;
				}
			}

			// If no custom source was set, default to WordPress
			if(!isset($source) || preg_match('#[^0-9a-zA-Z\s\-\_\./\'\(\):!]#', $source) == 1){
				$source = 'WordPress';
			}

			return $source;
		}, 10, 1);

		// Disable page caching for most caching plugins
		if(!defined('DONOTCACHEPAGE')){
			define('DONOTCACHEPAGE', true);
		}

		// Disable page caching in WP Rocket
		add_filter('do_rocket_generate_caching_files', '__return_false');

		// Disable WP Rocket preloads entirely (skip sitemap, preload order, and entire preload list)
		add_filter('rocket_sitemap_preload_list', '__return_empty_array');
		add_filter('rocket_preload_order', '__return_empty_array');
		add_filter('rocket_preload_exclude_urls', function($excluded_urls){
			return [ '*' ];
		});

		// Disable page caching in WP Super Cache
		add_filter('do_createsupercache', '__return_false');

		// Disable page caching in Cache Enabler
		add_filter('cache_enabler_bypass_cache', '__return_true');

		// Block all pingbacks
		if($this->settings['pingbacks/block'] === true){
			add_action('pre_ping', function(&$links){
				$links = array();
			}, 10, 1);
		}
		// Otherwise just block self pingbacks
		else{
			add_action('pre_ping', function(&$links){
				foreach($links as $index => $link){
					$home = get_option('home');
					if(strpos($link, $home) === 0){
						unset($links[$index]);
					}
				}
			}, 10, 1);
		}

		// Block trackbacks
		if($this->settings['trackbacks/block'] === true){
			if($_SERVER['SCRIPT_NAME'] == '/wp-trackback.php' && $_SERVER['REQUEST_METHOD'] == 'POST'){
				status_header(403);
				die('These are not the URLs you\'re looking for!');
			}
		}

		// Block comments
		if($this->settings['comments/block'] === true){
			if($_SERVER['SCRIPT_NAME'] == '/wp-comments-post.php' && $_SERVER['REQUEST_METHOD'] == 'POST'){
				status_header(403);
				die('These are not the URLs you\'re looking for!');
			}
		}

		// Block bots from engaging in unapproved activities
		if(!empty(str_replace('-','',$_SERVER['BOT']))){
			$bots = new Bots();

			// Block bot logins
			$this->loader->add_filter('wp_authenticate_user', $bots, 'handle_login', 10, 2);

			// Block bot password resets
			$this->loader->add_filter('allow_password_reset', $bots, 'handle_reset', 10, 2);

			if($this->settings['bot/register'] == true){
				$this->loader->add_filter('registration_errors', $bots, 'handle_registration', 10, 3);
			}

			if($this->settings['bot/comments'] == true){
				$this->loader->add_filter('preprocess_comment', $bots, 'handle_comments', 10, 3);
			}

			if($this->settings['bot/forms'] == true){
				// Contact Form 7 submissions
				$this->loader->add_filter('wpcf7_before_send_mail', $bots, 'handle_cf7', 10, 3);

				// WPForms submissions
				$this->loader->add_filter('wpforms_process_filter', $bots, 'handle_wpforms', 10, 3);

				// Gravity Forms submissions
				$this->loader->add_filter('gform_validation', $bots, 'handle_gravity_forms', 10, 1);

				// Formidable Forms submissions
				$this->loader->add_filter('frm_validate_field_entry', $bots, 'handle_formidable_forms', 10, 4);

				// Forminator Forms submissions
				$this->loader->add_filter('forminator_custom_form_submit_errors', $bots, 'handle_forminator_forms', 10, 3);

				// Ninja Forms submissions
				$this->loader->add_filter('ninja_forms_submit_data', $bots, 'handle_ninja_forms', 10, 1);

				// Fluent Forms submissions
				$this->loader->add_filter('fluentform/validation_errors', $bots, 'handle_fluent_forms', 10, 3);

				// MailChimp4WP submissions
				$this->loader->add_filter('mc4wp_form_errors', $bots, 'handle_mailchimp', 10, 2);

				// bbPress new topic submissions
				$this->loader->add_filter('bbp_new_topic_pre_extras', $bots, 'handle_bbpress_forum', 10, 1);

				// bbPress new reply submissions
				$this->loader->add_filter('bbp_new_reply_pre_extras', $bots, 'handle_bbpress_forum', 10, 1);

				// ElementorPro form submissions
				$this->loader->add_filter('elementor_pro/forms/validation', $bots, 'handle_elementor_pro_form', 10, 2);
			}
		}

		// Register country restrictions
		$countries = null;
		if($this->settings['login/restrict'] == true){
			if(is_null($countries)){
				$countries = new Countries();
			}

			$this->loader->add_filter('wp_authenticate_user', $countries, 'handle_login', 10, 2);
		}

		if ($this->settings['reset/restrict'] == true){
			if(is_null($countries)){
				$countries = new Countries();
			}

			$this->loader->add_filter('allow_password_reset', $countries, 'handle_reset', 10, 2);
		}

		if($this->settings['register/restrict'] == true){
			if(is_null($countries)){
				$countries = new Countries();
			}

			$this->loader->add_filter('registration_errors', $countries, 'handle_registration', 10, 3);
		}

		if($this->settings['comment/restrict'] == true){
			if(is_null($countries)){
				$countries = new Countries();
			}

			$this->loader->add_filter('preprocess_comment', $countries, 'handle_comments');
		}

		if($this->settings['forms/restrict'] == true){
			if(is_null($countries)){
				$countries = new Countries();
			}

			// Contact Form 7 submissions
			$this->loader->add_filter('wpcf7_before_send_mail', $countries, 'handle_cf7', 10, 3);

			// WPForms submissions
			$this->loader->add_filter('wpforms_process_filter', $countries, 'handle_wpforms', 10, 3);

			// Gravity Forms submissions
			$this->loader->add_filter('gform_validation', $countries, 'handle_gravity_forms', 10, 1);

			// Formidable Forms submissions
			$this->loader->add_filter('frm_validate_field_entry', $countries, 'handle_formidable_forms', 10, 4);

			// Forminator Forms submissions
			$this->loader->add_filter('forminator_custom_form_submit_errors', $countries, 'handle_forminator_forms', 10, 3);

			// Ninja Forms submissions
			$this->loader->add_filter('ninja_forms_submit_data', $countries, 'handle_ninja_forms', 10, 1);

			// Fluent Forms submissions
			$this->loader->add_filter('fluentform/validation_errors', $countries, 'handle_fluent_forms', 10, 3);

			// MailChimp4WP submissions
			$this->loader->add_filter('mc4wp_form_errors', $countries, 'handle_mailchimp', 10, 2);

			// bbPress new topic submissions
			$this->loader->add_filter('bbp_new_topic_pre_extras', $countries, 'handle_bbpress_forum', 10, 1);

			// bbPress new reply submissions
			$this->loader->add_filter('bbp_new_reply_pre_extras', $countries, 'handle_bbpress_forum', 10, 1);

			// ElementorPro form submissions
			$this->loader->add_filter('elementor_pro/forms/validation', $countries, 'handle_elementor_pro_form', 10, 2);
		}
	}


	/**
	 * Register all of the purge hooks
	 *
	 * @access		private
	 */
	private function define_purge_hooks(){
		$purge = new Purge();

		// Cache purge on post comments
		// Only purges the cache if the user is logged in or the comment is approved
		$this->loader->add_action('comment_post', $purge, 'purge_comment', 10, 2);

		// Submit the final purge request to the purger before the PHP process wraps
		$this->loader->add_action('shutdown', $purge, 'purge', 10);

		// Purges tied to admin changes
		$this->loader->add_action('admin_init', $purge, 'handle_bulk_operations');
		$this->loader->add_action('publish_future_post', $purge, 'queue_purge_post');
		$this->loader->add_action('transition_post_status', $purge, 'handle_status_transition', 10, 3);
		$this->loader->add_action('customize_save_after', $purge, 'purge_theme');
		$this->loader->add_action('switch_theme', $purge, 'purge_theme');

		// Add theme/plugin specific hooks
		$this->loader->add_action('fl_builder_cache_cleared', $purge, 'purge_cache');
		$this->loader->add_action('fl_builder_after_save_layout', $purge, 'purge_cache');
		$this->loader->add_action('fl_builder_after_save_user_template', $purge, 'purge_cache');

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

		add_action('elementor/core/files/clear_cache', function(){
			Purge::purge_cache();
		});

		// Get a list of all active plugins (both single and multisite)
		$plugins = get_option('active_plugins');
		$plugins = (is_array($plugins)) ? $plugins : array();
		if(defined('MULTISITE') && MULTISITE === true){
			$networkplugins = get_site_option('active_sitewide_plugins', array());
			$networkplugins = (is_array($networkplugins)) ? array_keys($networkplugins) : array();
			$plugins = array_merge($plugins, $networkplugins);
		}

		// For WooCommerce sites, purge the cache for product pages if stock changes
		if(in_array('woocommerce/woocommerce.php', $plugins) && 'yes' === get_option('woocommerce_manage_stock')){
			$this->loader->add_action('woocommerce_updated_product_stock', $purge, 'handle_stock_update');
		}

		// For wpDicuz sites, apply filter to fix issues created by wpDiscuz's lack of understanding of nonces and how to use them
		if(in_array('wpdiscuz/class.WpdiscuzCore.php', $plugins) && !has_filter('wpdiscuz_validate_nonce_for_guests')){
			add_filter('wpdiscuz_validate_nonce_for_guests', '__return_false');
		}

		// Get current environment context
		$isajax = ((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined('DOING_AJAX') && DOING_AJAX)) ? true : false;
		$iscron = (function_exists('wp_doing_cron') && wp_doing_cron()) ? true : false;
		$iscli = ((defined('WP_CLI') && \WP_CLI)) ? true : false;
		$isrest = (strpos($_SERVER['REQUEST_URI'], '/wp-json') === 0 || !empty($_GET['rest_route '])) ? true : false;

		// Context-specific purge hooks
		// Load Woo stock status change purge hooks
		if(!is_admin() || $isajax || $iscron || $iscli){
			$this->loader->add_action('woocommerce_variation_set_stock_status', $purge, 'purge_post', 10, 1);
			$this->loader->add_action('woocommerce_product_set_stock_status', $purge, 'purge_post', 10, 1);
		}

		// User is in the admin or using AJAX, register associated hooks
		if(is_admin() || $isajax || $iscron || $iscli || $isrest){
			// Post changes, hooks pass ID of post
			$this->loader->add_action('save_post', $purge, 'queue_purge_post');
			$this->loader->add_action('woocommerce_update_product_variation', $purge, 'purge_post');
			$this->loader->add_action('pre_post_update', $purge, 'purge_post');
			$this->loader->add_action('wp_trash_post', $purge, 'purge_post', 0);

			// Comment changes, hooks pass ID of comment
			$this->loader->add_action('transition_comment_status', $purge, 'purge_comment_transition', 10, 3);
			$this->loader->add_action('edit_comment', $purge, 'purge_comment', 10, 1);
			$this->loader->add_action('untrashed_comment', $purge, 'purge_comment', 10, 1);
			$this->loader->add_action('delete_comment', $purge, 'purge_comment', 10, 1);

			// Term/taxonomy changes, hooks pass ID of terms
			$this->loader->add_action('edit_terms', $purge, 'handle_term_change', 10, 2);
			$this->loader->add_action('delete_term', $purge, 'handle_term_change', 10, 5);
		}
	}


	/**
	 * Register all of the univeral hooks
	 *
	 * @access		private
	 */
	private function define_universal_hooks(){

		// Limit heartbeat
		$heartbeat = new Heartbeat();
		$this->loader->add_action('admin_enqueue_scripts', $heartbeat, 'limit', 100);
		$this->loader->add_action('wp_enqueue_scripts', $heartbeat, 'limit', 100);
		$this->loader->add_filter('heartbeat_settings', $heartbeat, 'frequency');

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
				$atts['headers']['X-Originating-IP'] = "X-Originating-IP: " . preg_replace('#[^0-9a-f\.:,]#', '', $_SERVER['REMOTE_ADDR']);
				$atts['headers']['X-Originating-URL'] = "X-Originating-URL: " . $url;
			}
			else{
				$atts['headers'] .= "X-Originating-IP: " . preg_replace('#[^0-9a-f\.:,]#', '', $_SERVER['REMOTE_ADDR']) . "\n";
				$atts['headers'] .= "X-Originating-URL: " . $url . "\n";
			}

			return $atts;
		}, 10, 1);

		// Single Language Mode control
		if(isset($this->settings['wp/translation']) && $this->settings['wp/translation'] === true){
			// Disable language filters since this is a single language site
			add_filter('pre_load_textdomain', '__return_false', -100);
			add_filter('pre_load_script_translations', '__return_false', -100);
		}

		// File edit control
		if(isset($this->settings['wp/editor']) && $this->settings['wp/editor'] === false){
			if(!defined('DISALLOW_FILE_EDIT')){
				define('DISALLOW_FILE_EDIT', true);
			}
		}
		elseif(isset($this->settings['wp/editor']) && $this->settings['wp/editor'] === true){
			if(!defined('DISALLOW_FILE_EDIT')){
				define('DISALLOW_FILE_EDIT', false);
			}
		}
		elseif(!isset($this->settings['wp/editor']) || !is_bool($this->settings['wp/editor'])){
			if(!defined('DISALLOW_FILE_EDIT')){
				define('DISALLOW_FILE_EDIT', true);
			}
		}

		// Disable web cron
		if(isset($this->settings['wp/cron/web']) && $this->settings['wp/cron/web'] === false){
			if(!defined('DISABLE_WP_CRON')){
				define('DISABLE_WP_CRON', true);
			}
		}
		elseif(isset($this->settings['wp/cron/web']) && $this->settings['wp/cron/web'] === true){
			if(!defined('DISABLE_WP_CRON')){
				define('DISABLE_WP_CRON', false);
			}
		}
		elseif(!isset($this->settings['wp/cron/web']) || !is_bool($this->settings['wp/cron/web'])){
			if(!defined('DISABLE_WP_CRON')){
				define('DISABLE_WP_CRON', false);
			}
		}

		// Force HTTPS
		if(isset($this->settings['https/force']) && $this->settings['https/force'] === true){
			if(!wp_doing_cron() && (!defined('WP_CLI') || WP_CLI === false)){
				if(false == isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'){
					add_action('init', function(){
						global $wp;
						if(strpos(home_url(), 'http://') === false){
							wp_redirect(home_url($wp->request) . add_query_arg($wp->query_vars), 301);
							exit;
						}
					}, 10);
				}
			}
		}

		// Image editor control
		// Since WP defaults to Imagick, we only need to set a different order if the preferred editor is GD
		if(isset($this->settings['wp/image/editor']) && $this->settings['wp/image/editor'] === 'gd'){
			add_filter('wp_image_editors', function(){
				return array('WP_Image_Editor_GD', 'WP_Image_Editor_Imagick');
			});
		}

		// Remove the URL field from the comment form if it exists
		// Block comments with URL submissions when URL field doesn't exist
		if(isset($this->settings['comments/url']) && $this->settings['comments/url'] === true){
			add_filter('comment_form_default_fields', function($fields){
				if(isset($fields['url'])){
					unset($fields['url']);
				}

				return $fields;
			}, 10, 1);

			add_filter('pre_comment_on_post', function($comment_post_id){
				if(strpos($_SERVER['SCRIPT_NAME'], '/wp-comments-post.php') !== false){
					if(isset($_POST['url']) && !empty($_POST['url'])){
						status_header(403);
						die('These are not the URLs you\'re looking for!');
					}
				}

				return $comment_post_id;
			}, 10, 1);
		}

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

		// Add mail filters if needed (require late instantiation)
		add_action('init', function(){
			$dynamic = Settings::get_instance()->get_dynamic();

			// Don't try to mess with mail from settings if another plugin already overrides delivery
			if(!$dynamic['mail/overridden']){
				// Override WP Mail FROM Name if its set
				if(isset($this->settings['mail/name']) && $this->settings['mail/name'] !== ''){
					add_filter('wp_mail_from_name', function($mail_from_name){
						return $this->settings['mail/name'];
					});
				}

				// Override WP Mail FROM Email if its set
				if(isset($this->settings['mail/email']) && $this->settings['mail/email'] !== ''){
					add_filter('wp_mail_from', function($mail_from_email){
						return $this->settings['mail/email'];
					});
				}
			}
		});

		// Override phpmailer FROM Email and FROM name if its set to be overridden
		if(
			((!empty($this->settings['mail/name'])) || (!empty($this->settings['mail/email']))) &&
			isset($this->settings['mail/force']) && $this->settings['mail/force'] != false
		){
			add_action('phpmailer_init', function($phpmailer){
				// make sure that the function is not overridden
				$reflector = new \ReflectionFunction('wp_mail');
				$origin = $reflector->getFileName();
				$core = ABSPATH . WPINC . '/pluggable.php';
				$overridden = ($origin !== $core);

				if($overridden){ // if its overridden, we dont do anything
					return $phpmailer;
				}

				$from_name = (!empty($this->settings['mail/name'])) ? $this->settings['mail/name'] : apply_filters('wp_mail_from_name', get_bloginfo('name'));
				$from_email = (!empty($this->settings['mail/email'])) ? $this->settings['mail/email'] : apply_filters('wp_mail_from', get_bloginfo('admin_email'));

				// Set your preferred From email and name
				$phpmailer->setFrom($from_email, $from_name, false);
				return $phpmailer;
			}, PHP_INT_MAX);
		}

		// Server mode-specific actions
		if(!empty($_SERVER['SERVER_MODE']) && $_SERVER['SERVER_MODE'] == '1'){
			// Flag authorized user
			add_filter('init', function(){
				if(is_user_logged_in()){
					// Make sure that get current user is defined
					if(!function_exists('wp_get_current_user')){
						require_once(\ABSPATH . '/wp-includes/pluggable.php');
					}

					// Log active user for malware/abuse detection
					$user = wp_get_current_user();
					header('X-Auth-User: ' . preg_replace('#[^0-9a-zA-Z\s%\.@]#', '', $user->user_login));
				}
			}, 10);

			// Post login followup
			add_filter('wp_login', function($user_login, $user){
				// If a previous/old wordkeeper cookie exists, remove it
				if(!empty($_COOKIE['wordkeeper'])){
					setcookie('wordkeeper', '', -1, '/');
				}
			}, 10, 2);

			// Flag failed logins
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
		}

		// Reject changes to core WP options when the change wasn't submitted by an approved admin
		if((!defined('WP_CLI') || !\WP_CLI) && (!defined('DOING_CRON') || !\DOING_CRON)){
			add_filter('pre_update_option', function($new, $name, $old){
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
					// Load required capabilities
					$this->load_capabilities();
					return (current_user_can('manage_options')) ? $new : $old;
				}
				else{
					return $new;
				}
			}, PHP_INT_MAX, 3);

			// Protect important multisite options against unauthenticated changes
			$options = array(
				'siteurl',
				'admin_email',
				'admin_user_id',
				'upload_filetypes',
				'illegal_names',
				'allowedthemes',
				'active_sitewide_plugins',
				'site_admins',
				'registration',
				'registrationnotification',
				'add_new_users',
			);

			// Since multisite doesn't have a catch all pre-update filter, add the important option filters manually
			foreach($options as $option){
				add_filter('pre_update_site_option_' . $option, function($new, $old, $option, $network_id){
					if(!empty($old)){
						// Load required capabilities
						$this->load_capabilities();
						return (current_user_can('manage_network_options')) ? $new : $old;
					}
					else{
						return $new;
					}
				}, PHP_INT_MAX, 4);
			}

			// Only allow System option insertion/update if the value doesn't yet exist
			// Otherwise require manage_network_options to update multisite options
			add_filter('pre_update_site_option_wordkeeper/system', function($new, $old, $option, $network_id){
				if(!empty($old)){
					// Load required capabilities
					$this->load_capabilities();
					return (current_user_can('manage_network_options')) ? $new : $old;
				}
				else{
					return $new;
				}
			}, PHP_INT_MAX, 4);
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

					// Load required capabilities
					$this->load_capabilities();

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

		// If the site is a dev/staging site, disable problem features
		if(preg_replace('#(?:dev|staging)_#', '', ABSPATH) != ABSPATH || strpos(get_home_url(), 'wordkeeper.net') !== false){
			// Disable WooCommerce subscriptions to prevent duplicate transactions
			add_filter('woocommerce_subscriptions_is_duplicate_site', '__return_true');
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress
	 */
	public function run(){
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality
	 *
	 * @return		string			The name of the plugin
	 */
	public function get_plugin_name(){
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin
	 *
	 * @return		Loader			Orchestrates the hooks of the plugin
	 */
	public function get_loader(){
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin
	 *
	 * @return		string		The version number of the plugin
	 */
	public function get_version(){
		return $this->version;
	}

	/**
	 * Load capabilities libraries if they haven't been loaded
	 *
	 * @return void
	 */
	public function load_capabilities(){
		// Make sure cookie constants exist before pluggable functions that use them.
		if(!defined('SECURE_AUTH_COOKIE')){
			require_once ABSPATH . 'wp-includes/default-constants.php';
			if(function_exists('wp_cookie_constants')){
				wp_cookie_constants();
			}
		}

		// Load required user capability-checking libraries
		if(!function_exists('current_user_can')){
			require_once(\ABSPATH . '/wp-includes/user.php');
			require_once(\ABSPATH . '/wp-includes/pluggable.php');
			require_once(\ABSPATH . '/wp-includes/capabilities.php');
		}
		elseif(!function_exists('wp_get_current_user')){
			require_once(\ABSPATH . '/wp-includes/pluggable.php');
		}
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}