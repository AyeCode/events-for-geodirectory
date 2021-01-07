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
 * Version:           2.1.0.2
 * Requires at least: 4.9
 * Requires PHP:      5.6
 * Author:            AyeCode Ltd
 * Author URI:        https://ayecode.io
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       geodirevents
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'GEODIR_EVENT_VERSION' ) ) {
	define( 'GEODIR_EVENT_VERSION', '2.1.0.2' );
}

if ( ! defined( 'GEODIR_EVENT_MIN_CORE' ) ) {
	define( 'GEODIR_EVENT_MIN_CORE', '2.1.0.0' );
}

global $geodir_event_manager_file;

$geodir_event_manager_file = __FILE__;

/**
 * Setup plugin rename.
 */
$_event_current_name = 'geodir_event_manager';
$_event_new_name = 'events-for-geodirectory';

if ( strpos( $geodir_event_manager_file, $_event_current_name ) !== false && is_admin() ) {
	require_once( dirname( $geodir_event_manager_file ) . '/includes/rename-plugin-functions.php' );

	try {
		geodir_event_rename_plugin( $_event_current_name, $_event_new_name );
	} catch( Exception $error ) { }
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
	global $geodir_event_manager, $geodir_event_manager_file;

	if ( ! defined( 'GEODIR_EVENT_PLUGIN_FILE' ) ) {
		define( 'GEODIR_EVENT_PLUGIN_FILE', $geodir_event_manager_file );
	}

	// min core version check
	if( !function_exists("geodir_min_version_check") || !geodir_min_version_check("Events Manager",GEODIR_EVENT_MIN_CORE)){
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
