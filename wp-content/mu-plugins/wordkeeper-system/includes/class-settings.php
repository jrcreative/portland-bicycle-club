<?php

namespace WordKeeper\System;

require_once ABSPATH . '/wp-admin/includes/plugin.php';

class Settings{
	protected static $instance;
	protected $settings = array();
	protected $dynamic = array();
	protected $schema = 8;

	/**
	 * Settings that trigger cache purges and corresponding caches to purge
	 *
	 * @var array
	 */
	protected $purgeable = array(
		'https/force' => 'all',
		'https/fix' => 'all',
		'cache/post' => 'all',
		'cache/page' => 'all',
		'comments/block' => 'all',
		'comments/url' => 'all',
		'bots/ahrefs' => 'robots',
		'bots/moz' => 'robots',
		'bots/semrush' => 'robots',
		'bots/screaming-frog' => 'robots',
		'bots/majestic' => 'robots',
		'bots/dataforseo' => 'robots',
		'bots/raven' => 'robots',
		'bots/yandex' => 'robots',
		'bots/baidu' => 'robots',
		'bots/huawei' => 'robots',
		'bots/seznam' => 'robots',
		'bots/mailru' => 'robots',
		'bots/qwant' => 'robots',
		'bots/sogou' => 'robots',
		'bots/coccoc' => 'robots',
		'bots/gptbot' => 'robots',
		'bots/google-extended' => 'robots',
		'bots/google-other' => 'robots',
		'bots/amazonbot' => 'robots',
		'bots/anthropic-ai' => 'robots',
		'bots/applebot' => 'robots',
		'bots/ccbot' => 'robots',
		'bots/facebookbot' => 'robots',
	);

	/**
	 * Supported settings keys and value formats
	 *
	 * @var array
	 */
	protected $supported = array(
		'schema' => array('type' => 'int'),
		'cache/post' => array('type' => 'enum', 'options' => array('default', 10800, 43200, 86400, 2592000)),
		'cache/page' => array('type' => 'enum', 'options' => array('default', 10800, 43200, 86400, 2592000)),
		'wp/cron' => array('type' => 'enum', 'options' => array(900, 1800, 3600)),
		'wp/cron/web' => array('type' => 'bool'),
		'wp/heartbeat/frequency' => array('type' => 'enum', 'options' => array('default', 30, 60, 300)),
		'wp/heartbeat/limits' => array('type' => 'enum', 'options' => array('default', 'off', 'dashboard', 'post-edit')),
		'wp/image/editor' => array('type' => 'enum', 'options' => array('imagick', 'gd')),
		'wp/translation' => array('type' => 'bool'),
		'wp/editor' => array('type' => 'bool'),
		'wp/updates/core' => array('type' => 'bool'),
		'wp/updates/plugins' => array('type' => 'bool'),
		'wp/updates/exclusions' => array('type' => 'bool'),
		'wp/updates/exclusions/list' => array('type' => 'plugins'),
		'wp/robots' => array('type' => 'string'),
		'https/force' => array('type' => 'bool'),
		'https/fix' => array('type' => 'bool'),
		'comments/block' => array('type' => 'bool'),
		'comments/url' => array('type' => 'bool'),
		'trackbacks/block' => array('type' => 'bool'),
		'pingbacks/block' => array('type' => 'bool'),
		'bots/ahrefs' => array('type' => 'bool'),
		'bots/moz' => array('type' => 'bool'),
		'bots/semrush' => array('type' => 'bool'),
		'bots/screaming-frog' => array('type' => 'bool'),
		'bots/majestic' => array('type' => 'bool'),
		'bots/dataforseo' => array('type' => 'bool'),
		'bots/raven' => array('type' => 'bool'),
		'bots/yandex' => array('type' => 'bool'),
		'bots/baidu' => array('type' => 'bool'),
		'bots/huawei' => array('type' => 'bool'),
		'bots/seznam' => array('type' => 'bool'),
		'bots/mailru' => array('type' => 'bool'),
		'bots/qwant' => array('type' => 'bool'),
		'bots/sogou' => array('type' => 'bool'),
		'bots/coccoc' => array('type' => 'bool'),
		'bots/google-extended' => array('type' => 'bool'),
		'bots/google-other' => array('type' => 'bool'),
		'bots/gptbot' => array('type' => 'bool'),
		'bots/amazonbot' => array('type' => 'bool'),
		'bots/anthropic-ai' => array('type' => 'bool'),
		'bots/applebot' => array('type' => 'bool'),
		'bots/ccbot' => array('type' => 'bool'),
		'bots/facebookbot' => array('type' => 'bool'),
		'images/optimize' => array('type' => 'enum', 'options' => array('recommended', 'advanced', 'off')),
		'images/frequency' => array('type' => 'enum', 'options' => array('daily', 'immediately')),
		'images/quality/type' => array('type' => 'enum', 'options' => array('lossless', 'lossy')),
		'images/quality/setting' => array('type' => 'int/range', 'min' => 0, 'max' => 100),
		'images/opaque' => array('type' => 'bool'),
		'images/resize' => array('type' => 'bool'),
		'images/width/threshold' => array('type' => 'int'),
		'images/height/threshold' => array('type' => 'int'),
		'database/optimize' => array('type' => 'enum', 'options' => array('recommended', 'advanced', 'off')),
		'database/frequency' => array('type' => 'enum', 'options' => array('monthly', 'weekly', 'daily')),
		'database/posts/revisions' => array('type' => 'bool'),
		'database/posts/autodrafts' => array('type' => 'bool'),
		'database/posts/orphans' => array('type' => 'bool'),
		'database/posts/trashed' => array('type' => 'bool'),
		'database/comments/trashed' => array('type' => 'bool'),
		'database/comments/spam' => array('type' => 'bool'),
		'database/comments/unapproved' => array('type' => 'bool'),
		'database/comments/orphans' => array('type' => 'bool'),
		'database/taxonomy/orphans' => array('type' => 'bool'),
		'database/users/orphans' => array('type' => 'bool'),
		'database/transients' => array('type' => 'bool'),
		'database/pingbacks' => array('type' => 'bool'),
		'database/trackbacks' => array('type' => 'bool'),
		'database/oembed' => array('type' => 'bool'),
		'database/logs' => array('type' => 'bool'),
		'bot/restrict' => array('type' => 'bool'),
		'bot/register' => array('type' => 'bool'),
		'bot/forms' => array('type' => 'bool'),
		'bot/comments' => array('type' => 'bool'),
		'login/protect' => array('type' => 'bool'),
		'login/whitelist' => array('type' => 'country'),
		'login/restrict' => array('type' => 'bool'),
		'reset/whitelist' => array('type' => 'country'),
		'reset/restrict' => array('type' => 'bool'),
		'register/whitelist' => array('type' => 'country'),
		'register/restrict' => array('type' => 'bool'),
		'geo/restrict' => array('type' => 'bool'),
		'comment/whitelist' => array('type' => 'country'),
		'comment/restrict' => array('type' => 'bool'),
		'forms/whitelist' => array('type' => 'country'),
		'forms/restrict' => array('type' => 'bool'),
		'mail/name' => array('type' => 'regex', 'regex' => '#[^0-9a-zA-Z\-\.\s,:&\(\)\#\$%]#', 'negate' => true),
		'mail/email' => array('type' => 'email'),
		'mail/force' => array('type' => 'bool'),
	);

	/**
	 * List of countries and country codes
	 *
	 * @var array
	 */
	protected $countries = array();

	/**
	 * Initialize settings
	 *
	 * @return void
	 */
	public function __construct(){
		$settings = (defined('MULTISITE') && MULTISITE === true) ? get_site_option('wordkeeper/system') : get_option('wordkeeper/system');

		if(!$settings){
			$migrate = get_option('wordkeeper-system-settings');
			if($migrate){
				$settings = $this->migrate($migrate);
				update_option('wordkeeper/system', $settings);
				delete_option('wordkeeper-system-settings');
				delete_option('wordkeeper-system-security-settings');
			}
			else{
				$settings = $this->get_default();

				if((defined('MULTISITE') && MULTISITE === true)){
					update_site_option('wordkeeper/system', $settings);
				}
				else{
					update_option('wordkeeper/system', $settings);
				}
			}
			$this->settings = $settings;
		}
		else{
			// Validate and sanitize settings
			$settings = $this->sanitize($settings);

			$this->settings = $settings;
			if($settings['schema'] != $this->schema){
				$this->upgrade();
			}
		}
	}

	/**
	 * Either returns an existing single class instance or creates a new and returns it.
	 *
	 * @access static
	 * @param void
	 * @return object
	 */
	public static function get_instance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Migrate legacy settings format to new format
	 *
	 * @param array $migrate
	 * @return void
	 */
	private function migrate($migrate){
		$default = $this->get_default();
		$default['wp/heartbeat/frequency'] = ($migrate['heartbeat-frequency'] != 'default') ? $migrate['heartbeat-frequency'] : 60;

		switch($migrate['heartbeat-permissions']){
			case 'default':
				$default['wp/heartbeat/frequency'] = 'post-edit';
				break;
			case 'disable-heartbeat-completely':
				$default['wp/heartbeat/frequency'] = 'off';
				break;
			case 'disable-heartbeat-dashboard':
				$default['wp/heartbeat/frequency'] = 'dashboard';
				break;
			case 'allow-heartbeat-post-edit':
				$default['wp/heartbeat/frequency'] = 'post-edit';
				break;
			default:
				break;
		}

		$bots = array(
			'ahrefs',
			'moz',
			'semrush',
			'screaming-frog',
			'gptbot',
			'majestic',
			'dataforseo',
			'raven',
			'yandex',
			'baidu',
			'huawei',
			'seznam',
			'mailru',
			'qwant',
			'sogou',
			'coccoc'
		);

		foreach($bots as $bot){
			$default['bots/' . $bot] = (isset($migrate['ahrefs']) && $migrate['ahrefs'] == 'on') ? true : false;
		}

		return $default;
	}

	/**
	 * Upgrade to a newer schema version
	 *
	 * @return void
	 */
	private function upgrade(){
		// Get the current schema default
		$default = $this->get_default();

		// Loop over schema and add any new key/value pairs that didn't exist previously
		foreach($default as $key => $value){
			if(!isset($this->settings[$key])){
				$this->settings[$key] = $value;
			}
		}

		if($this->settings['login/restrict'] ||
			$this->settings['reset/restrict'] ||
			$this->settings['register/restrict']){
				$this->settings['login/protect'] = true;
		}

		if($this->settings['comment/restrict'] ||
			$this->settings['forms/restrict']){
				$this->settings['geo/restrict'] = true;
		}

		// Set the schema version to the current schema #
		$this->settings['schema'] = $this->schema;

		// Save the changes
		$this->save($this->settings);
	}

	/**
	 * Get country list
	 *
	 * @return array
	 */
	public function get_countries(){
		$countries = (empty($this->countries)) ? $this->load_countries() : $this->countries;
		return $countries;
	}

	/**
	 * Get country list from file
	 *
	 * @return array
	 */
	private function load_countries(){
		$file = plugin_dir_path(__FILE__) . '/countries/countries.json';
		$countries = file_get_contents($file);
		$countries = json_decode($countries, true);
		$this->countries = $countries;
		return $countries;
	}

	/**
	 * Returns a two-letter country code list
	 * @return array
	 */
	private function get_country_codes(){
		$countries = array('AF','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BQ','BA','BW','BV','BR','IO','BN','BG','BF','BI','CV','KH','CM','CA','KY','CF','TD','CL','CN','CX','CC','CO','KM','CD','CG','CK','CR','HR','CU','CW','CY','CZ','CI','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','SZ','ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KP','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','NL','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','MK','RO','RU','RW','RE','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SX','SK','SI','SB','SO','ZA','GS','SS','ES','LK','SD','SR','SJ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','UM','US','UY','UZ','VU','VE','VN','VG','VI','WF','EH','YE','ZM','ZW','AX');
		return $countries;
	}

	/**
	 * Get System settings
	 *
	 * @return array
	 */
	public function get_settings(){
		$settings = (empty($this->settings)) ? $this->get_default() : $this->settings;
		return $settings;
	}

	/**
	 * Return dynamically determined settings
	 *
	 * @return void
	 */
	public function get_dynamic(){
		if(empty($this->dynamic)){
			$reflector = new \ReflectionFunction('wp_mail');
			$origin = $reflector->getFileName();
			$core = ABSPATH . WPINC . '/pluggable.php';
			$this->dynamic['mail/overridden'] = ($origin !== $core);
		}

		return $this->dynamic;
	}

	/**
	 * Validate and sanitize the settings for any malicious behavior
	 *
	 * @return array
	 */
	public function sanitize($settings){
		$default = $this->get_default();
		$valid_countries = $this->get_country_codes();
		$valid_plugins = null;

		foreach($settings as $setting => $value){
			if(!isset($this->supported[$setting])){
				// Something wrong. Saved settings may have some externally added data. Abort here.
				unset($settings[$setting]);

				continue;
			}

			// Validate that the values match the required formats
			switch($this->supported[$setting]['type']){
				case 'bool':
					if(($value === true || $value == 'true') || ($value === false || $value == 'false')){
						continue 2;
					}
					else{
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'string':
					// Empty values can be skipped
					if(empty($value)){
						continue 2;
					}

					// Temporary sanitation for wp/robots (which is not currently used)
					// Remove any value that is assigned to wp/robots
					// Once wp/robots is supported, remove this sanitation
					if($setting == 'wp/robots' && !empty($value)){
						$settings[$setting] = '';
					}

					// Verify that value is a string
					if(!is_string($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'email':
					// Empty values can be skipped
					if(empty($value)){
						continue 2;
					}

					// Verify that value is a string
					if(!is_string($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}

					// Verify that value is an email
					if(!is_email($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'regex':
					// Empty values can be skipped
					if(empty($value)){
						continue 2;
					}

					// Verify that value is a string
					if(!is_string($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}

					// Validate the value against the pattern
					if(!isset($this->supported[$setting]['regex'])){ // no pattern is specified
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					else{
						$regex = $this->supported[$setting]['regex'];
						$negate = isset($this->supported[$setting]['negate']) ? $this->supported[$setting]['negate'] : false;
						if($negate){
							if(preg_match($regex, $value) === 1){ // if it matches the pattern, it is bad
								// Bad value, pull from defaults
								$settings[$setting] = $default[$setting];
							}
						}
						elseif(preg_match($regex, $value) === 0){ // if it does not match the pattern, it is bad
							// Bad value, pull from defaults
							$settings[$setting] = $default[$setting];
						}
					}
					break;
				case 'int':
					// Verify that the value is digits (either int or string with only digits)
					if(!is_int($value) && !ctype_digit($value . '')){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'int/range':
					// Verify that the value is digits (either int or string with only digits)
					if(!is_int($value) && !ctype_digit($value . '')){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					// There are no specified min/max values to check against
					elseif(
						!isset($this->supported[$setting]['min']) ||
						empty($this->supported[$setting]['min']) ||
						!isset($this->supported[$setting]['max']) ||
						empty($this->supported[$setting]['max'])
					){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					// We have what we need to validate, make sure int is in range
					else{
						if($value < $settings[$setting]['min'] || $value > $settings[$setting['max']]){
							// Bad value, pull from defaults
							$settings[$setting] = $default[$setting];
						}
					}
					break;
				case 'float':
					// Verify that the value is numeric
					if(!is_numeric($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'float/range':
					// Verify that the value is digits (either int or string with only digits)
					if(!is_numeric($value)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					// There are no specified min/max values to check against
					elseif(
						!isset($this->supported[$setting]['min']) ||
						empty($this->supported[$setting]['min']) ||
						!isset($this->supported[$setting]['max']) ||
						empty($this->supported[$setting]['max'])
					){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					// We have what we need to validate, make sure int is in range
					else{
						if($value < $settings[$setting]['min'] || $value > $settings[$setting['max']]){
							// Bad value, pull from defaults
							$settings[$setting] = $default[$setting];
						}
					}
					break;
				case 'enum':
					// If we have options to check again, verify that value is in the array
					if(isset($this->supported[$setting]['options']) && !empty($this->supported[$setting]['options'])){
						if(is_array($value)){
							foreach($value as $item) {
								if (!in_array($item, $this->supported[$setting]['options'])){
									// Bad value, pull from defaults
									$settings[$setting] = $default[$setting];
								}
							}
						}
						elseif(!in_array($value, $this->supported[$setting]['options'])){
							// Bad value, pull from defaults
							$settings[$setting] = $default[$setting];
						}
					}
					// No options to validate against so set the value to default
					else{
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				case 'plugins':
					// Empty values can be skipped
					if(empty($value)){
						continue 2;
					}

					// initialise the plugins list
					if(is_null($valid_plugins)){
						$valid_plugins = \get_plugins();
					}

					// convert the value to an array
					$value_list = explode(',', $value);
					foreach($value_list as $param_val){
						if(!array_key_exists($param_val, $valid_plugins)){
							$settings[$setting] = $default[$setting];
						}
					}
					break;
				case 'country':
					// check against valid country list
					if(is_array($value)){
						foreach($value as $item){
							if(!in_array($item, $valid_countries)){
								// Bad value, pull from defaults
								$settings[$setting] = $default[$setting];
							}
						}
					}
					elseif(!in_array($value, $valid_countries)){
						// Bad value, pull from defaults
						$settings[$setting] = $default[$setting];
					}
					break;
				default:
					break;
			}
		}

		return $settings;
	}

	/**
	 * Get a default settings array
	 *
	 * @return array
	 */
	private function get_default(){
		preg_match('#[0-9]{1,}\.[0-9]{1,}#', phpversion(), $version);
		$default = array(
			'schema' => 1,
			'cache/post' => 'default',
			'cache/page' => 'default',
			'wp/cron' => 3600,
			'wp/cron/web' => (defined('DISABLE_WP_CRON')) ? !(DISABLE_WP_CRON) : true,
			'wp/heartbeat/frequency' => 60,
			'wp/heartbeat/limits' => 'post-edit',
			'wp/image/editor' => 'imagick',
			'wp/translation' => false,
			'wp/editor' => (defined('DISALLOW_FILE_EDIT')) ? DISALLOW_FILE_EDIT : false,
			'wp/updates/core' => false,
			'wp/updates/plugins' => false,
			'wp/updates/exclusions' => false,
			'wp/updates/exclusions/list' => '',
			'wp/robots' => '',
			'https/force' => false,
			'https/fix' => false,
			'comments/block' => false,
			'comments/url' => false,
			'trackbacks/block' => false,
			'pingbacks/block' => false,
			'bots/ahrefs' => true,
			'bots/moz' => true,
			'bots/semrush' => true,
			'bots/screaming-frog' => true,
			'bots/majestic' => true,
			'bots/dataforseo' => true,
			'bots/raven' => true,
			'bots/yandex' => true,
			'bots/baidu' => true,
			'bots/huawei' => true,
			'bots/seznam' => true,
			'bots/mailru' => true,
			'bots/qwant' => true,
			'bots/sogou' => true,
			'bots/coccoc' => true,
			'bots/gptbot' => true,
			'bots/google-extended' => true,
			'bots/google-other' => true,
			'bots/amazonbot' => true,
			'bots/anthropic-ai' => true,
			'bots/applebot' => true,
			'bots/ccbot' => true,
			'bots/facebookbot' => true,
			'images/optimize' => 'off',
			'images/frequency' => 'daily',
			'images/quality/type' => 'lossless',
			'images/quality/setting' => 85,
			'images/opaque' => false,
			'images/resize' => false,
			'images/width/threshold' => 0,
			'images/height/threshold' => 0,
			'database/optimize' => 'off',
			'database/frequency' => 'monthly',
			'database/posts/revisions' => false,
			'database/posts/autodrafts' => false,
			'database/posts/orphans' => false,
			'database/posts/trashed' => false,
			'database/comments/trashed' => false,
			'database/comments/spam' => false,
			'database/comments/unapproved' => false,
			'database/comments/orphans' => false,
			'database/taxonomy/orphans' => false,
			'database/users/orphans' => false,
			'database/transients' => false,
			'database/pingbacks' => false,
			'database/trackbacks' => false,
			'database/oembed' => false,
			'database/logs' => false,
			'bot/restrict' => false,
			'bot/register' => false,
			'bot/forms' => false,
			'bot/comments' => false,
			'login/protect' => false,
			'login/whitelist' => array(),
			'login/restrict' => false,
			'reset/whitelist' => array(),
			'reset/restrict' => false,
			'register/whitelist' => array(),
			'register/restrict' => false,
			'geo/restrict' => false,
			'comment/whitelist' => array(),
			'comment/restrict' => false,
			'forms/whitelist' => array(),
			'forms/restrict' => false,
			'mail/name' => '',
			'mail/email' => '',
			'mail/force' => false,
		);

		return $default;
	}

	/**
	 * Determine the changes (if any) between saved and new values
	 *
	 * @param array $settings
	 * @return array
	 */
	public function diff($settings){
		// Compare values to see what changed
		// Ignore values that are arrays since PHP doesn't handle those well
		// Will require separate comparison for array value fields
		$diff = array();

		foreach($settings as $key => $value){
			if(array_key_exists($key, $this->settings)){
				if($this->settings[$key] != $value){
					$diff[$key] = $value;
				}
			}
		}

		return $diff;
	}

	/**
	 * Save changes to settings
	 * Assumes that any passed data has already been validated
	 *
	 * @param array $data
	 * @return void
	 */
	public function save($data){
		$purge = '';

		// Update the settings that changed
		foreach($data as $key => $value){
			if(in_array($key, array_keys($this->settings))){
				// If the changes involve different values for purgeable settings, trigger a cache purge
				if(in_array($key, array_keys($this->purgeable))){
					if($this->settings[$key] != $value){
						$purge = ($purge == 'all') ? $purge : $this->purgeable[$key];
					}
				}

				// Fix for boolean string evaluations
				if(in_array($value, ['true', 'false'])) {
					$value = $value == 'true' ? true : ($value == 'false' ? false : false);
				}

				// Switch whitelist csv strings to arrays
				switch($key){
					case 'login/whitelist':
					case 'reset/whitelist':
					case 'register/whitelist':
					case 'comment/whitelist':
					case 'forms/whitelist':
						$value = is_string($value) ? explode(',', $value) : $value;
						break;
					default:
						break;
				}

				$this->settings[$key] = $value;
			}
		}

		$login_whitelist_merger = array('login/whitelist' => array(), 'reset/whitelist' => array(), 'register/whitelist' => array());
		$geo_whitelist_merger = array('comment/whitelist' => array(), 'forms/whitelist' => array());

		// Merge whitelist arrays of login protect
		if(!empty($this->settings['login/protect']) && $this->settings['login/protect'] == true){
			foreach($login_whitelist_merger as $key => $value ){
				$restrict_key = str_replace('whitelist', 'restrict', $key);

				if($this->settings[$restrict_key] == true && (!empty($this->settings[$key]) || count($this->settings[$key]) > 0)){
					$login_whitelist_merger[$key] = $this->settings[$key];
				}
			}

			// Merge whitelist arrays of login protect
			$login_whitelist_merged = array();
			foreach($login_whitelist_merger as $arr){
				if(empty($login_whitelist_merged)){
					$login_whitelist_merged = $arr;
					continue;
				}

				if(array_diff($login_whitelist_merged, $arr) || array_diff($arr, $login_whitelist_merged)){
					$login_whitelist_merged = array_values(array_unique(array_merge($login_whitelist_merged, $arr)));
				}
			}

			// Re-index whitelist arrays
			$this->settings['login/whitelist'] = $login_whitelist_merged;
			$this->settings['reset/whitelist'] = $login_whitelist_merged;
			$this->settings['register/whitelist'] = $login_whitelist_merged;
		}

		// Merge whitelist arrays of geo restrict
		if(!empty($this->settings['geo/restrict']) == true){
			foreach($geo_whitelist_merger as $key => $value){
				$restrict_key = str_replace('whitelist', 'restrict', $key);

				if($this->settings[$restrict_key] == true && (!empty($this->settings[$key]) || count($this->settings[$key]) > 0)){
					$geo_whitelist_merger[$key] = $this->settings[$key];
				}
			}

			// Merge whitelist arrays of geo restrict
			$geo_whitelist_merged = array();
			foreach($geo_whitelist_merger as $arr){
				if(empty($geo_whitelist_merged)){
					$geo_whitelist_merged = $arr;
					continue;
				}

				if(array_diff($geo_whitelist_merged, $arr) || array_diff($arr, $geo_whitelist_merged)){
					$geo_whitelist_merged = array_values(array_unique(array_merge($geo_whitelist_merged, $arr)));
				}
			}

			// Re-index whitelist arrays
			$this->settings['comment/whitelist'] = $geo_whitelist_merged;
			$this->settings['forms/whitelist'] = $geo_whitelist_merged;
		}

		// Validate and sanitize settings
		$this->settings = $this->sanitize($this->settings);

		// Execute the designated cache purge
		switch($purge){
			case 'all':
				Purge::purge_cache();
				break;
			case 'robots':
				$home = trim(preg_replace('#http[s]?://#', '', get_option('home')), '/');
				Purge::purge_single('https://' . $home . '/robots.txt');
				Purge::purge_single('http://' . $home . '/robots.txt');
				break;
			default:
				break;
		}

		// Save the settings
		if((defined('MULTISITE') && MULTISITE === true)){
			update_site_option('wordkeeper/system', $this->settings);
		}
		else{
			update_option('wordkeeper/system', $this->settings);
		}
	}

	/**
	 * Wakeup magic method.
	 */
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}