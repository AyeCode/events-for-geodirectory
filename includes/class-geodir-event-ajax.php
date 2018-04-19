<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Events AJAX class.
 *
 * AJAX Event Handler.
 *
 * @class    GeoDir_Event_AJAX
 * @package  GeoDirectory_Event_Manager/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_Event_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// geodirectory_EVENT => nopriv
		$ajax_events = array(
			'ayi_action'		=> false,
			'ajax_calendar'		=> true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GeoDir AJAX can be used for frontend ajax requests.
				add_action( 'geodir_event_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function ayi_action() {
		GeoDir_Event_AYI::ajax_ayi_action();
		exit;
	}

	public static function ajax_calendar() {
		GeoDir_Event_Calendar::ajax_calendar();
		exit;
	}
}