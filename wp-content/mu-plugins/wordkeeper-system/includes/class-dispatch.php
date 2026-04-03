<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use file_exists;
use count;
use dirname;
use explode;

/**
 * The WordKeeper System Purge class
 */
class Dispatch {

	/**
	 * Route purge API request to purger
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function clear_cache(\WP_REST_Request $request){
		$cache = $request['cache'];
		$network = (from_network_admin()) ? true : false;

		switch($cache){
			case 'all':
				$response = array(
					'status' => true,
					'dispatch' => true,
					'network' => $network,
				);

				return rest_ensure_response($response);
				break;
			case 'page':
				Purge::purge_by_url();
				$response = array(
					'status' => true,
					'network' => $network,
				);

				return rest_ensure_response($response);
				break;
			default:
				$response = array(
					'status' => false,
					'network' => $network,
				);

				return rest_ensure_response($response);
				break;
		}
	}

	/**
	 * Download log file
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function download_log(\WP_REST_Request $request){
		$log = $request->get_param('log');
		$type = (strpos($log, 'debug.log') === false) ? 'default' : 'debug';
		$logpath = '';

		// Log basepath for server logs is the user home dir
		if($type == 'default'){
			$parts = explode('/', trim(ABSPATH, '/'));
			$basepath = '/' . $parts[0] . '/' . $parts[1] . '/logs';
		}
		// Log basepath for debug logs is the WP content dir
		else{
			$basepath = WP_CONTENT_DIR;
		}

		// Block paths that aren't within the user's home path
		if(strpos($basepath, dirname(ABSPATH)) === false){
			exit;
		}

		$parts = explode('.', $log);
		$types = array(
			'access',
			'error',
			'phpslow',
			'debug'
		);

		// Exit if the log isn't one of the supported log types
		if(empty($parts) || empty($parts[0] || !in_array($parts[0], $types))){
			exit;
		}

		// Limit file download to document root log, gz, or zip file
		if(file_exists($basepath . '/' . $log)){
			$parts = explode('.', $log);
			switch($parts[count($parts) - 1]){
				case 'log':
				case 'gz':
				case 'zip':
					$logpath = $basepath . '/' . $log;
					break;
				default:
					exit;
					break;
			}
		}

		// If we've set the logpath to a legitimate log, gz, or zip file, download it
		if(!empty($logpath)){
			// Remove existing headers
			header_remove();

			// Set download headers
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($logpath) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($logpath));

			// Read file into output
			readfile($logpath);
			exit;
		}
	}

	/**
	 * Route backup request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function create_backup(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		return rest_ensure_response($response);
	}

	/**
	 * Route backup request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function restore_backup(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		return rest_ensure_response($response);
	}

	/**
	 * Route staging sync request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function sync_staging(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		// Check active (and network active) plugins
		// Also handle edge cases where these options checks return false
		$plugins = get_option('active_plugins');
		$plugins = (is_array($plugins)) ? $plugins : array();
		if($multisite){
			$networkplugins = get_site_option('active_sitewide_plugins', array());
			$networkplugins = (is_array($networkplugins)) ? array_keys($networkplugins) : array();
			$plugins = array_unique(array_merge($plugins, $networkplugins));
		}

		// Divi Supreme/Divi Supreme Pro
		if(
			in_array('supreme-modules-pro-for-divi/supreme-modules-pro-for-divi.php', $plugins) ||
			in_array('supreme-modules-for-divi/supreme-modules-for-divi.php', $plugins)
		){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Oxygen builder
		if(in_array('oxygen/functions.php', $plugins)){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Old domain mapping plugin
		if(in_array('wordpress-mu-domain-mapping/domain_mapping.php', $plugins)){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Subdomain multisite install
		if(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL === true){
			$response['status'] = false;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => '',
				'text' => 'Subdomain and multi-domain multisites need special attention.  Contact support.'
			);
		}

		return rest_ensure_response($response);
	}

	/**
	 * Route live sync request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function sync_live(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		// Check active (and network active) plugins
		// Also handle edge cases where these options checks return false
		$plugins = get_option('active_plugins');
		$plugins = (is_array($plugins)) ? $plugins : array();
		if($multisite){
			$networkplugins = get_site_option('active_sitewide_plugins', array());
			$networkplugins = (is_array($networkplugins)) ? array_keys($networkplugins) : array();
			$plugins = array_unique(array_merge($plugins, $networkplugins));
		}

		// Divi Supreme/Divi Supreme Pro
		if(
			in_array('supreme-modules-pro-for-divi/supreme-modules-pro-for-divi.php', $plugins) ||
			in_array('supreme-modules-for-divi/supreme-modules-for-divi.php', $plugins)
		){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Oxygen builder
		if(in_array('oxygen/functions.php', $plugins)){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Old domain mapping plugin
		if(in_array('wordpress-mu-domain-mapping/domain_mapping.php', $plugins)){
			$response['status'] = true;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => 'https://wordkeeper.helpscoutdocs.com/article/86-site-sync-follow-up',
				'text' => 'Sync started, but some of your plugins require follow up after sync.'
			);
		}

		// Subdomain multisite install
		if(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL === true){
			$response['status'] = false;
			$response['messages'] = array();
			$response['messages'][] = array(
				'action' => '',
				'text' => 'Subdomain and multi-domain multisites need special attention.  Contact support.'
			);
		}

		return rest_ensure_response($response);
	}

	/**
	 * Route permissions fix request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function fix_permissions(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		return rest_ensure_response($response);
	}

	/**
	 * Route SSL install request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function install_ssl(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		return rest_ensure_response($response);
	}

	/**
	 * Route Speed install request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function install_speed(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
			'messages' => array(
				array(
					'action' => '/wp-admin/plugins.php',
					'text' => 'Installed but inactive.  Activate plugin to use.'
				)
			)
		);

		return rest_ensure_response($response);
	}

	/**
	 * Route clean database junk request to controller
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function clean_database(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		// Clear previous db junk counts
		delete_transient('wordkeeper/database/junk');

		return rest_ensure_response($response);
	}

	/**
	 * Save PHP settings
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function save_php(\WP_REST_Request $request){
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;

		$response = array(
			'status' => true,
			'dispatch' => true,
			'network' => $network,
		);

		return rest_ensure_response($response);
	}

	/**
	 * Save settings
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function save_settings(\WP_REST_Request $request){
		$settings = Settings::get_instance();
		$values = $settings->get_settings();
		$multisite = (defined('MULTISITE') && MULTISITE === true);
		$network = (from_network_admin()) ? true : false;
		$response = array(
			'status' => true,
			'network' => $network,
		);
		$params = $request->get_params();

		// Handle database optimization changes
		if(isset($params['database/optimize'])){
			// Only dispatch if the settings have changed from what they currently are
			$diff = $settings->diff($params);
			if(!empty(array_filter($diff, function($key){
				return (strpos($key, 'database/') !== false);
			}, ARRAY_FILTER_USE_KEY))){
				$response['dispatch'] = true;
			}

			// Delete previous db junk counts
			delete_transient('wordkeeper/database/junk');
		}

		// Handle image optimization changes
		if(isset($params['images/optimize'])){
			// Only dispatch if the settings have changed from what they currently are
			$diff = $settings->diff($params);

			if(!empty(array_filter($diff, function($key){
				return (strpos($key, 'images/') !== false);
			}, ARRAY_FILTER_USE_KEY))){
				$response['dispatch'] = true;
			}
		}

		// Only dispatch if settings have changed from what they currently are
		if(isset($params['wp/cron']) && $values['wp/cron'] != $params['wp/cron']){
			$response['dispatch'] = true;
		}

		// If robots.txt exists as an actual file, save the changes
		$bots = array_filter($params, function($key){
			return (strpos($key, 'bots') === 0);
		}, ARRAY_FILTER_USE_KEY);

		// If there are robots.txt changes, save them
		if(!empty($bots)){
			$robots = new Robots();
			$robots->save($params);
		}

		// Save settings
		$settings->save($params);

		return rest_ensure_response($response);
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
