<?php
/**
 * Contains the query functions for GeoDirectory event which alter the front-end post queries and loops
 *
 * @class 		GeoDir_Event_Query
 * @version		2.0.0
 * @package		GeoDirectory_Event_Manager/Classes
 * @category	Class
 * @author 		AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Event_Query Class.
 */
class GeoDir_Event_Query {

	/**
	 * Constructor for the query class. Hooks in methods.
	 *
	 * @access public
	 */
	public function __construct() {

		add_filter( 'geodir_posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
		add_filter( 'geodir_posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
		add_filter( 'geodir_posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
		add_filter( 'geodir_posts_order_by_sort', array( __CLASS__, 'posts_orderby' ), 10, 4 );
		add_filter( 'geodir_posts_groupby', array( __CLASS__, 'posts_groupby' ), 10, 2 );

		if ( wp_doing_ajax() ) {
			add_action( 'pre_get_posts', array( __CLASS__, 'filter_calender_posts' ), 10, 2 );
		}
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_rest_api_posts' ), 10, 1 );

		add_filter( 'geodir_filter_widget_listings_fields', array( __CLASS__, 'widget_posts_fields' ), 1, 3 );
		add_filter( 'geodir_filter_widget_listings_join', array( __CLASS__, 'widget_posts_join' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_where', array( __CLASS__, 'widget_posts_where' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_groupby', array( __CLASS__, 'widget_posts_groupby' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_orderby', array( __CLASS__, 'widget_posts_orderby' ), 1, 3 );
	}

	public static function filter_calender_posts( $query ) {
		if ( get_query_var( 'post_type' ) == 'gd_event' && get_query_var( 'gd_event_calendar' ) ) {
			add_filter( 'posts_fields', array( __CLASS__, 'calendar_posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( __CLASS__, 'calendar_posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( __CLASS__, 'calendar_posts_where' ), 10, 2 );
			add_filter( 'posts_groupby', array( __CLASS__, 'calendar_posts_groupby' ), 10, 2 );
			add_filter( 'posts_orderby', array( __CLASS__, 'calendar_posts_orderby' ), 10, 2 );
		}
	}

	public static function is_rest( $query ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $query->query_vars ) && $query->query_vars['post_type'] == 'gd_event' ) {
			return true;
		}
		return false;
	}

	public static function filter_rest_api_posts( $query ) {
		if ( self::is_rest( $query ) ) {
			add_filter( 'geodir_rest_posts_clauses_fields', array( __CLASS__, 'rest_posts_fields' ), 11, 3 );
			add_filter( 'geodir_rest_posts_clauses_join', array( __CLASS__, 'rest_posts_join' ), 11, 3 );
			add_filter( 'geodir_rest_posts_clauses_where', array( __CLASS__, 'geodir_rest_posts_clauses_where' ), 11, 3 );
			add_filter( 'geodir_rest_posts_clauses_groupby', array( __CLASS__, 'geodir_rest_posts_clauses_groupby' ), 11, 3 );
			add_filter( 'geodir_rest_posts_clauses_orderby', array( __CLASS__, 'geodir_rest_posts_clauses_orderby' ), 11, 3 );
		}
	}

	public static function widget_posts_fields( $fields, $table, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( $post_type == 'gd_event' ) {
			$fields .= ", " . GEODIR_EVENT_SCHEDULES_TABLE . ".*";
		}
		return $fields;
	}

	public static function widget_posts_join( $join, $post_type ) {
		global $wpdb, $gd_query_args_widgets;

		if ( $post_type == 'gd_event' ) {
			$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = " . $wpdb->posts . ".ID";	
		}
		return $join;
	}

	public static function widget_posts_where( $where, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( $post_type == 'gd_event' && ! empty( $gd_query_args_widgets ) ) {
			if ( isset( $gd_query_args_widgets['event_type'] ) && ( $condition = GeoDir_Event_Schedules::event_type_condition( $gd_query_args_widgets['event_type'] ) ) ) {
				$where .= " AND " . $condition;
			}
			if ( ! empty( $gd_query_args_widgets['link_business'] ) ) {
				$where .= " AND " . GEODIR_EVENT_DETAIL_TABLE . ".link_business = " . (int)$gd_query_args_widgets['link_business'];
			}
		}
		return $where;
	}

	public static function widget_posts_groupby( $groupby, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( $post_type == 'gd_event' ) {
			if ( ! empty( $gd_query_args_widgets['single_event'] ) ) {
				$groupby = " GROUP BY " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id";
				//$groupby = " GROUP BY " .  $wpdb->posts . ".ID";
			} else {
				$groupby = " GROUP BY " . GEODIR_EVENT_SCHEDULES_TABLE . ".schedule_id";
				//$groupby = " GROUP BY " .  $wpdb->posts . ".ID, " . GEODIR_EVENT_SCHEDULES_TABLE . ".schedule_id";
			}
		}
		return $groupby;
	}

	public static function widget_posts_orderby( $orderby, $table, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( $post_type == 'gd_event' ) {
			$order_by = ! empty( $gd_query_args_widgets['order_by'] ) ? $gd_query_args_widgets['order_by'] : '';

			if ( $order_by == 'random' ) {
				return $orderby;
			}

			if ( trim( $orderby ) != '' ) {
				$orderby .= ", ";
			}

			if ( $order_by == 'event_dates_asc' || $order_by == 'event_dates_desc' ) {
				$orderby = '';
			}

			if ( $order_by == 'event_dates_desc' ) {
				$orderby .= GEODIR_EVENT_SCHEDULES_TABLE . ".start_date DESC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time DESC";
			} else {
				$orderby .= GEODIR_EVENT_SCHEDULES_TABLE . ".start_date ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC";
			}
		}
		return $orderby;
	}
	
	public static function posts_fields( $fields, $query = array() ) {
		global $geodir_post_type;
		
		if ( $geodir_post_type != 'gd_event' ) {
			return $fields;
		}
		if ( trim( $fields ) != '' ) {
			$fields .= ", ";
		}

		$fields .= GEODIR_EVENT_SCHEDULES_TABLE . ".*";

		return $fields;
	}

	public static function posts_join( $join, $query = array() ) {
		global $wpdb, $geodir_post_type;
		
		if ( $geodir_post_type != 'gd_event' ) {
			return $join;
		}

		$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = {$wpdb->posts}.ID )  ";

		return $join;
	}

	public static function posts_where( $where, $query = array() ) {
		global $geodir_post_type, $gd_session;
		
		if ( $geodir_post_type != 'gd_event' ) {
			return $where;
		}

		$table 				= GEODIR_EVENT_DETAIL_TABLE;
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;

		$event_type = ! empty( $_REQUEST['etype'] ) ? sanitize_text_field( $_REQUEST['etype'] ) : geodir_get_option( 'event_defalt_filter' );
		if ( ( $gd_event_type = get_query_var( 'gd_event_type' ) ) ) {
			$event_type = $gd_event_type;
		}

		if ( ( $condition = GeoDir_Event_Schedules::event_type_condition( $event_type ) ) ) {
			$where .= " AND " . $condition;
		}

		if ( geodir_is_page( 'search' ) ) {
			if ( ! empty( $_REQUEST['event_calendar'] ) ) {
				$filter_date = $_REQUEST['event_calendar'];
				$filter_date = substr( $filter_date, 0, 4 ) . '-' . substr( $filter_date , 4, 2 ) . '-' . substr( $filter_date, 6, 2 );

				$where .= " AND ( start_date = '" . $filter_date . "' OR ( start_date <= '" . $filter_date . "' AND end_date >= '" . $filter_date . "' ) )";
			}
		}

		if ( $gd_session->get('all_near_me' ) ) {
			$radius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
			$latitude = $gd_session->get('user_lat');
			$longitude = $gd_session->get('user_lon');
			
			if ( $latitude && $longitude ) {
				if ( ( $near_me_range = $gd_session->get( 'near_me_range' ) ) ) {
					$distance =  $near_me_range;
				} else if ( ( $near_me_dist = $gd_session->get( 'near_me_dist' ) ) ) {
					$distance = $near_me_dist;
				} else {
					$distance = 200;
				}

				$lat1 = $latitude - ( $distance / 69 );
				$lat2 = $latitude + ( $distance / 69 );
				$lon1 = $longitude - $distance / abs( cos( deg2rad( $latitude ) ) * 69 ); 
				$lon2 = $longitude + $distance / abs( cos( deg2rad( $latitude ) ) * 69 );

				$min_latitude = is_numeric( min( $lat1, $lat2 ) ) ? min( $lat1, $lat2 ) : '';
				$max_latitude = is_numeric( max( $lat1, $lat2 ) ) ? max( $lat1, $lat2 ) : '';
				$min_longitude = is_numeric( min( $lon1, $lon2 ) ) ? min( $lon1, $lon2 ) : '';
				$max_longitude = is_numeric( max( $lon1, $lon2 ) ) ? max( $lon1, $lon2 ) : '';

				$where .= " AND ( " . $table . ".latitude BETWEEN " . $min_latitude . " AND " . $max_latitude . " ) AND ( " . $table . ".longitude BETWEEN " . $min_longitude . " AND " . $max_longitude . " )";
			}
		}
		
		$venue = get_query_var( 'venue' );
		if ( empty( $venue ) && ! empty( $_REQUEST['venue'] ) ) {
			$venue = sanitize_text_field( $_REQUEST['venue'] );
		}

		if ( ! empty( $venue ) ) {
			$venue = explode( '-', $venue, 2);
			if ( ! empty( $venue[0] ) && (int)$venue[0] > 0 ) {
				if ( geodir_is_gd_post_type( get_post_type( (int)$venue[0] ) ) ) {
					$link_business = (int)$venue[0];
				} else {
					$link_business = '-1';
				}

				$where .= " AND " . $table . ".link_business = " . (int)$link_business;
			}
		}

		return $where;
	}

	public static function posts_groupby( $groupby, $query = array() ) {
		global $wpdb, $geodir_post_type;

		if ( $geodir_post_type != 'gd_event' ) {
			return $groupby;
		}
		
		if ( trim( $groupby ) != '' ) {
			$groupby .= ", ";
		} else {
			$groupby = "GROUP BY ";
		}

		$groupby = "{$wpdb->posts}.ID, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date";

		return $groupby;
	}

	public static function posts_orderby( $orderby, $sortby, $table, $query = array() ) {
		global $geodir_post_type;

		if ( $geodir_post_type != 'gd_event' || $sortby == 'random' ) {
			return $orderby;
		}

		if ( trim( $orderby ) != '' ) {
			$orderby .= ", ";
		}

		if ( $sortby == 'event_dates_asc' || $sortby == 'event_dates_desc' ) {
			$orderby = '';
		}

		if ( $sortby == 'event_dates_desc' ) {
			$orderby .= GEODIR_EVENT_SCHEDULES_TABLE . ".start_date DESC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time DESC";
		} else {
			$orderby .= GEODIR_EVENT_SCHEDULES_TABLE . ".start_date ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC";
		}

		return $orderby;
	}

	public static function calendar_posts_fields( $fields, $query = array() ) {
		global $wpdb, $gd_session;

		$table 				= GEODIR_EVENT_DETAIL_TABLE;
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;
		$date 				= get_query_var( 'gd_event_calendar' );
		$current_year 		= date_i18n( 'Y', $date );
		$current_month 		= date_i18n( 'm', $date );
		$month_start 		= $current_year . '-' . $current_month . '-01'; // First day of the month.
		$month_end 			= date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Last day of the month.
		
		$condition 	= "( ( ( '" . $month_start . "' BETWEEN start_date AND end_date ) OR ( start_date BETWEEN '" . $month_start . "' AND end_date ) ) AND ( ( '" . $month_end . "' BETWEEN start_date AND end_date ) OR ( end_date BETWEEN start_date AND '" . $month_end . "' ) ) ) AND " . $schedules_table . ".event_id = " . $wpdb->posts . ".ID";
		
		$fields = "( SELECT GROUP_CONCAT( DISTINCT CONCAT( DATE_FORMAT( " . $schedules_table . ".start_date, '%d%m%y' ), '', DATE_FORMAT( " . $schedules_table . ".end_date, '%d%m%y' ) ) ) FROM " . $schedules_table . " WHERE " . $condition . " ) AS schedules";
		if ( $gd_session->get( 'all_near_me' ) ) {
			$radius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
			$latitude = $gd_session->get('user_lat');
			$longitude = $gd_session->get('user_lon');
			
			$fields .= ", ( " . $radius . " * 2 * ASIN( SQRT( POWER( SIN( ( ABS( " . $latitude . " ) - ABS( " . $table . ".latitude ) ) * PI() / 180 / 2 ), 2 ) + COS( ABS( " . $latitude . " ) * PI() / 180 ) * COS( ABS( " . $table . ".latitude ) * PI() / 180 ) * POWER( SIN( ( " . $longitude . " - " . $table . ".longitude ) * PI() / 180 / 2 ), 2 ) ) ) ) AS distance";
		}

		return $fields;
	}

	public static function calendar_posts_join( $join, $query = array() ) {
		global $wpdb;

		$table 				= GEODIR_EVENT_DETAIL_TABLE;
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;

		$join .= " LEFT JOIN " . $table . " ON " . $table . ".post_id = " . $wpdb->posts . ".ID";
		$join .= " LEFT JOIN " . $schedules_table . " ON " . $schedules_table . ".event_id = " . $wpdb->posts . ".ID";

		return $join;
	}

	public static function calendar_posts_where( $where, $query = array() ) {
		$table 				= GEODIR_EVENT_DETAIL_TABLE;
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;
		$date 				= get_query_var( 'gd_event_calendar' );
		$current_year 		= date_i18n( 'Y', $date );
		$current_month 		= date_i18n( 'm', $date );
		$month_start 		= $current_year . '-' . $current_month . '-01'; // First day of the month.
		$month_end 			= date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Last day of the month.

		$where .= " AND " . $table . ".post_id > 0 AND ( ( ( '" . $month_start . "' BETWEEN start_date AND end_date ) OR ( start_date BETWEEN '" . $month_start . "' AND end_date ) ) AND ( ( '" . $month_end . "' BETWEEN start_date AND end_date ) OR ( end_date BETWEEN start_date AND '" . $month_end . "' ) ) )";
		if ( get_query_var( 'gd_location' ) && function_exists( 'geodir_default_location_where' ) ) {
            $where .= geodir_default_location_where( '', $table );
        }

		return $where;
	}

	public static function calendar_posts_groupby( $groupby, $query = array() ) {

		return $groupby;
	}

	public static function calendar_posts_orderby( $orderby, $query = array() ) {

		return $orderby;
	}

	public static function rest_posts_fields( $fields, $wp_query, $post_type ) {
		if ( ! self::is_rest( $wp_query ) ) {
			return $fields;
		}
		
		$schedules_table = GEODIR_EVENT_SCHEDULES_TABLE;

		$fields .= ", {$schedules_table}.*";

		return $fields;
	}

	public static function rest_posts_join( $join, $wp_query, $post_type ) {
		global $wpdb;

		if ( ! self::is_rest( $wp_query ) ) {
			return $join;
		}
		
		$schedules_table = GEODIR_EVENT_SCHEDULES_TABLE;

		$join .= " LEFT JOIN {$schedules_table} ON {$schedules_table}.event_id = {$wpdb->posts}.ID";

		return $join;
	}

	public static function geodir_rest_posts_clauses_where( $where, $wp_query, $post_type ) {
		if ( ! self::is_rest( $wp_query ) ) {
			return $where;
		}

		$event_type = ! empty( $wp_query->query_vars['gd_event_type'] ) ? $wp_query->query_vars['gd_event_type'] : geodir_get_option( 'event_defalt_filter' );

		if ( ( $condition = GeoDir_Event_Schedules::event_type_condition( $event_type ) ) ) {
			$where .= " AND " . $condition;
		}

		return $where;
	}

	public static function geodir_rest_posts_clauses_groupby( $groupby, $wp_query, $post_type ) {
		if ( ! self::is_rest( $wp_query ) ) {
			return $groupby;
		}

		$schedules_table = GEODIR_EVENT_SCHEDULES_TABLE;

		if ( ! empty( $wp_query->query_vars['single_event'] ) ) {
			$groupby = "{$schedules_table}.event_id";
		} else {
			$groupby = "{$schedules_table}.schedule_id";
		}

		return $groupby;
	}

	public static function geodir_rest_posts_clauses_orderby( $orderby, $wp_query, $post_type ) {
		if ( ! self::is_rest( $wp_query ) ) {
			return $orderby;
		}

		return $orderby;
	}
}
