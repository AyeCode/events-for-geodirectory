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

		// Non Widgets
        new GeoDir_Event_Widget_Linked_Events();
    }
}

function geodir_event_calender_search_page_title($title){
		
	global $condition_date;	
	
	if(isset($_REQUEST['event_calendar']) && !empty($_REQUEST['event_calendar']) && geodir_is_page('search'))
		$title = apply_filters('geodir_calendar_search_page_title', __(' Browsing Day', 'geodirevents').'" '.date_i18n('F  d, Y',strtotime($condition_date)).'"');
	
	return $title;
}

function geodir_event_fill_listings( $term ) {
	//$listings = geodir_event_get_my_listings( 'gd_place', $term );
	$listings = geodir_event_get_my_listings( 'all', $term );
	$options = '<option value="">' . __( 'No Business', 'geodirevents' ) . '</option>';
	if( !empty( $listings ) ) {
		foreach( $listings as $listing ) {
			$options .= '<option value="' . $listing->ID . '">' . $listing->post_title . '</option>';
		}
	}
	return $options;
}

function geodir_event_manager_ajax(){

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : '';
	switch( $task ) {
		case 'geodir_fill_listings' :
			$term = isset( $_REQUEST['term'] ) ? $_REQUEST['term'] : '';
			echo geodir_event_fill_listings( $term );
			exit;
		break;
	}
	
	if(isset($_REQUEST['auto_fill']) && $_REQUEST['auto_fill'] == 'geodir_business_autofill'){
		
		if(isset($_REQUEST['place_id']) && $_REQUEST['place_id'] != '' && isset($_REQUEST['_wpnonce']))
		{
			
			if ( !wp_verify_nonce( $_REQUEST['_wpnonce'], 'link_business_autofill_nonce' ) )
						exit;
			
			geodir_business_auto_fill($_REQUEST);
			exit;
			
		}else{
		
			wp_redirect(geodir_login_url());
			exit();
		}
		
	}
	
}

function geodir_business_auto_fill($request){

	if(!empty($request)){
		
		$place_id = $request['place_id'];
		$post_type = get_post_type( $place_id );
		$package_id = geodir_get_post_meta($place_id,'package_id',true);
		$custom_fields = geodir_post_custom_fields($package_id,'all',$post_type); 
		
		$json_array = array();
		
		$content_post = get_post($place_id);
		$content = $content_post->post_content;

		$excluded = apply_filters('geodir_business_auto_fill_excluded', array());

		$post_title_value = geodir_get_post_meta($place_id,'post_title',true);
		if (in_array('post_title', $excluded)) {
			$post_title_value = '';
		}

		$post_desc_value = $content;
		if (in_array('post_desc', $excluded)) {
			$post_desc_value = '';
		}

		$json_array['post_title'] = array('key' => 'text', 'value' => $post_title_value);

		$json_array['post_desc'] = array(	'key' => 'textarea', 'value' => $post_desc_value);

		foreach($custom_fields as $key=>$val){
			
			$type = $val['type'];
			
			switch($type){
			
				case 'phone':
				case 'email':
				case 'text':
				case 'url':					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'text', 'value' => $value);
					
				break;
				
				case 'html':
				case 'textarea':
					
					$value = geodir_get_post_meta($place_id,$val['htmlvar_name'],true);
					$json_array[$val['htmlvar_name']] = array('key' => 'textarea', 'value' => $value);
					
				break;
				
				case 'address':
					
					$json_array['street'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'street',true));
					$json_array['zip'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'zip',true));
					$json_array['latitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'latitude',true));
					$json_array['longitude'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'longitude',true));
					$extra_fields = unserialize($val['extra_fields']);
					
					$show_city = isset($extra_fields['show_city']) ? $extra_fields['show_city'] : '';
					
					if($show_city){

						$json_array['country'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'country',true));
						$json_array['region'] = array('key' => 'text',
																				'value' => geodir_get_post_meta($place_id,'region',true));
						$json_array['city'] = array('key' => 'text',
																			'value' => geodir_get_post_meta($place_id,'city',true));
						
					}
					
					
				break;
				case 'checkbox':
				case 'radio':
				case 'select':
				case 'datepicker':
				case 'time':
					$value = geodir_get_post_meta( $place_id, $val['htmlvar_name'], true );
					$json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
				break;
				case 'multiselect':
					$value = geodir_get_post_meta( $place_id, $val['htmlvar_name'] );
					$value = $value != '' ? explode( ",", $value ) : array();
					$json_array[$val['htmlvar_name']] = array( 'key' => $type, 'value' => $value );
				break;
				
			}
			
		}

	}
	
	if ( !empty( $json_array ) ) {
		// attach terms
		$post_tags = wp_get_post_terms( $place_id, $post_type . '_tags', array( "fields" => "names" ) );
		$post_tags = !empty( $post_tags ) && is_array( $post_tags ) ? implode( ",", $post_tags ) : '';
		$json_array['post_tags'] = array( 'key' => 'tags', 'value' => $post_tags );
		
		echo json_encode( $json_array );
	}	
}

function geodir_wp_default_date_time_format()
{
	return get_option('date_format'). ' ' .	get_option('time_format');
}

function geodir_event_get_my_listings( $post_type = 'all', $search = '', $limit = 5 ) {
	global $wpdb, $current_user;
	
	if( empty( $current_user->ID ) ) {
		return NULL;
	} 
	$geodir_postypes = geodir_get_posttypes();

	$search = trim( $search );
	$post_type = $post_type != '' ? $post_type : 'all';
	
	if( $post_type == 'all' ) {
		$geodir_postypes = implode( ",", $geodir_postypes );
		$condition = $wpdb->prepare( " AND FIND_IN_SET( post_type, %s )" , array( $geodir_postypes ) );
	} else {
		$post_type = in_array( $post_type, $geodir_postypes ) ? $post_type : 'gd_place';
		$condition = $wpdb->prepare( " AND post_type = %s" , array( $post_type ) );
	}


	if(!geodir_get_option('event_link_any_user')){
		$condition .= !current_user_can( 'manage_options' ) ? $wpdb->prepare( "AND post_author=%d" , array( (int)$current_user->ID ) ) : '';
	}
	$condition .= $search != '' ? $wpdb->prepare( " AND post_title LIKE %s", array( $search . '%%' ) ) : "";
	
	$orderby = " ORDER BY post_title ASC";
	$limit = " LIMIT " . (int)$limit;
	
	$sql = $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_status = %s AND post_type != 'gd_event' " . $condition . $orderby . $limit, array( 'publish' ) );
	$rows = $wpdb->get_results($sql);
	
	return $rows;
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
 
function geodir_event_date_occurrences( $type = 'year', $start_date, $end_date = '', $interval = 1, $limit = '', $repeat_end = '', $repeat_days = array(), $repeat_weeks = array() ) {
	$dates = array();
	$start_time = strtotime( $start_date );
	$end_time = strtotime( $repeat_end );

	switch ( $type ) {
		case 'year': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' year' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					for ( $i = 1; $i < $limit ; $i++ ) {
						$every = $interval * $i;
						$time = strtotime( $start_date . '+' . $every . ' year' );
						
						$year = date_i18n( 'Y', $time );
						$month = date_i18n( 'm', $time );
						$day = date_i18n( 'd', $time );
						
						$date_occurrence = $year . '-' . $month . '-' . $day;
						
						$dates[] = $date_occurrence;
					}
				}
			}
		}
		break;
		case 'month': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' month' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( !empty( $repeat_days ) || !empty( $repeat_weeks ) ) {
						$month_days = cal_days_in_month( CAL_GREGORIAN, $month, $year );												
						for ( $d = 1; $d <= $month_days; $d++ ) {
							$recurr_time = strtotime( $year . '-' . $month . '-' . $d );
							$week_day = date_i18n( 'w', $recurr_time );
							$week_diff = ( $recurr_time - strtotime( $year . '-' . $month . '-01' ) );
							$week_num = $week_diff > 0 ? (int)( $week_diff / ( DAY_IN_SECONDS * 7 ) ) : 0;
							$week_num++;														
							
							if ( $recurr_time >= $start_time && $recurr_time <= $end_time ) {
								if ( empty( $repeat_days ) && !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								} else if ( !empty( $repeat_days ) && empty( $repeat_weeks ) && in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								} else if ( !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) && !empty( $repeat_days ) && in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								}
							}
						}
					} else {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					if ( !empty( $repeat_days ) || !empty( $repeat_weeks ) ) {
						$dates = array();
						$week_dates = array();
						$days_limit = 0;
						
						$i = 0;
						while ( $days_limit <= $limit ) {
							$time = strtotime( $start_date . '+' . ( $interval * $i ) . ' month' );
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$month_days = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							for ( $d = 1; $d <= $month_days; $d++ ) {
								$recurr_time = strtotime( $year . '-' . $month . '-' . $d );
								$week_day = date_i18n( 'w', $recurr_time );
								$week_diff = ( $recurr_time - strtotime( $year . '-' . $month . '-01' ) );
								$week_num = $week_diff > 0 ? (int)( $week_diff / ( DAY_IN_SECONDS * 7 ) ) : 0;
								$week_num++;
								
								if ( $recurr_time >= $start_time && in_array( $week_day, $repeat_days ) ) {
									$week_date = '';
									
									if ( empty( $repeat_days ) && !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									} else if ( !empty( $repeat_days ) && empty( $repeat_weeks ) && in_array( $week_day, $repeat_days ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									} else if ( !empty( $repeat_weeks ) && in_array( $week_num, $repeat_weeks ) && !empty( $repeat_days ) && in_array( $week_day, $repeat_days ) ) {
										$week_date = date_i18n( 'Y-m-d', $recurr_time );
									}
									if ( $week_date != '' ) {
										$dates[] = $week_date;
										$days_limit++;
									}
									
									if ( count( $dates ) == $limit ) {
										break 2;
									}
								}
							}
							$i++;
							
						}
						
						$dates = !empty( $dates ) ? $dates : date_i18n( 'Y-m-d', $start_time );
					} else {
						for ( $i = 1; $i < $limit ; $i++ ) {
							$every = $interval * $i;
							$time = strtotime( $start_date . '+' . $every . ' month' );
							
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							$dates[] = $date_occurrence;
						}
					}
				}
			}
		}
		break;
		case 'week': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' week' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						if ( !empty( $repeat_days ) ) {
							for ( $d = 0; $d <= 6; $d++ ) {
								$recurr_time = strtotime( $date_occurrence . '+' . $d . ' day' );
								$week_day = date_i18n( 'w', $recurr_time );
								
								if ( in_array( $week_day, $repeat_days ) ) {
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
								}
							}
						} else {
							$dates[] = $date_occurrence;
						}
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					if ( !empty( $repeat_days ) ) {
						$dates = array();
						$week_dates = array();
						$days_limit = 0;
						
						$i = 0;
						while ( $days_limit <= $limit ) {
							$time = strtotime( $start_date . '+' . ( $interval * $i ) . ' week' );
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							for ( $d = 0; $d <= 6; $d++ ) {
								$recurr_time = strtotime( $date_occurrence . '+' . $d . ' day' );
								$week_day = date_i18n( 'w', $recurr_time );
								
								if ( in_array( $week_day, $repeat_days ) ) {
									$week_dates[] = date_i18n( 'Y-m-d', $recurr_time );
									$dates[] = date_i18n( 'Y-m-d', $recurr_time );
									$days_limit++;
									
									if ( count( $dates ) == $limit ) {
										break 2;
									}
								}
							}
							$i++;
							
						}
						
						$dates = !empty( $dates ) ? $dates : date_i18n( 'Y-m-d', $start_time );
					} else {
						for ( $i = 1; $i < $limit ; $i++ ) {
							$every = $interval * $i;
							$time = strtotime( $start_date . '+' . $every . ' week' );
							
							$year = date_i18n( 'Y', $time );
							$month = date_i18n( 'm', $time );
							$day = date_i18n( 'd', $time );
							
							$date_occurrence = $year . '-' . $month . '-' . $day;
							
							$dates[] = $date_occurrence;
						}
					}
				}
			}
		}
		break;
		case 'day': {
			if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
				for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' day' ) ) {
					$year = date_i18n( 'Y', $time );
					$month = date_i18n( 'm', $time );
					$day = date_i18n( 'd', $time );
					
					$date_occurrence = $year . '-' . $month . '-' . $day;
					$time_occurrence = strtotime( $date_occurrence );
					
					if ( $time_occurrence <= $end_time ) {
						$dates[] = $date_occurrence;
					}
				}
			} else {
				$dates[] = date_i18n( 'Y-m-d', $start_time );
				
				if ( $limit > 0 ) {
					for ( $i = 1; $i < $limit ; $i++ ) {
						$every = $interval * $i;

						$time = strtotime( $start_date . '+' . $every . ' day' );
						
						$year = date_i18n( 'Y', $time );
						$month = date_i18n( 'm', $time );
						$day = date_i18n( 'd', $time );
						
						$date_occurrence = $year . '-' . $month . '-' . $day;
						
						$dates[] = $date_occurrence;
					}
				}
			}
		}
		break;
	}

	$dates = !empty( $dates ) ? array_unique( $dates ) : $dates;
	return $dates;
}

function geodir_event_schedule_exist( $date, $event_id ) {
	global $wpdb;
	
	$date = date_i18n( 'Y-m-d', strtotime( $date ) );
	
	$sql = "SELECT * FROM `" . GEODIR_EVENT_SCHEDULES_TABLE . "` WHERE event_id=" . (int)$event_id . " AND ( ( end_date = '0000-00-00' AND DATE_FORMAT( start_date, '%Y-%m-%d') = '" . $date . "' ) OR ( end_date != '0000-00-00' AND DATE_FORMAT( start_date, '%Y-%m-%d') <= '" . $date . "' AND '" . $date . "' <= DATE_FORMAT( end_date, '%Y-%m-%d') ) )";
	
	if ( $wpdb->get_var( $sql ) ) {
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

/**
 * Retrieve the page title for the listing page.
 *
 * @since 1.1.8
 *
 * @param  string $page_title Page title.
 * @return string Listing page title.
 */
function geodir_event_listing_page_title($page_title = '') {
	$current_posttype = geodir_get_current_posttype();
	
	if ( geodir_is_page( 'listing' ) && $current_posttype == 'gd_event' && isset( $_REQUEST['venue'] ) && $_REQUEST['venue'] != '' ) {
		$venue = explode( '-', $_REQUEST['venue'], 2);
		$venue_info = !empty($venue) && isset($venue[0]) ? get_post((int)$venue[0]) : array();
		
		if ( !empty( $venue_info ) && isset( $venue_info->post_title ) && $venue_info->post_title != '' )
			$page_title = wp_sprintf( __( '%s at %s', 'geodirevents' ), $page_title, $venue_info->post_title );
	}
		
	return $page_title;
}

/**
 * Filter the past events count in terms array results.
 *
 * @since 1.1.9
 *
 * @param array $terms Array of terms.
 * @param array $taxonomies Array of post taxonomies.
 * @param array $args Terms arguments.
 * @return array Array of terms.
 */
function geodir_event_get_terms( $terms, $taxonomies, $args ) {
	if ( isset( $args['gd_event_no_loop'] ) ) {
		return $terms; // Avoid an infinite loop.
	}
	
	$args['gd_event_no_loop'] = true;
	
	$gd_event_post_type = 'gd_event';
	
	$gd_event_taxonomy = $gd_event_post_type . 'category';
	
	if ( !empty( $terms ) && in_array( $gd_event_taxonomy, $taxonomies ) ) {
		$query_args = array (
			'is_geodir_loop' => true,
			'post_type' => $gd_event_post_type,
			'gd_location' => true,
		);
			
		$new_terms = array();
		
		foreach ( $terms as $key => $term ) {
			$new_term = $term;
			
			if ( isset( $term->taxonomy ) && $term->taxonomy == $gd_event_taxonomy ) {
				$tax_query = array(
					'taxonomy' => $gd_event_taxonomy,
					'field' => 'id',
					'terms' => $term->term_id
				);
				
				$query_args['tax_query'] = array($tax_query);
				
				$new_term->count = geodir_get_widget_listings( $query_args, true );
			}
			
			$new_terms[$key] = $new_term;
		}
		
		$terms = $new_terms;
	}

	return $terms;
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
	if ( $post_type != 'gd_event' ) {
		return $term_link;
	}
	
	$term_link = add_query_arg( array( 'etype' => 'upcoming' ), $term_link );

	return $term_link;
}

/**
 * Filter reviews sql query fro upcoming events.
 *
 * @since 1.2.4
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $sql Database sql query.
 * @param int $term_id The term ID.
 * @param int $taxonomy The taxonomy Id.
 * @param string $post_type The post type.
 * @return string Database sql query.
 */
function geodir_event_count_reviews_by_term_sql($sql, $term_id, $taxonomy, $post_type) {
	if ($term_id > 0 && $post_type == 'gd_event') {
		global $wpdb, $plugin_prefix;
		
		$listing_table = $plugin_prefix . $post_type . '_detail';
		
		$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
		
		$sql = "SELECT COALESCE(SUM(ed.rating_count),0) FROM `" . $listing_table . "` AS ed INNER JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_status = 'publish' AND ed.rating_count > 0 AND FIND_IN_SET(" . $term_id . ", ed.post_category)";
		$sql .= " AND (es.start_date >= '" . $current_date . "' OR (es.start_date <= '" . $current_date . "' AND es.end_date >= '" . $current_date . "'))";
	}
	
	return $sql;
}

/**
 * Filter reviews sql query fro upcoming events for current location.
 *
 * @since 1.2.4
 * @since 1.3.0 Fixed post term count for neighbourhood locations.
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $sql Database sql query.
 * @param int $term_id The term ID.
 * @param int $taxonomy The taxonomy Id.
 * @param string $post_type The post type.
 * @param string $location_type Location type .
 * @param array $loc Current location terms.
 * @param string $count_type The term count type.
 * @return string Database sql query.
 */
function geodir_event_count_reviews_by_location_term_sql($sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type) {
	if ($term_id > 0 && $post_type == 'gd_event') {
		global $wpdb, $plugin_prefix;
		
		if ($count_type == 'review_count') {			
			$listing_table = $plugin_prefix . $post_type . '_detail';
			
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
			
			if (!$loc) {
				$loc = geodir_get_current_location_terms();
			}
			
			$country = isset($loc['gd_country']) && $loc['gd_country'] != '' ? $loc['gd_country'] : '';
			$region = isset($loc['gd_region']) && $loc['gd_region'] != '' ? $loc['gd_region'] : '';
			$city = isset($loc['gd_city']) && $loc['gd_city'] != '' ? $loc['gd_city'] : '';
			$neighbourhood = '';
			if ($city != '' && isset($loc['gd_neighbourhood']) && $loc['gd_neighbourhood'] != '') {
				$location_type = 'gd_neighbourhood';
				$neighbourhood = $loc['gd_neighbourhood'];
			}
			
			$where = '';
			if ( $country!= '') {
				$where .= " AND ed.locations LIKE '%,[".$country."]' ";
			}
			
			if ( $region != '' && $location_type!='gd_country' ) {
				$where .= " AND ed.locations LIKE '%,[".$region."],%' ";
			}
			
			if ( $city != '' && $location_type!='gd_country' && $location_type!='gd_region' ) {
				$where .= " AND ed.locations LIKE '[".$city."],%' ";
			}
			
			if ($location_type == 'gd_neighbourhood' && $neighbourhood != '' && $wpdb->get_var("SHOW COLUMNS FROM " . $listing_table . " WHERE field = 'neighbourhood'")) {
				$where .= " AND ed.neighbourhood = '" . $neighbourhood . "' ";
			}
			
			$sql = "SELECT COALESCE(SUM(ed.rating_count),0) FROM `" . $listing_table . "` AS ed INNER JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_status = 'publish' " . $where . " AND ed.rating_count > 0 AND FIND_IN_SET(" . $term_id . ", ed.post_category)";
			
			$sql .= " AND (es.start_date >= '" . $current_date . "' OR (es.start_date <= '" . $current_date . "' AND es.end_date >= '" . $current_date . "'))";
		}
	}
	
	return $sql;
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
function geodir_event_bestof_widget_view_all_link($view_all_link, $post_type, $term) {
	if ($post_type == 'gd_event') {
		$view_all_link = add_query_arg(array('etype' => 'upcoming'), $view_all_link) ;
	}
	return $view_all_link;
}

/**
 * Displays the event dates in the meta info in the map info window.
 *
 * @since 1.2.7
 * @since 1.4.6 Same day events should just show date and from - to time.
 *
 * @global string $geodir_date_format Date format.
 * @global string $geodir_date_time_format Date time format.
 *
 * @param object $post_id The post id.
 * @param object $post The post info as an object.
 * @param bool|string $preview True if currently in post preview page. Empty string if not.                           *
 */
function geodir_event_infowindow_meta_event_dates($post_id, $post, $preview) {
	global $geodir_date_format, $geodir_date_time_format, $geodir_time_format;
	if (empty($post)) {
		return NULL;
	}
	
	$limit = (int)geodir_get_option('event_map_popup_count', 1); // no of event dates to show in map infowindow.
	if (!$limit > 0) {
		return NULL;
	}
	
	$post_type = isset($post->post_type) ? $post->post_type : NULL;
	if ((int)$post_id > 0) {
		$post_type = get_post_type($post_id);
	}
	
	if (empty($post_type) && $preview) {
		$post_type = !empty($post->listing_type) ? $post->listing_type : (!empty($post->post_type) ? $post->post_type : NULL);
	}
	
	if ($post_type != 'gd_event') {
		return NULL;
	}
	
	$event_type = geodir_get_option('event_map_popup_dates', 'upcoming');
	$schedule_dates = geodir_event_get_schedule_dates($post, $preview, $event_type);

	$dates = array();
	if (!empty($schedule_dates)) {
		$count = 0;
		foreach ($schedule_dates as $date) {
			$event_date = $date['start_date'];
			$event_enddate = $date['end_date'];
			$event_starttime = $date['start_time'];
			$event_endtime = $date['end_time'];
			
			if ($event_enddate == '0000-00-00') {
				$event_enddate = $event_date;
			}
			
			$full_day = false;
			$same_datetime = false;
			$same_day = false;
			
			if ($event_starttime == $event_endtime && ($event_starttime == '00:00:00' || $event_starttime == '00:00' || $event_starttime == '')) {
				$full_day = true;
			}
			
			if ($event_date == $event_enddate && $full_day) {
				$same_datetime = true;
			}
			
			$ievent_date = strtotime($event_date . ' ' . $event_starttime);
			$ievent_enddate = strtotime($event_enddate . ' ' . $event_endtime);
			
			if ($full_day) {
				$start_date = date_i18n($geodir_date_format, $ievent_date);
				$end_date = date_i18n($geodir_date_format, $ievent_enddate);
			} else {
				$start_date = date_i18n($geodir_date_time_format, $ievent_date);
				
				if (!$same_datetime && date_i18n( 'Y-m-d', $ievent_date ) == date_i18n( 'Y-m-d', $ievent_enddate ) ) {
					$same_day = true;
					
					$start_date .= ' - ' . date_i18n( $geodir_time_format, $ievent_enddate );
				} else {
					$end_date = date_i18n($geodir_date_time_format, $ievent_enddate);
				}
			}
			
			$schedule = '<span class="geodir_schedule clearfix"><span class="geodir_schedule_start"><i class="fas fa-caret-right"></i> ' . $start_date . '</span>';
			if (!$same_datetime && !$same_day) {
				$schedule .= '<br /><span class="geodir_schedule_end"><i class="fas fa-caret-left"></i> ' . $end_date . '</span>';
			}
			$schedule .= '</span>';
			
			$dates[] = $schedule;
			
			$count++;
			if ($limit == $count) {
				break;
			}
		}
	}
	
	if (empty($dates)) {
		return NULL;
	}
		
	$content = '<div class="geodir_event_schedule">' . implode('', $dates) . '</div>';
	
	echo $content;
}

/**
 * Get the event schedule dates array.
 *
 * @since 1.2.7
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param object|int $post The post id or the post object.
 * @param bool|string $preview True if currently in post preview page. Empty string if not.
 * @param string $event_type Event type filter. Default 'upcoming'.
 * @return array Array of event schedule dates.
 */
function geodir_event_get_schedule_dates($post, $preview = false, $event_type = 'upcoming') {
	global $wpdb;
	
	$today = date_i18n('Y-m-d', current_time('timestamp'));
	$today_timestamp = strtotime($today);
	
	$results = array();
	if (!$preview) {
		$post_id = NULL;
		
		if (is_int($post)) {
			$post_id = $post;
		} else {
			$post_data = (array)$post;
			$post_id = !empty($post_data['post_id']) ? $post_data['post_id'] : (!empty($post_data['ID']) ? $post_data['ID'] : NULL);
			
		}
		
		if (!$post_id > 0) {
			return NULL;
		}
		
		$where = "";
		
		switch($event_type) {
			case 'past':
				$where = " AND start_date < '" . $today . "'";
			break;
			case 'today':
				$where = " AND (start_date LIKE '" . $today . "%%' OR (start_date <= '" . $today . "' AND end_date >= '" . $today . "')) ";
			break;
			case 'upcoming':
				$where = " AND (start_date >= '" . $today . "' OR (start_date <= '" . $today . "' AND end_date >= '" . $today . "')) ";
			break;
			case 'all':
			default:
			break;
		}
		
		$sql = "SELECT *, DATE_FORMAT(start_date, '%Y-%m-%d') AS start_date FROM `" . GEODIR_EVENT_SCHEDULES_TABLE . "` WHERE event_id=" . (int)$post_id . " " . $where . " GROUP BY CONCAT(event_id, '-', start_date) ORDER BY start_date ASC";
		
		$results = $wpdb->get_results($sql, ARRAY_A);
	} else {
		$post_data = (array)$post;
		
		if (empty($post_data)) {
			return NULL;
		}
		
		$post_id = isset($post_data['ID']) ? $post_data['ID'] : NULL;
		
		// Check recurring enabled
		$recurring_pkg = geodir_event_recurring_pkg($post);
			
		if (!$recurring_pkg) {
			$post_data['recurring'] = false;
		}
		
		// all day
		$all_day = isset($post_data['all_day']) && !empty($post_data['all_day']) ? true : false;
		$different_times = isset($post_data['different_times']) && !empty($post_data['different_times']) ? true : false;
		$starttime = !$all_day && isset($post_data['starttime']) ? $post_data['starttime'] : '';
		$endtime = !$all_day && isset($post_data['endtime']) ? $post_data['endtime'] : '';
		$starttimes = !$all_day && isset($post_data['starttimes']) ? $post_data['starttimes'] : array();
		$endtimes = !$all_day && isset($post_data['endtimes']) ? $post_data['endtimes'] : array();

		if (!empty($post_data['recurring']) && $recurring_pkg) {
			$repeat_type = isset($post_data['repeat_type']) && in_array($post_data['repeat_type'], array('day', 'week', 'month', 'year', 'custom')) ? $post_data['repeat_type'] : 'year'; // day, week, month, year, custom
			
			$start_date = geodir_event_is_date($post_data['event_start']) ? $post_data['event_start'] : date_i18n('Y-m-d', current_time('timestamp'));
			$end_date = isset($post_data['event_end']) ? trim($post_data['event_end']) : '';
			
			$repeat_x = isset($post_data['repeat_x']) ? trim($post_data['repeat_x']) : '';
			$duration_x = isset($post_data['duration_x']) ? trim($post_data['duration_x']) : 1;
			$repeat_end_type = isset($post_data['repeat_end_type']) ? trim($post_data['repeat_end_type']) : 0;
			
			$max_repeat = $repeat_end_type != 1 && isset($post_data['max_repeat']) ? (int)$post_data['max_repeat'] : 0;
			$repeat_end = $repeat_end_type == 1 && isset($post_data['repeat_end']) ? $post_data['repeat_end'] : '';
										 
			if (geodir_event_is_date($end_date) && strtotime($end_date) < strtotime($start_date)) {
				$end_date = $start_date;
			}
				
			$repeat_x = $repeat_x > 0 ? (int)$repeat_x : 1;
			$duration_x = $duration_x > 0 ? (int)$duration_x : 1;
			$max_repeat = $max_repeat > 0 ? (int)$max_repeat : 1;
				
			if ($repeat_type == 'custom') {
				$event_recurring_dates = explode(',', $post_data['event_recurring_dates']);
			} else {
				// week days
				$repeat_days = array();
				if ($repeat_type == 'week' || $repeat_type == 'month') {
					$repeat_days = isset($post_data['repeat_days']) ? $post_data['repeat_days'] : $repeat_days;
				}
				
				// by week
				$repeat_weeks = array();
				if ($repeat_type == 'month') {
					$repeat_weeks = isset($post_data['repeat_weeks']) ? $post_data['repeat_weeks'] : $repeat_weeks;
				}
		
				$event_recurring_dates = geodir_event_date_occurrences($repeat_type, $start_date, $end_date, $repeat_x, $max_repeat, $repeat_end, $repeat_days, $repeat_weeks);
			}
			
			if (empty($event_recurring_dates)) {
				return NULL;
			}
			
			$duration_x--;
		
			$c = 0;
			foreach($event_recurring_dates as $key => $date) {
				$result = array();
				if ($repeat_type == 'custom' && $different_times) {
					$duration_x = 0;
					$starttime = isset($starttimes[$c]) ? $starttimes[$c] : '';
					$endtime = isset($endtimes[$c]) ? $endtimes[$c] : '';
				}
				
				if ($all_day == 1) {
					$starttime = '';
					$endtime = '';
				}
				
				$event_enddate = date_i18n('Y-m-d', strtotime($date . ' + ' . $duration_x . ' day'));
				$event_start_timestamp = strtotime($date);
				$event_end_timestamp = strtotime($event_enddate);
				
				if ($event_type == 'past' && !($event_end_timestamp < $today_timestamp)) {
					continue;
				} else if ($event_type == 'today' && !($event_start_timestamp == $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
					continue;
				} else if ($event_type == 'upcoming' && !($event_start_timestamp >= $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
					continue;
				}
				
				$result['event_id'] = $post_id;
				$result['start_date'] = $date;
				$result['end_date'] = $event_enddate;
				$result['start_time'] = $starttime;
				$result['end_time'] = $endtime;
				$result['recurring'] = true;
				$result['all_day'] = $all_day;
				
				$c++;
				
				$results[] = $result;
			}
		} else {
			$start_date = isset($post_data['event_start']) ? $post_data['event_start'] : '';
			$end_date = isset($post_data['event_end']) ? $post_data['event_end'] : $start_date;
			
			if (!geodir_event_is_date($start_date) && !empty($post_data['event_recurring_dates'])) {
				$event_recurring_dates = explode(',', $post_data['event_recurring_dates']);
				$start_date = $event_recurring_dates[0];
			}
			
			$start_date = geodir_event_is_date($start_date) ? $start_date : $today;
			
			if (strtotime($end_date) < strtotime($start_date)) {
				$end_date = $start_date;
			}
			
			if ($starttime == '' && !empty($starttimes)) {
				$starttime = $starttimes[0];
				$endtime = $endtimes[0];
			}
			
			if ($all_day) {
				$starttime = '';
				$endtime = '';
			}
			
			$event_start_timestamp = strtotime($start_date);
			$event_end_timestamp = strtotime($end_date);
			
			if ($event_type == 'past' && !($event_end_timestamp < $today_timestamp)) {
				return NULL;
			} else if ($event_type == 'today' && !($event_start_timestamp == $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
				return NULL;
			} else if ($event_type == 'upcoming' && !($event_start_timestamp >= $today_timestamp || ($event_start_timestamp >= $today_timestamp && $event_end_timestamp <= $today_timestamp))) {
				return NULL;
			}
			
			$result['event_id'] = $post_id;
			$result['start_date'] = $start_date;
			$result['end_date'] = $end_date;
			$result['start_time'] = $starttime;
			$result['end_time'] = $endtime;
			$result['recurring'] = false;
			$result['all_day'] = $all_day;
			$results[] = $result;
		}
	}
	
	return $results;
}

function geodir_event_home_map_marker_query_join($join = '') {
	global $plugin_prefix;
	
	$join .= " INNER JOIN " . $plugin_prefix . "event_schedule AS es ON es.event_id = pd.post_id";
	return $join;
}

function geodir_event_home_map_marker_query_where($where = '') {
	$today = date_i18n('Y-m-d');
	
	$where .= " AND (es.start_date >= '" . $today . "' OR (es.start_date <= '" . $today . "' AND es.end_date >= '" . $today . "')) ";
	return $where;
}

function geodir_get_detail_page_related_events($request) {
	if (!empty($request)) {
		$post_number = (isset($request['post_number']) && !empty($request['post_number'])) ? $request['post_number'] : '5';
		$relate_to = (isset($request['relate_to']) && !empty($request['relate_to'])) ? $request['relate_to'] : 'category';
		$add_location_filter = (isset($request['add_location_filter']) && !empty($request['add_location_filter'])) ? $request['add_location_filter'] : '0';
		$listing_width = (isset($request['listing_width']) && !empty($request['listing_width'])) ? $request['listing_width'] : '';
		$list_sort = (isset($request['list_sort']) && !empty($request['list_sort'])) ? $request['list_sort'] : 'latest';
		$character_count = (isset($request['character_count']) && !empty($request['character_count'])) ? $request['character_count'] : '';
		$event_type = (isset($request['event_type']) && !empty($request['event_type'])) ? $request['event_type'] : 'upcoming';
		$event_type = apply_filters('geodir_detail_page_related_event_type', $event_type);
        $layout = !empty($request['layout']) ? $request['layout'] : '';

		global $post, $map_jason;
		$current_map_jason = $map_jason;
		$post_type = $post->post_type;
		$post_id = $post->ID;
		$category_taxonomy = '';
		$tax_field = 'id';
		$category = array();

		if ($relate_to == 'category') {

			$category_taxonomy = $post_type . $relate_to;
			if (isset($post->{$category_taxonomy}) && $post->{$category_taxonomy} != '')
				$category = explode(',', trim($post->{$category_taxonomy}, ','));

		} elseif ($relate_to == 'tags') {

			$category_taxonomy = $post_type . '_' . $relate_to;
			if ($post->post_tags != '')
				$category = explode(',', trim($post->post_tags, ','));
			$tax_field = 'name';
		}

		/* --- return false in invalid request --- */
		if (empty($category))
			return false;

		$all_postypes = geodir_get_posttypes();

		if (!in_array($post_type, $all_postypes))
			return false;

		$query_args = array(
				'gd_event_type' => $event_type,
				'posts_per_page' => $post_number,
				'is_geodir_loop' => true,
				'gd_location' 	 => $add_location_filter ? true : false,
				'post_type' => 'gd_event',
				'post__not_in'     => array( $post_id ),
				'order_by' => $list_sort,
				'excerpt_length' => $character_count,
				'character_count' => $character_count,
				'listing_width' => $listing_width
		);

		$tax_query = array('taxonomy' => $category_taxonomy,
				'field' => $tax_field,
				'terms' => $category
		);

		$query_args['tax_query'] = array( $tax_query );


		add_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_related_post_ids_where' );
		$output = geodir_get_post_widget_events($query_args, $layout);
		remove_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_related_post_ids_where' );
		
		$map_jason = $current_map_jason;

		$map_jason = $current_map_jason;

		return $output;
	}
	return false;
}

function geodir_event_function_related_post_ids_where( $where ) {
	global $wpdb, $plugin_prefix, $gd_query_args;

	if ( !empty( $gd_query_args ) && isset( $gd_query_args['related_post_ids'] ) ) {
		if ($gd_query_args['related_post_ids']) {
			$where .= " AND ".$wpdb->posts .".ID IN (" . implode(',', $gd_query_args['related_post_ids']) . ")";
		}
	}

	return $where;
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 1.4.2
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_event_uninstall_settings($settings) {
    $settings[] = plugin_basename(dirname(__FILE__));
    
    return $settings;
}

/**
 * Filter the related posts widget query args.
 *
 * @since 1.4.3
 *
 * @param array $query_args The query array.
 * @param array $request Related posts request array.
 * @return array Modified query args.
 */
function geodir_event_related_posts_query_args($query_args, $request) {
    if (!empty($query_args['post_type']) && $query_args['post_type'] == 'gd_event') {
        $query_args['geodir_event_listing_filter'] = 'upcoming';
    }
    
    return $query_args;
}

/**
 * Display notice when site is running with older then PHP 5.3.
 *
 * @since 1.4.5
 *
 */
function geodir_event_PHP_version_notice() {
    echo '<div class="error" style="margin:12px 0"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by <b>GeoDirectory Events</b>. Please contact your host and request that your PHP version be upgraded to <b>5.3 or later</b>.', 'geodirevents' ) . '</p></div>';
}

/*
add_filter('geodir_show_filters','geodir_add_search_fields',10,2);

add_action('pre_get_posts','geodir_event_loop_filter' ,2 );

add_action('geodir_after_description_on_listing_detail', 'geodir_event_before_description', 1);

add_action('geodir_after_description_on_listing_preview', 'geodir_event_before_description', 1);

add_filter('geodir_search_page_title',"geodir_event_calender_search_page_title", 1);


add_filter('geodir_diagnose_multisite_conversion' , 'geodir_diagnose_multisite_conversion_events', 10,1); */
function geodir_diagnose_multisite_conversion_events($table_arr){
	
	// Diagnose Claim listing details table
	$table_arr['geodir_gd_event_detail'] = __('Events','geodirevents');
	$table_arr['geodir_event_schedule'] = __('Event schedule','geodirevents');
	return $table_arr;
}

// add date to title for recurring event
function geodir_event_title_recurring_event( $title, $post_id = null ) {
	global $post, $gd_post;

    $post_type = !empty( $post ) && isset( $post->post_type ) ? $post->post_type : '';
    if ( ! ( ! empty( $post->post_type ) && $post->post_type == 'gd_event' ) ) {
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

	if ( $event_post->ID == $post_id && !empty( $event_post->recurring ) ) {
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
			if ( is_single() && isset( $_REQUEST['gde'] ) && geodir_event_is_date( $_REQUEST['gde'] ) && geodir_event_schedule_exist( $_REQUEST['gde'], $post_id ) ) {
				$title .= "<span class='gd-date-in-title'> " . wp_sprintf( __( '- %s', 'geodirevents' ), date_i18n( $geodir_date_format, strtotime( $_REQUEST['gde'] ) ) ) . "</span>";
			}
		}
	}
	return $title;
}
add_filter( 'the_title', 'geodir_event_title_recurring_event', 100, 2 );

// get link for recurring event
function geodir_event_link_recurring_event( $link ) {
	global $post;

    if($post->post_type!='gd_event'){return $link;}
	
	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $post );
	
	if ( !$recurring_pkg ) {
		return $link;
	}
	
	if ( !empty( $post ) && isset( $post->ID ) && !empty( $post->recurring ) && !empty( $post->start_date ) ) {
		if ( geodir_event_is_date( $post->start_date ) && get_permalink() == get_permalink( $post->ID ) ) {
			$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ));
			$current_time = strtotime($current_date);
			
			$event_start_time = strtotime(date_i18n( 'Y-m-d', strtotime($post->start_date)));
			$event_end_time = isset($post->end_date) && geodir_event_is_date($post->end_date) ? strtotime($post->end_date) : 0;
			
			if ($event_end_time > $event_start_time && $event_start_time <= $current_time && $event_end_time >= $current_time) {
				$link_date = date_i18n( 'Y-m-d', strtotime( $current_time ) );
			} else {
				$link_date = date_i18n( 'Y-m-d', strtotime( $post->start_date ) );
			}
		
			// recuring event link
			$link = geodir_getlink( get_permalink( $post->ID ), array( 'gde' => $link_date ) );
		}
	}
	return $link;
}
add_filter( 'the_permalink', 'geodir_event_link_recurring_event', 100 );

// Filter the page title for event listing.
add_filter( 'geodir_listing_page_title', 'geodir_event_listing_page_title', 2, 10);

// Remove past event count from popular category count.
if ( !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	add_filter( 'get_terms', 'geodir_event_get_terms', 20, 3 );
	
	add_filter( 'geodir_category_term_link', 'geodir_event_category_term_link', 20, 3 );
}

add_filter('geodir_count_reviews_by_term_sql', 'geodir_event_count_reviews_by_term_sql', 10, 4);
add_filter('geodir_location_count_reviews_by_term_sql', 'geodir_event_count_reviews_by_location_term_sql', 10, 7);
add_filter('geodir_bestof_widget_view_all_link', 'geodir_event_bestof_widget_view_all_link', 10, 3);
add_filter('geodir_event_filter_widget_events_join', 'geodir_function_widget_listings_join', 10, 1);
add_filter('geodir_event_filter_widget_events_where', 'geodir_function_widget_listings_where', 10, 1);
add_action('geodir_infowindow_meta_before', 'geodir_event_infowindow_meta_event_dates', 10, 3);

// Remove past event count from popular category count.
if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] == 'cat' && isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] == 'gd_event') {
	add_filter('geodir_home_map_listing_join', 'geodir_event_home_map_marker_query_join', 99, 1);
	add_filter('geodir_home_map_listing_where', 'geodir_event_home_map_marker_query_where', 100, 1);
}



add_filter('geodir_details_schema','geodir_event_schema_filter',10,2);
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
function geodir_event_schema_filter($schema, $post) {
    $event_schema_types = geodir_event_get_schema_types();
    if ( isset( $event_schema_types[ $schema['@type'] ] ) ) {
        if (!empty($post->link_business)) {
            $place = array();
            $linked_post = geodir_get_post_info($post->link_business);
            $place["@type"] = "Place";
            $place["name"] =  $linked_post->post_title;
            $place["address"] = array(
                "@type" => "PostalAddress",
                "streetAddress" => $linked_post->street,
                "addressLocality" => $linked_post->city,
                "addressRegion" => $linked_post->region,
                "addressCountry" => $linked_post->country,
                "postalCode" => $linked_post->zip
            );
            $place["telephone"] = $linked_post->geodir_contact;
            
            if($linked_post->latitude && $linked_post->longitude) {
                $schema['geo'] = array(
                    "@type" => "GeoCoordinates",
                    "latitude" => $linked_post->latitude,
                    "longitude" => $linked_post->longitude
                );
            }
        } else {
            $place = array();
            $place["@type"] = "Place";
            $place["name"] = $schema['name'];
            $place["address"] = $schema['address'];
            if ( ! empty( $schema['telephone'] ) ) {
				$place["telephone"] = $schema['telephone'];
			}
            $place["geo"] = $schema['geo'];
        }

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

add_filter('geodir_advance_search_filter_titles', 'geodir_event_search_calendar_day_filter_title', 10, 1);
add_filter('geodir_title_meta_settings', 'geodir_event_filter_title_meta_vars', 10, 1);
add_filter('geodir_filter_title_variables_vars', 'geodir_event_filter_title_variables_vars', 10, 4);
add_filter('geodir_related_posts_widget_query_args', 'geodir_event_related_posts_query_args', 10, 2);

if (is_admin()) {
    add_filter('geodir_plugins_uninstall_settings', 'geodir_event_uninstall_settings', 10, 1);
}

add_filter('geodir_popular_post_view_list_sort','geodir_event_add_sort_option',10,2);
/**
 * Add upcoming sort option to popular post view widget options.
 *
 * @since 1.4.7
 * @param array $list_sort_arr The array of key value pairs of settings.
 * @param array $instance The array of widget settings.
 *
 * @return array The array of filtered sort options.
 */
function geodir_event_add_sort_option($list_sort_arr,$instance){

	$list_sort_arr['upcoming'] = __('Upcoming (Events Only)','geodirevents');
	return $list_sort_arr;
}


/**
 * Group the recurring events in search results.
 *
 * @since 1.4.7
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $groupby The GROUP BY clause of the query.
 * @param WP_Query $wp_query The WP_Query instance.
 * @return string Filtered GROUP BY clause of the query.
 */
function geodir_event_group_recurring_events( $groupby, $wp_query ) {
    global $wpdb;
    
    // No proximity parameter set.
    if ( !( isset( $_REQUEST['sgeo_lat'] ) && $_REQUEST['sgeo_lat'] != '' && isset( $_REQUEST['sgeo_lon'] ) && $_REQUEST['sgeo_lon'] != '' ) ) {
        return $groupby;
    }

    if ( !empty( $_REQUEST['stype'] ) && $_REQUEST['stype'] == 'gd_event' && $wp_query->is_main_query() && geodir_is_page( 'search' ) ) {
        $groupby = $wpdb->posts . ".ID";
    }

    return $groupby;
}
add_filter( 'posts_groupby', 'geodir_event_group_recurring_events', 100, 2 );

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

function geodir_event_array_insert ( $array, $position, $insert_array ) {
	$first_array = array_splice ( $array, 0, $position );
	return array_merge ( $first_array, $insert_array, $array );
}

function geodir_event_filter_options() {
	$options = array(
		'all' => __( 'All Events', 'geodirevents' ),
		'today' => __( 'Today', 'geodirevents' ),
		'upcoming' => __( 'Upcoming', 'geodirevents' ),
		'past' => __( 'Past', 'geodirevents' )
	);
	return apply_filters( 'geodir_event_filter_options', $options );
}

add_shortcode( 'gd_event_calendar', 'geodir_sc_event_calendar' );
function geodir_sc_event_calendar( $atts ) {
	ob_start();
	$defaults = array(
		'title' => '',
		'day'   => '',
		'before_widget'		  => '',
		'after_widget'		  => '',
		'before_title'		  => '<h3 class="widget-title">',
		'after_title'		  => '</h3>',
		'add_location_filter' => 0
	);

	$params = shortcode_atts( $defaults, $atts );

	GeoDir_Event_Calendar::display_calendar($params, $params);

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'gd_related_events', 'geodir_sc_related_events' );
function geodir_sc_related_events( $atts ) {
	ob_start();
	$defaults = array(
		'post_number'         => 5,
		'layout'              => 'gridview_onehalf',
		'event_type'          => 'all',
		'add_location_filter' => 0,
		'listing_width'       => '',
		'list_sort'           => 'latest',
		'character_count'     => '20',
	);

	$params = shortcode_atts( $defaults, $atts );

	/**
	 * Begin validating params
	 */

	// Validate that post_number is a number and is 1 or higher
	$params['post_number'] = absint( $params['post_number'] );
	if ( 0 === $params['post_number'] ) {
		$params['post_number'] = 1;
	}

	// Validate layout selection
	$params['layout'] = gdsc_validate_layout_choice( $params['layout'] );

	// Validate event type selection
	$params['event_type'] = gdsc_validate_event_type( $params['event_type'] );

	// Validate listing_width
	$params['listing_width'] = gdsc_validate_listing_width( $params['listing_width'] );

	// Validate sorting option
	$params['list_sort'] = $params['list_sort'] == 'upcoming' ? $params['list_sort'] : gdsc_validate_sort_choice( $params['list_sort'] );

	// Validate character_count
	$params['character_count'] = absint( $params['character_count'] );
	if ( 20 > $params['character_count'] ) {
		$params['character_count'] = 20;
	}

	/**
	 * End validating params
	 */

	global $post;
	$post_id   = '';
	$post_type = '';

	if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
		$post      = geodir_get_post_info( $_REQUEST['pid'] );
		$post_type = $post->post_type;
		$post_id   = $_REQUEST['pid'];
	} elseif ( isset( $post->post_type ) && $post->post_type != '' ) {
		$post_type = $post->post_type;
		$post_id   = $post->ID;
	}

	$all_postypes = geodir_get_posttypes();

	if ( ! ( in_array( $post_type, $all_postypes ) ) ) {
		return false;
	}

	if ( $post_type == 'gd_place' && $post_id != '' ) {
		$query_args = array(

			'gd_event_type' 	=> $params['event_type'],
			'event_related_id'  => $post_id,
			'posts_per_page'    => $params['post_number'],
			'is_geodir_loop'    => true,
			'gd_location'       => $params['add_location_filter'],
			'post_type'         => 'gd_event',
			'order_by'          => $params['list_sort'],
			'excerpt_length'    => $params['character_count'],

		);

		geodir_get_post_feature_events( $query_args, $params['layout'] );
		geodir_get_post_past_events( $query_args, $params['layout'] );

	}
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function gdsc_validate_event_type( $event_type ) {
	$options = array(
		'all',
		'feature',
		'past',
		'future',
	);

	if ( ! ( in_array( $event_type, $options ) ) ) {
		$event_type = 'feature';
	}
}