<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use apply_filters;
use array_keys;
use class_exists;
use count;
use function_exists;
use header;
use implode;
use is_archive;
use is_array;
use is_search;
use is_string;
use ltrim;
use parse_url;
use rtrim;
use str_replace;
use strpos;
use strtotime;
use substr;
use wc_get_page_permalink;
use wp_deregister_script;

/**
 * The public-facing functionality of the plugin
 *
 */
class Front {

	/**
	 * The settings for this plugin
	 *
	 * @access   private
	 * @var      string    $settings    The settings for the plugin
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties
	 */
	public function __construct(){
		Settings::get_instance();
		$this->settings = Settings::get_instance()->get_settings();
	}


	/**
	 * Register the stylesheets for the public-facing side of the site
	 */
	public function enqueue_styles(){
		// There are no public styles for this
		// wp_enqueue_style($this->wordkeeper_system, plugin_dir_url(__FILE__) . 'css/front.css', array(), $this->version, 'all');
	}


	/**
	 * Register the stylesheets for the public-facing side of the site
	 */
	public function enqueue_scripts(){
		// There are no public scripts for this
		// wp_enqueue_script($this->wordkeeper_system, plugin_dir_url(__FILE__) . 'js/front.js', array('jquery'), $this->version, false);
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
