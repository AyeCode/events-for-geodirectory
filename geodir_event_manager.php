<?php
/**
 * GeoDirectory Events
 *
 * @package           GeoDir_Event_Manager
 * @author            AyeCode Ltd
 * @copyright         2019 AyeCode Ltd
 * @license           GPLv3
 *
 * @wordpress-plugin
 * Plugin Name:       GeoDirectory Events
 * Plugin URI:        https://wpgeodirectory.com/downloads/events/
 * Description:       Events add-on allows to extend your GeoDirectory with a versatile event manager.
 * Version:           2.0.1.0
 * Requires at least: 4.9
 * Requires PHP:      5.6
 * Author:            AyeCode Ltd
 * Author URI:        https://ayecode.io
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       geodirevents
 * Domain Path:       /languages
 * Update URL:        https://wpgeodirectory.com
 * Update ID:         65116
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'GEODIR_EVENT_VERSION' ) ) {
	define( 'GEODIR_EVENT_VERSION', '2.0.1.0' );
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
