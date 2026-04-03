<?php

namespace WordKeeper\System;

class Heartbeat{

	private $settings;


	/**
	 * Constructor
	 */
	public function __construct(){
		$settings = Settings::get_instance()->get_settings();

		// Filter the settings down to just bots settings
		$this->settings = array_filter($settings, function($key){
			return (strpos($key, 'wp/heartbeat') === 0);
		}, ARRAY_FILTER_USE_KEY);

		// Sort by key to maintain a consistent serialization check
		ksort($this->settings);
	}


	/**
	 * All configuration of where WP's core heartbeat functionality is allowed to operate
	 *
	 * @access public
	 * @return void
	 */
	public function limit(){
		global $pagenow;

		switch($this->settings['wp/heartbeat/limits']){
			case 'off':
				wp_deregister_script('heartbeat');
				break;
			case 'dashboard':
				if($pagenow == 'index.php'){
					wp_deregister_script('heartbeat');
				}
				break;
			case 'post-edit':
				if($pagenow != 'post.php' && $pagenow != 'post-new.php'){
					wp_deregister_script('heartbeat');
				}
			break;
		default:
			break;
		}
	}


	/**
	 * Sets the frequency allowed for WP's core heartbeat functionality
	 *
	 * @access public
	 * @param mixed $settings
	 * @return void
	 */
	public function frequency($interval){
		if(!empty($this->settings['wp/heartbeat/frequency'])){
			$frequency = (int) $this->settings['wp/heartbeat/frequency'];

			// Change interval for supported values
			switch($frequency){
				case 30:
				case 60:
				case 300:
					$interval['interval'] = $frequency;
					break;
				default:
					break;
			}
		}

		return $interval;
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}