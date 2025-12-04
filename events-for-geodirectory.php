<?php
/**
 * Events for GeoDirectory
 *
 * @package           GeoDir_Event_Manager
 * @author            AyeCode Ltd
 * @copyright         2019 AyeCode Ltd
 * @license           GPLv3
 *
 * @wordpress-plugin
 * Plugin Name:       Events for GeoDirectory
 * Plugin URI:        https://wpgeodirectory.com/downloads/events/
 * Description:       Events add-on allows to extend your GeoDirectory with a versatile event manager.
 * Version:           2.3.24
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            AyeCode Ltd
 * Author URI:        https://ayecode.io
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Requires Plugins:  geodirectory
 * Text Domain:       geodirevents
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'GEODIR_EVENT_VERSION' ) ) {
	define( 'GEODIR_EVENT_VERSION', '2.3.24' );
}

if ( ! defined( 'GEODIR_EVENT_MIN_CORE' ) ) {
	define( 'GEODIR_EVENT_MIN_CORE', '2.3' );
}

/**
 * Check & install GeoDirectory core plugin.
 */
if ( ! class_exists( 'GeoDirectory' ) ) {
	/**
	 * Include TGM Plugin Activation library to register required plugins.
	 */
	require_once( dirname( __FILE__ ) . '/includes/tgm-register-plugin.php' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-geodir-event-activator.php
 */
function activate_events_for_geodirectory( $network_wide = false ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-geodir-event-activator.php';

	GeoDir_Event_Activator::activate( $network_wide );
}
register_activation_hook( __FILE__, 'activate_events_for_geodirectory' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-geodir-event-activator.php
 */
function deactivate_events_for_geodirectory( $network_wide = false ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-geodir-event-activator.php';

	GeoDir_Event_Activator::deactivate( $network_wide );
}
register_deactivation_hook( __FILE__, 'deactivate_events_for_geodirectory' );

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

	if ( ! defined( 'GEODIR_EVENT_PLUGIN_FILE' ) ) {
		define( 'GEODIR_EVENT_PLUGIN_FILE', __FILE__ );
	}

	// Min core version check
	if ( ! function_exists( "geodir_min_version_check" ) || ! geodir_min_version_check( "Events Manager", GEODIR_EVENT_MIN_CORE ) ) {
		return '';
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * dashboard-specific hooks, and public-facing site hooks.
	 */
	require_once ( plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ) . 'includes/class-geodir-event-manager.php' );

    return $geodir_event_manager = GeoDir_Event_Manager::instance();
}
add_action( 'geodirectory_loaded', 'GeoDir_Event' );
