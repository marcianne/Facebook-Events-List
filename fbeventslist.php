<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.marcianneoday.com
 * @since             1.0.0
 * @package           Fbeventslist
 *
 * @wordpress-plugin
 * Plugin Name:       Facebook Events List
 * Plugin URI:        https://github.com/marcianne/Facebook-Events-List
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Marcianne O'Day
 * Author URI:        http://www.marcianneoday.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fbeventslist
 * Domain Path:       /languages
 * GitHub Plugin URI: marcianne/Facebook-Events-List
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fbeventslist-activator.php
 */
function activate_fbeventslist() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fbeventslist-activator.php';
	Fbeventslist_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fbeventslist-deactivator.php
 */
function deactivate_fbeventslist() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fbeventslist-deactivator.php';
	Fbeventslist_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fbeventslist' );
register_deactivation_hook( __FILE__, 'deactivate_fbeventslist' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fbeventslist.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fbeventslist() {

	$plugin = new Fbeventslist();
	$plugin->run();

}
run_fbeventslist();
