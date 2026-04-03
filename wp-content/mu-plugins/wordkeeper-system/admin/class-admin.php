<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use add_menu_page;
use array_key_exists;
use check_ajax_referer;
use current_user_can;
use filter_input_array;
use get_current_user_id;
use get_option;
use get_permalink;
use get_userdata;
use is_admin;
use is_user_logged_in;
use json_encode;
use plugin_dir_url;
use preg_replace;
use str_replace;
use strpos;
use update_option;
use wp_create_nonce;
use wp_die;
use wp_enqueue_script;
use wp_enqueue_style;


/**
 * The admin-specific functionality of the plugin.
 *
 */
class Admin {

	/**
	 * Plugin name
	 */
	private $plugin_name = 'wordkeeper-system';

	/**
	 * The settings of this plugin
	 *
	 * @access		private
	 * @var			array		$settings		The settings of this plugin
	 */
	private $settings;

	/**
	 * Plugins on the site
	 *
	 * @var array
	 */
	private $plugins = array();

	/**
	 * The feature limits tied to the site's plan
	 *
	 * @var array
	 */
	private $limits = array();

	/**
	 * Initialize the class and set its properties
	 */
	public function __construct(){
		Settings::get_instance();
		$this->settings = Settings::get_instance()->get_settings();
		$this->limits = Limits::get_instance()->get();
		add_filter('current_screen', function($screen){
			if($screen->id == 'plugin-install'){
				$path = __DIR__ . '/../includes/banned.php';
				if(file_exists($path)){
					require_once $path;
					$this->plugins = $bans;
				}

				if(count($this->plugins) > 0){
					wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/plugins.min.js', array('jquery'), '', false);
					wp_localize_script($this->plugin_name, 'banned', array('list' => $this->plugins));
				}
			}
		}, 10 , 1);
	}


	/**
	 * Register the stylesheets for the admin area
	 */
	public function enqueue_styles(){
		$screen = get_current_screen();
		if(strpos($screen->id, 'wordkeeper-system') !== false){
			wp_enqueue_style('choice-system-css', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css', array(), '', 'all');
			wp_enqueue_style('animate-system-css', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '', 'all');
			wp_add_inline_style('animate-system-css', '.animate__animated.animate__zoomIn, .animate__animated.animate__fadeOut{
				--animate-duration: 0.1s;
				--animate-delay: 0;
			}');
			wp_enqueue_style('sweetalert2-system', plugin_dir_url(__FILE__) . 'css/sweetalert2.min.css', array(), '11.26.3', 'all');
		}

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/admin.min.css', array(), WORDKEEPER_SYSTEM_VERSION, 'all');
	}

	/**
	 * Register the Global stylesheets for the admin area
	 */
	public function enqueue_global_styles(){
		wp_enqueue_style('wordkeeper-system-global', plugin_dir_url( __FILE__ ) . 'css/admin-menu.min.css', array(), WORDKEEPER_SYSTEM_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area
	 */
	public function enqueue_scripts(){
		$screen = get_current_screen();
		if(strpos($screen->id, 'wordkeeper-system') !== false){
			wp_enqueue_script('sweetalert2-system', plugin_dir_url(__FILE__) . 'js/sweetalert2.all.min.js', array(), '11.26.3');
			wp_enqueue_script('choices-system', 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js', array(), false);
			wp_enqueue_script('popper-system', 'https://unpkg.com/@popperjs/core@2', array(), false);
			wp_enqueue_script('tippy-system', 'https://unpkg.com/tippy.js@6', array(), false);
			wp_enqueue_script('wp-api-request');
		}

		wp_enqueue_script('wordkeeper-system-admin', plugin_dir_url(__FILE__) . 'js/admin.min.js', array('wp-api'), WORDKEEPER_SYSTEM_VERSION, false);
		wp_enqueue_script('wordkeeper-system-lib', plugin_dir_url(__FILE__) . 'js/wordkeeper.min.js', array('wordkeeper-system-admin'), WORDKEEPER_SYSTEM_VERSION, false);

		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$screen = str_replace('wordkeeper_page_wordkeeper-system-', '', $screen->id);
		if($multisite){
			$screen = str_replace('-network', '', $screen);
		}
		if(file_exists(plugin_dir_path(__FILE__) . 'js/pages/' . $screen . '.min.js')){
			wp_enqueue_script('wordkeeper-system-' . $screen, plugin_dir_url(__FILE__) . 'js/pages/' . $screen . '.min.js', array(), '', false);
		}
	}

	/**
	 * Dequeue conflicting assets from poorly coded plugins that encroach on the admin screens of other plugins
	 *
	 * @return void
	 */
	public function dequeue_conflicts(){
		$screen = get_current_screen();
		if(strpos($screen->id, 'wordkeeper-system') !== false){
			// FAQ Schema For Pages And Posts
			wp_dequeue_script('sweetalert2');
			wp_dequeue_script('wp-faq-schema-scripts');
		}
	}


	/**
	 * Add admin menu
	 */
	public function add_menu(){
		$data = get_userdata(get_current_user_id());

		// Is this a multisite?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$adminmenu = ($multisite) ? 'manage_network_options' : 'manage_options';
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		add_menu_page(
			__('WordKeeper Settings', 'wordkeeper-system'),
			__('WordKeeper', 'wordkeeper-system'),
			'publish_pages',
			$this->plugin_name,
			array($this, 'display_general'),
			plugin_dir_url(dirname(__FILE__)) . 'admin/images/logo.svg',
			3
		);

		add_submenu_page($this->plugin_name, 'General', 'General', 'publish_pages', $this->plugin_name.'-general', array($this, 'display_general'));

		// Only show in network admin on multisite.  Otherwise show for admins
		if($admin){
			add_submenu_page($this->plugin_name, 'Hosting', 'Hosting', 'manage_options', $this->plugin_name.'-hosting', array($this, 'display_hosting'));
		}

		add_submenu_page($this->plugin_name, 'Backups', 'Backups', 'manage_options', $this->plugin_name.'-backups', array($this, 'display_backups'));

		// Only show in network admin on multisite.  Otherwise show for admins
		if($admin){
			add_submenu_page($this->plugin_name, 'Speed', 'Speed', 'manage_options', $this->plugin_name.'-speed', array($this, 'display_speed'));
			add_submenu_page($this->plugin_name, 'Security', 'Security', 'manage_options', $this->plugin_name.'-security', array($this, 'display_security'));
			add_submenu_page($this->plugin_name, 'Deliverability', 'Deliverability', 'manage_options', $this->plugin_name.'-deliverability', array($this, 'display_deliverability'));
			add_submenu_page($this->plugin_name, 'Images', 'Images', $adminmenu, $this->plugin_name.'-images', array($this, 'display_images'));
			add_submenu_page($this->plugin_name, 'Bots', 'Bots', 'manage_options', $this->plugin_name.'-bots', array($this, 'display_bots'));
		}

		add_submenu_page($this->plugin_name, 'Database', 'Database', 'manage_options', $this->plugin_name.'-database', array($this, 'display_database'));
		add_submenu_page($this->plugin_name, 'Video Library', 'Video Library', 'publish_pages', $this->plugin_name.'-video-library', array($this, 'display_videos'));
		//add_submenu_page($this->plugin_name, 'Redirects', 'Redirects', 'manage_options', $this->plugin_name.'-redirects', array($this, 'display_redirects'));
		add_submenu_page($this->plugin_name, 'Status', 'Status', 'manage_options', $this->plugin_name.'-status', array($this, 'display_status'));
		//add_submenu_page($this->plugin_name, 'Announcements', 'Announcements', 'publish_pages', $this->plugin_name.'-announcements', array($this, 'display_announcements'));

		remove_submenu_page($this->plugin_name, $this->plugin_name);
	}


	/**
	 * Display dashboard page
	 *
	 * @return void
	 */
	public function display_general(){
		// Verify user is allowed to view this page
		if(!current_user_can('publish_pages')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Determine whether both live and staging accounts exist
		// Only support valid usernames
		if(preg_match('#(?:staging[0-9]_|dev[0-9]?_)#', ABSPATH) === 0){
			if(file_exists(dirname(ABSPATH) . '/account')){
				$account = file_get_contents(dirname(ABSPATH) . '/account');
				$account = json_decode($account, true);

				// Get live/staging account existence
				if(!empty($account)){
					$mode = $account['mode'];
					$live = (bool) $account['live'];
					$staging = (bool) $account['staging'];
				}
				// Use defaults
				else{
					$mode = (preg_match('#(?:staging[0-9]?_|dev[0-9]?_)#', ABSPATH) !== 1) ? 'staging' : 'live';
					$live = true;
					$staging = true;
				}
			}
			// Use defaults
			else{
				$mode = (preg_match('#(?:staging[0-9]?_|dev[0-9]?_)#', ABSPATH) !== 1) ? 'staging' : 'live';
				$live = true;
				$staging = true;
			}

			// Are various sync types possible
			if($live === false || $staging === false){
				$syncstaging = $live;
				$synclive = $staging;
			}
			else{
				$syncstaging = true;
				$synclive = true;
			}
		}
		else{
			$syncstaging = false;
			$synclive = false;
		}

		// Is a sync domain required
		$syncdomain = ($mode === 'staging' && $live === false);

		// Get account size if available
		$sizes = (file_exists(dirname(ABSPATH) . '/sizes')) ? file_get_contents(dirname(ABSPATH) . '/sizes') : array();
		$sizes = (!empty($sizes)) ? json_decode($sizes, true) : array();

		// Format sizes into human readable units
		foreach($sizes as $type => $size){
			$bytes = $size;
			$units = array('KB', 'MB', 'GB');
			$power = floor(($bytes ? log($bytes) : 0) / log(1024));
			$power = min($power, count($units) - 1);
			$bytes = $bytes / pow(1024, $power);
			$sizes[$type] = round($bytes, 2) . ' ' . $units[$power];
		}

		// Get domain Info if available
		$domain = (file_exists(dirname(ABSPATH) . '/domain')) ? file_get_contents(dirname(ABSPATH) . '/domain') : array();
		$domain = (!empty($domain)) ? json_decode($domain, true) : $domain;
		$allns = '';

		if(!empty($domain['domains'])){
			$site_url = preg_replace('#https?://(?:www\.)?#', '', site_url());
			$domain = $domain['domains'][$site_url];
		}

		if(!empty($domain['ns'])){
			foreach($domain['ns'] as $ns){
				$allns .= $ns . PHP_EOL;
			}
		}

		// format Date using Native WP date function
		if(!empty($domain['expiry'])){
			$expiry = null;
			$expiry_diff = null;

			if(!empty($domain['expiry'])) {
				$tz = wp_timezone();
				$expiry_datetime = new \DateTime();
				$expiry_datetime->setTimestamp($domain['expiry']);
				$expiry_datetime->setTimezone($tz);
				$expiry = $expiry_datetime->format('m/d/Y h:ia');
				$today = new \DateTime();
				$today->setTimezone($tz);
				$expiry_diff = $today->diff($expiry_datetime);
			}
		}

		$user = wp_get_current_user();
		require_once dirname(__FILE__) . '/partials/general.php';
	}


	/**
	 * Display hosting page/tabs
	 *
	 * @return void
	 */
	public function display_hosting(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Set types of permissions for different user types
		$permissions  = array(
			'default' => (current_user_can('manage_options') || current_user_can('manage_network_options')),
			'multisite' => $admin
		);

		// Set the tabs and associated permissions
		$tabs = array(
			'logs' => array('title' => __('Logs', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
			'caching' => array('title' => __('Caching', 'wordkeeper-system'), 'permission' => $permissions['default']),
			'php-settings' => array('title' => __('PHP Settings', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
			'wp-cron' => array('title' => __('WP Cron', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
			//'update-management' => array('title' => __('Update Management', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
		);

		// Set default and current tab
		$default_tab = ($tabs['logs']['permission']) ? 'logs' : 'caching';
		$current_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

		// Deny access to tabs that the user is not permitted to access
		if(in_array($current_tab, array_keys($tabs)) && !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		// Load appropriate view data for selected tab
		switch($current_tab){
			// Caching tab
			case 'caching':
				// Get a list of public post types
				//$types = get_post_types(array('show_ui' => true, 'show_in_nav_menus' => true), 'objects');

				$times = array(
					'default' => 'Automatic',
					'10800' => '3 hours',
					'43200' => '12 hours',
					'86400' => '1 day',
					'2592000' => '1 month'
				);
				break;
			// Logs tab
			case 'logs':
				$tz = wp_timezone();
				$current = dirname(__FILE__);
				$parts = explode('/', trim($current, '/'));
				$home = '/' . $parts[0] . '/' . $parts[1];
				$files = scandir($home . '/logs');
				$files = array_diff($files, ['.', '..']);
				$debugs = scandir(WP_CONTENT_DIR);
				$today = date('Ymd');
				$logs = array(
					'access' => array(),
					'error' => array(),
					'optimize-images' => array(),
					'wp-cron' => array(),
					'phpslow' => array(),
					'debug' => array(),
				);

				$log_keys = array('access.log', 'error.log', 'optimize-images.log', 'wp-cron.log' ,'phpslow.log', 'debug.log');

				uasort($files, function ($a, $b) use ($log_keys) {
					// If the first value is one of the main logs, it's first
					if(in_array($a, $log_keys)){
						return PHP_INT_MIN;
					}

					// If the second value is one of the main logs, it's first
					if(in_array($b, $log_keys)){
						return PHP_INT_MAX;
					}

					if(preg_match('#[0-9]{8,10}#', $a) === 1 && preg_match('#[0-9]{8,10}#', $b) === 1){
						preg_match('#[0-9]{8,10}#', $a, $date_one);
						preg_match('#[0-9]{8,10}#', $b, $date_two);

						return $date_two[0] - $date_one[0];
					}
					elseif(preg_match('#[0-9]{8,10}#', $a) === 0 && preg_match('#[0-9]{8,10}#', $b) === 1){
						return 1;
					}
					elseif(preg_match('#[0-9]{8,10}#', $a) === 1 && preg_match('#[0-9]{8,10}#', $b) === 0){
						return -1;
					}
					else{
						return 0;
					}
				});

				foreach($files as $file){
					$file_clean = str_replace(array('wp-cron', 'optimize-images'), '', $file);
					if(strpos($file_clean, '-') !== false){
						preg_match('#[0-9]{8,10}#', $file, $date);
						$index = (!empty($date) && !empty($date[0])) ? strtotime(substr($date[0],0,8)) : false;

						$date = new \DateTime();
						$date->setTimestamp($index);
						$date->setTimezone($tz);
						$name = $date->format('m/d/Y');
					}
					elseif(preg_match('#\.[0-9]\.#', $file) === 1){
						$name = 'Earlier Today';
						$index = strtotime('today');
					}
					else{
						$name = 'Today';
						$index = strtotime('today');
					}

					$parts = explode('.', $file);
					switch($parts[0]){
						case 'access':
						case 'error':
						case 'optimize-images':
						case 'wp-cron':
						case 'phpslow':
							if($index !== false){
								// For files with the same date, set rotated index higher and current log file lower
								// Prevents overwriting today's log
								if(isset($logs[$parts[0]][$index])){
									if(strpos($file, '.gz') !== false){
										if(preg_match('#\.(log-)?([0-9])*\.#', $file, $match) === 1){
											$num = str_replace('.log-', '', $match[0]);
											$num = str_replace('.', '', $num);
											$decrement = (int) $num;
											$index = $index - $decrement;
										}
										else{
											$index++;
										}
									}
									else{
										$index--;
									}
								}

								// Save file name and order
								$logs[$parts[0]][$index] = array('name' => $name, 'file' => $file);
							}
							break;
						default:
							break;
					}
				}

				foreach($debugs as $file){
					if(strpos($file, 'debug.log') !== false){
						if($file == 'debug.log'){
							$name = 'Today';
							$index = strtotime('today');
						}
						else{
							preg_match('#[0-9]{8}#', $file, $date);
							$index = (!empty($date) && !empty($date[0])) ? strtotime($date[0]) : false;
							// we only want to get archived log files till yesterday
							if(!empty($date) && !empty($date[0]) && $date[0] == $today){ continue; }

							$date = new \DateTime();
							$date->setTimestamp($index);
							$date->setTimezone($tz);
							$name = $date->format('m/d/Y');
						}

						// Save file name and order
						if($index !== false){
							$logs['debug'][$index] = array('name' => $name, 'file' => $file);
						}
					}
				}

				break;
			// PHP Settings tab
			case 'php-settings':
				$email = (file_exists(dirname(ABSPATH) . '/mail')) ? file_get_contents(dirname(ABSPATH) . '/mail') : false;
				$email = ($email == 'true') ? true : false;
				$versions = array();
				if(preg_match('#el(?:9|10)#', php_uname()) == 1){
					$versions = array(
						'8.5',
						'8.4',
						'8.3',
						'8.2',
						'8.1',
						'8.0',
						'7.4',
					);
				}
				break;
			// Update management tab
			case 'update-management':
				$plugins = get_plugins();
				$settings = Settings::get_instance()->get_settings();
				$exclude_plugin_list = explode(',', $settings['wp/updates/exclusions/list']);
				break;
			// WP cron tab
			case 'wp-cron':

				$options = array(
					'wp/cron' => array(
						900 => '15 minutes',
						1800 => '30 minutes',
						3600 => '1 hour'
					),
					'wp/cron/web' => array(
						true => 'Enabled',
						false => 'Disabled'
					)
				);
				break;
			default:
				break;
		}

		$limits = $this->limits;
		$settings = $this->settings;
		require_once 'partials/hosting.php';
	}


	/**
	 * Display backups page
	 *
	 * @return void
	 */
	public function display_backups(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		$user = wp_get_current_user();
		$current = dirname(__FILE__);
		$parts = explode('/', trim($current, '/'));
		$backups_path = '/' . $parts[0] . '/' . $parts[1] . '/backups';
		$backups = file_exists($backups_path) ? file_get_contents($backups_path) : array();
		$tz = wp_timezone();

		if(!empty($backups)) {
			$backups = array_reverse(json_decode($backups, true));
		}
		else {
			$backups = array();
		}

		$total_backups = count($backups);
		$per_page = 30;
		$pages = ceil($total_backups / $per_page);
		$curr_page = $_GET['page_num'] ?? 1;
		$start = ($curr_page - 1) * $per_page;
		$backups = array_slice($backups, $start, $per_page);

		// Set the date/time based on the current WP timezone
		foreach($backups as $index => $backup){
			$timestamp = strtotime($backup['timestamp']);
			$datetime = new \DateTime($backup['timestamp']);
			$datetime->setTimezone($tz);
			$offset = $datetime->getOffset();
			$backups[$index]['date'] = $datetime->format('m/d/Y');
			$backups[$index]['time'] = $datetime->format('h:ia');
			$backups[$index]['offset'] = $offset;
		}

		require_once 'partials/backups.php';
	}


	/**
	 * Display speed page
	 *
	 * @return void
	 */
	public function display_speed(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		$options = array(
			'wp/heartbeat/frequency' => array(
				'default' => 'WordPress Default',
				'30' => 30,
				'60' => 60,
				'300' => 300
			),
			'wp/heartbeat/limits' => array(
				'default' => 'WordPress Default',
				'off' => 'Disable Completely',
				'dashboard' => 'Disable on Dashboard',
				'post-edit' => 'Allow on Edit Pages'
			),
			'wp/image/editor' => array(
				'imagick' => 'Imagick',
				'gd' => 'GD'
			),
			'wp/translation' => array(
				true => 'Enabled',
				false => 'Disabled'
			)
		);

		$settings = $this->settings;
		require_once 'partials/speed.php';
	}


	/**
	 * Display security page/tabs
	 *
	 * @return void
	 */
	public function display_security(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Set types of permissions for different user types
		$permissions  = array(
			'default' => (current_user_can('manage_options') || current_user_can('manage_network_options')),
			'multisite' => ($multisite && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options'))
		);

		// Set the tabs and associated permissions
		$tabs = array(
			'ssl-settings' => array('title' => __('SSL Settings', 'wordkeeper-system'), 'permission' => $permissions['default']),
			'login-settings' => array('title' => __('Login Settings', 'wordkeeper-system'), 'permission' => $permissions['default']),
			// 'comment-settings' => array('title' => __('Comment Settings', 'wordkeeper-system'), 'permission' => $permissions['default']),
			//'form-settings' => array('title' => __('Form Settings', 'wordkeeper-system'), 'permission' => $permissions['default']),
			//'firewall-settings' => array('title' => __('Firewall Settings', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
			'spam-blocking' => array('title' => __('Spam Blocking', 'wordkeeper-system'), 'permission' => $permissions['multisite']),
			'miscellaneous' => array('title' => __('Miscellaneous', 'wordkeeper-system'), 'permission' => $permissions['default']),
		);

		// Set default tab and current tab
		$default_tab = 'ssl-settings';
		$current_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

		// If the HOME URL is using HTTP instead of HTTPS, we dont want to show SSL settings tab at all
		if(strpos(home_url(), 'http://') !== false){
			unset($tabs['ssl-settings']);
			$default_tab = current(array_keys($tabs));
		}

		// Deny access to unsupported tabs
		if(!in_array($current_tab, array_keys($tabs)) || !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page...'), 403);
		}

		// Deny access to tabs that the user is not permitted to access
		if(in_array($current_tab, array_keys($tabs)) && !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		$settings = $this->settings;

		if($current_tab == 'login-settings'){
			$countries = Settings::get_instance()->get_countries();
			$settings['login/protect'] = isset($settings['login/protect']) ? $settings['login/protect'] : false;
			$settings['login/whitelist'] = isset($settings['login/whitelist']) ? $settings['login/whitelist'] : array();
			$settings['login/restrict'] = isset($settings['login/restrict']) ? $settings['login/restrict'] : false;
			$settings['reset/whitelist'] = isset($settings['reset/whitelist']) ? $settings['reset/whitelist'] : array();
			$settings['reset/restrict'] = isset($settings['reset/restrict']) ? $settings['reset/restrict'] : false;
			$settings['register/whitelist'] = isset($settings['register/whitelist']) ? $settings['register/whitelist'] : array();
			$settings['register/restrict'] = isset($settings['register/restrict']) ? $settings['register/restrict'] : false;

			$selected_countries = array();
			if(isset($settings['login/protect']) && !empty($settings['login/protect'])){
				$merged = array();
				if($settings['login/restrict']){
					$merged = array_merge($merged, $settings['login/whitelist']);
				}

				if($settings['reset/restrict']){
					$merged = array_merge($merged, $settings['reset/whitelist']);
				}

				if($settings['register/restrict']){
					$merged = array_merge($merged, $settings['register/whitelist']);
				}

				$unique = array_unique($merged);
				$selected_countries = array_values($unique);
			}
		}
		elseif($current_tab == 'comment-settings'){
			$countries = Settings::get_instance()->get_countries();
			$settings['comment/whitelist'] = isset($settings['comment/whitelist']) ? $settings['comment/whitelist'] : array();
			$settings['comment/restrict'] = isset($settings['comment/restrict']) ? $settings['comment/restrict'] : false;
		}
		elseif($current_tab == 'form-settings'){
			$countries = Settings::get_instance()->get_countries();
			$settings['forms/whitelist'] = isset($settings['forms/whitelist']) ? $settings['forms/whitelist'] : array();
			$settings['forms/restrict'] = isset($settings['forms/restrict']) ? $settings['forms/restrict'] : false;
		}
		elseif($current_tab == 'spam-blocking'){
			$countries = Settings::get_instance()->get_countries();
			$setting['bot/restrict'] = isset($settings['bot/restrict']) ? $settings['bot/restrict'] : false;
			$setting['bot/register'] = isset($settings['bot/register']) ? $settings['bot/register'] : false;
			$setting['bot/comments'] = isset($settings['bot/comments']) ? $settings['bot/comments'] : false;
			$setting['bot/forms'] = isset($settings['bot/forms']) ? $settings['bot/forms'] : false;

			$settings['comment/whitelist'] = isset($settings['comment/whitelist']) ? $settings['comment/whitelist'] : array();
			$settings['comment/restrict'] = isset($settings['comment/restrict']) ? $settings['comment/restrict'] : false;
			$settings['forms/whitelist'] = isset($settings['forms/whitelist']) ? $settings['forms/whitelist'] : array();
			$settings['forms/restrict'] = isset($settings['forms/restrict']) ? $settings['forms/restrict'] : false;

			$selected_countries = array();
			if(isset($settings['geo/restrict']) && !empty($settings['geo/restrict'])){
				$merged = array();
				if($settings['comment/restrict']){
					$merged = array_merge($merged, $settings['comment/whitelist']);
				}

				if($settings['forms/restrict']){
					$merged = array_merge($merged, $settings['forms/whitelist']);
				}

				$unique = array_unique($merged);
				$selected_countries = array_values($unique);
			}
		}

		require_once 'partials/security.php';
	}

	/**
	 * Display devliverability page
	 *
	 * @return void
	 */
	public function display_deliverability(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Set types of permissions for different user types
		$permissions  = array(
			'default' => (current_user_can('manage_options') || current_user_can('manage_network_options')),
			'multisite' => ($multisite && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options'))
		);

		// Set the tabs and associated permissions
		$tabs = array(
			'mail' => array('title' => __('Mail', 'wordkeeper-system'), 'permission' => $permissions['default']),
		);

		// Set default tab and current tab
		$default_tab = 'mail';
		$current_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

		// Deny access to unsupported tabs
		if(!in_array($current_tab, array_keys($tabs)) || !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page...'), 403);
		}

		// Deny access to tabs that the user is not permitted to access
		if(in_array($current_tab, array_keys($tabs)) && !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		$settings = $this->settings;

		$from_name  = apply_filters('wp_mail_from_name', get_bloginfo('name'));
		$from_email = apply_filters('wp_mail_from', get_bloginfo('admin_email'));
		$dynamic = Settings::get_instance()->get_dynamic();
		$overridden = $dynamic['mail/overridden'];

		require_once 'partials/deliverability.php';
	}

	/**
	 * Display images page
	 *
	 * @return void
	 */
	public function display_images(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		$settings = $this->settings;
		foreach($settings as $index => $setting){
			if((strpos($index, 'images/height/') === 0 || strpos($index, 'images/width/') === 0) && !empty($setting)){
				$settings[$index] = $setting . 'px';
			}
		}

		$limits = $this->limits;

		require_once 'partials/images.php';
	}


	/**
	 * Display bots page
	 *
	 * @return void
	 */
	public function display_bots(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		$settings = $this->settings;
		require_once 'partials/bots.php';
	}


	/**
	 * Display database page/tabs
	 *
	 * @return void
	 */
	public function display_database(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Set types of permissions for different user types
		$permissions  = array(
			'default' => (current_user_can('manage_options') || current_user_can('manage_network_options')),
			'multisite' => ($multisite && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')),
			'single' => (!$multisite && current_user_can('manage_options')),
			'subsite' => ($multisite && !$network && current_user_can('manage_options')),
			'network' => ($multisite && $network && current_user_can('manage_network_options')),
		);

		// Set the tabs and associated permissions
		$tabs = array(
			'one-time-cleaning' => array('title' => __('One Time Cleaning', 'wordkeeper-system'), 'permission' => ($permissions['single'] || $permissions['subsite'])),
			'automatic-cleaning' => array('title' => __('Automatic Cleaning', 'wordkeeper-system'), 'permission' => ($permissions['network'] || $permissions['single']))
		);

		// Set default and current tab
		$default_tab = ($permissions['single'] || $permissions['subsite']) ? 'one-time-cleaning' : 'automatic-cleaning';
		$current_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

		// Deny access to tabs that the user is not permitted to access
		if(in_array($current_tab, array_keys($tabs)) && !$tabs[$current_tab]['permission']){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Get site details if it's a multisite, default to null
		$site_id = $multisite ? get_blog_details()->site_id : null;
		$parts = parse_url($_SERVER['REQUEST_URI']);

		// Multisite
		if($multisite){
			$site_path = get_blog_details()->path;
		}
		// Non-multisite w/ subfolder
		elseif(strpos($parts['path'], '/wp-admin') !== 0){
			$site_path = str_replace('/wp-admin/admin.php', '', $parts['path']) . '/';
		}
		// Non-multisite
		else{
			$site_path = '/';
		}

		$settings = $this->settings;
		$counts = get_transient('wordkeeper/database/junk');

		if(false === $counts){
			global $wpdb;
			$counts = array();

			$sql = $wpdb->prepare("SELECT count(ID) as total FROM %i WHERE post_type = 'revision'", $wpdb->posts);
			$revision_count = $wpdb->get_var($sql);
			$counts['post/revisions'] = $revision_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(ID) as total FROM %i WHERE post_status = 'auto-draft'", $wpdb->posts);
			$autodraft_count = $wpdb->get_var($sql);
			$counts['post/autodrafts'] = $autodraft_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(ID) as total FROM %i WHERE post_status = 'trash'", $wpdb->posts);
			$trash_count = $wpdb->get_var($sql);
			$counts['post/trash'] = $trash_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(comment_id) as total FROM %i WHERE comment_approved = 'spam'", $wpdb->comments);
			$spam_count = $wpdb->get_var($sql);
			$counts['comment/spam'] = $spam_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(comment_id) as total FROM %i WHERE comment_approved = 'trash'", $wpdb->comments);
			$comment_trash = $wpdb->get_var($sql);
			$counts['comment/trash'] = $comment_trash ?? 0;

			$sql = $wpdb->prepare("SELECT count(comment_id) as total FROM %i WHERE comment_approved = 0", $wpdb->comments);
			$unapproved_comments_count = $wpdb->get_var($sql);
			$counts['comment/unapproved'] = $unapproved_comments_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(meta_id) as total FROM %i WHERE post_id NOT IN (SELECT ID FROM %i)", $wpdb->postmeta, $wpdb->posts);
			$post_orphan_count = $wpdb->get_var($sql);
			$counts['post/orphans'] = $post_orphan_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(meta_id) as total FROM %i WHERE comment_ID NOT IN (SELECT comment_ID FROM %i)", $wpdb->commentmeta, $wpdb->comments);
			$comment_orphan_count = $wpdb->get_var($sql);
			$counts['comment/orphans'] = $comment_orphan_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(umeta_id) as total FROM %i WHERE user_id NOT IN (SELECT ID FROM %i)", $wpdb->usermeta, $wpdb->users);
			$user_orphancount = $wpdb->get_var($sql);
			$counts['user/orphans'] = $user_orphancount ?? 0;

			$sql = $wpdb->prepare("SELECT count(meta_id) as total FROM %i WHERE term_id NOT IN (SELECT term_id FROM %i)", $wpdb->termmeta, $wpdb->terms);
			$taxonomy_orphancount = $wpdb->get_var($sql);
			$counts['taxonomy/orphans'] = $taxonomy_orphancount ?? 0;

			$sql = $wpdb->prepare("SELECT COUNT(a.option_id) FROM %i a, %i b WHERE a.option_name LIKE '%_transient_%' AND
				a.option_name NOT LIKE '%_transient_timeout_%' AND
				b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, CHAR_LENGTH('_transient_') + 1))
				AND b.option_value < UNIX_TIMESTAMP()", $wpdb->options, $wpdb->options
			);

			$transients_count = $wpdb->get_var($sql);
			$counts['transients'] = $transients_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(comment_id) FROM %i WHERE comment_type = 'pingback'", $wpdb->comments);
			$pingbacks_count = $wpdb->get_var($sql);
			$counts['pingbacks'] = $pingbacks_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(comment_id) FROM %i WHERE comment_type = 'trackback'", $wpdb->comments);
			$trackback_count = $wpdb->get_var($sql);
			$counts['trackbacks'] = $trackback_count ?? 0;

			$sql = $wpdb->prepare("SELECT count(meta_id) FROM %i WHERE meta_key LIKE '_oembed%' AND meta_value = '{{unknown}}'", $wpdb->postmeta);
			$oembed_count = $wpdb->get_var($sql);
			$counts['oembed'] = $oembed_count ?? 0;

			$total_log_count = 0;
			$old_date_gmt = gmdate('Y-m-d H:i:s', strtotime('-30 days'));
			$old_date = date('Y-m-d H:i:s', strtotime('-30 days'));

			$log_table_name = $wpdb->prefix . 'actionscheduler_logs';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(log_id) FROM ' . $log_table_name . " WHERE log_date_gmt < %s", $old_date_gmt);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$log_table_name = $wpdb->prefix . 'ualp_user_activity';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(uactid) FROM ' . $log_table_name . " WHERE modified_date < %s", $old_date_gmt);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$log_table_name = $wpdb->prefix . 'itsec_logs';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(ID) FROM ' . $log_table_name . " WHERE init_timestamp < %s", $old_date_gmt);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$log_table_name = $wpdb->prefix . 'stream';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(ID) FROM ' . $log_table_name . " WHERE created < %s", $old_date);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$log_table_name = $wpdb->prefix . 'redirection_logs';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(ID) FROM ' . $log_table_name . " WHERE created < %s", $old_date);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$log_table_name = $wpdb->prefix . 'redirection_404';
			if($wpdb->get_var("SHOW TABLES LIKE '$log_table_name'") == $log_table_name){
				$sql = $wpdb->prepare('SELECT COUNT(ID) FROM ' . $log_table_name . " WHERE created < %s", $old_date);
				$log_count = (int) $wpdb->get_var($sql);
				$total_log_count += $log_count;
			}

			$counts['logs'] = $total_log_count;
			set_transient('wordkeeper/database/junk', $counts, DAY_IN_SECONDS);
		}

		require_once 'partials/database.php';
	}


	/**
	 * Display status page
	 *
	 * @return void
	 */
	public function display_status(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		// Get path to tasks file
		$status = realpath(ABSPATH . '../tasks');

		// Load tasks if any exist
		if(!empty($status) && file_exists($status)){
			$tasks = file_get_contents($status);
			$tasks = json_decode($tasks, true);
			$tz = wp_timezone();

			if(!empty($tasks)){
				$site_id = ($multisite) ? get_current_blog_id() : null;
				foreach($tasks as $taskid => $task){
					if(isset($task['view'])){

						// Hide tasks that are only intended for the network admin
						if($task['view'] === 'network' && $multisite && !$network){
							unset($tasks[$taskid]);
							continue;
						}

						// Hide tasks that are only intended for the non-network admins
						if($task['view'] === 'site' && $multisite && $network){
							unset($tasks[$taskid]);
							continue;
						}

						// Hide tasks that are only for site admins in non-multisites
						if($task['view'] === 'admin' && !$multisite && !$admin){
							unset($tasks[$taskid]);
							continue;
						}

						// Manage permissions in non-network admin multisites
						if(($task['view'] === 'admin' || $task['view'] === 'site') && $multisite && !$network){
							// Remove task status reports for sites that the user doesn't have access to
							if(!empty($site_id) && isset($task['subsite'])){
								if($site_id != $task['subsite'] && !$task['subsite'] !== 0){
									unset($tasks[$taskid]);
									continue;
								}
							}

							// Remove the task if the current user doesn't have access to view this task
							if(!current_user_can('manage_options')){
								unset($tasks[$taskid]);
								continue;
							}
						}
					}

					// Format the task timestamp
					if(!empty($task['timestamp'])){
						$task_datetime = new \DateTime();
						$task_datetime->setTimestamp($task['timestamp']);
						$task_datetime->setTimezone($tz);
						$tasks[$taskid]['time'] = $task_datetime->format('m/d/Y h:ia');
					}
				}
			}
		}

		// Submit an empty tasks array if the $tasks var hasn't been populated for any reason
		if(empty($tasks)){
			$tasks = array();
		}

		require_once 'partials/status.php';
	}


	/**
	 * Display redirects page
	 *
	 * @return void
	 */
	public function display_redirects(){
		// Verify user is allowed to view this page
		if(!current_user_can('manage_options') && !current_user_can('manage_network_options')){
			wp_die(__('Sorry, you are not allowed to access this page.'), 403);
		}

		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		require_once 'partials/redirects.php';
	}


	/**
	 * Display videos page
	 *
	 * @return void
	 */
	public function display_videos(){
		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());

		$videos = file_get_contents(__DIR__ . '/videos.dat');
		$videos = unserialize($videos, array('allowed_classes' => false));
		$pages = count($videos);
		$per_page = 9;
		$pages = ceil($pages / $per_page);
		$curr_page = $_GET['page_num'] ?? 1;
		$start = ($curr_page - 1) * $per_page;
		$videos = array_slice($videos, $start, $per_page);

		require_once 'partials/video-library.php';
	}


	/**
	 * Display announcements page
	 *
	 * @return void
	 */
	public function display_announcements(){
		// Is this a multisite?  Is it the network admin?
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (is_network_admin());
		$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && !$network && current_user_can('manage_options')));

		require_once 'partials/announcements.php';
	}


	/**
	 * Register the cache purge menu
	 *
	 * @return void
	 */
	public function add_purge_menu(){
		global $wp_admin_bar, $pagenow;
		// 1. If there's no speed plugin, only add one main menu entry
		// 2. If there's speed plugin active, add a drop down and first entry should be from this plugin.

		if(!class_exists('\WordKeeper_Speed\Config') && !class_exists('\WordKeeper\Speed\Config')){
			if((!is_admin() || (is_admin() && $pagenow == 'post.php')) && (current_user_can('publish_posts') || current_user_can('publish_pages'))){
				$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$current_page = preg_replace('#[\?&]wordkeeper-purge-current#', '', $current_page);
				if(strpos($current_page, '?') !== false){
					$wordkeeper_purge_current = $current_page . '&wordkeeper-purge-current';
				}
				else{
					$wordkeeper_purge_current = $current_page . '?wordkeeper-purge-current';
				}

				$wp_admin_bar->add_menu(array(
					'parent' => 'top-secondary', // use 'false' for a root menu, or pass the ID of the parent menu
					'id' => 'wordkeeper-purge-current', // link ID, defaults to a sanitized title value
					'title' => __('Purge this Page'), // link title
					'href' => $wordkeeper_purge_current, // name of file
					'meta' => false // array of any of the following options: array('html' => '', 'class' => '', 'onclick' => '', target => '', title => '');
				));
			}
		}
		else{
			// This action will be excuted from the speed plugin
			if(has_action('wordkeeper_register_purge_submenu')) {
				add_action('wordkeeper_register_purge_submenu', array($this, 'add_purge_submenu'), 10, 2);
			}
			else {
				add_action('wordkeeper/speed/submenu', array($this, 'add_purge_submenu'), 10, 2);
			}
		}
	}


	/**
	 * Register the cache purge submenu
	 *
	 * @param object $wp_admin_bar
	 * @param string $parent
	 * @return void
	 */
	public function add_purge_submenu(&$wp_admin_bar, $parent){
		global $pagenow;

		if((!is_admin() || (is_admin() && $pagenow == 'post.php')) && (current_user_can('publish_posts') || current_user_can('publish_pages'))){
			$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$current_page = preg_replace('#[\?&]wordkeeper-purge-current#', '', $current_page);

			if(strpos($current_page, '?') !== false){
				$wordkeeper_purge_current = $current_page . '&wordkeeper-purge-current';
			}
			else{
				$wordkeeper_purge_current = $current_page . '?wordkeeper-purge-current';
			}

			$wp_admin_bar->add_menu(array(
				'parent' => $parent, // use 'false' for a root menu, or pass the ID of the parent menu
				'id' => 'wordkeeper-purge-current', // link ID, defaults to a sanitized title value
				'title' => __('Purge this Page'), // link title
				'href' => $wordkeeper_purge_current, // name of file
				'meta' => false // array of any of the following options: array('html' => '', 'class' => '', 'onclick' => '', target => '', title => '');
			));
		}
	}


	/**
	 * Process necessary cache purges after a page is loaded with a given cache purge query param
	 * This method is fired after the page is loaded and is used to check if purge cache action was requested
	 *
	 * @return void
	 */
	public function handle_purge_request(){
		global $pagenow;

		$wordkeeper_purge_current = isset($_GET['wordkeeper-purge-current']);

		// Verify that the current URL has permissions to purge
		if((current_user_can('publish_posts') || current_user_can('publish_pages')) && $wordkeeper_purge_current){
			$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

			// If we're in the post editor, get the post ID and pass it to the regular post purger
			if(is_admin() && $pagenow == 'post.php'){
				$current_page = Purge::purge_post((int) $_GET['post']);
				return;
			}
			// Otherwise for frontend pages, pass the purge URL to the URL purger
			elseif(!is_admin()){
				$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$current_page = preg_replace('#[\?&]wordkeeper-purge-current#', '', $current_page);
				Purge::purge_single($current_page);
			}
		}
	}


	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
