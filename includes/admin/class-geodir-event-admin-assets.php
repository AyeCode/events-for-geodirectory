<?php
/**
 * Load admin assets
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDir_Event_Manager/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Event_Admin_Assets', false ) ) {

/**
 * GeoDir_Event_Admin_Assets Class.
 */
class GeoDir_Event_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10 );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		global $post, $pagenow;
		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';

		// Register admin styles
		// YUI Calendar
		wp_register_style( 'yui-calendar', GEODIR_EVENT_PLUGIN_URL . '/assets/yui/calendar.css', array(), '2.9.0' );
		wp_register_style( 'geodir-event-admin', GEODIR_EVENT_PLUGIN_URL . '/assets/css/admin.css', array(), GEODIR_EVENT_VERSION );

		// Admin styles for GD pages only
		if ( in_array( $screen_id, geodir_get_screen_ids() ) ) {
			if ( ( 'edit.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' == $pagenow ) && ! empty( $post->post_type ) && GeoDir_Post_types::supports( $post->post_type, 'events' ) ) {
				wp_enqueue_style( 'yui-calendar' );
			}

			wp_enqueue_style( 'geodir-event-admin' );
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $post, $pagenow;

		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';

		$suffix       	= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		// Register scripts
		// YUI Calendar
		wp_register_script( 'yui-calendar', GEODIR_EVENT_PLUGIN_URL . '/assets/yui/calendar.min.js', array( 'jquery' ), '2.9.0' );
		wp_register_script( 'geodir-event', GEODIR_EVENT_PLUGIN_URL . '/assets/js/common' . $suffix . '.js', array( 'jquery', 'geodir-admin-script' ), GEODIR_EVENT_VERSION );
		wp_register_script( 'geodir-event-admin', GEODIR_EVENT_PLUGIN_URL . '/assets/js/admin' . $suffix . '.js', array( 'jquery', 'geodir-event' ), GEODIR_EVENT_VERSION );
		wp_register_script( 'geodir-event-widget', GEODIR_EVENT_PLUGIN_URL . '/assets/js/widget' . $suffix . '.js', array( 'jquery' ), GEODIR_EVENT_VERSION );

		// Admin scripts for GD pages only
		if ( in_array( $screen_id, geodir_get_screen_ids() ) ) {
			if ( ( 'edit.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' == $pagenow ) && ! empty( $post->post_type ) && GeoDir_Post_types::supports( $post->post_type, 'events' ) ) {
				// YUI Calendar
				wp_enqueue_script( 'yui-calendar' );
				wp_localize_script( 'yui-calendar', 'cal_trans', geodir_event_yui_calendar_params() );
			}

			wp_enqueue_script( 'geodir-event' );
			wp_localize_script( 'geodir-event', 'geodir_event_params', geodir_event_params() );
			wp_enqueue_script( 'geodir-event-admin' );
			wp_localize_script( 'geodir-event-admin', 'geodir_event_admin_params', geodir_event_admin_params() );
		}

		// Script for backend widgets page only
		if ( $screen_id == 'widgets' ) {
			wp_enqueue_script( 'geodir-event-widget' );
		}
	}
}
}

return new GeoDir_Event_Admin_Assets();