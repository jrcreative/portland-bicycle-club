<?php

namespace WordKeeper\System;

/**
 * The REST functionality of the plugin
 */
class Rest{

	/**
	 * Initialize the class and set its properties
	 */
	public function __construct(){

	}

	/**
	 * Registers REST routes
	 *
	 * @return void
	 */
	public function register_routes(){
		// Register our custom endpoint and custom routes
		add_action('rest_api_init', function(){
			$dispatch = new Dispatch();
			$countries = Settings::get_instance()->get_countries();

			// Logs route
			register_rest_route('wordkeeper-system/v1', '/log/download', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'download_log'),
				'args' => array(
					'log' => array(
						'required' => true,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Reject long strings
							if(!empty($param) && strlen($param) > 50){
								return false;
							}

							return (preg_match('#^(?:access|error|optimize-images|wp-cron|phpslow|debug)\.log(?:\-[0-9]{1,}\.(?:gz|zip))?$#i', $param) === 1);
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9a-zA-Z\-\_\.]#', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Clear caches route
			register_rest_route('wordkeeper-system/v1', '/cache/clear', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'clear_cache'),
				'args' => array(
					'cache' => array(
						'required' => true,
						'type' => 'string',
						'enum' => array('all','page'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					if(defined('MULTISITE') && MULTISITE === true){
						return (current_user_can('manage_network_options') || current_user_can('manage_options') || current_user_can('publish_posts') || current_user_can('publish_pages'));
					}
					else{
						return (current_user_can('manage_options') || current_user_can('publish_posts') || current_user_can('publish_pages'));
					}
				}
			));

			// Create backup route
			register_rest_route('wordkeeper-system/v1', '/backup/create', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'create_backup'),
				'args' => array(
					'name' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Reject long strings
							if(!empty($param) && strlen($param) > 50){
								return false;
							}

							return (preg_match('#[^0-9a-zA-Z\-_\.\s]#', $param) !== 1);
						},
						'sanitize_callback' => function($param, $request, $key){
							$param = str_replace(' ', '-', $param);
							$param = preg_replace('#\.?checkpoint(?:\.[0-9]{1,})?$#i', '', $param);
							return $param;
						}
					),
					'notify' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Not required.  Return true if no address is provided
							if(empty($param)){
								return true;
							}

							// Validate as single email
							if(strpos($param, ',') === false){
								// Reject email addresses over 60 chars
								if(strlen($param) > 62){
									return false;
								}

								// If the "email" is not actually an email, fail the request
								return (filter_var($param, FILTER_VALIDATE_EMAIL) !== false);
							}
							// Validate as CSV of emails
							else{
								$emails = explode(',', $param);
								$valid = true;

								// Don't allow more than 6 addon emails in the notification
								if(count(array_unique($emails)) > 6){
									$valid = false;
								}

								// Validate emails separately
								foreach($emails as $email){
									$email = trim($email);

									// Reject email addresses over 60 chars
									if(strlen($email) > 62){
										$valid = false;
									}

									// If any of the "emails" are not actually emails, fail the request
									if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
										$valid = false;
									}
								}

								return $valid;
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^\p{L}0-9@\._\+\-,\s_!]#u', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					if(defined('MULTISITE') && MULTISITE === true){
						return (current_user_can('manage_network_options') || current_user_can(('manage_options')));
					}
					else{
						return (current_user_can('manage_options'));
					}
				}
			));

			// Create backup route
			register_rest_route('wordkeeper-system/v1', '/backup/restore', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'restore_backup'),
				'args' => array(
					'name' => array(
						'required' => true,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Reject long strings
							if(!empty($param) && strlen($param) > 50){
								return false;
							}

							return (preg_match('#[^0-9a-zA-Z\-_\.\s]#', $param) !== 1);
						},
						'sanitize_callback' => function($param, $request, $key){
							$param = str_replace(' ', '-', $param);
							$param = preg_replace('#\.?checkpoint(?:\.[0-9]{1,})?$#i', '', $param);
							return $param;
						}
					),
					'restore' => array(
						'required' => true,
						'type' => 'string',
						'enum' => array('everything','files','database','plugins','themes'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'offset' => array(
						'required' => true,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							return (preg_match('#^(?:\+|\-)?[0-9]{1,}$#', $param) === 1);
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9\+\-]#', '', $param);
						}
					),
					'notify' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Not required.  Return true if no address is provided
							if(empty($param)){
								return true;
							}

							// Validate as single email
							if(strpos($param, ',') === false){
								// Reject email addresses over 60 chars
								if(strlen($param) > 62){
									return false;
								}

								// If the "email" is not actually an email, fail the request
								return (filter_var($param, FILTER_VALIDATE_EMAIL) !== false);
							}
							// Validate as CSV of emails
							else{
								$emails = explode(',', $param);
								$valid = true;

								// Don't allow more than 6 addon emails in the notification
								if(count(array_unique($emails)) > 6){
									$valid = false;
								}

								// Validate emails separately
								foreach($emails as $email){
									$email = trim($email);

									// Reject email addresses over 60 chars
									if(strlen($email) > 62){
										$valid = false;
									}

									// If any of the "emails" are not actually emails, fail the request
									if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
										$valid = false;
									}
								}

								return $valid;
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^\p{L}0-9@\._\+\-,\s_!]#u', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Sync to staging
			register_rest_route('wordkeeper-system/v1', '/sync/staging', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'sync_staging'),
				'args' => array(
					'notify' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Not required.  Return true if no address is provided
							if(empty($param)){
								return true;
							}

							// Validate as single email
							if(strpos($param, ',') === false){
								// Reject email addresses over 60 chars
								if(strlen($param) > 62){
									return false;
								}

								// If the "email" is not actually an email, fail the request
								return (filter_var($param, FILTER_VALIDATE_EMAIL) !== false);
							}
							// Validate as CSV of emails
							else{
								$emails = explode(',', $param);
								$valid = true;

								// Don't allow more than 6 addon emails in the notification
								if(count(array_unique($emails)) > 6){
									$valid = false;
								}

								// Validate emails separately
								foreach($emails as $email){
									$email = trim($email);

									// Reject email addresses over 60 chars
									if(strlen($email) > 62){
										$valid = false;
									}

									// If any of the "emails" are not actually emails, fail the request
									if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
										$valid = false;
									}
								}

								return $valid;
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^\p{L}0-9@\._\+\-,\s_!]#u', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Sync to live
			register_rest_route('wordkeeper-system/v1', '/sync/live', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'sync_live'),
				'args' => array(
					'domain' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Determine whether both live and staging accounts exist
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

							// Is a sync domain required
							$syncdomain = ($mode === 'staging' && $live === false);

							// If we're syncing to live from staging and no pre-existing staging site exists, we need a domain
							if($syncdomain && empty($param)){
								return false;
							}

							// Make sure that we're only dealing with a hostname (even if in URL form)
							try{
								$param = (preg_match('#^https?://#', strtolower($param)) === 0) ? 'https://' . strtolower($param) : strtolower($param);
								$parts = parse_url($param);

								if(
									!empty($parts) &&
									(!empty($parts['host']) && filter_var($parts['host'], FILTER_VALIDATE_DOMAIN)) &&
									empty($parts['port']) &&
									empty($parts['user']) &&
									empty($parts['pass']) &&
									empty($parts['query']) &&
									empty($parts['fragment']) &&
									(empty($parts['path']) || $parts['path'] == '/') &&
									preg_match('#^[0-9\p{L}]{1}[0-9\p{L}\-\.]*$#u', $parts['host']) === 1 &&
									preg_match("#^(?:[0-9\p{L}](?:-*[0-9\p{L}])*)(?:\.(?:[0-9\p{L}](?:-*[0-9\p{L}])*))*$#iu", $parts['host']) === 1 &&
									strlen($parts['host']) <= 253 &&
									preg_match("#^[^\.]{1,63}(?:\.[^\.]{1,63})*$#", $parts['host']) === 1
								){
									$sections = explode('.', $parts['host']);
									if(count($sections) >= 2){
										return true;
									}
									else{
										return false;
									}
								}
								else{
									return false;
								}
							}
							catch(\Exception $ex){
								return false;
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							$param = (preg_match('#^https?://#', $param) === 0) ? 'https://' . $param : $param;
							$parts = parse_url($param);

							// Make sure that we're only dealing with a hostname (even if in URL form)
							try{
								$parts = parse_url($param);
								if(!empty($parts) && (!empty($parts['host']))){
									return strtolower(preg_replace('#[^\p{L}0-9\.\-]#u', '', $parts['host']));
								}
								else{
									return '';
								}
							}
							catch(\Exception $ex){
								return '';
							}
						}
					),
					'notify' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Not required.  Return true if no address is provided
							if(empty($param)){
								return true;
							}

							// Validate as single email
							if(strpos($param, ',') === false){
								// Reject email addresses over 60 chars
								if(strlen($param) > 62){
									return false;
								}

								// If the "email" is not actually an email, fail the request
								return (filter_var($param, FILTER_VALIDATE_EMAIL) !== false);
							}
							// Validate as CSV of emails
							else{
								$emails = explode(',', $param);
								$valid = true;

								// Don't allow more than 6 addon emails in the notification
								if(count(array_unique($emails)) > 6){
									$valid = false;
								}

								// Validate emails separately
								foreach($emails as $email){
									$email = trim($email);

									// Reject email addresses over 60 chars
									if(strlen($email) > 62){
										$valid = false;
									}

									// If any of the "emails" are not actually emails, fail the request
									if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
										$valid = false;
									}
								}

								return $valid;
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^\p{L}0-9@\._\+\-,\s_!]#u', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Fix permissions
			register_rest_route('wordkeeper-system/v1', '/permissions/fix', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'fix_permissions'),
				'args' => array(),
				'permission_callback' => function(){
					if(defined('MULTISITE') && MULTISITE === true){
						return (current_user_can('manage_network_options') || current_user_can(('manage_options')));
					}
					else{
						return (current_user_can('manage_options'));
					}
				}
			));

			// Install SSL
			register_rest_route('wordkeeper-system/v1', '/ssl/install', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'install_ssl'),
				'args' => array(),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Install Speed plugin
			register_rest_route('wordkeeper-system/v1', '/speed/install', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'install_speed'),
				'args' => array(),
				'permission_callback' => function(){
					return (current_user_can('install_plugins'));
				}
			));

			// Clean Junk
			register_rest_route('wordkeeper-system/v1', '/database/clean', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'clean_database'),
				'args' => array(
					'database/posts/revisions' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/autodrafts' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/trashed' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/spam' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/unapproved' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/transients' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/pingbacks' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/trackbacks' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/taxonomy/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/users/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return !$network && (current_user_can('manage_network_options') || current_user_can('manage_options'));
				}
			));

			// PHP version change
			register_rest_route('wordkeeper-system/v1', '/php/version', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'save_php'),
				'args' => array(
					'php/version' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
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
								return in_array($param, $versions);
							}
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9\.]#', '', $param);
						}
					),
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// PHP email change
			register_rest_route('wordkeeper-system/v1', '/php/email', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'save_php'),
				'args' => array(
					'php/email' => array(
						'required' => true,
						'type' => 'boolean',
					),
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Updates management
			register_rest_route('wordkeeper-system/v1', '/updates/save', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'updates_save'),
				'args' => array(
					'wp/updates/plugins' => array(
						'required' => true,
						'type' => 'boolean',
					),
					'wp/updates/core' => array(
						'required' => true,
						'type' => 'boolean',
					),
					'wp/updates/exclusions' => array(
						'required' => true,
						'type' => 'boolean',
					),
					'wp/updates/exclusions/list' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// Not required.  Return true if no value is provided
							if(empty($param)){
								return true;
							}

							$plugins = get_plugins();
							$param_list = explode(',', $param);
							foreach($param_list as $param_key => $param_val){
								if(!array_key_exists($param_val, $plugins)){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]#u', '', $param);
						}
					)
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// PHP settings change
			register_rest_route('wordkeeper-system/v1', '/php', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'save_php'),
				'args' => array(
					'php/max-execution' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// return (preg_match('#^[0-9]{1,3}[sS]?$#', $param) == 1);

							// Temporarily disable this.  We don't yet support it
							return false;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9]#', '', $param);
						},
					),
					'php/max-upload' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// return (preg_match('#^[0-9]{1,3}[mM]?$#', $param) == 1);

							// Temporarily disable this.  We don't yet support it
							return false;
						},
						'sanitize_callback' => function($param, $request, $key){
							return strtoupper(preg_replace('#[^0-9mM]#', '', $param));
						}
					),
					'php/max-post' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							// return (preg_match('#^[0-9]{1,3}[mM]?$#', $param) == 1);

							// Temporarily disable this.  We don't yet support it
							return false;
						},
						'sanitize_callback' => function($param, $request, $key){
							return strtoupper(preg_replace('#[^0-9mM]#', '', $param));
						}
					),
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));

			// Settings save
			register_rest_route('wordkeeper-system/v1', '/settings', array(
				'methods' => 'POST',
				'callback' => array($dispatch, 'save_settings'),
				'args' => array(
					'cache/post' => array(
						'type' => 'string',
						'enum' => array('default','10800','43200','86400','2592000'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9a-zA-Z]#', '', $param);
						}
					),
					'cache/page' => array(
						'type' => 'string',
						'enum' => array('default','10800','43200','86400','2592000'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9a-zA-Z]#', '', $param);
						}
					),
					'wp/cron' => array(
						'required' => false,
						'type' => 'integer',
						'enum' => array(900, 1800, 3600),
						'validate_callback' => function($param, $request, $key){
							// Reject any settings that are outside of the active plan's limits
							$limits = Limits::get_instance()->get();
							if((((int) $param) / 60) < $limits['cron']){
								return false;
							}

							// Otherwise allow
							return (in_array($param, array(900, 1800, 3600)));
						},
					),
					'wp/cron/web' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'wp/updates' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'wp/updates/exclude' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'wp/updates/exclude/list' => array(
						'required' => false,
						'type' => 'boolean'  //  temporarily set a harmless type requirement until we start using this
					),
					'wp/heartbeat/frequency' => array(
						'required' => false,
						'enum' => array('default', 30, 60, 300)
					),
					'wp/heartbeat/limits' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('default', 'off', 'dashboard', 'post-edit'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z\-]#', '', $param);
						}
					),
					'wp/image/editor' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('imagick', 'gd'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'wp/translation' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'wp/editor' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'wp/robots' => array(
						'required' => false,
						'type' => 'string',
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]#u', '', $param);
						}
					),
					'https/force' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'https/fix' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'comments/block' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'comments/url' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'trackbacks/block' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'pingbacks/block' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/ahrefs' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/moz' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/semrush' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/screaming-frog' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/majestic' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/dataforseo' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/raven' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/yandex' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/baidu' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/huawei' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/seznam' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/mailru' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/qwant' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/sogou' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/coccoc' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/gptbot' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/google-extended' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/google-other' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/amazonbot' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/applebot' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/anthropic-ai' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/ccbot' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bots/facebookbot' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'images/optimize' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('recommended', 'advanced', 'off'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'images/frequency' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('daily', 'immediately'),
						'validate_callback' => function($param, $request, $key){
							// Reject any settings that are outside of the active plan's limits
							if($param == 'immediately'){
								$limits = Limits::get_instance()->get();
								if($limits['images'] != 'ondemand'){
									return false;
								}
							}

							// Otherwise allow
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'images/quality/type' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('lossless', 'lossy'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'images/quality/setting' => array(
						'required' => false,
						'type' => 'integer',
						'validate_callback' => function($param, $request, $key){
							return ($param >= 0 && $param <= 100);
						},
						'sanitize_callback' => function($param, $request, $key){
							return (int) preg_replace('#[^0-9]#', '', (string) $param);
						}
					),
					'images/opaque' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'images/resize' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'images/width/threshold' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$width = (int) preg_replace('#[^0-9]#', '', $param);

							// Allow widths between 800px and 6000px
							return ($width >= 800 && $width <= 6000);
						},
						'sanitize_callback' => function($param, $request, $key){
							return (int) preg_replace('#[^0-9]#', '', $param);
						}
					),
					'images/height/threshold' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$height = (int) preg_replace('#[^0-9]#', '', $param);

							// Allow heights between 800px and 6000px
							return ($height >= 800 && $height <= 6000);
						},
						'sanitize_callback' => function($param, $request, $key){
							return (int) preg_replace('#[^0-9]#', '', $param);
						}
					),
					'database/optimize' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('recommended', 'advanced', 'off'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'database/frequency' => array(
						'required' => false,
						'type' => 'string',
						'enum' => array('monthly', 'weekly', 'daily'),
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z]#', '', $param);
						}
					),
					'database/posts/revisions' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/autodrafts' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/trashed' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/posts/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/trashed' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/spam' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/unapproved' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/comments/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/taxonomy/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/users/orphans' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/transients' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/pingbacks' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/trackbacks' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/oembed' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'database/logs' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bot/restric' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bot/register' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bot/forms' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'bot/comments' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'login/protect' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'login/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'login/whitelist' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$countries = Settings::get_instance()->get_countries();
							$params = explode(',', $param);
							foreach($params as $v){
								if(!isset($countries[$v])){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z,]#', '', $param);
						}
					),
					'reset/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'reset/whitelist' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$countries = Settings::get_instance()->get_countries();
							$params = explode(',', $param);
							foreach($params as $v){
								if(!isset($countries[$v])){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z,]#', '', $param);
						}
					),
					'register/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'register/whitelist' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$countries = Settings::get_instance()->get_countries();
							$params = explode(',', $param);
							foreach($params as $v){
								if(!isset($countries[$v])){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z,]#', '', $param);
						}
					),
					'geo/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'comment/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'comment/whitelist' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							$countries = Settings::get_instance()->get_countries();
							$params = explode(',', $param);
							foreach($params as $v){
								if(!isset($countries[$v])){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z,]#', '', $param);
						}
					),
					'forms/restrict' => array(
						'required' => false,
						'type' => 'boolean'
					),
					'forms/whitelist' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							if(!empty($param)){
								$countries = Settings::get_instance()->get_countries();
								$params = explode(',', $param);
								foreach($params as $v){
									if(!isset($countries[$v])){
										return false;
									}
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z,]#', '', $param);
						}
					),
					'mail/name' => array(
						'required' => false,
						'type' => 'string',
						'validate_callback' => function($param, $request, $key){
							if(!empty($param)){
								if(preg_match('#[^0-9a-zA-Z\-\.\s,:&\(\)\#\$%]#', $param)){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^0-9a-zA-Z\-\.\s,:&\(\)\#\$%]#', '', $param);
						}
					),
					'mail/email' => array(
						'required' => false,
						'type' => 'email',
						'validate_callback' => function($param, $request, $key){
							if(!empty($param)){
								if(!is_email($param)){
									return false;
								}
							}
							return true;
						},
						'sanitize_callback' => function($param, $request, $key){
							return preg_replace('#[^a-zA-Z0-9@\._\+\-_!]#', '', $param);
						}
					),
					'mail/force' => array(
						'required' => false,
						'type' => 'boolean'
					),
				),
				'permission_callback' => function(){
					// Is this a multisite?  Is it the network admin?
					$multisite = (defined('MULTISITE') && MULTISITE === true);
					$network = (from_network_admin()) ? true : false;
					$admin = (($multisite && $network && current_user_can('manage_network_options')) || (!$multisite && current_user_can('manage_options')));

					return $admin;
				}
			));
		});

		// Filter REST index of available routes to hide our admin-only custom routes
		// Specific to the index of routes in the global list
		add_filter('rest_index', function($response){
			if(!is_user_logged_in()){
				// Filter the list of namespaces in the index to hide the system namespaces
				$namespaces = array_filter($response->data['namespaces'], function($namespace){
					return $namespace !== 'wordkeeper-system/v1';
				});

				$response->data['namespaces'] = array_values($namespaces);

				// Filter the list of routes in the index to hide the system routes
				$filtered = array_filter($response->data['routes'], function($key){
					return strpos($key, 'wordkeeper-system/v1') === false;
				}, ARRAY_FILTER_USE_KEY);

				$response->data['routes'] = $filtered;
			}

			return $response;
		});

		// Filter REST index of available routes to hide our admin-only custom routes
		// Specific to index of routes available in our own namespace
		add_filter('rest_namespace_index', function($response, $version){
			if(!is_user_logged_in() && $version === 'wordkeeper-system/v1'){
				$response->data['routes'] = [];
			}

			return $response;
		}, 10, 2);
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
