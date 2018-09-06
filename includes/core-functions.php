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
    if ( get_option( 'geodir_event_version' )) {
        register_widget( 'GeoDir_Event_Widget_Calendar' );
        register_widget( 'GeoDir_Event_Widget_AYI' );
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
function geodir_event_recurring_pkg( $post, $package_info = array() ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$package_info = geodir_post_package_info( $package_info, $post );
	
	$recurring_pkg = true;
	
	if ( is_plugin_active( 'geodir_payment_manager/geodir_payment_manager.php' ) ) {
		if ( !empty( $package_info ) && isset( $package_info->recurring_pkg ) && (int)$package_info->recurring_pkg == 1 ) {
			$recurring_pkg = false;
		};
	}
	
	if ( ! geodir_event_is_recurring_active() ) {
		$recurring_pkg = false;
	}
	 
	return apply_filters( 'geodir_event_recurring_pkg', $recurring_pkg, $post, $package_info );
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
    $event_schema_types = geodir_event_get_schema_types();
    if ( isset( $event_schema_types[ $schema['@type'] ] ) ) {
		$place = array();
		$place["@type"] = "Place";
		$place["name"] = $schema['name'];
		$place["address"] = $schema['address'];
		if ( ! empty( $schema['telephone'] ) ) {
			$place["telephone"] = $schema['telephone'];
		}
		$place["geo"] = $schema['geo'];

        if (!empty($post->event_dates)) {
            $dates = maybe_unserialize($post->event_dates);

            $start_date = isset($dates['start_date']) ? $dates['start_date'] : '';
            $end_date = isset($dates['end_date']) ? $dates['end_date'] : $start_date;
            $all_day = isset($dates['all_day']) && !empty( $dates['all_day'] ) ? true : false;
            $start_time = isset($dates['start_time']) ? $dates['start_time'] : '';
            $end_time = isset($dates['end_time']) ? $dates['end_time'] : '';
            
            $startDate = $start_date;
            $endDate = $end_date;
            $startTime = $start_time;
            $endTime = $end_time;
            
            if (isset($dates['recurring'])) {
                if ($dates['recurring']) {
                    $rdates = explode(',', $dates['recurring_dates']);
                                    
                    $repeat_type = isset($dates['repeat_type']) && in_array($dates['repeat_type'], array('day', 'week', 'month', 'year', 'custom')) ? $dates['repeat_type'] : 'custom';
                    $duration = isset($dates['duration_x']) && $repeat_type != 'custom' && (int)$dates['duration_x'] > 0 ? (int)$dates['duration_x'] : 1;
                    $duration--;
                    
                    $different_times = isset($dates['different_times']) && !empty($dates['different_times']) ? true : false;
                    $astarttimes = isset($dates['starttimes']) && !empty($dates['starttimes']) ? $dates['starttimes'] : array();
                    $aendtimes = isset($dates['endtimes']) && !empty($dates['endtimes']) ? $dates['endtimes'] : array();
                    
                    if (isset($_REQUEST['gde']) && in_array($_REQUEST['gde'], $rdates)) {
                        $key = array_search($_REQUEST['gde'], $rdates);
                        
                        $startDate =  sanitize_text_field($_REQUEST['gde']);
                        
                        if ($repeat_type == 'custom' && $different_times) {
                            if (!empty($astarttimes) && isset($astarttimes[$key])) {
                                $startTime = $astarttimes[$key];
                                $endTime = $aendtimes[$key];
                            } else {
                                $startTime = '';
                                $endTime = '';
                            }
                        }
                    } else {
                        $day_today = date_i18n('Y-m-d');
                        
                        foreach ($rdates as $key => $rdate) {
                            if (strtotime($rdate) >= strtotime($day_today)) {
                                $startDate = date_i18n('Y-m-d', strtotime($rdate));
                                
                                if ($repeat_type == 'custom' && $different_times) {
                                    if (!empty($astarttimes) && isset($astarttimes[$key])) {
                                        $startTime = $astarttimes[$key];
                                        $endTime = $aendtimes[$key];
                                    } else {
                                        $startTime = '';
                                        $endTime = '';
                                    }
                                }
                                break;
                            }
                        }
                    }
                    
                    $endDate = date_i18n('Y-m-d', strtotime($startDate . ' + ' . $duration . ' day'));
                }
            } else {
                if (!empty($dates['recurring_dates']) && $event_recurring_dates = explode(',', $dates['recurring_dates'])) {
                    $day_today = date_i18n('Y-m-d');
                    
                    foreach ($event_recurring_dates as $rdate) {
                        if (strtotime($rdate) >= strtotime($day_today)) {
                            $startDate = date_i18n('Y-m-d', strtotime($rdate));
                            break;
                        }
                    }
                    
                    if ($startDate === '' && !empty($event_recurring_dates)) {
                        $startDate = $event_recurring_dates[0];
                    }
                }
            }
            
            if ($endDate === '') {
                $endDate = $startDate;
            }
            
            $startTime = $startTime !== '' ? $startTime : '00:00';
            $endTime = $endTime !== '' ? $endTime : '00:00';
            
            if ($startDate == $endDate && $startTime == $endTime && $startTime == '00:00') {
                $endTime = '23:59';
            }
            
            $schema['startDate'] = $startDate . 'T' . $startTime;
            $schema['endDate'] = $endDate . 'T' . $endTime;
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
		'today' => __( 'Today', 'geodirevents' ),
		'upcoming' => __( 'Upcoming', 'geodirevents' ),
		'past' => __( 'Past', 'geodirevents' )
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