<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * this starts the plugin.
 *
 * @since             1.0.0
 * @package           GeoDir_Event_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       GeoDirectory Events
 * Plugin URI:        http://wpgeodirectory.com/
 * Description:       GeoDirectory Events allows you to extend your GeoDirectory with a versatile event manager.
 * Version:           2.0.0.1-rc
 * Author:            GeoDirectory
 * Author URI:        https://wpgeodirectory.com/
 * Requires at least: 4.9
 * Tested up to:      4.9.9
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       geodirevents
 * Domain Path:       /languages
 * Update URL:        https://wpgeodirectory.com
 * Update ID:         65116
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( !defined( 'GEODIR_EVENT_VERSION' ) ) {
	define( 'GEODIR_EVENT_VERSION', '2.0.0.0-beta' );
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function GeoDir_Event() {
    global $geodir_event_manager;

	if ( !defined( 'GEODIR_EVENT_PLUGIN_FILE' ) ) {
		define( 'GEODIR_EVENT_PLUGIN_FILE', __FILE__ );
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * dashboard-specific hooks, and public-facing site hooks.
	 */
	require_once ( plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ) . 'includes/class-geodir-event-manager.php' );

    return $geodir_event_manager = GeoDir_Event_Manager::instance();
}
add_action( 'geodirectory_loaded', 'GeoDir_Event' );
