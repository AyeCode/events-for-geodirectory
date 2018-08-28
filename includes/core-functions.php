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
 * Filter reviews sql query fro upcoming events for current location.
 *
 * @since 1.2.4
 * @since 1.3.0 Fixed post term count for neighbourhood locations.
 *
 * @global object $wpdb WordPress Database object.
 *
 * @param string $sql Database sql query.
 * @param int $term_id The term ID.
 * @param int $taxonomy The taxonomy Id.
 * @param string $post_type The post type.
 * @param string $location_type Location type .
 * @param array $loc Current location terms.
 * @param string $count_type The term count type.
 * @return string Database sql query.
 * @todo move to LMv2 and use filter
 */
function geodir_event_count_reviews_by_location_term_sql( $sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type ) {
	if ($term_id > 0 && $post_type == 'gd_event') {
		global $wpdb;
		
		if ($count_type == 'review_count') {
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
				$where .= $wpdb->prepare( " AND ed.country LIKE %s", array( $country ) );
			}
			
			if ( $region != '' && $location_type!='gd_country' ) {
				$where .= $wpdb->prepare( " AND ed.region LIKE %s", array( $region ) );
			}
			
			if ( $city != '' && $location_type!='gd_country' && $location_type!='gd_region' ) {
				$where .= $wpdb->prepare( " AND ed.city LIKE %s", array( $city ) );
			}
			
			if ( $location_type == 'gd_neighbourhood' && $neighbourhood != '' && GeoDir_Location_Neighbourhood::is_active() ) {
				$where .= $wpdb->prepare( " AND ed.neighbourhood LIKE %s", array( $neighbourhood ) );
			}
			
			$sql = "SELECT COALESCE(SUM(ed.rating_count),0) FROM `" . GEODIR_EVENT_DETAIL_TABLE . "` AS ed INNER JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " AS es ON (es.event_id = ed.post_id) WHERE ed.post_status = 'publish' " . $where . " AND ed.rating_count > 0 AND FIND_IN_SET(" . $term_id . ", ed.post_category) AND (es.start_date >= '" . $current_date . "' OR (es.start_date <= '" . $current_date . "' AND es.end_date >= '" . $current_date . "'))";
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
function geodir_event_bestof_widget_view_all_link( $view_all_link, $post_type, $term ) {
	if ( $post_type == 'gd_event' ) {
		$view_all_link = add_query_arg( array( 'etype' => geodir_get_option( 'event_default_filter' ) ), $view_all_link ) ;
	}
	return $view_all_link;
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

function geodir_event_filter_options() {
	$options = array(
		'all' => __( 'All Events', 'geodirevents' ),
		'today' => __( 'Today', 'geodirevents' ),
		'upcoming' => __( 'Upcoming', 'geodirevents' ),
		'past' => __( 'Past', 'geodirevents' )
	);
	return apply_filters( 'geodir_event_filter_options', $options );
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