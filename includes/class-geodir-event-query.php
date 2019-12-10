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

		add_filter( 'geodir_filter_widget_listings_count_fields', array( __CLASS__, 'widget_count_posts_fields' ), 1, 3 );
		add_filter( 'geodir_filter_widget_listings_fields', array( __CLASS__, 'widget_posts_fields' ), 1, 3 );
		add_filter( 'geodir_filter_widget_listings_join', array( __CLASS__, 'widget_posts_join' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_where', array( __CLASS__, 'widget_posts_where' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_groupby', array( __CLASS__, 'widget_posts_groupby' ), 1, 2 );
		add_filter( 'geodir_filter_widget_listings_orderby', array( __CLASS__, 'widget_posts_orderby' ), 1, 3 );

		// Events map markers
		add_filter( 'geodir_rest_markers_query_join', array( __CLASS__, 'rest_markers_query_join' ), 9, 2 );
		add_filter( 'geodir_rest_markers_query_where', array( __CLASS__, 'rest_markers_query_where' ), 9, 2 );
		add_filter( 'geodir_rest_markers_query_group_by', array( __CLASS__, 'rest_markers_query_group_by' ), 9, 2 );

		if ( wp_doing_ajax() || ! is_admin() ) {
			add_filter( 'get_terms', array( __CLASS__, 'get_terms' ), 9, 4 );
		}
	}

	public static function filter_calender_posts( $query ) {
		if ( self::is_calender_query( $query ) ) {
			add_filter( 'posts_fields', array( __CLASS__, 'calendar_posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( __CLASS__, 'calendar_posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( __CLASS__, 'calendar_posts_where' ), 10, 2 );
			add_filter( 'posts_groupby', array( __CLASS__, 'calendar_posts_groupby' ), 10, 2 );
			add_filter( 'posts_orderby', array( __CLASS__, 'calendar_posts_orderby' ), 10, 2 );
		}
	}

	public static function is_calender_query( $query ) {
		if ( ! empty( $query ) && ! empty( $query->query_vars['post_type'] ) && ! empty( $query->query_vars['gd_event_calendar'] ) && GeoDir_Post_types::supports( $query->query_vars['post_type'], 'events' ) ) {
			return true;
		}
		return false;
	}

	public static function is_rest( $query ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $query->query_vars ) && ! empty( $query->query_vars['post_type'] ) && GeoDir_Post_types::supports( $query->query_vars['post_type'], 'events' ) ) {
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

	public static function widget_count_posts_fields( $fields, $table, $post_type ) {
		global  $gd_query_args_widgets;

		if ( empty( $gd_query_args_widgets['single_event'] ) && GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$fields = "COUNT( DISTINCT schedule_id ) AS total";
		}
		return $fields;
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
			$join .= " JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = " . $wpdb->posts . ".ID"; // An INNER JOIN is faster than a LEFT JOIN
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

		if ( ! GeoDir_Query::is_gd_main_query( $query ) ) {
			return $fields;
		}

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
		
		if ( ! GeoDir_Query::is_gd_main_query( $query ) ) {
			return $join;
		}

		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
			return $join;
		}

		$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = {$wpdb->posts}.ID )  ";

		return $join;
	}

	public static function posts_where( $where, $query = array() ) {
		global $geodir_post_type;
		
		if ( ! GeoDir_Query::is_gd_main_query( $query ) ) {
			return $where;
		}

		if ( ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
			return $where;
		}

		$schedules_table = GEODIR_EVENT_SCHEDULES_TABLE;

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
				$event_dates = $_REQUEST['event_dates'];

				if ( is_array( $event_dates ) ) {
					$from_date = ! empty( $event_dates['from'] ) ? date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $event_dates['from'] ) ) ) : '';
					$to_date = ! empty( $event_dates['to'] ) ? date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $event_dates['to'] ) ) ) : '';

					if ( ! empty( $from_date ) && ! empty( $to_date ) ) {
						$where .= " AND ( ( '{$from_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.start_date BETWEEN '{$from_date}' AND {$schedules_table}.end_date ) ) AND ( ( '{$to_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.end_date BETWEEN {$schedules_table}.start_date AND '{$to_date}' ) ) ";
					} else {
						if ( $from_date || $to_date ) {
							$date = ! empty( $from_date ) ? $from_date : $to_date;
							$where .= " AND ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ";
						}
					}
				} else {
					$date = date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $event_dates ) ) );
					$where .= " AND ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ";
				}
			}
		}

		return $where;
	}

	public static function posts_groupby( $groupby, $query = array() ) {
		global $wpdb, $geodir_post_type;

		if ( ! GeoDir_Query::is_gd_main_query( $query ) ) {
			return $groupby;
		}

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

		if ( ! GeoDir_Query::is_gd_main_query( $query ) ) {
			return $orderby;
		}

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

		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;
		$date 				= $query->query_vars['gd_event_calendar'];
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

		$table 				= geodir_db_cpt_table( $query->query_vars['post_type'] );
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;

		$join .= " LEFT JOIN " . $table . " ON " . $table . ".post_id = " . $wpdb->posts . ".ID";
		$join .= " LEFT JOIN " . $schedules_table . " ON " . $schedules_table . ".event_id = " . $wpdb->posts . ".ID";

		return $join;
	}

	public static function calendar_posts_where( $where, $query = array() ) {
		global $wpdb;

		$table 				= geodir_db_cpt_table( $query->query_vars['post_type'] );
		$schedules_table	= GEODIR_EVENT_SCHEDULES_TABLE;
		$date 				= $query->query_vars['gd_event_calendar'];
		$current_year 		= date_i18n( 'Y', $date );
		$current_month 		= date_i18n( 'm', $date );
		$month_start 		= $current_year . '-' . $current_month . '-01'; // First day of the month.
		$month_end 			= date_i18n( 'Y-m-t', strtotime( $month_start ) ); // Last day of the month.

		$where .= " AND " . $table . ".post_id > 0 AND ( ( ( '" . $month_start . "' BETWEEN start_date AND end_date ) OR ( start_date BETWEEN '" . $month_start . "' AND end_date ) ) AND ( ( '" . $month_end . "' BETWEEN start_date AND end_date ) OR ( end_date BETWEEN start_date AND '" . $month_end . "' ) ) )";

		// @todo: move this to location manager during new calendar features.
		if ( get_query_var( 'gd_location' ) && function_exists( 'geodir_location_main_query_posts_where' ) ) {
			$where .= geodir_location_main_query_posts_where( '', $query, $query->query_vars['post_type'] );

			if ( ! empty( $_REQUEST['my_lat'] ) && ! empty( $_REQUEST['my_lon'] ) ) {
				$between = geodir_get_between_latlon( sanitize_text_field( $_REQUEST['my_lat'] ), sanitize_text_field( $_REQUEST['my_lon'] ) );
				$where .= $wpdb->prepare( " AND $table.latitude BETWEEN %f AND %f AND $table.longitude BETWEEN %f AND %f ", $between['lat1'], $between['lat2'], $between['lon1'], $between['lon2'] );
			}
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

	public static function rest_markers_query_join( $join, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && geodir_get_option( 'event_default_filter' ) != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) ) {
			$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = p.ID";
		}

		return $join;
	}

	public static function rest_markers_query_where( $where, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && geodir_get_option( 'event_default_filter' ) != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) ) {
			if ( $condition = GeoDir_Event_Schedules::event_type_condition( geodir_get_option( 'event_default_filter' ) ) ) {
				$where .= " AND " . $condition;
			}
		}

		return $where;
	}

	public static function rest_markers_query_group_by( $group_by, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && geodir_get_option( 'event_default_filter' ) != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) ) {
			$group_by = "p.ID";
		}

		return $group_by;
	}

	public static function get_terms( $terms, $taxonomy, $query_vars, $term_query ) {
		global $geodirectory;

		if ( isset( $query_vars['gd_no_loop'] ) || empty( $terms ) ) {
			return $terms;
		}

		if ( ! empty( $geodirectory->location ) && ! empty( $geodirectory->location->type ) ) {
			return $terms;
		}

		foreach ( $terms as $key => $term ) {
			if ( ! empty( $term->count ) && ! empty( $term->taxonomy ) && GeoDir_Taxonomies::supports( $term->taxonomy, 'events' ) && ( $count = self::get_term_count( $term->term_id, $term->taxonomy ) ) !== null ) {
				$terms[ $key ]->count = $count;
			}
		}

		return $terms;
	}

	public static function get_term_count( $term_id, $taxonomy ) {
		global $wpdb;

		$cache_key = 'geodir_event_term_count:' . $term_id;

		$cache = wp_cache_get( $cache_key );
		if ( $cache !== false ) {
			return $cache;
		}

		$count = null;
		$post_type = self::get_taxonomy_post_type( $taxonomy );
		$term_count_sql = GeoDir_Event_Schedules::location_term_counts( '', $term_id, $taxonomy, $post_type, '', array(), 'term_count', '' );

		if ( $term_count_sql ) {
			$count = (int) $wpdb->get_var( $term_count_sql );
		}

		wp_cache_set( $cache_key, $count );

		return $count;
	}

	public static function get_taxonomy_post_type( $taxonomy ) {
		$cache_key = 'geodir_event_taxonomy_post_type:' . $taxonomy;

		$cache = wp_cache_get( $cache_key );
		if ( $cache ) {
			return $cache;
		}

		if ( geodir_taxonomy_type( $taxonomy ) == 'category' ) {
			$post_type = substr( $taxonomy, 0, strlen( $taxonomy ) - 8 );
		} else if ( geodir_taxonomy_type( $taxonomy ) == 'tag' ) {
			$post_type = substr( $taxonomy, 0, strlen( $taxonomy ) - 5 );
		} else {
			$post_type = $taxonomy;
		}

		wp_cache_set( $cache_key, $post_type );

		return $post_type;
	}
}
