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
		'week_start_day' => apply_filters( 'geodir_event_calendar_week_start_day', get_option( 'start_of_week' ) ),
		'monthsArray' => '["' . __( 'January' ) . '", "' . __( 'February' ) . '", "' . __( 'March' ) . '", "' . __( 'April' ) . '", "' . __( 'May' ) . '", "' . __( 'June' ) . '", "' . __( 'July' ) . '", "' . __( 'August' ) . '", "' . __( 'September' ) . '", "' . __( 'October' ) . '", "' . __( 'November' ) . '", "' . __( 'December' ) . '"]',
		'calendar_params' => apply_filters( 'geodir_event_calendar_extra_params', '' ),
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

	$design_style = geodir_design_style();
	
	$event_type 	= ! empty( $_REQUEST['etype'] ) ? sanitize_text_field( $_REQUEST['etype'] ) : geodir_get_option( 'event_default_filter' );
	$current_url 	= str_replace( '#038;', '&', geodir_curPageURL() );
	
	//Search and remove the current page number from url
	$current_url 	= preg_replace ( '/(\/page\/\d+)/mi' , '' , $current_url );

	//In case of ugly permalinks
	$current_url	= remove_query_arg( array( 'page', 'etype' ), $current_url );

	$template = $design_style ? $design_style."/loop/filter.php" : "legacy/loop/filter.php";
	$args = array(
		'event_types'    => $event_types,
		'event_type'    => $event_type,
		'current_url'  => $current_url
	);

	echo geodir_get_template_html( $template , $args, '', plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ). "templates/");

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
		$start_date_ymd = date( 'Y-m-d', $current_time );
		$end_date_ymd = date( 'Y-m-d', $current_time + DAY_IN_SECONDS );
		$start_time_hi = date( 'H:i:s', $current_time );
		$end_time_hi = date( 'H:i:s', $current_time + ( HOUR_IN_SECONDS * 8 ) );
		$timezone = geodir_gmt_offset();

		$vars['%%event_start_date%%'] = wp_sprintf( __( 'Event start date. Eg: %s', 'geodirevents' ), $start_date );
		$vars['%%event_end_date%%'] = wp_sprintf( __( 'Event past date. Eg: %s', 'geodirevents' ), $end_date );
		$vars['%%event_start_time%%'] = wp_sprintf( __( 'Event start time. Eg: %s', 'geodirevents' ), $start_time );
		$vars['%%event_end_time%%'] = wp_sprintf( __( 'Event end time. Eg: %s', 'geodirevents' ), $end_time );
		$vars['%%event_start_to_end_date%%'] = wp_sprintf( __( 'Event start date - end date. Eg: %s', 'geodirevents' ), $start_date . ' - ' . $end_date );
		$vars['%%event_start_to_end_time%%'] = wp_sprintf( __( 'Event start time - end time. Eg: %s', 'geodirevents' ), $start_time . ' - ' . $end_time );
		$vars['%%event_start_date_ymd%%'] = wp_sprintf( __( 'Event start date (Y-m-d). Eg: %s', 'geodirevents' ), $start_date_ymd );
		$vars['%%event_end_date_ymd%%'] = wp_sprintf( __( 'Event past date (Y-m-d). Eg: %s', 'geodirevents' ), $end_date_ymd );
		$vars['%%event_start_time_hi%%'] = wp_sprintf( __( 'Event start time (H:i:s). Eg: %s', 'geodirevents' ), $start_time_hi );
		$vars['%%event_end_time_hi%%'] = wp_sprintf( __( 'Event end time (H:i:s). Eg: %s', 'geodirevents' ), $end_time_hi );
		$vars['%%event_start_datetime_iso%%'] = wp_sprintf( __( 'Event start time (ISO). Eg: %s', 'geodirevents' ), $start_date_ymd . 'T' . $start_time_hi . $timezone );
		$vars['%%event_end_datetime_iso%%'] = wp_sprintf( __( 'Event start time (ISO). Eg: %s', 'geodirevents' ), $end_date_ymd . 'T' . $end_time_hi . $timezone );
		$vars['%%event_tz_offset%%'] = wp_sprintf( __( 'Event Timezone Offset. Eg: %s', 'geodirevents' ), $timezone );
	}
    return $vars;
}

function geodir_event_replace_seo_vars( $title, $gd_page ) {
    global $gd_post;

	if ( strpos( $title, '%%event_' ) === false ) {
		return $title;
	}

	$event_replacements = geodir_event_get_replacements();

	$title = str_replace( array_keys( $event_replacements ), array_values( $event_replacements ), $title );

    return $title;
}

/**
 * Filter the title variables after standard ones have been filtered for wpseo.
 *
 * @since 2.0.0.13
 *
 * @param array $replacements The replacements.
 * @param array $location_array The array of location variables.
 * @return array Filtered replacements.
 */
function geodir_event_wpseo_replacements( $replacements, $location_array ) {
	$event_replacements = geodir_event_get_replacements();

	if ( ! empty( $event_replacements ) ) {
		$replacements = array_merge( $replacements, $event_replacements );
	}

	return $replacements;
}

/**
 * Get the event title & meta replacements.
 *
 * @since 2.0.0.13
 *
 * @return array The replacements.
 */
function geodir_event_get_replacements() {
	global $gd_post;

	$event_type_archive = '';
	$event_start_date = '';
	$event_end_date = '';
	$event_start_to_end_date = '';
	$event_start_time = '';
	$event_end_time = '';
	$event_start_to_end_time = '';
	$start_date_ymd = '';
	$end_date_ymd = '';
	$start_time_hi = '';
	$end_time_hi = '';
	$start_datetime_iso = '';
	$end_datetime_iso = '';
	$timezone = geodir_gmt_offset();
	if ( ! empty( $gd_post->timezone_offset ) ) {
		$timezone = $gd_post->timezone_offset;
	}

	if ( ! empty( $_REQUEST['etype'] ) ) {
		$event_type_archive = geodir_event_type_title( sanitize_text_field( $_REQUEST['etype'] ) );
	}
	
	if ( ! empty( $gd_post ) && is_single() && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
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
			$startTime = ! empty( $schedule->start_time ) ? date( 'H:i', strtotime( $schedule->start_time ) ) : '00:00';
			$endTime = ! empty( $schedule->end_time ) ? date( 'H:i', strtotime( $schedule->end_time ) ) : '00:00';

			$event_start_date = date_i18n( $date_format, strtotime( $schedule->start_date ) );
			$event_end_date = date_i18n( $date_format, strtotime( $schedule->end_date ) );
			if ( $event_end_date === '' || empty( $schedule->end_date ) ) {
				$event_end_date = $event_start_date;
			}
			$event_start_time = date_i18n( $time_format, strtotime( $startTime ) );
			$event_end_time = date_i18n( $time_format, strtotime( $endTime ) );
			$event_start_to_end_date = $event_start_date;
			if ( $event_start_date !== $event_end_date ) {
				$event_start_to_end_date .= ' - ' . $event_end_date;
			}
			$event_start_to_end_time = $event_start_time . ' ' . __( 'to', 'geodirevents' ) . ' ' . $event_end_time;
			
			$start_date_ymd = $schedule->start_date;
			$end_date_ymd = $schedule->end_date;
			if ( $end_date_ymd === '' || empty( $end_date_ymd ) || $end_date_ymd === '0000-00-00' ) {
				$end_date_ymd = $start_date_ymd;
			}
			$start_time_hi = date( 'H:i:s', strtotime( $startTime ) );
			$end_time_hi = date( 'H:i:s', strtotime( $endTime ) );
			if ( ! empty( $schedule->all_day ) ) {
				$start_time_hi = '00:00:00';
				$end_time_hi = '23:59:59';
			}
			if ( $start_date_ymd == $end_date_ymd && $start_time_hi == $end_time_hi && $start_time_hi == '00:00' ) {
				$end_time_hi = '23:59:59';
			}
			$start_datetime_iso = $start_date_ymd . 'T' . $start_time_hi . $timezone;
			$end_datetime_iso = $end_date_ymd . 'T' . $end_time_hi . $timezone;
		}
	}

	$replacements = array();
	$replacements['%%event_type_archive%%'] = $event_type_archive;
	$replacements['%%event_start_date%%'] = $event_start_date;
	$replacements['%%event_end_date%%'] = $event_end_date;
	$replacements['%%event_start_to_end_date%%'] = $event_start_to_end_date;
	$replacements['%%event_start_time%%'] = $event_start_time;
	$replacements['%%event_end_time%%'] = $event_end_time;
	$replacements['%%event_start_to_end_time%%'] = $event_start_to_end_time;
	$replacements['%%event_start_date_ymd%%'] = $start_date_ymd;
	$replacements['%%event_end_date_ymd%%'] = $end_date_ymd;
	$replacements['%%event_start_time_hi%%'] = $start_time_hi;
	$replacements['%%event_end_time_hi%%'] = $end_time_hi;
	$replacements['%%event_start_datetime_iso%%'] = $start_datetime_iso;
	$replacements['%%event_end_datetime_iso%%'] = $end_datetime_iso;
	$replacements['%%event_tz_offset%%'] = $timezone;

	return $replacements;
}

function geodir_event_filter_title_seo_vars( $title, $location_array, $gd_page, $sep ) {
    return geodir_event_replace_seo_vars( $title, $gd_page );
}

function geodir_event_filter_searched_params( $params = array(), $post_type = '', $fields = array() ) {
	global $geodir_date_format, $aui_bs5;

	$frontend_title = __( 'Event date', 'geodirectory' );
	if ( ! empty( $fields ) ) {
		foreach( $fields as $key => $field ) {
			if ( $field->htmlvar_name == 'event_dates' ) {
				$frontend_title = $field->frontend_title != '' ? $field->frontend_title : $field->admin_title;
				$frontend_title = stripslashes( __( $frontend_title, 'geodirectory' ) );
			}
		}
	}

	$design_style = geodir_design_style();

	$label_class = 'gd-adv-search-label';
	$sublabel_class = 'gd-adv-search-label-t';
	if ( $design_style ) {
		$label_class .= ' badge c-pointer ';
		$label_class .= $aui_bs5 ? 'bg-info me-2' : 'badge-info mr-2';
		$sublabel_class .= ' mb-0 c-pointer ' . ( $aui_bs5 ? 'me-1' : 'mr-1' );
	}

	$event_date = !empty( $_REQUEST['event_date'] ) ? sanitize_text_field( $_REQUEST['event_date'] ) : '';

	if ( $event_date ) {
		$params[] = '<label class="' . $label_class . ' gd-adv-search-date gd-adv-search-event_date" data-name="event_date"><i class="fas fa-calendar-alt" aria-hidden="true"></i> <label class="' . $sublabel_class . '">' . $frontend_title . ': </label>' . date_i18n( $geodir_date_format, strtotime( $event_date ) ) . '</label>';
	}

	if ( ! empty( $_REQUEST['event_dates'] ) ) {
		$date_format = geodir_event_date_format();

		$event_dates = geodir_event_sanitize_text_field( $_REQUEST['event_dates'] );

		// Date range
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

		$dates = '';
		$extra_attrs = 'data-name="event_dates"';
		if ( is_array( $event_dates ) ) {
			$from_date = ! empty( $event_dates['from'] ) ? date_i18n( $date_format, strtotime( sanitize_text_field( $event_dates['from'] ) ) ) : '';
			$to_date = ! empty( $event_dates['to'] ) ? date_i18n( $date_format, strtotime( sanitize_text_field( $event_dates['to'] ) ) ) : '';

			if ( ! $design_style ) {
				$extra_attrs = 'data-name="event_dates[from]" data-names="event_dates[to]"';
			}
			if ( $from_date != '' && $to_date == '' ) {
				$dates .= wp_sprintf( __( 'from %s', 'geodiradvancesearch' ), $from_date );
			} else if ( $from_date == '' && $to_date != '' ) {
				$dates .= wp_sprintf( __( 'to %s', 'geodiradvancesearch' ), $to_date );
			} else if ( $from_date != '' && $to_date != '' ) {
				$dates .= wp_sprintf( __( '%s to %s', 'geodiradvancesearch' ), $from_date, $to_date );
			}
		} else {
			$dates .= date_i18n( $date_format, strtotime( sanitize_text_field( $event_dates ) ) );
		}

		if ( $dates != '' ) {
			$params[] = '<label class="' . $label_class . ' gd-adv-search-date gd-adv-search-event_dates" ' . $extra_attrs . '><i class="fas fa-times" aria-hidden="true"></i> <label class="' . $sublabel_class . '">' . $frontend_title . ': </label>' . $dates . '</label>';
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
 * @param int    $term_id The term id.
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

	$remove_date = geodir_get_option( 'event_remove_title_date' ) ? true : false;

	/**
	 * Remove date form recurring event schedule title.
	 *
	 * @since 2.1.1.8
	 *
	 * @param bool $remove_date True to remove date from title.
	 * @param object $event_post Event post object.
	 */
	$remove_date = apply_filters( 'geodir_event_remove_title_recurring_date', $remove_date, $event_post );

	if ( $remove_date ) {
		return $title;
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
			if ( is_single() && isset( $_REQUEST['gde'] ) && geodir_event_is_date( sanitize_text_field( $_REQUEST['gde'] ) ) && GeoDir_Event_Schedules::has_schedule( $post_id, sanitize_text_field( $_REQUEST['gde'] ) ) ) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( sanitize_text_field( $_REQUEST['gde'] ) ) ) ) . "</span>";
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
		
			// recurring event link
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
		// Check script on call.
		if ( ! is_admin() && function_exists( 'geodir_load_scripts_on_call' ) && geodir_load_scripts_on_call() ) {
			return;
		}

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

/**
 * Add event date & time in RSS feed.
 *
 * @since 2.0.0.16
 *
 * @global WP_Post $post The post to edit.
 */
function geodir_event_rss_item() {
	global $post;

	if ( ! empty( $post ) && ! empty( $post->start_date ) && ! empty( $post->end_date ) && isset( $post->start_time ) && isset( $post->end_time ) && geodir_is_gd_post_type( $post->post_type ) ) {
		$start_date = $post->start_date . ' ' . $post->start_time;
		$end_date = $post->end_date . ' ' . $post->end_time;
		if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
			$end_date = date_i18n( 'Y-m-d ' . $post->end_time, strtotime( $end_date ) + DAY_IN_SECONDS );
		}

		$start_date = mysql2date( 'D, d M Y H:i:s +0000', get_gmt_from_date( $start_date ), false );
		$end_date = mysql2date( 'D, d M Y H:i:s +0000', get_gmt_from_date( $end_date ), false );

		$item = '<ev:gd_event_meta xmlns:ev="Event">';
		$item .= '<ev:startdate>' . $start_date . '</ev:startdate>';
		if ( $start_date != $end_date ) {
			$item .= '<ev:enddate>' . $end_date . '</ev:enddate>';
		}
		$item .= '</ev:gd_event_meta>';

		$item = apply_filters( 'geodir_event_rss_item', $item, $post );

		echo $item;
	}
}

/**
 * Get event CPT default sort fields..
 *
 * @since 2.2.1
 *
 * @param string $post_type The post type.
 * @return array Sort fields array.
 */
function geodir_event_default_sort_fields( $post_type = 'gd_event' ) {
	$fields = array(
		array(
			'post_type' => $post_type,
			'data_type' => '',
			'field_type' => 'datetime',
			'frontend_title' => __( 'Event Date', 'geodirevents' ),
			'htmlvar_name' => 'event_dates',
			'sort' => 'asc',
			'is_active' => '1',
			'is_default' => '1',
		),
		array(
			'post_type' => $post_type,
			'data_type' => '',
			'field_type' => 'datetime',
			'frontend_title' => __( 'Newest', 'geodirevents' ),
			'htmlvar_name' => 'post_date',
			'sort' => 'desc',
			'is_active' => '1',
			'is_default' => '0',
		),
		array(
			'post_type' => $post_type,
			'data_type' => 'VARCHAR',
			'field_type' => 'text',
			'frontend_title' => __( 'Title','geodirevents' ),
			'htmlvar_name' => 'post_title',
			'sort' => 'asc',
			'is_active' => '1',
			'is_default' => '0',
		),
		array(
			'post_type' => $post_type,
			'data_type' => 'VARCHAR',
			'field_type' => 'float',
			'frontend_title' => __( 'Rating', 'geodirevents' ),
			'htmlvar_name' => 'overall_rating',
			'sort' => 'desc',
			'is_active' => '1',
			'is_default' => '0',
		)
	);

	return $fields;
}