<?php
/**
 * Event Schedules class
 *
 *
 * @link       https://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    GeoDir_Event_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_Schedules class
 *
 * @class       GeoDir_Event_Schedules
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 */
class GeoDir_Event_Schedules {

    public function __construct() {
	}

	public static function init() {
		add_action( 'delete_post', array( __CLASS__, 'delete_schedules' ), 10, 1 );
		add_filter( 'geodir_location_count_reviews_by_term_sql', array( __CLASS__, 'location_term_counts' ), 10, 8 );
	}

	public static function save_schedules( $event_data, $post_id ) {
		if ( empty( $event_data ) || empty( $post_id ) ) {
			return false;
		}

		$format 				= geodir_event_field_date_format();
		$default_start_date 	= date_i18n( $format );

		$data					= maybe_unserialize( $event_data );
		$recurring 				= ! empty( $data['recurring'] ) ? true : false;
		$all_day 				= ! empty( $data['all_day'] ) ? true : false;
		$start_date 			= ! empty( $data['start_date'] ) ? $data['start_date'] : '';
		$end_date 				= ! empty( $data['end_date'] ) ? $data['end_date'] : $start_date;
		$start_time 			= ! $all_day && ! empty( $data['start_time'] ) ? $data['start_time'] : '';
		$end_time 				= ! $all_day && ! empty( $data['end_time'] ) ? $data['end_time'] : '';

		$schedules = array();
		if ( $recurring ) {
			$duration 			= isset( $data['duration_x'] ) && (int)$data['duration_x'] > 0 ? (int)$data['duration_x'] : 1;
			$repeat_type 		= !empty( $data['repeat_type'] ) ? $data['repeat_type'] : 'custom';
			$different_times 	= !empty( $data['different_times'] ) ? true : false;
			$start_times 		= $different_times && ! $all_day && isset( $data['start_times'] ) ? $data['start_times'] : array();
			$end_times 			= $different_times && ! $all_day && isset( $data['end_times'] ) && !empty( $data['end_times'] ) ? $data['end_times'] : array();
			$duration--;

			if ( $repeat_type == 'custom' ) {
				$recurring_dates = $data['recurring_dates'];
			} else {
				$recurring_dates = GeoDir_Event_Schedules::get_occurrences( $repeat_type, $start_date, $end_date, $data['repeat_x'], $data['max_repeat'], $data['repeat_end'], $data['repeat_days'], $data['repeat_weeks'] );
			}

			if ( empty( $recurring_dates ) ) {
				$recurring_dates = array( $start_date );
			}

			foreach ( $recurring_dates as $key => $date ) {
				if ( $data['repeat_type'] == 'custom' && $different_times ) {
					$duration 		= 0;
					$start_time 	= ! empty( $start_times[ $key ] ) ? $start_times[ $key ] : '';
					$end_time 		= ! empty( $end_times[ $key ] ) ? $end_times[ $key ] : '';
				}
				if ( $all_day == 1 ) {
					$start_time = '';
					$end_time 	= '';
				}
				$start_date 	= $date;
				$end_date 		= date_i18n( 'Y-m-d', strtotime( $start_date . ' + ' . $duration . ' day' ) );

				$schedules[] = array(
					'event_id' 		=> $post_id,
					'start_date' 	=> $start_date,
					'end_date' 		=> $end_date,
					'start_time' 	=> $start_time,
					'end_time' 		=> $end_time,
					'all_day' 		=> $all_day,
					'recurring' 	=> $recurring,
				);
			}
		} else {
			$schedules[] = array(
				'event_id' 		=> $post_id,
				'start_date' 	=> $start_date,
				'end_date' 		=> $end_date,
				'start_time' 	=> $start_time,
				'end_time' 		=> $end_time,
				'all_day' 		=> $all_day,
				'recurring' 	=> $recurring,
			);
		}

		if ( ! empty( $schedules ) ) {
			return self::create_schedules( $schedules, $post_id );
		}

		return false;
	}

	public static function create_schedules( $schedules, $post_id ) {
		global $wpdb;

		if ( empty( $schedules ) || empty( $post_id ) ) {
			return false;
		}

		self::delete_schedules( $post_id );

		foreach( $schedules as $schedule ) {
			$wpdb->insert( GEODIR_EVENT_SCHEDULES_TABLE, $schedule, array( '%d', '%s', '%s', '%s', '%s', '%d', '%d' ) );
		}

		return true;
	}

	public static function delete_schedules( $post_id, $post_type = '' ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		if ( empty( $post_type ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return false;
		}

		$return = $wpdb->query( $wpdb->prepare( "DELETE FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE event_id = %d", array( $post_id ) ) );
		if ( $return ) {
			do_action( 'geodir_event_deleted_schedules', $post_id, $post_type );
		}

		return $return;
	}

	public static function get_occurrences( $type = 'year', $start_date, $end_date = '', $interval = 1, $limit = '', $repeat_end = '', $repeat_days = array(), $repeat_weeks = array() ) {
		$dates = array();
		$start_time = strtotime( $start_date );
		$end_time = strtotime( $repeat_end );

		switch ( $type ) {
			case 'year': {
				if ( $repeat_end != '' && geodir_event_is_date( $repeat_end ) ) {
					for ( $time = $start_time; $time <= $end_time; $time = strtotime( date_i18n( 'Y-m-d', $time ) . '+' . $interval . ' year' ) ) {
						$year 	= date_i18n( 'Y', $time );
						$month 	= date_i18n( 'm', $time );
						$day 	= date_i18n( 'd', $time );

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
							$every 	= $interval * $i;
							$time 	= strtotime( $start_date . '+' . $every . ' year' );

							$year 	= date_i18n( 'Y', $time );
							$month 	= date_i18n( 'm', $time );
							$day 	= date_i18n( 'd', $time );

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
						$year 	= date_i18n( 'Y', $time );
						$month 	= date_i18n( 'm', $time );
						$day 	= date_i18n( 'd', $time );

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
								$every 	= $interval * $i;
								$time 	= strtotime( $start_date . '+' . $every . ' month' );
								$year 	= date_i18n( 'Y', $time );
								$month 	= date_i18n( 'm', $time );
								$day 	= date_i18n( 'd', $time );

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
						$year 	= date_i18n( 'Y', $time );
						$month 	= date_i18n( 'm', $time );
						$day 	= date_i18n( 'd', $time );

						$date_occurrence = $year . '-' . $month . '-' . $day;
						$time_occurrence = strtotime( $date_occurrence );

						if ( $time_occurrence <= $end_time ) {
							if ( !empty( $repeat_days ) ) {
								for ( $d = 0; $d <= 6; $d++ ) {
									$recurr_time 	= strtotime( $date_occurrence . '+' . $d . ' day' );
									$week_day 		= date_i18n( 'w', $recurr_time );

									if ( in_array( $week_day, $repeat_days ) && $recurr_time <= $end_time ) {
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
								$time 		= strtotime( $start_date . '+' . ( $interval * $i ) . ' week' );
								$year 		= date_i18n( 'Y', $time );
								$month 		= date_i18n( 'm', $time );
								$day 		= date_i18n( 'd', $time );

								$date_occurrence = $year . '-' . $month . '-' . $day;

								for ( $d = 0; $d <= 6; $d++ ) {
									$recurr_time 	= strtotime( $date_occurrence . '+' . $d . ' day' );
									$week_day 		= date_i18n( 'w', $recurr_time );

									if ( in_array( $week_day, $repeat_days ) ) {
										$week_dates[] 	= date_i18n( 'Y-m-d', $recurr_time );
										$dates[] 		= date_i18n( 'Y-m-d', $recurr_time );
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
								$every 		= $interval * $i;
								$time 		= strtotime( $start_date . '+' . $every . ' week' );
								$year 		= date_i18n( 'Y', $time );
								$month 		= date_i18n( 'm', $time );
								$day 		= date_i18n( 'd', $time );

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
						$year 		= date_i18n( 'Y', $time );
						$month 		= date_i18n( 'm', $time );
						$day 		= date_i18n( 'd', $time );
						
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
							$every 	= $interval * $i;
							$time 	= strtotime( $start_date . '+' . $every . ' day' );
							$year 	= date_i18n( 'Y', $time );
							$month 	= date_i18n( 'm', $time );
							$day 	= date_i18n( 'd', $time );
							
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

	public static function get_schedules( $post_id, $event_type = '', $limit = 0 ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$where = array( 'event_id = %d' );
		if ( ( $condition = GeoDir_Event_Schedules::event_type_condition( $event_type ) ) ) {
			$where[] = $condition;
		}

		$limit = absint( $limit ) > 0 ? " LIMIT 0, " . absint( $limit ) : '';
		$where = implode( ' AND ', $where );

		$schedules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE {$where} ORDER BY start_date ASC, start_time ASC{$limit}", array( $post_id ) ) );

		return $schedules;
	}

	public static function get_upcoming_schedule( $post_id, $date = '' ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$where = array( 'event_id = %d' );
		if ( ( $condition = self::event_type_condition( 'upcoming', '', $date ) ) ) {
			$where[] = $condition;
		}

		$where = implode( ' AND ', $where );

		$schedules = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE {$where} ORDER BY start_date ASC, start_time ASC LIMIT 1", array( $post_id ) ) );

		return $schedules;
	}

	public static function get_start_schedule( $post_id ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$where = array( 'event_id = %d' );
		$where = implode( ' AND ', $where );

		$schedules = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE {$where} ORDER BY start_date ASC, start_time ASC LIMIT 1", array( $post_id ) ) );

		return $schedules;
	}

	public static function get_schedules_html( $schedules, $link = true ) {
		global $schedule_links;

		if ( empty( $schedules ) ) {
			return NULL;
		}

		if ( empty( $schedule_links ) ) {
			$schedule_links = array();
		}

		$date_time_format 	= geodir_event_date_time_format();
		$date_format 		= geodir_event_date_format();
		$time_format		= geodir_event_time_format();
		$schedule_seperator = apply_filters( 'geodir_event_schedule_start_end_seperator', '<div class="geodir-schedule-sep">-</div>' );
		$gmt_offset			= geodir_gmt_offset();
		$current			= ! empty( $_REQUEST['gde'] ) ? $_REQUEST['gde'] : '';

		$html		= '';
		foreach ( $schedules as $key => $row ) {
			if ( ! empty( $row->start_date ) && $row->start_date != '0000-00-00' ) {
				$start_date		= $row->start_date;
				$end_date		= ! empty( $row->end_date ) && $row->end_date != '0000-00-00' ? $row->end_date : $start_date;
				$start_time		= ! empty( $row->start_time ) ? $row->start_time : '00:00:00';
				$end_time		= ! empty( $row->end_time ) ? $row->end_time : '00:00:00';
				$all_day		= ! empty( $row->all_day ) ? true : false;

				$schedule = '<div class="geodir-schedule-start"><i class="fas fa-caret-right"></i>';
				if ( empty( $all_day ) ) {
					if ( $start_date == $end_date && $start_time == $end_time && $end_time == '00:00:00' ) {
						$end_date = date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . $start_time . ' +1 day' ) );
					}

					if ( $start_date == $end_date ) {
						$schedule .= date_i18n( $date_format, strtotime( $start_date ) );
						$schedule .= ', ' . date_i18n( $time_format, strtotime( $start_time ) );
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end">';
						$schedule .= date_i18n( $time_format, strtotime( $end_time ) );
					} else {
						$schedule .= date_i18n( $date_time_format, strtotime( $start_date . ' '. $start_time ) );
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end">';
						$schedule .= date_i18n( $date_time_format, strtotime( $end_date . ' '. $end_time ) );
					}
					$meta_startDate = $start_date . 'T' . date_i18n( 'H:i:s', strtotime( $start_time ) );
					$meta_endDate = $end_date . 'T' . date_i18n( 'H:i:s', strtotime( $end_time ) );
				} else {
					$schedule .= date_i18n( $date_format, strtotime( $start_date ) );
					if ( $start_date != $end_date ) {
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end">';
						$schedule .= date_i18n( $date_format, strtotime( $end_date ) );
						$meta_endDate = $end_date . 'T00:00:00';
					} else {
						$meta_endDate = date_i18n( 'Y-m-d', strtotime( $start_date . ' 00:00:00 +1 day' ) ) . 'T00:00:00';
					}
					$meta_startDate = $start_date . 'T00:00:00';
				}
				$schedule .= '</div>';

				if ( $link ) {
					if ( ! empty( $schedule_links[ $row->event_id ] ) ) {
						$schedule_url = $schedule_links[ $row->event_id ];
					} else {
						$schedule_url = get_permalink( $row->event_id );
						$schedule_links[ $row->event_id ] = $schedule_url;
					}
					$schedule_url = add_query_arg( array( 'gde' => $start_date ), $schedule_url );
					$schedule_url = apply_filters( 'geodir_event_recurring_schedule_url', $schedule_url, $row->event_id, $row );

					$schedule = '<a href="' . esc_url( $schedule_url ) . '">' . $schedule . '</a>';
				}

				$class = $current == $start_date ? ' geodir-schedule-current' : '';
				$html .= '<div class="geodir-schedule' . $class . '"><meta itemprop="startDate" content="' . $meta_startDate . $gmt_offset . '"><meta itemprop="endDate" content="' . $meta_endDate . $gmt_offset . '">' . $schedule . '</div>';
			}
		}
		if ( ! empty( $html ) ) {
			$html = '<div class="geodir-schedules">' . $html . '</div>';
		}

		return $html;
	}

	public static function event_type_condition( $event_type, $alias = NULL, $date = '' ) {

		//Maybe abort early
		if( false === $event_type ) {
			return '1=1 ';
		}

		if ( $alias === NULL ) {
			$alias = GEODIR_EVENT_SCHEDULES_TABLE;
		}

		if ( ! empty( $alias ) ) {
			$alias = $alias . '.';
		}
		if ( empty( $date ) ) {
			$date = date_i18n( 'Y-m-d' );
		}

		//Set end of the week
		$day 			 = date( 'l', strtotime( $date ));
		$sunday			 = date( 'Y-m-d', strtotime( 'this sunday'));
		if( $day == 'sunday' ) {
			$sunday = $date;
		}

		//Prepare durations
		$tomorrow 	  	 		= date( 'Y-m-d', strtotime( $date. ' + 1 days'));
		$next_7_days  	 		= date( 'Y-m-d', strtotime( $date. ' + 7 days'));
		$next_30_days 	 		= date( 'Y-m-d', strtotime( $date. ' + 30 days'));
		$last_day_month  		= date('Y-m-t');
		$first_day_next_week    = date( 'Y-m-d', strtotime( 'next week monday'));
		$last_day_next_week  	= date( 'Y-m-d', strtotime( 'next week sunday'));
		$first_day_next_month   = date( 'Y-m-d', strtotime( 'first day of next month'));
		$last_day_next_month  	= date( 'Y-m-d', strtotime( 'last day of next month'));
		

		//Get this weekend days
		if( in_array( $day, explode( ' ', 'friday saturday sunday'))){	
			//Is this a weekend day
			$weekend_start = $date;
		} else {
			//This is a weekday
			$weekend_start = date( 'Y-m-d', strtotime( 'this friday'));
		}

		$filters = array(
			'past'			=> "{$alias}start_date < '$date' ",
			'upcoming'		=> " {$alias}start_date >= '$date' ",
			'today'			=> " {$alias}start_date = '$date' ",
			'tomorrow'  	=> "{$alias}start_date = '$tomorrow' ",
			'next_7_days'   => "( {$alias}start_date BETWEEN '$date' AND '$next_7_days' ) ",
			'next_30_days'  => "( {$alias}start_date BETWEEN '$date' AND '$next_30_days' ) ",
			'this_week'  	=> "( {$alias}start_date BETWEEN '$date' AND '$sunday' ) ",
			'this_weekend'  => "( {$alias}start_date BETWEEN '$weekend_start' AND '$sunday' ) ",
			'this_month'  	=> "( {$alias}start_date BETWEEN '$date' AND '$last_day_month' ) ",
			'next_month'  	=> "( {$alias}start_date BETWEEN '$first_day_next_month' AND '$last_day_next_month' ) ", 
			'next_week'  	=> "( {$alias}start_date BETWEEN '$first_day_next_week' AND '$last_day_next_week' ) ", 
		);
		//echo '<pre>'; var_dump($filters); echo '</pre>';  exit;

		//If the filter is provided, filter the events
		if(! empty( $filters[$event_type] ) ) {
			return $filters[$event_type];
		}

		//Handle the special between filter where dates are separated by |
		$dates 		= explode( '|', strtolower( $event_type ) );

		//If there are two dates provided...
		if( 2 === count( $dates) ) {
			$date1  = date( 'Y-m-d', strtotime( $dates[0] ) );
			$date2  = date( 'Y-m-d', strtotime( $dates[1] ) );
			$filter = "( {$alias}start_date BETWEEN '$date1' AND '$date2' ) ";
			//echo '<pre>'; var_dump($filter); echo '</pre>';  exit;
			return $filter;
		}
		

		//If we are here, this filter has not been implemented so we just return all events
		return '1=1 ';
	}

	public static function has_schedule( $post_id, $date ) {
		$schedule = self::get_upcoming_schedule( $post_id, $date );
		return ! empty( $schedule ) ? true : false;
	}

	public static function location_term_counts( $sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type, $where ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' )  ) {
			$table = geodir_db_cpt_table( $post_type );

			$join = "LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = post_id";
			$condition = self::event_type_condition( 'upcoming' );

			if ( $count_type == 'review_count' ) {
				$sql = "SELECT COALESCE(SUM(rating_count),0) FROM {$table} {$join} WHERE post_status = 'publish' $where AND FIND_IN_SET( " . $term_id . ", post_category )";
			} else {
				$sql = "SELECT COUNT(post_id) FROM {$table} {$join} WHERE post_status = 'publish' $where AND FIND_IN_SET( " . $term_id . ", post_category )";
			}

			 $sql .= " AND {$condition}";
		}

		return $sql;
	}
}