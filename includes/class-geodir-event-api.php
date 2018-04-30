<?php
/**
 * GeoDirectory Event Manager API
 *
 * Handles GD-API endpoint requests.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDir_Event_Manager/API
 * @since    2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Event_API {

	/**
	 * Setup class.
	 * @since 2.0
	 */
	public function __construct() {
	}

	public static function init() {
		add_filter( 'rest_gd_event_collection_params', array( __CLASS__, 'event_collection_params' ), 10, 2 );
		add_filter( 'rest_gd_event_query', array( __CLASS__, 'event_query' ), 10, 2 );
		add_filter( 'geodir_rest_post_custom_fields_schema', array( __CLASS__, 'event_feild_schema' ), 10, 6 );
		add_filter( 'geodir_listing_item_schema', array( __CLASS__, 'event_item_schema' ), 10, 6 );
		add_filter( 'geodir_rest_get_post_data', array( __CLASS__, 'event_post_data' ), 10, 4 );
	}

	public static function event_collection_params( $params, $post_type_obj ) {
		$params['event_type'] = array(
			'description'        => __( 'Filter the events to show.', 'geodirevents' ),
			'type'               => 'string',
			'default'            => geodir_get_option( 'event_defalt_filter' ),
			'enum'               => array_keys( geodir_event_filter_options() ),
		);
		$params['single_event'] = array(
			'description'        => __( 'Show single listing for recurring event.', 'geodirevents' ),
			'type'               => 'boolean',
			'default'            => false,
		);
		return $params;
	}

	public static function event_query( $args, $request ) {
		$mappings = array(
			'event_type'    => 'gd_event_type',
			'single_event' 	=> 'single_event'
		);
		$collection_params = self::event_collection_params( array(), array() ) ;

		foreach ( $collection_params as $key => $param ) {
			if ( isset( $request[ $key ] ) ) {
				$field = isset( $mappings ) ? $mappings[ $key ] : $key;
				$args[ $field ] = $request[ $key ];
			}
		}
		return $args;
	}

	public static function event_feild_schema( $args, $post_type, $field, $custom_fields, $package_id, $default ) {
		$empty = array();
		if ( $post_type != 'gd_event' ) {
			return $empty;
		}
		if ( $field['name'] == 'link_business' ) {
			$args['type']   = geodir_rest_data_type_to_field_type( $field['data_type'] );
		} else if ( $field['name'] == 'recurring' ) {
			$args['type']   = 'integer';
		} else if ( $field['name'] == 'event_dates' ) {
			$args['type']   = 'object';
		} else {
			return $empty;
		}

		return $args;
	}

	public static function event_item_schema( $schema, $post_type, $package_id, $default ) {
		if ( $post_type != 'gd_event' ) {
			return $schema;
		}

		$new_schema = array();
		foreach ( $schema as $key => $data ) {
			if ( $key == 'event_dates' ) {
				$new_schema[ $key ] = $data;
				$new_schema['start_date'] = array(
					'description'  => __( "Event start date, in the site's timezone." ),
					'type'         => 'string',
					'format'       => 'date',
					'context'      => array( 'view', 'edit', 'embed' ),
					'field_type'   => 'event'
				);
				$new_schema['start_time'] = array(
					'description'  => __( "Event start time." ),
					'type'         => 'string',
					'format'       => 'time',
					'context'      => array( 'view', 'edit', 'embed' ),
					'field_type'   => 'event'
				);
				$new_schema['end_date'] = array(
					'description'  => __( "Event end date, in the site's timezone." ),
					'type'         => 'string',
					'format'       => 'date',
					'context'      => array( 'view', 'edit', 'embed' ),
					'field_type'   => 'event'
				);
				$new_schema['end_time'] = array(
					'description'  => __( "Event end time." ),
					'type'         => 'string',
					'format'       => 'time',
					'context'      => array( 'view', 'edit', 'embed' ),
					'field_type'   => 'event'
				);
				$new_schema['all_day'] = array(
					'description'  => __( "All day event?" ),
					'type'         => 'boolean',
					'context'      => array( 'view', 'edit', 'embed' ),
					'field_type'   => 'event'
				);
			}
		}

		return $new_schema;
	}

	public static function event_post_data( $data, $post, $request, $controller ) {
		if ( isset( $post->start_date ) && isset( $post->end_date ) ) {
			$date_format = geodir_event_date_time_format();

			$start_date = $post->start_date;
			$end_date = $post->end_date;
			$start_time = $post->start_time;
			$end_time = $post->end_time;
			if ( ! empty( $post->all_day ) ) {
				$start_time = '00:00:00';
				$end_time = '00:00:00';
				$data['start_time'] = $start_time;
				$data['end_time'] = $end_time;
			}

			$data['event_dates'] = array(
				'start_datetime' => array(
					'raw'		=> self::prepare_date_response( $start_date . ' '. $start_time ),
					'rendered' 	=> date_i18n( $date_format, strtotime( $start_date . ' '. $start_time ) )
				),
				'end_datetime' => array(
					'raw'		=> self::prepare_date_response( $end_date . ' '. $end_time ),
					'rendered' 	=> date_i18n( $date_format, strtotime( $end_date . ' '. $end_time ) )
				)
			);
		}
		return $data;
	}

	public static function prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	}

}
