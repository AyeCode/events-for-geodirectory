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
		$event_post_types = GeoDir_Event_Post_Type::get_event_post_types();

		foreach ( $event_post_types as $post_type ) {
			// Categories
			add_filter( 'rest_' . $post_type . 'category_collection_params', array( __CLASS__, 'taxonomy_collection_params' ), 11, 2 );
			add_filter( 'rest_' . $post_type . 'category_query', array( __CLASS__, 'rest_taxonomy_query' ), 11, 2 );
			add_filter( 'rest_prepare_' . $post_type . 'category', array( __CLASS__, 'rest_taxonomy_response' ), 11, 3 );
			add_filter( 'geodir_rest_' . $post_type . 'category_item_links', array( __CLASS__, 'rest_taxonomy_item_links' ), 11, 3 );

			// Tags
			add_filter( 'rest_' . $post_type . '_tags_collection_params', array( __CLASS__, 'taxonomy_collection_params' ), 11, 2 );
			add_filter( 'rest_' . $post_type . '_tags_query', array( __CLASS__, 'rest_taxonomy_query' ), 11, 2 );
			add_filter( 'rest_prepare_' . $post_type . '_tags', array( __CLASS__, 'rest_taxonomy_response' ), 11, 3 );
			add_filter( 'geodir_rest_' . $post_type . '_tags_item_links', array( __CLASS__, 'rest_taxonomy_item_links' ), 11, 3 );

			add_filter( 'rest_' . $post_type . '_collection_params', array( __CLASS__, 'event_collection_params' ), 10, 2 );
			add_filter( 'rest_' . $post_type . '_query', array( __CLASS__, 'event_query' ), 10, 2 );
		}

		add_filter( 'geodir_rest_post_custom_fields_schema', array( __CLASS__, 'event_feild_schema' ), 10, 6 );
		add_filter( 'geodir_rest_get_post_data', array( __CLASS__, 'event_post_data' ), 10, 4 );
	}

	public static function taxonomy_collection_params( $params, $taxonomy ) {
		global $geodir_event_manager;

		$post_type = $geodir_event_manager->query->get_taxonomy_post_type( $taxonomy->name );

		return self::event_collection_params( $params, get_post_type_object( $post_type ) );
	}

	public static function rest_taxonomy_query( $prepared_args, $request ) {
		global $wp, $geodirectory;

		$prepared_args['event_type'] = $request->get_param( 'event_type' );
		$prepared_args['single_event'] = (bool) $request->get_param( 'single_event' );

		return $prepared_args;
	}

	public static function event_collection_params( $params, $post_type_obj ) {
		if ( empty( $post_type_obj ) ) {
			return $params;
		}

		$params['event_type'] = array(
			'description'        => __( 'Filter the events to show.', 'geodirevents' ),
			'type'               => 'string',
			'default'            => geodir_get_option( 'event_default_filter' ) ? geodir_get_option( 'event_default_filter' ) : 'upcoming',
			'enum'               => array_keys( geodir_event_filter_options( $post_type_obj->name ) ),
		);
		$params['single_event'] = array(
			'description'        => __( 'Show single listing for recurring event.', 'geodirevents' ),
			'type'               => 'boolean',
			'default'            => false,
		);
		return $params;
	}

	public static function rest_taxonomy_response( $response, $item, $request ) {
		if ( ! empty( $response->data['link'] ) && ( ( $event_type = $request->get_param( 'event_type' ) ) || $request->get_param( 'id' ) ) ) {
			$default_filter = geodir_get_option( 'event_default_filter', 'upcoming' );

			if ( empty( $event_type ) ) {
				$event_type = $default_filter;
			}

			if ( $event_type != $default_filter ) {
				$response->data['link'] = add_query_arg( array( 'etype' => $event_type ), $response->data['link'] );
			}

			if ( $request->get_param( 'id' ) ) {
				$response->data['count'] = (int) GeoDir_Event_Query::get_term_count( $item->term_id, $item->taxonomy, array( 'event_type' => $event_type, 'single_event' => (bool) $request->get_param( 'single_event' ) ) );
			}
		}

		return $response;
	}

	public static function rest_taxonomy_item_links( $links, $item, $request = array() ) {
		if ( ! empty( $request ) && ( ( $event_type = $request->get_param( 'event_type' ) ) || $request->get_param( 'id' ) ) ) {
			$default_filter = geodir_get_option( 'event_default_filter', 'upcoming' );

			if ( empty( $event_type ) ) {
				$event_type = $default_filter;
			}

			if ( $event_type == $default_filter ) {
				return $links;
			}

			$args = array( 'event_type' => $event_type );

			if ( $request->get_param( 'single_event' ) ) {
				$args['single_event'] = true;
			}

			if ( ! empty( $links['self']['href'] ) ) {
				$links['self']['href'] = add_query_arg( $args, $links['self']['href'] );
			}

			if ( ! empty( $links['collection']['href'] ) ) {
				$links['collection']['href'] = add_query_arg( $args, $links['collection']['href'] );
			}
		}

		return $links;
	}

	public static function event_query( $args, $request ) {
		$mappings = array(
			'event_type'    => 'gd_event_type',
			'single_event' 	=> 'single_event'
		);

		$post_type_obj = ! empty( $args['post_type'] ) ? get_post_type_object( $args['post_type'] ) : array();

		$collection_params = self::event_collection_params( array(), $post_type_obj ) ;

		foreach ( $collection_params as $key => $param ) {
			if ( isset( $request[ $key ] ) ) {
				$field = isset( $mappings ) ? $mappings[ $key ] : $key;
				$args[ $field ] = $request[ $key ];
			}
		}
		return $args;
	}

	public static function event_feild_schema( $args, $post_type, $field, $custom_fields, $package_id, $default ) {
		if ( $field['type'] != 'event' ) {
			return $args;
		}

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return array();
		}

		if ( $field['name'] == 'recurring' ) {
			$args['type']   = 'integer';
		} else if ( $field['name'] == 'event_dates' ) {
			$times = array_keys( geodir_event_get_times() );

			$args['type']   = 'object';
			$args['properties'] = array(
				'start_date' => array(
					'description' => __( "Event start date (Y-m-d)." ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'format'       => 'date',
					'field_type'   => 'event'
				),
				'end_date' => array(
					'description' => __( "Event end date (Y-m-d)." ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'format'       => 'date',
					'field_type'   => 'event'
				),
				'duration_x' => array(
					'description' => __( "Event duration (days)." ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'field_type'   => 'event'
				),
				'repeat_type' => array(
					'description' => __( "Recurring type." ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'enum'		  => array( 'day', 'week', 'month', 'year', 'custom' ),
					'default'	  => 'custom',
					'field_type'  => 'event'
				),
				'repeat_x' => array(
					'description' => __( "Recurring interval" ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'enum'		  => range( 1, 30 ),
					'default'	  => 1,
					'field_type'  => 'event'
				),
				'repeat_days' => array(
					'description' => __( "Repeat on days." ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'enum'		  => range( 0, 6 ),
					'field_type'  => 'event'
				),
				'repeat_weeks' => array(
					'description' => __( "Repeat on weeks." ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'enum'		  => range( 1, 5 ),
					'field_type'  => 'event'
				),
				'repeat_end_type' => array(
					'description' => __( "Recurring end type." ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'enum'		  => range( 0, 1 ),
					'default'	  => 0,
					'field_type'  => 'event'
				),
				'max_repeat' => array(
					'description' => __( "Max repeat." ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'field_type'  => 'event'
				),
				'repeat_end' => array(
					'description' => __( "Recurring end date (Y-m-d)." ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'format'      => 'date',
					'field_type'  => 'event'
				),
				'recurring_dates' => array(
					'description' => __( "Custom recurring dates." ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'field_type'  => 'event'
				),
				'all_day' => array(
					'description'  => __( "All day event?" ),
					'type'         => 'boolean',
					'context'      => array( 'view', 'edit' ),
					'field_type'   => 'event'
				),
				'start_time' => array(
					'description'  => __( "Event start time (H:i)." ),
					'type'         => 'string',
					'format'       => 'time',
					'context'      => array( 'view', 'edit' ),
					'enum'		   => $times,
					'field_type'   => 'event'
				),
				'end_time' => array(
					'description'  => __( "Event end time (H:i)." ),
					'type'         => 'string',
					'format'       => 'time',
					'context'      => array( 'view', 'edit' ),
					'enum'		   => $times,
					'field_type'   => 'event'
				),
				'different_times' => array(
					'description'  => __( "Different event times?" ),
					'type'         => 'boolean',
					'context'      => array( 'view', 'edit' ),
					'field_type'   => 'event'
				),
				'start_times' => array(
					'description'  => __( "Event start times (H:i)." ),
					'type'         => 'array',
					'format'       => 'time',
					'context'      => array( 'view', 'edit' ),
					'enum'		   => $times,
					'field_type'   => 'event'
				),
				'end_times' => array(
					'description'  => __( "Event end times (H:i)." ),
					'type'         => 'array',
					'format'       => 'time',
					'context'      => array( 'view', 'edit' ),
					'enum'		   => $times,
					'field_type'   => 'event'
				)
			);
		}

		return $args;
	}

	public static function event_post_data( $data, $gd_post, $request, $controller ) {
		if ( isset( $gd_post->event_dates ) ) {
			if ( ! empty( $gd_post->set_schedule_id ) ) {
				$schedule = GeoDir_Event_Schedules::get_schedule( $gd_post->set_schedule_id );
				if ( ! empty( $schedule ) ) {
					foreach ( $schedule as $key => $value ) {
						$gd_post->{$key} = $value;
					}
				}
			}

			$event_type = geodir_get_option( 'event_hide_past_dates' ) ? 'upcoming' : 'all';

			$data['event_dates'] = maybe_unserialize( $gd_post->event_dates );

			$event_data = self::prepare_schedule_response( $gd_post );

			$schedules 	= GeoDir_Event_Schedules::get_schedules( $gd_post->ID, $event_type );
			$event_schedules = array();
			foreach ( $schedules as $schedule ) {
				$event_schedules[] = self::prepare_schedule_response( $schedule );
			}
			$event_data['event_schedules'] = $event_schedules;

			$data = geodir_array_splice_assoc( $data, ( array_search( 'event_dates', array_keys( $data ) ) + 1 ), 0, $event_data );
		}

		return $data;
	}

	public static function prepare_schedule_response( $item ) {
		if ( empty( $item->start_date ) ) {
			return array();
		}

		$date_format = geodir_event_date_format();
		$time_format = geodir_event_time_format();
		$date_time_format = geodir_event_date_time_format();

		$start_date = $item->start_date;
		$end_date = $item->end_date;
		if ( empty( $item->all_day ) ) {
			$start_time = $item->start_time;
			$end_time = $item->end_time;
		} else {
			$start_time = '00:00:00';
			$end_time = '00:00:00';
		}

		$schedule = array();
		if ( isset( $item->schedule_id ) ) {
			$schedule['schedule_id'] = $item->schedule_id;
		}
		$schedule['start_date'] = array(
			'raw'		=> $start_date,
			'rendered' 	=> date_i18n( $date_format, strtotime( $start_date ) )
		);
		$schedule['start_time'] = array(
			'raw'		=> date_i18n( 'H:i', strtotime( $start_time ) ),
			'rendered' 	=> date_i18n( $time_format, strtotime( $start_time ) )
		);
		$schedule['end_date'] = array(
			'raw'		=> $end_date,
			'rendered' 	=> date_i18n( $date_format, strtotime( $end_date ) )
		);
		$schedule['end_time'] = array(
			'raw'		=> date_i18n( 'H:i', strtotime( $end_time ) ),
			'rendered' 	=> date_i18n( $time_format, strtotime( $end_time ) )
		);
		$schedule['all_day'] = $item->all_day;
		$schedule['start_datetime'] = array(
			'raw'		=> self::prepare_date_response( $start_date . ' '. $start_time ),
			'rendered' 	=> date_i18n( $date_time_format, strtotime( $start_date . ' '. $start_time ) )
		);
		$schedule['end_datetime'] = array(
			'raw'		=> self::prepare_date_response( $end_date . ' '. $end_time ),
			'rendered' 	=> date_i18n( $date_time_format, strtotime( $end_date . ' '. $end_time ) )
		);
		return $schedule;
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
