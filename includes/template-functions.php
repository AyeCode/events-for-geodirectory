<?php
/**
 * GeoDirectory Events template functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

function geodir_event_params() {
	$input_date_format = geodir_event_field_date_format();
	$display_date_format = geodir_event_date_format();
	$jqueryui_date_format = geodir_event_date_format_php_to_jqueryui( $input_date_format );

	$params = array(
		'text_to' => __( 'to', 'geodirevents' ),
		'input_date_format' => $input_date_format,
		'display_date_format' => $display_date_format,
		'jqueryui_date_format' => $jqueryui_date_format,
		'week_start_day' => apply_filters( 'geodir_event_calendar_week_start_day', '0' ),
		'monthsArray' => '["' . __( 'January' ) . '", "' . __( 'February' ) . '", "' . __( 'March' ) . '", "' . __( 'April' ) . '", "' . __( 'May' ) . '", "' . __( 'June' ) . '", "' . __( 'July' ) . '", "' . __( 'August' ) . '", "' . __( 'September' ) . '", "' . __( 'October' ) . '", "' . __( 'November' ) . '", "' . __( 'December' ) . '"]'
    );

    return apply_filters( 'geodir_event_params', $params );
}

function geodir_event_yui_calendar_params() {
	$params = array(
		'month_long_1' => __( 'January' ),
		'month_long_2' => __( 'February' ),
		'month_long_3' => __( 'March' ),
		'month_long_4' => __( 'April' ),
		'month_long_5' => __( 'May' ),
		'month_long_6' => __( 'June' ),
		'month_long_7' => __( 'July' ),
		'month_long_8' => __( 'August' ),
		'month_long_9' => __( 'September' ),
		'month_long_10' => __( 'October' ),
		'month_long_11' => __( 'November' ),
		'month_long_12' => __( 'December' ),
		'month_s_1' => _x( 'Jan','January abbreviation' ),
		'month_s_2' => _x( 'Feb','February abbreviation' ),
		'month_s_3' => _x( 'Mar','March abbreviation' ),
		'month_s_4' => _x( 'Apr' ,'April abbreviation'),
		'month_s_5' => _x( 'May' ,'May abbreviation'),
		'month_s_6' => _x( 'Jun' ,'June abbreviation'),
		'month_s_7' => _x( 'Jul','July abbreviation' ),
		'month_s_8' => _x( 'Aug' ,'August abbreviation'),
		'month_s_9' => _x( 'Sep' ,'September abbreviation'),
		'month_s_10' => _x( 'Oct' ,'October abbreviation'),
		'month_s_11' => _x( 'Nov','November abbreviation' ),
		'month_s_12' => _x( 'Dec','December abbreviation' ),
		'day_s1_1' => _x( 'S' ,'Sunday initial'),
		'day_s1_2' => _x( 'M' ,'Monday initial'),
		'day_s1_3' => _x( 'T' ,'Tuesday initial'),
		'day_s1_4' => _x( 'W' ,'Wednesday initial'),
		'day_s1_5' => _x( 'T' ,'Friday initial'),
		'day_s1_6' => _x( 'F' ,'Thursday initial'),
		'day_s1_7' => _x( 'S' ,'Saturday initial'),
		'day_s2_1' => __( 'Su' ,'geodirevents'),
		'day_s2_2' => __( 'Mo' ,'geodirevents'),
		'day_s2_3' => __( 'Tu','geodirevents' ),
		'day_s2_4' => __( 'We','geodirevents' ),
		'day_s2_5' => __( 'Th','geodirevents' ),
		'day_s2_6' => __( 'Fr' ,'geodirevents'),
		'day_s2_7' => __( 'Sa' ,'geodirevents'),
		'day_s3_1' => __( 'Sun' ),
		'day_s3_2' => __( 'Mon' ),
		'day_s3_3' => __( 'Tue' ),
		'day_s3_4' => __( 'Wed' ),
		'day_s3_5' => __( 'Thu' ),
		'day_s3_6' => __( 'Fri' ),
		'day_s3_7' => __( 'Sat' ),
		'day_s5_1' => __( 'Sunday' ),
		'day_s5_2' => __( 'Monday' ),
		'day_s5_3' => __( 'Tuesday' ),
		'day_s5_4' => __( 'Wednesday' ),
		'day_s5_5' => __( 'Thursday' ),
		'day_s5_6' => __( 'Friday' ),
		'day_s5_7' => __( 'Saturday' ),
		's_previousMonth' => __( 'Previous Month' ),
		's_nextMonth' => __( 'Next Month' ),
		's_close' => __( 'Close' )
	);

	return apply_filters( 'geodir_event_yui_calendar_params', $params );
}

function geodir_event_display_event_type_filter( $post_type ) {
	if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		return;
	}

	$event_types = geodir_event_filter_options( $post_type );

	if ( empty( $event_types ) ) {
		return;
	}

	$event_type 	= ! empty( $_REQUEST['etype'] ) ? sanitize_text_field( $_REQUEST['etype'] ) : geodir_get_option( 'event_default_filter' );
	$current_url 	= str_replace( '#038;', '&', geodir_curPageURL() );
	$current_url	= remove_query_arg( array( 'etype' ), $current_url );

	$options = '';
	foreach ( $event_types as $value => $label ) {
		$url = add_query_arg( array( 'etype' => $value ), $current_url );
		$url = apply_filters( 'geodir_event_type_filter_url', $url, $value );

		$options .= '<option ' . selected( $value, $event_type, false ) . ' value="' . esc_url( $url ) . '">' . $label . '</option>';
	}

	$content = '<div class="geodir-event-filter">';
	$content .= '<select name="etype" id="etype" class="geodir-select" onchange="javascript:window.location=this.value;">' . $options . '</select>';
	$content .= '</div>';

	echo $content;
}

function geodir_event_seo_variables( $vars, $gd_page = '' ) {
	if ( $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' ) {
		$vars['%%event_type_archive%%'] = __( 'Event type. Eg: Past, Today, Upcoming', 'geodirevents' );
	}

	// Single event
	if ( $gd_page == 'single' ) {
		$current_time = current_time( 'timestamp' );
		$display_date_format = geodir_event_date_format();
		$display_time_format = geodir_event_time_format();
		$start_date = date_i18n( $display_date_format, $current_time );
		$end_date = date_i18n( $display_date_format, $current_time + DAY_IN_SECONDS );
		$start_time = date_i18n( $display_time_format, $current_time );
		$end_time = date_i18n( $display_time_format, $current_time + ( HOUR_IN_SECONDS * 8 ) );

		$vars['%%event_start_date%%'] = wp_sprintf( __( 'Evevt start date. Eg: %s', 'geodirevents' ), $start_date );
		$vars['%%event_end_date%%'] = wp_sprintf( __( 'Evevt past date. Eg: %s', 'geodirevents' ), $end_date );
		$vars['%%event_start_time%%'] = wp_sprintf( __( 'Evevt start time. Eg: %s', 'geodirevents' ), $start_time );
		$vars['%%event_end_time%%'] = wp_sprintf( __( 'Evevt end time. Eg: %s', 'geodirevents' ), $end_time );
		$vars['%%event_start_to_end_date%%'] = wp_sprintf( __( 'Evevt start date - end date. Eg: %s', 'geodirevents' ), $start_date . ' - ' . $end_date );
		$vars['%%event_start_to_end_time%%'] = wp_sprintf( __( 'Evevt start time - end time. Eg: %s', 'geodirevents' ), $start_time . ' - ' . $end_time );
	}
    return $vars;
}

function geodir_event_replace_seo_vars( $title, $gd_page ) {
    global $gd_post;

	if ( strpos( $title, '%%event_' ) === false ) {
		return $title;
	}

	$event_type_archive = '';
	$event_start_date = '';
	$event_end_date = '';
	$event_start_to_end_date = '';
	$event_start_time = '';
	$event_end_time = '';
	$event_start_to_end_time = '';

	if ( strpos( $title, '%%event_type_archive%%' ) !== false && ! empty( $_REQUEST['etype'] ) ) {
		$event_type_archive = geodir_event_type_title( sanitize_text_field( $_REQUEST['etype'] ) );
	}
	
	if ( ( $gd_page == 'detail' || $gd_page == 'single' ) && is_single() && ! empty( $gd_post ) && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
		$date_format = geodir_event_date_format();
		$time_format = geodir_event_time_format();

		if ( ! empty( $gd_post->recurring ) ) { // Recurring event
			if ( ! empty( $_REQUEST['gde'] ) ) {
				$schedule = GeoDir_Event_Schedules::get_upcoming_schedule( $gd_post->ID, sanitize_text_field( $_REQUEST['gde'] ) );
			} else {
				if ( ! ( $schedule = GeoDir_Event_Schedules::get_upcoming_schedule( $gd_post->ID, date_i18n( 'Y-m-d' ) ) ) ) {
					$schedule = GeoDir_Event_Schedules::get_start_schedule( $gd_post->ID );
				}
			}
		} else {
			$schedule = GeoDir_Event_Schedules::get_start_schedule( $gd_post->ID );
		}

		if ( ! empty( $schedule ) ) {
			$event_start_date = date_i18n( $date_format, strtotime( $schedule->start_date ) );
			$event_end_date = date_i18n( $date_format, strtotime( $schedule->end_date ) );
			$event_start_time = date_i18n( $time_format, strtotime( $schedule->start_time ) );
			$event_end_time = date_i18n( $time_format, strtotime( $schedule->end_time ) );
			$event_start_to_end_date = $event_start_date;
			if ( $event_start_date !== $event_end_date ) {
				$event_start_to_end_date .= ' - ' . $event_end_date;
			}
			$event_start_to_end_time = $event_start_time . ' ' . __( 'to', 'geodirevents' ) . ' ' . $event_end_time;
		}
	}

	$event_title_vars = array();
	$event_title_vars['%%event_type_archive%%'] = $event_type_archive;
	$event_title_vars['%%event_start_date%%'] = $event_start_date;
	$event_title_vars['%%event_end_date%%'] = $event_end_date;
	$event_title_vars['%%event_start_to_end_date%%'] = $event_start_to_end_date;
	$event_title_vars['%%event_start_time%%'] = $event_start_time;
	$event_title_vars['%%event_end_time%%'] = $event_end_time;
	$event_title_vars['%%event_start_to_end_time%%'] = $event_start_to_end_time;

	$title = str_replace( array_keys( $event_title_vars ), array_values( $event_title_vars ), $title );

    return $title;
}

function geodir_event_filter_title_seo_vars( $title, $location_array, $gd_page, $sep ) {
    return geodir_event_replace_seo_vars( $title, $gd_page );
}

function geodir_event_filter_searched_params( $params = array(), $post_type ) {
    global $geodir_date_format;
    
    $event_date = !empty( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : '';

    if ( $event_date ) {
        $params[] = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-event_date" data-name="event_date"><i class="fas fa-calendar-alt" aria-hidden="true"></i> ' . date_i18n( $geodir_date_format, strtotime( $event_date ) ) . '</label>';
    }

	if ( ! empty( $_REQUEST['event_dates'] ) ) {
		$date_format = geodir_event_date_format();

		$event_date = $_REQUEST['event_dates'];

		$dates = '';
		if ( is_array( $event_date ) ) {
			$from_date = ! empty( $event_date['from'] ) ? date_i18n( $date_format, strtotime( sanitize_text_field( $event_date['from'] ) ) ) : '';
			$to_date = ! empty( $event_date['to'] ) ? date_i18n( $date_format, strtotime( sanitize_text_field( $event_date['to'] ) ) ) : '';

			$extra_attrs = 'data-name="event_dates[from]" data-names="event_dates[to]"';
			if ( $from_date != '' && $to_date == '' ) {
				$dates .= wp_sprintf( __( 'From: %s', 'geodiradvancesearch' ), $from_date );
			} else if ( $from_date == '' && $to_date != '' ) {
				$dates .= wp_sprintf( __( 'To: %s', 'geodiradvancesearch' ), $to_date );
			} else if ( $from_date != '' && $to_date != '' ) {
				$dates .= $from_date.' - ' . $to_date;
			}
		} else {
			$extra_attrs = 'data-name="event_dates"';
			$dates .= date_i18n( $date_format, strtotime( sanitize_text_field( $event_date ) ) );
		}

		if ( $dates != '' ) {
			$params[] = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-event_dates" ' . $extra_attrs . '><i class="fas fa-times" aria-hidden="true"></i> ' . $dates . '</label>';
		}
	}

    return $params;
}

function geodir_event_type_title( $event_type, $post_type = 'gd_event' ) {
	$event_types = geodir_event_filter_options( $post_type );

	if ( ! empty( $event_type ) ) {
		$title = isset( $event_types[ $event_type ] ) ? $event_types[ $event_type ] : $event_type;
	} else {
		$title = $event_type;
	}	

	return apply_filters( 'geodir_event_type_title', $title, $event_type, $post_type );
}

/**
 * Add the query vars to the term link to retrieve today & upcoming events.
 *
 * @since 1.1.9
 *
 * @param string $term_link The term permalink.
 * @param int    $cat->term_id The term id.
 * @param string $post_type Wordpress post type.
 * @return string The category term link.
 */
function geodir_event_category_term_link( $term_link, $term_id, $post_type ) {
	if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		return $term_link;
	}
	
	$term_link = add_query_arg( array( 'etype' => geodir_get_option( 'event_default_filter' ) ), $term_link );

	return $term_link;
}

// add date to title for recurring event
function geodir_event_title_recurring_event( $title, $post_id = null ) {
	global $post, $gd_post;

    $post_type = ! empty( $post->post_type ) ? $post->post_type : '';
    if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		return $title;
	}

	if ( ! empty( $gd_post ) && isset( $gd_post->start_date ) ) {
		$event_post = $gd_post;
	} else {
		$event_post = $post;
	}

	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $event_post );
	if ( ! $recurring_pkg ) {
		return $title;
	}

	if ( isset($event_post->ID ) && $event_post->ID == $post_id && !empty( $event_post->recurring ) ) {
		$geodir_date_format = geodir_event_date_format();
		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ));
		$current_time = strtotime( $current_date );
		
		if ( !empty( $event_post->start_date ) && geodir_event_is_date( $event_post->start_date ) ) {
			$event_start_time = strtotime( date_i18n( 'Y-m-d', strtotime( $event_post->start_date ) ) );
			$event_end_time = isset( $event_post->end_date ) && geodir_event_is_date( $event_post->end_date ) ? strtotime( $event_post->end_date ) : 0;
			
			if ($event_end_time > $event_start_time && $event_start_time <= $current_time && $event_end_time >= $current_time) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, $current_time ) ) . "</span>";
			} else {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( $event_post->start_date ) ) ) . "</span>";
			}
		} else {
			if ( is_single() && isset( $_REQUEST['gde'] ) && geodir_event_is_date( $_REQUEST['gde'] ) && GeoDir_Event_Schedules::has_schedule( $post_id, sanitize_text_field( $_REQUEST['gde'] ) ) ) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( $_REQUEST['gde'] ) ) ) . "</span>";
			}
		}
	}
	return $title;
}

// get link for recurring event
function geodir_event_recurring_event_link( $link ) {
	global $post, $gd_post;

	if ( ! ( ! empty( $post ) && ! empty( $gd_post ) && isset( $post->ID ) && isset( $gd_post->ID ) && $post->ID == $gd_post->ID ) ) {
		return $link;
	}

    $post_type = ! empty( $gd_post->post_type ) ? $gd_post->post_type : '';
	if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) { 
		return $link;
	}

	if ( isset( $gd_post->start_date ) ) {
		$event_post = $gd_post;
	} else {
		$event_post = $post;
	}
	
	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $event_post );
	
	if ( ! $recurring_pkg ) {
		return $link;
	}
	
	if ( ! empty( $event_post->recurring ) && ! empty( $event_post->start_date ) ) {
		if ( geodir_event_is_date( $event_post->start_date ) && get_permalink() == get_permalink( $event_post->ID ) ) {
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ));
			$current_time = strtotime( $current_date );
			
			$event_start_time = strtotime( date_i18n( 'Y-m-d', strtotime( $event_post->start_date ) ) );
			$event_end_time = isset( $event_post->end_date ) && geodir_event_is_date( $event_post->end_date ) ? strtotime( $event_post->end_date ) : 0;
			
			if ( $event_end_time > $event_start_time && $event_start_time <= $current_time && $event_end_time >= $current_time ) {
				$link_date = date_i18n( 'Y-m-d', strtotime( $current_time ) );
			} else {
				$link_date = date_i18n( 'Y-m-d', strtotime( $event_post->start_date ) );
			}
		
			// recuring event link
			$link = geodir_getlink( get_permalink( $event_post->ID ), array( 'gde' => $link_date ) );
		}
	}
	return $link;
}

/**
 * Filter the page link to best of widget view all listings.
 *
 * @since 1.2.4
 *
 * @param string $view_all_link View all listings page link.
 * @param string $post_type The Post type.
 * @param object $term The category term object.
 * @return string Link url.
 */
function geodir_event_bestof_widget_view_all_link( $view_all_link, $post_type, $term ) {
	if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		$view_all_link = add_query_arg( array( 'etype' => geodir_get_option( 'event_default_filter' ) ), $view_all_link ) ;
	}
	return $view_all_link;
}

function geodir_event_super_duper_widget_init( $options, $super_duper ) {
	global $gd_listings_widget_js;

	if ( ! $gd_listings_widget_js && ! empty( $options['base_id'] ) && $options['base_id'] == 'gd_listings' ) {
		$gd_listings_widget_js = true;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'geodir-event-widget', GEODIR_EVENT_PLUGIN_URL . '/assets/js/widget' . $suffix . '.js', array( 'jquery' ), GEODIR_EVENT_VERSION );

		wp_enqueue_script( 'geodir-event-widget' );
	}
}

function geodir_event_super_duper_arguments( $arguments, $options, $instance = array() ) {
	if ( ! empty( $options['textdomain'] ) && $options['textdomain'] == GEODIRECTORY_TEXTDOMAIN && ! defined( 'GEODIR_CP_VERSION' ) ) {
		if ( $options['base_id'] == 'gd_listings' ) {
			if ( ! empty( $arguments['category'] ) && ! empty( $instance['post_type'] ) ) {
				$arguments['category']['options'] = geodir_category_options( $instance['post_type'] );
			}
			if ( ! empty( $arguments['sort_by'] ) && ! empty( $instance['post_type'] ) ) {
				$arguments['sort_by']['options'] = geodir_sort_by_options( $instance['post_type'] );
			}
		}
	}
	return $arguments;
}