<?php

namespace WordKeeper\System;

/**
 *
 * @wordpress-plugin
 * Plugin Name:			WordKeeper System
 * Plugin URI:			https://wordkeeper.com/plugins/wordkeeper-system
 * Description:			WordKeeper hosting management plugin
 * Version:				2.1.1
 * Author:				WordKeeper
 * Author URI:			https://wordkeeper.com/
 * License:				GPL-2.0+
 * License URI:			http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:			wordkeeper-system
 * Domain Path:			/languages
 */

// If this file is called directly, abort
if (! defined('WPINC')){
	die;
}

/**
 * The code that runs during plugin activation
 * This action is documented in includes/class-wordkeeper-system-activator.php
 */
function activate_wordkeeper_system(){
	require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
	Activator::activate();
}

// Register activation hook
register_activation_hook(__FILE__, function(){
	require_once plugin_dir_path(__FILE__) . 'includes/class-activator.php';
	Activator::activate();
});

// Register deactivation hook
register_deactivation_hook(__FILE__, function(){
	require_once plugin_dir_path(__FILE__) . 'includes/class-deactivator.php';
	Deactivator::deactivate();
});

// Class for miscellaneous utility functions
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

/**
 * The core app class to initialize the plugin
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-app.php';

/**
 * Begins execution of the plugin
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle
 *
 */
function run_wordkeeper_system(){
	$plugin = new App();
	$plugin->run();
}

run_wordkeeper_system();
