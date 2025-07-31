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
		add_filter( 'geodir_custom_key_orderby', array( __CLASS__, 'custom_key_orderby' ), 20, 7 );
		add_filter( 'geodir_advanced_search_autocomplete_script_posts_request', array( __CLASS__, 'search_autocomplete_set_single_event' ) );

		// Events map markers
		add_filter( 'geodir_rest_markers_query_join', array( __CLASS__, 'rest_markers_query_join' ), 9, 2 );
		add_filter( 'geodir_rest_markers_query_where', array( __CLASS__, 'rest_markers_query_where' ), 9, 2 );
		add_filter( 'geodir_rest_markers_query_group_by', array( __CLASS__, 'rest_markers_query_group_by' ), 9, 2 );

		// Set event schedule to global $gd_post data.
		add_action( 'the_post', array( __CLASS__, 'the_gd_post' ), 20, 2 );

		if ( wp_doing_ajax() || ! is_admin() ) {
			add_filter( 'get_terms', array( __CLASS__, 'get_terms' ), 9, 4 );
		}

		add_filter( 'seopress_sitemaps_index_post_types_query', array( __CLASS__, 'seopress_sitemaps_index_post_types_query' ), 10, 2 );
		add_filter( 'seopress_sitemaps_single_query', array( __CLASS__, 'seopress_sitemaps_single_query' ), 10, 2 );
		add_filter( 'posts_clauses_request', array( __CLASS__, 'seopress_posts_clauses_request' ), 10, 2 );
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

			if ( ! empty( $gd_query_args_widgets['single_event'] ) ) {
				$order = ! empty( $gd_query_args_widgets['order_by'] ) && $gd_query_args_widgets['order_by'] == 'event_dates_desc' ? 'MAX' : 'MIN';
				$fields .= ", " . $order . "( " . GEODIR_EVENT_SCHEDULES_TABLE . ".schedule_id ) AS set_schedule_id";
			}
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

			if ( $order_by == 'event_dates_desc' ) {
				$_orderby = GEODIR_EVENT_SCHEDULES_TABLE . ".start_date DESC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time DESC";
			} else {
				$_orderby = GEODIR_EVENT_SCHEDULES_TABLE . ".start_date ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC";
			}

			if ( strripos( $orderby, $_orderby ) === false ) {
				if ( trim( $orderby ) != '' ) {
					$orderby .= ", ";
				}

				if ( $order_by == 'event_dates_asc' || $order_by == 'event_dates_desc' ) {
					$orderby = '';
				}

				$orderby .= $_orderby;
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
				$filter_date = sanitize_text_field( $_REQUEST['event_calendar'] );
				$filter_date = substr( $filter_date, 0, 4 ) . '-' . substr( $filter_date , 4, 2 ) . '-' . substr( $filter_date, 6, 2 );

				$where .= " AND ( start_date = '" . $filter_date . "' OR ( start_date <= '" . $filter_date . "' AND end_date >= '" . $filter_date . "' ) )";
			}

			if ( ! empty( $_REQUEST['event_dates'] ) ) {
				$event_dates = geodir_event_sanitize_text_field( $_REQUEST['event_dates'] );

				if ( ! is_array( $event_dates ) && ( strpos( $event_dates, ' to ' ) > 0 || strpos( $event_dates, __( ' to ', 'geodirectory' ) ) > 0 ) ) {
					$_event_dates = strpos( $event_dates, __( ' to ', 'geodirectory' ) ) > 0 ? explode( __( ' to ', 'geodirectory' ), $event_dates, 2 ) : explode( ' to ', $event_dates, 2 );

					$event_dates = array();
					if ( ! empty( $_event_dates[0] ) ) {
						$event_dates['from'] = trim( $_event_dates[0] );
					}
					if ( ! empty( $_event_dates[1] ) ) {
						$event_dates['to'] = trim( $_event_dates[1] );
					}
				}

				if ( is_array( $event_dates ) ) {
					$from_date = ! empty( $event_dates['from'] ) ? date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $event_dates['from'] ) ) ) : '';
					$to_date = ! empty( $event_dates['to'] ) ? date_i18n( 'Y-m-d', strtotime( sanitize_text_field( $event_dates['to'] ) ) ) : '';

					if ( ! empty( $from_date ) && ! empty( $to_date ) ) {
						$where .= " AND ( ( '{$from_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.start_date BETWEEN '{$from_date}' AND {$schedules_table}.end_date ) ) AND ( ( '{$to_date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) OR ( {$schedules_table}.end_date BETWEEN {$schedules_table}.start_date AND '{$to_date}' ) ) ";
					} else {
						if ( $from_date || $to_date ) {
							$date = ! empty( $from_date ) ? $from_date : $to_date;

							if ( $from_date ) {
								$where .= " AND ( {$schedules_table}.start_date >='{$date}' OR ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ) ";
							} elseif ( $to_date ) {
								$where .= " AND ( {$schedules_table}.end_date <='{$date}' OR ( '{$date}' BETWEEN {$schedules_table}.start_date AND {$schedules_table}.end_date ) ) ";
							}
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

		$groupby = "{$wpdb->posts}.ID, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date";

		return $groupby;
	}

	public static function posts_orderby( $orderby, $sortby, $table, $query = array() ) {
		global $geodir_post_type;

		if ( ! GeoDir_Query::is_gd_main_query( $query ) && ! self::is_rest( $query ) ) {
			return $orderby;
		}

		if ( $sortby == 'random' || ! GeoDir_Post_types::supports( $geodir_post_type, 'events' ) ) {
			return $orderby;
		}

		if ( $sortby == 'event_dates_desc' ) {
			$_orderby = GEODIR_EVENT_SCHEDULES_TABLE . ".start_date DESC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time DESC";
		} else {
			$_orderby = GEODIR_EVENT_SCHEDULES_TABLE . ".start_date ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC";
		}

		if ( strripos( $orderby, $_orderby ) === false ) {
			if ( trim( $orderby ) != '' ) {
				$orderby .= ", ";
			}

			if ( $sortby == 'event_dates_asc' || $sortby == 'event_dates_desc' ) {
				$orderby = '';
			}

			$orderby .= $_orderby;
		}

		return $orderby;
	}

	public static function calendar_posts_fields( $fields, $query = array() ) {
		$fields = "*";

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

		$where .= " AND " . $table . ".post_id > 0";
		if ( ( $condition = GeoDir_Event_Schedules::event_type_condition( 'today', $schedules_table, $date ) ) ) {
			$where .= " AND " . $condition;
		}

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

		if ( ! empty( $wp_query->query_vars['single_event'] ) ) {
			$sort_by = isset( $wp_query->query_vars['orderby'] ) ? $wp_query->query_vars['orderby'] : '';
			$sort_by = apply_filters( 'geodir_rest_posts_order_sort_by_key', $sort_by, '', $post_type, $wp_query );
			$order = $sort_by == 'event_dates_desc' ? 'MAX' : 'MIN';
			$fields .= ", " . $order . "( {$schedules_table}.schedule_id ) AS set_schedule_id";
		}

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

	/**
	 * REST API request get event type filter.
	 *
	 * @since 2.3.3
	 *
	 * @param array $request REST request.
	 * @return string REST API event type filter.
	 */
	public static function rest_markers_event_type( $request ) {
		if ( ! empty( $request['event_type'] ) ) {
			$event_type = $request['event_type'];
		} else if ( ! empty( $_REQUEST['event_type'] ) ) {
			$event_type = sanitize_text_field( $_REQUEST['event_type'] );
		} else {
			$event_type = geodir_get_option( 'event_map_filter' );
		}

		if ( empty( $event_type ) ) {
			$event_type = 'ongoing_upcoming';
		}

		/**
		 * Filter REST API request get event type.
		 *
		 * @since 2.3.3
		 *
		 * @param string $event_type Event type filter.
		 * @param array  $request REST request.
		 */
		return apply_filters( 'geodir_event_rest_markers_event_type', $event_type, $request );
	}

	public static function rest_markers_query_join( $join, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && ( $event_type = self::rest_markers_event_type( $request ) ) ) {
			if ( $event_type != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) ) {
				$join .= " LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = p.ID";
			}
		}

		return $join;
	}

	public static function rest_markers_query_where( $where, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && ( $event_type = self::rest_markers_event_type( $request ) ) ) {
			if ( $event_type != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) && ( $condition = GeoDir_Event_Schedules::event_type_condition( $event_type ) ) ) {
				$where .= " AND " . $condition;
			}
		}

		return $where;
	}

	public static function rest_markers_query_group_by( $group_by, $request ) {
		if ( empty( $request['post'] ) && ! empty( $request['post_type'] ) && ( $event_type = self::rest_markers_event_type( $request ) ) ) {
			if ( $event_type != 'all' && GeoDir_Post_types::supports( $request['post_type'], 'events' ) ) {
				$group_by = "p.ID";
			}
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
			if ( ! empty( $term->count ) && ! empty( $term->taxonomy ) && GeoDir_Taxonomies::supports( $term->taxonomy, 'events' ) && ( $count = self::get_term_count( $term->term_id, $term->taxonomy, $query_vars ) ) !== null ) {
				$terms[ $key ]->count = $count;
			}
		}

		return $terms;
	}

	public static function get_term_count( $term_id, $taxonomy, $query_vars ) {
		global $wpdb, $geodir_event_query_vars;

		$cache_key = 'geodir_event_term_count:' . $term_id;

		$cache = wp_cache_get( $cache_key );
		if ( $cache !== false ) {
			return $cache;
		}

		$geodir_event_query_vars = $query_vars;
		$count = null;
		$post_type = self::get_taxonomy_post_type( $taxonomy );

		$term_count_sql = GeoDir_Event_Schedules::location_term_counts( '', $term_id, $taxonomy, $post_type, '', array(), 'term_count', '' );

		if ( $term_count_sql ) {
			$count = (int) $wpdb->get_var( $term_count_sql );
		}

		unset( $geodir_event_query_vars );
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

	public static function custom_key_orderby( $orderby, $sort_by, $order, $current_orderby, $table, $post_type, $wp_query ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) && $sort_by == 'event_dates' ) {
			$order = strtolower( $order ) == 'desc' ? "DESC" : "ASC";
			$orderby = GEODIR_EVENT_SCHEDULES_TABLE . ".start_date {$order}, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time {$order}";
		}

		return $orderby;
	}

	/**
	 * Set event schedule to global $gd_post data.
	 *
	 * ORDER BY & GROUP BY clause issue in sorting event by date with single event filter.
	 *
	 * @since 2.0.0.16
	 *
	 * @param WP_Post $post The Post object (passed by reference).
	 * @param WP_Query $wp_query The current Query object (passed by reference).
	 *
	 * @return WP_Post The Post object.
	 */
	public static function the_gd_post( $post, $wp_query = array() ) {
		global $gd_post;

		if ( ! empty( $post ) && is_object( $post ) && ! empty( $gd_post ) && is_object( $gd_post ) && ! empty( $post->ID ) && ! empty( $gd_post->ID ) && $post->ID == $gd_post->ID ) {
			if ( ! empty( $post->set_schedule_id ) ) {
				$schedule_id = $post->set_schedule_id;
			} elseif ( ! empty( $gd_post->set_schedule_id ) ) {
				$schedule_id = $gd_post->set_schedule_id;
			} else {
				return $post;
			}

			$schedule = GeoDir_Event_Schedules::get_schedule( $schedule_id );

			if ( ! empty( $schedule ) ) {
				foreach ( $schedule as $key => $value ) {
					$gd_post->{$key} = $value;

					if ( isset( $post->start_date ) || isset( $post->post_category ) ) {
						$post->{$key} = $value;
					}
				}
			}
		}

		return $post;
	}

	/**
	 * Filter SEOPress sitemaps index post types query args.
	 *
	 * @since 2.3.2
	 *
	 * @param array  $args Query args.
	 * @param string $cpt_key Post type.
	 * @return array Query args.
	 */
	public static function seopress_sitemaps_index_post_types_query( $args, $cpt_key ) {
		if ( geodir_is_gd_post_type( $cpt_key ) && geodir_get_option( 'seopress_recurring_schedules' ) && GeoDir_Post_types::supports( $cpt_key, 'events' ) ) {
			$args['is_event_post_type'] = true;
			$args['is_event_index'] = true;
			$args['suppress_filters'] = false;
		}

		return $args;
	}

	/**
	 * Filter SEOPress single sitemap query args.
	 *
	 * @since 2.3.2
	 *
	 * @param array  $args Query args.
	 * @param string $path Current path.
	 * @return array Query args.
	 */
	public static function seopress_sitemaps_single_query( $args, $path ) {
		if ( ! empty( $args['post_type'] ) && geodir_is_gd_post_type( $args['post_type'] ) && geodir_get_option( 'seopress_recurring_schedules' ) && GeoDir_Post_types::supports( $args['post_type'], 'events' ) ) {
			$args['is_event_post_type'] = true;
			$args['is_event_archive'] = true;
			$args['suppress_filters'] = false;
		}

		return $args;
	}

	/**
	 * Filter posts query clauses.
	 *
	 * @since 2.3.2
	 *
	 * @param array  $clauses Query clauses.
	 * @param object $wp_query WP_Query.
	 * @return array Query clauses.
	 */
	public static function seopress_posts_clauses_request( $clauses, $wp_query ) {
		global $wpdb;

		if ( ! empty( $wp_query->query_vars['is_event_post_type'] ) && function_exists( 'seopress_activation' ) && geodir_get_option( 'seopress_recurring_schedules' ) && ! empty( $wp_query->query_vars['post_type'] ) && GeoDir_Post_types::supports( $wp_query->query_vars['post_type'], 'events' ) ) {
			$table = geodir_db_cpt_table( $wp_query->query_vars['post_type'] );

			if ( ! empty( $wp_query->query_vars['is_event_archive'] ) ) {
				$clauses['fields'] .= ", gdes.*";
			}
			$clauses['join'] .= " INNER JOIN `{$table}` AS `gdp` ON `gdp`.`post_id` = `{$wpdb->posts}`.`ID` LEFT JOIN `" . GEODIR_EVENT_SCHEDULES_TABLE . "` AS `gdes` ON `gdes`.`event_id` = `{$wpdb->posts}`.`ID`";
			$clauses['groupby'] = "{$wpdb->posts}.ID, `gdes`.`start_date` ASC";
			$clauses['orderby'] .= ", `gdes`.`start_date`";
		}

		return $clauses;
	}

	/**
	 * Set single event parameter for autocomplete event search.
	 *
	 * @since 2.3.21
	 *
	 * @return mixed.
	 */
	public static function search_autocomplete_set_single_event() {
		$post_types = geodir_get_posttypes( 'object' );

		foreach ( $post_types as $post_type => $data ) {
			if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
				echo ' if("' . esc_attr( esc_js( $post_type ) ) . '"==post_type' . ( ! empty( $data->rewrite->slug ) ? '||"' . esc_attr( esc_js( $data->rewrite->slug ) ) . '"==post_type_slug' : '' ) . '){request_url+="&single_event=1";} ';
			}
		}
	}
}
