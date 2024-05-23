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
 * Register event widgets.
 *
 * @since 2.0.0.0
 *
 * @param array $widgets The list of available widgets.
 * @return array Available GD widgets.
 */
function goedir_event_register_widgets( $widgets ) {
	if ( get_option( 'geodir_event_version' ) ) {
		$widgets[] = 'GeoDir_Event_Widget_AYI';
		$widgets[] = 'GeoDir_Event_Widget_Calendar';
		$widgets[] = 'GeoDir_Event_Widget_Schedules';
	}

	return $widgets;
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

	if ( ! empty( $schema['@type']) && isset( $event_schema_types[ $schema['@type'] ] ) ) {
		$place = array();
		$place["@type"] = "Place";
		$place["name"] = !empty( $schema['name'] )? $schema['name']: '';
		$place["address"] = !empty( $schema['address'] )? $schema['address']: '';
		$telephone = !empty( $schema['telephone'] )? $schema['telephone']: '';
		if ( ! empty( $telephone ) ) {
			$place["telephone"] = $schema['telephone'];
		}

		$place["geo"] = !empty( $schema['geo'] )? $schema['geo']: '';

		if ( GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
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
				$timezone = geodir_gmt_offset();
				if ( ! empty( $gd_post->timezone_offset ) ) {
					$timezone = $gd_post->timezone_offset;
				}
				$startDate = $schedule->start_date;
				$endDate = $schedule->end_date;
				$startTime = ! empty( $schedule->start_time ) ? date_i18n( 'H:i', strtotime( $schedule->start_time ) ) : '00:00';
				$endTime = ! empty( $schedule->end_time ) ? date_i18n( 'H:i', strtotime( $schedule->end_time ) ) : '00:00';

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

				$schema['startDate'] = $startDate . 'T' . $startTime . $timezone;
				$schema['endDate'] = $endDate . 'T' . $endTime . $timezone;
			}

			// eventAttendanceMode. If we have an address then its likely not online
			if ( ! empty( $gd_post->city ) ) {
				if ( isset( $gd_post->event_status ) && $gd_post->event_status == 'moved-online' ) {
					$schema['eventAttendanceMode'] = "https://schema.org/OnlineEventAttendanceMode";
				} else {
					$schema['eventAttendanceMode'] = "https://schema.org/OfflineEventAttendanceMode";
				}
			} else {
				$schema['eventAttendanceMode'] = "https://schema.org/OnlineEventAttendanceMode";
			}

			// set if online event
			if ( $schema['eventAttendanceMode'] == 'https://schema.org/OnlineEventAttendanceMode' && ! empty( $gd_post->website ) ) {
				$place["@type"] = "VirtualLocation";
				$place["url"] = esc_url_raw( $gd_post->website );

				// The properties address & geo are not recognized by Google for an object of type VirtualLocation.
				if ( isset( $place["address"] ) ) {
					unset( $place["address"] );
				}

				if ( isset( $place["geo"] ) ) {
					unset( $place["geo"] );
				}
			}

			// eventStatus
			if ( ! empty( $gd_post->event_status ) ) {
				$event_statuses = array(
					'cancelled' => 'https://schema.org/EventCancelled',
					'postponed' => 'https://schema.org/EventPostponed',
					'rescheduled' => 'https://schema.org/EventRescheduled',
					'moved-online' => 'https://schema.org/EventMovedOnline',
					'scheduled' => 'https://schema.org/EventScheduled',
				);
				if ( isset( $event_statuses[ $gd_post->event_status ] ) ) {
					$schema['eventStatus'] = $event_statuses[ $gd_post->event_status ];
				}
			} else {
				$schema['eventStatus'] = 'https://schema.org/EventScheduled';
			}

			// Event performer: performer
			$performer = array(
				'@type' => 'Person'
			);

			// Performer name fields
			$performer_name_fields = array( 'event_performer', 'eventperformer', 'event_performer_name', 'eventperformername', 'performer', 'performer_name', 'performername' );
			$performer_name_fields = apply_filters( 'geodir_event_schema_performer_name_fields', $performer_name_fields, $gd_post, $schema );

			if ( ! empty( $performer_name_fields ) ) {
				foreach ( $performer_name_fields as $_field ) {
					if ( ! empty( $gd_post->{$_field} ) ) {
						$performer_name = stripslashes( sanitize_text_field( $gd_post->{$_field} ) );

						if ( ! empty( $performer_name ) ) {
							// performer.name
							$performer['name'] = $performer_name;
							break;
						}
					}
				}

				if ( ! empty( $performer['name'] ) ) {
					$performer_url_fields = array( 'event_performer_url', 'eventperformerurl', 'performer_url', 'performerurl' );
					$performer_url_fields = apply_filters( 'geodir_event_schema_performer_url_fields', $performer_url_fields, $gd_post, $schema );

					if ( ! empty( $performer_url_fields ) ) {
						foreach ( $performer_url_fields as $_field ) {
							if ( ! empty( $gd_post->{$_field} ) ) {
								$performer_url = esc_url_raw( $gd_post->{$_field} );

								if ( ! empty( $performer_url ) ) {
									// performer.url
									$performer['url'] = $performer_url;
									break;
								}
							}
						}
					}
				}
			}

			$performer = apply_filters( 'geodir_event_schema_performer', $performer, $gd_post, $schema );
			if ( is_array( $performer ) && ! empty( $performer['name'] ) ) {
				$schema['performer'] = $performer;
			}

			// Event organizer: organizer
			$organizer = array(
				'@type' => 'Person'
			);

			// Organizer name fields
			$organizer_name_fields = array( 'event_organizer', 'eventorganizer', 'event_organizer_name', 'eventorganizername', 'organizer', 'organizer_name', 'organizername' );
			$organizer_name_fields = apply_filters( 'geodir_event_schema_organizer_name_fields', $organizer_name_fields, $gd_post, $schema );

			if ( ! empty( $organizer_name_fields ) ) {
				foreach ( $organizer_name_fields as $_field ) {
					if ( ! empty( $gd_post->{$_field} ) ) {
						$organizer_name = stripslashes( sanitize_text_field( $gd_post->{$_field} ) );

						if ( ! empty( $organizer_name ) ) {
							// organizer.name
							$organizer['name'] = $organizer_name;
							break;
						}
					}
				}

				// Use author name as organizer.
				if ( empty( $organizer['name'] ) ) {
					$organizer['name'] = geodir_get_client_name( $gd_post->post_author );
				}

				if ( ! empty( $organizer['name'] ) ) {
					$organizer_url_fields = array( 'event_organizer_url', 'eventorganizerurl', 'organizer_url', 'organizerurl' );
					$organizer_url_fields = apply_filters( 'geodir_event_schema_organizer_url_fields', $organizer_url_fields, $gd_post, $schema );

					if ( ! empty( $organizer_url_fields ) ) {
						foreach ( $organizer_url_fields as $_field ) {
							if ( ! empty( $gd_post->{$_field} ) ) {
								$organizer_url = esc_url_raw( $gd_post->{$_field} );

								if ( ! empty( $organizer_url ) ) {
									// organizer.url
									$organizer['url'] = $organizer_url;
									break;
								}
							}
						}
					}
				}
			}

			$organizer = apply_filters( 'geodir_event_schema_organizer', $organizer, $gd_post, $schema );
			if ( is_array( $organizer ) && ! empty( $organizer['name'] ) ) {
				$schema['organizer'] = $organizer;
			}
		}
        $schema['location'] = $place;

        unset($schema['telephone']);
        unset($schema['address']);
        unset($schema['geo']);
    }

    return $schema;
}

/**
 * Event Schema types filter.
 *
 * @return mixed
 */
function geodir_event_get_schema_types() {
	$schemas = array(
		'Event'         => 'Event',
		'BusinessEvent' => '- BusinessEvent',
		'ChildrensEvent' => '- ChildrensEvent',
		'ComedyEvent' => '- ComedyEvent',
		'CourseInstance' => '- CourseInstance',
		'DanceEvent' => '- DanceEvent',
		'DeliveryEvent' => '- DeliveryEvent',
		'EducationEvent' => '- EducationEvent',
		'EventSeries' => '- EventSeries',
		'ExhibitionEvent' => '- ExhibitionEvent',
		'Festival' => '- Festival',
		'FoodEvent' => '- FoodEvent',
		'Hackathon' => 'Hackathon',
		'LiteraryEvent' => '- LiteraryEvent',
		'MusicEvent' => '- MusicEvent',
		'PublicationEvent' => '- PublicationEvent',
		'BroadcastEvent' => '- - BroadcastEvent',
		'OnDemandEvent' => '- - OnDemandEvent',
		'SaleEvent' => '- SaleEvent',
		'ScreeningEvent' => '- ScreeningEvent',
		'SocialEvent' => '- SocialEvent',
		'SportsEvent' => '- SportsEvent',
		'TheaterEvent' => '- TheaterEvent',
		'VisualArtsEvent' => '- VisualArtsEvent',
	);
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
		'ongoing' => __( 'Ongoing', 'geodirevents' ),
		'ongoing_upcoming' => __( 'Ongoing + Upcoming', 'geodirevents' ),
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

/**
 * Sanitizes array or string event field values.
 *
 * @since 2.1.1.0
 *
 * @param array|string $value Value to sanitize.
 * @return array|string Sanitized value.
 */
function geodir_event_sanitize_text_field( $value ) {
	if ( is_scalar( $value ) ) {
		$value = sanitize_text_field( $value );
	} elseif ( is_array( $value ) ) {
		$_value = array();

		foreach ( $value as $k => $v ) {
			$_value[ sanitize_text_field( $k ) ] = geodir_event_sanitize_text_field( $v );
		}

		$value = $_value;
	}

	return $value;
}

/**
 * Add tool to handle pat events manually.
 *
 * @since 2.1.1.6
 *
 * @param array $tools Tools array.
 * @return array Filtered tools array.
 */
function geodir_event_debug_tools( $tools = array() ) {
	$post_types = geodir_event_past_event_types();

	if ( empty( $post_types ) ) {
		return $tools;
	}

	$tools['tool_handle_past_events'] = array(
		'name' => __( 'Handle Past Events', 'geodirevents' ),
		'button' => __( 'Run', 'geodirevents' ),
		'desc' => __( 'This tool allows to unpublish / remove past events after end date. More settings are configured at Events CPT > Settings > General > Manage Past Events.', 'geodirevents' ),
		'callback' => 'geodir_event_tool_handle_past_events'
	);

	return $tools;
}

/**
 * Get post type to handle past events.
 *
 * @since 2.1.1.6
 *
 * @return array Post types array.
 */
function geodir_event_past_event_types() {
	$_post_types = array();

	$post_types = geodir_get_posttypes( 'array' );

	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type => $data ) {
			if ( GeoDir_Post_types::supports( $post_type, 'events' ) && ! empty( $data['past_event'] ) ) {
				$_post_types[] = $post_type;
			}
		}
	}

	return $_post_types;
}

/**
 * Run to auto update past events.
 *
 * @since 2.1.1.6
 *
 * @param string $post_type The post type to process expired events.
 * @return string Tool output message.
 */
function geodir_event_tool_handle_past_events() {
	$items = (int) geodir_event_handle_past_events();

	if ( $items > 0 ) {
		$message = wp_sprintf( _n( '%d past item processed.', '%d past items processed.', $items, 'geodirevents' ), $items );
	} else {
		$message = __( 'No past items found.', 'geodirevents' );
	}

	return $message;
}

/**
 * Handle past events.
 *
 * @since 2.1.1.6
 *
 * @param string $post_type The post type to process expired events.
 * @return int No. of past events processed.
 */
function geodir_event_handle_past_events() {
	$post_types = geodir_event_past_event_types();

	$processed = 0;

	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$processed += (int) GeoDir_Event_Schedules::handle_past_events( $post_type );
		}
	}

	return $processed;
}

/**
 * Event time format for input field.
 *
 * @since 2.2.2
 *
 * @param bool $picker If true returns in jQuery UI/Flatpickr format. Default False.
 * @return string Time format.
 */
function geodir_event_input_time_format( $picker = false ) {
	$time_format = geodir_time_format();

	$time_format = apply_filters( 'geodir_event_input_time_format', $time_format );

	if ( $picker ) {
		if ( geodir_design_style() ) {
			$time_format = geodir_date_format_php_to_aui( $time_format ); // AUI Flatpickr
		} else {
			$time_format = geodir_date_format_php_to_jqueryui( $time_format );
		}
	}

	return $time_format;
}