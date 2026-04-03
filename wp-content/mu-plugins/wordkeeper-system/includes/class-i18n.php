<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use dirname;
use plugin_basename;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation
 *
 */
class i18n {

	/**
	 * The domain specified for this plugin
	 *
	 * @access   private
	 * @var      string    $domain    The domain identifier for this plugin
	 */
	private $domain;

	/**
	 * Load the plugin text domain for translation
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			$this->domain,
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);

	}

	/**
	 * Set the domain equal to that of the specified domain
	 *
	 * @param    string    $domain    The domain that represents the locale of this plugin
	 */
	public function set_domain($domain) {
		$this->domain = $domain;
	}

	/**
	* Wakeup magic method.
	*/
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}
}
