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
		if ( GeoDir_Post_types::supports( get_query_var( 'post_type' ), 'events' ) && get_query_var( 'gd_event_calendar' ) ) {
			add_filter( 'posts_fields', array( __CLASS__, 'calendar_posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( __CLASS__, 'calendar_posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( __CLASS__, 'calendar_posts_where' ), 10, 2 );
			add_filter( 'posts_groupby', array( __CLASS__, 'calendar_posts_groupby' ), 10, 2 );
			add_filter( 'posts_orderby', array( __CLASS__, 'calendar_posts_orderby' ), 10, 2 );
		}
	}

	public static function is_rest( $query ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $query->query_vars ) && GeoDir_Post_types::supports( $query->query_vars['post_type'], 'events' ) ) {
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

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$fields .= ", " . GEODIR_EVENT_SCHEDULES_TABLE . ".*";
		}
		return $fields;
	}

	public static function widget_posts_join( $join, $post_type ) {
		global $wpdb, $gd_query_args_widgets;

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = " . $wpdb->posts . ".ID";	
		}
		return $join;
	}

	public static function widget_posts_where( $where, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) && ! empty( $gd_query_args_widgets ) ) {
			if ( isset( $gd_query_args_widgets['event_type'] ) && ( $condition = GeoDir_Event_Schedules::event_type_condition( $gd_query_args_widgets['event_type'] ) ) ) {
				$where .= " AND " . $condition;
			}
		}
		return $where;
	}

	public static function widget_posts_groupby( $groupby, $post_type ) {
		global  $wpdb, $gd_query_args_widgets;

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
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

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
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
		
		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
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
		
		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
			return $join;
		}

		$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = {$wpdb->posts}.ID )  ";

		return $join;
	}

	public static function posts_where( $where, $query = array() ) {
		global $geodir_post_type;
		
		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
			return $where;
		}

		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;

		$event_type = ! empty( $_REQUEST['etype'] ) ? sanitize_text_field( $_REQUEST['etype'] ) : geodir_get_option( 'event_default_filter' );
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

			if ( ! empty( $_REQUEST['event_dates'] ) ) {
				$date_format = geodir_event_date_format();
				$date_format = apply_filters( 'geodir_event_serach_date_format', $date_format, $geodir_post_type );

				if ( is_array( $_REQUEST['event_dates'] ) ) {
					$from_date = ! empty( $_REQUEST['event_dates']['from'] ) ? geodir_event_date_to_ymd( sanitize_text_field( $_REQUEST['event_dates']['from'] ), $date_format ) : '';
					$to_date = ! empty( $_REQUEST['event_dates']['to'] ) ? geodir_event_date_to_ymd( sanitize_text_field( $_REQUEST['event_dates']['to'] ), $date_format ) : '';

					if ( ! empty( $from_date ) && ! empty( $to_date ) ) {
						$where .= " AND ( ( '{$from_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.start_date BETWEEN '{$from_date}' AND {$schedules_table}.end_date ) ) AND ( ( '{$to_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.end_date BETWEEN {$schedules_table}.start_date AND '{$to_date}' ) ) ";
					} else {
						$date = ! empty( $from_date ) ? $from_date : $to_date;
						$where .= " AND ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ";
					}
				} else {
					$date = geodir_event_date_to_ymd( sanitize_text_field( $_REQUEST['event_dates'] ), $date_format );
					$where .= " AND ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ";
				}
			}
		}

		return $where;
	}

	public static function posts_groupby( $groupby, $query = array() ) {
		global $wpdb, $geodir_post_type;

		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
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

		if ( $sortby == 'random' || ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
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
		global $wpdb;

		$table 				= geodir_db_cpt_table( 'gd_event' );
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;
		$date 				= get_query_var( 'gd_event_calendar' );
		$current_year 		= date_i18n( 'Y', $date );
		$current_month 		= date_i18n( 'm', $date );
		$month_start 		= $current_year . '-' . $current_month . '-01'; // First day of the month.
		$month_end 			= date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Last day of the month.
		
		$condition 	= "( ( ( '" . $month_start . "' BETWEEN start_date AND end_date ) OR ( start_date BETWEEN '" . $month_start . "' AND end_date ) ) AND ( ( '" . $month_end . "' BETWEEN start_date AND end_date ) OR ( end_date BETWEEN start_date AND '" . $month_end . "' ) ) ) AND " . $schedules_table . ".event_id = " . $wpdb->posts . ".ID";
		
		$fields = "( SELECT GROUP_CONCAT( DISTINCT CONCAT( DATE_FORMAT( " . $schedules_table . ".start_date, '%d%m%y' ), '', DATE_FORMAT( " . $schedules_table . ".end_date, '%d%m%y' ) ) ) FROM " . $schedules_table . " WHERE " . $condition . " ) AS schedules";

		return $fields;
	}

	public static function calendar_posts_join( $join, $query = array() ) {
		global $wpdb;

		$table 				= geodir_db_cpt_table( 'gd_event' );
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;

		$join .= " LEFT JOIN " . $table . " ON " . $table . ".post_id = " . $wpdb->posts . ".ID";
		$join .= " LEFT JOIN " . $schedules_table . " ON " . $schedules_table . ".event_id = " . $wpdb->posts . ".ID";

		return $join;
	}

	public static function calendar_posts_where( $where, $query = array() ) {
		$table 				= geodir_db_cpt_table( 'gd_event' );
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

		$event_type = ! empty( $wp_query->query_vars['gd_event_type'] ) ? $wp_query->query_vars['gd_event_type'] : geodir_get_option( 'event_default_filter' );

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
