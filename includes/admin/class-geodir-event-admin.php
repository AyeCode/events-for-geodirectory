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
		global $pagenow;

		$post_action = ! empty( $_POST['action'] ) ? $_POST['action'] : '';

		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_filter( 'geodir_get_settings_pages', array( $this, 'load_settings_page' ), 10.2, 1 );
		add_filter( 'geodir_cat_schemas', 'geodir_event_filter_schemas', 10, 1 );
		add_filter( 'geodir_add_custom_sort_options', 'geodir_event_custom_sort_options', 10, 2 );
		add_filter( 'geodir_uninstall_options', 'geodir_event_uninstall_settings', 10, 1 );
		add_action( 'geodir_pricing_package_settings', 'geodir_event_pricing_package_settings', 9, 2 );
		add_action( 'geodir_pricing_process_data_for_save', 'geodir_event_pricing_process_data_for_save', 1, 3 );

		// Dummy data
		add_filter( 'geodir_dummy_data_types' , array( 'GeoDir_Event_Admin_Dummy_Data', 'dummy_data_types' ), 10, 2 );
		add_action( 'geodir_dummy_data_include_file' , array( 'GeoDir_Event_Admin_Dummy_Data', 'include_file' ), 10, 4 );

		// Add the required DB columns
		add_filter('geodir_db_cpt_default_columns', array(__CLASS__,'add_db_columns'),10,3);
	}

	/**
	 * Add the event column to the CPTs tables if events are enabled.
	 *
	 * @param $columns
	 * @param $cpt
	 *
	 * @return mixed
	 */
	public static function add_db_columns($columns,$cpt,$post_type){

		// check if ratings are disabled on the CPT first.
		if(isset($cpt['supports_events']) && $cpt['supports_events']){
			$columns['recurring'] = "recurring TINYINT(1) DEFAULT '0'";
			$columns['event_dates'] = "event_dates TEXT NOT NULL";
			$columns['rsvp_count'] = "rsvp_count INT(11) DEFAULT '0'";
		}

		return $columns;
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
		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'gd_place';

		if ( ! ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == $post_type . '-settings' ) ) {
			$settings_pages[] = include( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/settings/class-geodir-event-settings-events.php' );
		}

		return $settings_pages;
	}
}