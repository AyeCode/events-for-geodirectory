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
			'ayi_action'			=> false,
			'ajax_calendar'			=> true,
			'widget_post_type_field_options' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

			// GeoDir AJAX can be used for frontend ajax requests.
			add_action( 'geodir_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );
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

	public static function widget_post_type_field_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		try {
			$post_type = ! empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

			$category_options = '';
			if ( $categories = geodir_category_options( $post_type ) ) {
				foreach ( $categories as $value => $name ) {
					$category_options .= '<option value="' . $value . '">' . $name . '</option>';
				}
			}

			$sort_by_options = '';
			if ( $sort_by = geodir_sort_by_options( $post_type ) ) {
				foreach ( $sort_by as $value => $name ) {
					$sort_by_options .= '<option value="' . $value . '">' . $name . '</option>';
				}
			}

			$data = array( 
				'category' => array( 
					'options' => $category_options 
				),
				'sort_by' => array( 
					'options' => $sort_by_options 
				)
			);

			$data = apply_filters( 'geodir_widget_post_type_field_options', $data, $post_type );

			wp_send_json_success( $data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}