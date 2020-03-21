<?php
/**
 * GeoDirectory Events core functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Widgets.
 *
 * @since 2.0.0
 */
function goedir_event_register_widgets() {
	if ( get_option( 'geodir_event_version' ) ) {
		register_widget( 'GeoDir_Event_Widget_Calendar' );
		register_widget( 'GeoDir_Event_Widget_AYI' );
		register_widget( 'GeoDir_Event_Widget_Schedules' );
	}
}

/*
 * Matches each symbol of PHP date format standard
 * with jQuery equivalent codeword
 */
function geodir_event_date_format_php_to_jqueryui( $php_format ) {
	$symbols = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => 'tt',
		'A' => 'TT',
		'B' => '',
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => '',
		'u' => ''
	);

	$jqueryui_format = "";
	$escaping = false;

	for ( $i = 0; $i < strlen( $php_format ); $i++ ) {
		$char = $php_format[$i];

		// PHP date format escaping character
		if ( $char === '\\' ) {
			$i++;

			if ( $escaping ) {
				$jqueryui_format .= $php_format[$i];
			} else {
				$jqueryui_format .= '\'' . $php_format[$i];
			}

			$escaping = true;
		} else {
			if ( $escaping ) {
				$jqueryui_format .= "'";
				$escaping = false;
			}

			if ( isset( $symbols[$char] ) ) {
				$jqueryui_format .= $symbols[$char];
			} else {
				$jqueryui_format .= $char;
			}
		}
	}

	return $jqueryui_format;
}

function geodir_event_is_date( $date ) {
	$date = trim( $date );
	
	if ( $date == '' || $date == '0000-00-00 00:00:00' || $date == '0000-00-00' ) {
		return false;
	}
	
	$year = (int)date_i18n( 'Y', strtotime( $date ) );
	
	if ( $year > 1970 ) {
		return true;
	}
	
	return false;
}
 
function geodir_event_is_recurring_active() {
	if ( geodir_get_option( 'event_disable_recurring' ) ) {
		$active = false;
	} else {
		$active = true;
	}

	return apply_filters( 'geodir_event_is_recurring_active', $active );
}

/**
 * Check package has recurring enabled
 */
function geodir_event_recurring_pkg( $post ) {
	$recurring_pkg = geodir_event_is_recurring_active() ? true : false;

	$package = geodir_get_post_package( $post );
	if ( ! empty( $package ) && ! empty( $package->no_recurring ) ) {
		$recurring_pkg = false;
	};

	return apply_filters( 'geodir_event_recurring_pkg', $recurring_pkg, $post, $package );
}

function geodir_event_parse_dates( $dates_input, $array = true ) {
	$dates = array();
	
	if ( !empty( $dates_input ) && $dates_input != '' ) {
		if ( !is_array( $dates_input ) ) {
			$dates_input = explode( ',', $dates_input );
		}
		
		if ( !empty( $dates_input ) ) {
			foreach ( $dates_input as $date ) {
				$date = trim( $date );
				if ( $date != '' && geodir_event_is_date( $date ) ) {
					$dates[] = $date;
				}
			}
		}
	}
	
	if ( !$array ) {
		$dates = implode( ',', $dates );
	}
	
	return $dates;
}

/**
 * Event calendar date format
 *
 */
function geodir_event_field_date_format() {
	$date_format = geodir_get_option( 'event_field_date_format' );
    
    if ( empty( $date_format ) ) {
        $date_format = 'F j, Y';
    }
    // if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.
	return apply_filters( 'geodir_event_field_date_format', $date_format);
}

/**
 * Display event dates date format
 *
 */
function geodir_event_date_format() {
	$date_format = geodir_get_option( 'event_display_date_format' );
    
    if ( geodir_get_option( 'event_use_custom_format' ) ) {
        $date_format = geodir_get_option( 'event_custom_date_format' );
    }
    
    if ( empty( $date_format ) ) {
        $date_format = get_option('date_format');
    }
    
    // if the separator is a slash (/), then the American m/d/y is assumed; whereas if the separator is a dash (-) or a dot (.), then the European d-m-y format is assumed.
	return apply_filters( 'geodir_event_date_format', $date_format );
}

/**
 * Display event dates date format
 *
 */
function geodir_event_time_format() {
	$time_format = get_option('time_format');
    
	return apply_filters( 'geodir_event_time_format', $time_format );
}

/**
 * Display event dates date time format.
 *
 */
function geodir_event_date_time_format() {
    $date_time_format = geodir_event_date_format() . ', ' . geodir_event_time_format();

    return apply_filters( 'geodir_event_date_time_format', $date_time_format );
}

/*
 * Filter the schema data for events.
 *
 * Used to filter the schema data to remove non event fields and add location and event start date/time.
 *
 * @since 1.3.1
 * @param array $schema The schema array of info.
 * @param object $post The post object.
 * @return array The filtered schema array.
 */
function geodir_event_schema( $schema, $post ) {
	global $gd_post;

    $event_schema_types = geodir_event_get_schema_types();
    if ( !empty( $schema['@type']) && isset( $event_schema_types[ $schema['@type'] ] ) ) {
		$place = array();
		$place["@type"] = "Place";
		$place["name"] = $schema['name'];
		$place["address"] = $schema['address'];
		if ( ! empty( $schema['telephone'] ) ) {
			$place["telephone"] = $schema['telephone'];
		}

	    if(isset($schema['geo'])){
		    $place["geo"] = $schema['geo'];
	    }
	    
		if ( GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
			$schedule = NULL;

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

			if ( ! empty($schedule ) ) {
				$startDate = $schedule->start_date;
				$endDate = $schedule->end_date;
				$startTime = ! empty( $schedule->start_time ) ? date_i18n( 'H:i', strtotime( $schedule->start_time ) ) : '00:00';
				$endTime = ! empty( $schedule->end_time ) ? date_i18n( 'H:i', strtotime( $schedule->end_time ) ) : '00:00';
				$all_day = ! empty( $schedule->all_day ) ? true : false;

				if ( $endDate === '' ) {
					$endDate = $startDate;
				}

				if ( ! empty( $schedule->all_day ) ) {
					$startTime = '00:00';
					$endTime = '23:59';
				}

				if ( $startDate == $endDate && $startTime == $endTime && $startTime == '00:00' ) {
					$endTime = '23:59';
				}

				$schema['startDate'] = $startDate . 'T' . $startTime;
				$schema['endDate'] = $endDate . 'T' . $endTime;
			}

			// eventAttendanceMode. If we have an address then its likely not online
			if(!empty($gd_post->city)){
				if(isset($gd_post->event_status) && $gd_post->event_status=='moved-online'){
					$schema['eventAttendanceMode'] = "https://schema.org/OnlineEventAttendanceMode";
				}else{
					$schema['eventAttendanceMode'] = "https://schema.org/OfflineEventAttendanceMode";
				}
			}else{
				$schema['eventAttendanceMode'] = "https://schema.org/OnlineEventAttendanceMode";
			}

			// set if online event
			if($schema['eventAttendanceMode']=='https://schema.org/OnlineEventAttendanceMode' && !empty($gd_post->website)){
				$place["@type"] = "VirtualLocation";
				$place["url"] = esc_url_raw($gd_post->website);
			}

			// eventStatus
			if(!empty($gd_post->event_status)){
				$event_statuses = array(
					'cancelled' => 'https://schema.org/EventCancelled',
					'postponed' => 'https://schema.org/EventPostponed',
					'rescheduled' => 'https://schema.org/EventRescheduled',
					'moved-online' => 'https://schema.org/EventMovedOnline',
					'scheduled' => 'https://schema.org/EventScheduled',
				);
				if(isset($event_statuses[$gd_post->event_status])){
					$schema['eventStatus'] = $event_statuses[$gd_post->event_status];
				}
			}else{
				$schema['eventStatus'] = 'https://schema.org/EventScheduled';
			}

		}
        $schema['location'] = $place;

        unset($schema['telephone']);
        unset($schema['address']);
        unset($schema['geo']);
    }

    return $schema;
}

function geodir_event_get_schema_types() {
	$schemas = array();
	$schemas['Event'] = 'Event';
	$schemas['EventVenue'] = 'EventVenue';
	$schemas['BusinessEvent'] = 'BusinessEvent';
	$schemas['ChildrensEvent'] = 'ChildrensEvent';
	$schemas['ComedyEvent'] = 'ComedyEvent';
	$schemas['CourseInstance'] = 'CourseInstance';
	$schemas['DanceEvent'] = 'DanceEvent';
	$schemas['DeliveryEvent'] = 'DeliveryEvent';
	$schemas['EducationEvent'] = 'EducationEvent';
	$schemas['ExhibitionEvent'] = 'ExhibitionEvent';
	$schemas['Festival'] = 'Festival';
	$schemas['FoodEvent'] = 'FoodEvent';
	$schemas['LiteraryEvent'] = 'LiteraryEvent';
	$schemas['MusicEvent'] = 'MusicEvent';
	$schemas['PublicationEvent'] = 'PublicationEvent';
	$schemas['SaleEvent'] = 'SaleEvent';
	$schemas['ScreeningEvent'] = 'ScreeningEvent';
	$schemas['SocialEvent'] = 'SocialEvent';
	$schemas['SportsEvent'] = 'SportsEvent';
	$schemas['TheaterEvent'] = 'TheaterEvent';
	$schemas['VisualArtsEvent'] = 'VisualArtsEvent';
	return apply_filters( 'geodir_event_get_schema_types', $schemas );
}

function geodir_event_input_date_formats() {
	$formats = array(
		'm/d/Y',
		'd/m/Y',
		'Y/m/d',
		'm-d-Y',
		'd-m-Y',
		'Y-m-d',
		'd.m.Y',
		'j F Y',
		'F j, Y'
	);
	return apply_filters( 'geodir_event_input_date_formats', $formats );
}

function geodir_event_display_date_formats() {
	$formats = array(
		get_option( 'date_format' ),
		'm/d/Y',
		'd/m/Y',
		'Y/m/d',
		'm-d-Y',
		'd-m-Y',
		'Y-m-d',
		'd.m.Y',
		'j F Y',
		'F j, Y',
		'j M Y'
	);
	return apply_filters( 'geodir_event_display_date_formats', $formats );
}

function geodir_event_date_to_ymd( $date, $from_format ) {
	$date 		= geodir_maybe_untranslate_date( $date );
	$temp_date 	= DateTime::createFromFormat( $from_format, $date );
	$date 		= !empty( $temp_date ) ? $temp_date->format( 'Y-m-d') : $date;
	return $date;
}

function geodir_event_array_insert( $array, $position, $insert_array ) {
	$first_array = array_splice ( $array, 0, $position );
	return array_merge ( $first_array, $insert_array, $array );
}

function geodir_event_filter_options( $post_type = 'gd_event' ) {
	$options = array(
		'all' => wp_sprintf( _x( 'All %s', 'Event type filter', 'geodirevents' ), geodir_post_type_name( $post_type, true ) ),
		'upcoming' => __( 'Upcoming', 'geodirevents' ),
		'past' => __( 'Past', 'geodirevents' ),
		'today' => __( 'Today', 'geodirevents' ),
		'tomorrow' => __( 'Tomorrow', 'geodirevents' ),
		'next_7_days' => __( '+7 Days', 'geodirevents' ),
		'next_30_days' => __( '+30 Days', 'geodirevents' ),
		'this_weekend' => __( 'This Weekend', 'geodirevents' ),
		'this_week' => __( 'This Week', 'geodirevents' ),
		'this_month' => __( 'This Month', 'geodirevents' ),
		'next_month' => __( 'Next Month', 'geodirevents' ),
		'next_week' => __( 'Next Week', 'geodirevents' ),
		//'custom' => __( 'Custom Dates', 'geodirevents' ), // @todo implement a lightbox to select two dates for this https://github.com/AyeCode/geodir_event_manager-v2/pull/44/files#diff-d51ba513e10b94969787eb99a1aa1e72R609
	);
	return apply_filters( 'geodir_event_filter_options', $options, $post_type );
}

function geodir_event_time_options( $default = '' ) {
	$event_times = geodir_event_get_times();

	$all_times = '';
	foreach( $event_times as $key => $times ) {
		$selected = ''; 
		if ( $default == $key || $default == $times || '0 '. $default == $times ) {
			 $selected = 'selected="selected"';
		}
		$all_times.= '<option ' . $selected . ' value="' . $key . '">' . $times . '</option>'; 
	}
	return $all_times;
}

function geodir_event_get_times() {
	$time_increment = apply_filters( 'geodir_event_time_increment' , 15 ) ;
	$am = __( '%s AM', 'geodirevents' );
	$pm = __( '%s PM', 'geodirevents' );


	$event_time_array = array();
	for ( $i = 0; $i < 24; $i++ ) {
		 for ( $j = 0; $j < 60; $j += $time_increment ) {
		 	$time_hr_abs = $i;
		 	$time_am_pm = $am;

			if ( $i >= 12) {
				$time_am_pm = $pm;
			}

			if ( $i > 12 ) {
				$time_hr_abs = $i - 12;
			}
		 	if ( $time_hr_abs < 10 ) {
				$time_hr = '0' . $time_hr_abs;
			} else {
				$time_hr = $time_hr_abs;
			}

			if ( $j < 10 ) {
				$time_min = '0' . $j;
			} else {
				$time_min = $j;
			}

			if ( $i < 10 ) {
				$time_hr_index = '0' . $i;
			} else {
				$time_hr_index = $i;
			}

		 	$event_time_array[ $time_hr_index  . ":" . $time_min ] = wp_sprintf( $time_am_pm, $time_hr . ":" . $time_min );
		 }
	}

	return apply_filters( 'geodir_event_schedule_times' , $event_time_array);
}