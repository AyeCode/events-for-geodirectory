<?php
/**
 * GeoDirectory Events Admin
 *
 * @class    GeoDir_Event_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDir_Event_Manager/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_Admin class.
 */
class GeoDir_Event_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_filter( 'geodir_get_settings_pages', array( $this, 'load_settings_page' ), 9, 1 );
		add_filter( 'parent_file', array( $this, 'set_parent_file' ), 10, 1 );
		add_filter( 'geodir_cat_schemas', 'geodir_event_filter_schemas', 10, 1 );
		add_filter( 'geodir_add_custom_sort_options', 'geodir_event_custom_sort_options', 10, 2 );
	}

	/**
	 * Highlights the correct top level admin menu item for post type add screens.
	 */
	public function set_parent_file( $parent_file ) {
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'gd-cpt-settings' && ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'gd_event' ) {
			$parent_file  = 'edit.php?post_type=gd_event';
		}
		return $parent_file;
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/class-geodir-event-admin-assets.php' );
		include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/class-geodir-event-admin-import-export.php' );
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Nonced plugin install redirects (whitelisted)
		if ( ! empty( $_GET['geodir-event-install-redirect'] ) ) {
			$plugin_slug = geodir_clean( $_GET['geodir-event-install-redirect'] );

			$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );

			wp_safe_redirect( $url );
			exit;
		}

		// Setup wizard redirect
		if ( get_transient( '_geodir_event_activation_redirect' ) ) {
			delete_transient( '_geodir_event_activation_redirect' );
		}
	}

	public static function load_settings_page( $settings_pages ) {
		if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'gd-cpt-settings' && ! empty( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'gd_event' ) {
			$settings_pages[] = include( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/settings/class-geodir-event-settings-cpt-general.php' );
		}

		return $settings_pages;
	}
}