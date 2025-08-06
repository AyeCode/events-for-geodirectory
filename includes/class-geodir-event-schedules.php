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
		add_filter( 'seopress_sitemaps_single_url', array( __CLASS__, 'seopress_sitemaps_single_url' ), 10, 2 );
		add_filter( 'geodir_elementor_tag_url_render_value', array( __CLASS__, 'elementor_tag_url_render_value' ), 10, 3 );
	}

	public static function save_schedules( $event_data, $post_id ) {
		if ( empty( $event_data ) || empty( $post_id ) ) {
			return false;
		}

		// Prevent object injection.
		if ( is_serialized( $event_data ) ) {
			$data = unserialize( $event_data, array( 'allowed_classes' => false ) );
		} else {
			$data = $event_data;
		}

		if ( is_object( $data ) ) {
			return false;
		}

		$recurring 				= ! empty( $data['recurring'] ) ? true : false;
		$all_day 				= ! empty( $data['all_day'] ) ? true : false;
		$start_date 			= ! empty( $data['start_date'] ) ? $data['start_date'] : '';
		$end_date 				= ! empty( $data['end_date'] ) ? $data['end_date'] : $start_date;
		$start_time 			= ! $all_day && ! empty( $data['start_time'] ) ? $data['start_time'] : '';
		$end_time 				= ! $all_day && ! empty( $data['end_time'] ) ? $data['end_time'] : '';

		$schedules = array();
		if ( $recurring ) {
			$duration 			= isset( $data['duration_x'] ) && (int) $data['duration_x'] > 0 ? (int) $data['duration_x'] : 1;
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
					$start_time = ! empty( $start_times[ $key ] ) ? $start_times[ $key ] : '';
					$end_time = ! empty( $end_times[ $key ] ) ? $end_times[ $key ] : '';
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

		// Max schedules allowed for the event.
		$max_schedules = (int) self::max_schedules( $post_id );

		if ( $max_schedules > 0 && count( $schedules ) > $max_schedules ) {
			// Extract a slice of the schedules.
			$schedules = array_slice( $schedules, 0, $max_schedules );
		}

		/**
		 * Filter event created schedules.
		 *
		 * @since 2.1.1.5
		 *
		 * @param array $schedules Array of event schedules.
		 * @param int   $post_id Post ID.
		 */
		$schedules = apply_filters( 'geodir_event_create_schedules', $schedules, $post_id );

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

		if ( wp_is_post_revision( $post_id ) && ( $_post_id = wp_get_post_parent_id( $post_id ) ) ) {
			$post_type = get_post_type( $_post_id );
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

	public static function get_occurrences( $type = 'year', $start_date = '', $end_date = '', $interval = 1, $limit = '', $repeat_end = '', $repeat_days = array(), $repeat_weeks = array() ) {
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
							$days_limit = 0;

							$i = 0;
							while ( $days_limit <= $limit ) {
								$time = strtotime( $start_date . '+' . ( $interval * $i ) . ' month' );
								$year = date_i18n( 'Y', $time );
								$month = date_i18n( 'm', $time );

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

	public static function get_schedule( $schedule_id ) {
		global $wpdb;

		if ( empty( $schedule_id ) ) {
			return false;
		}

		$cache_key = 'geodir_event_schedule:' . $schedule_id;

		$schedule = wp_cache_get( $cache_key );
		if ( $schedule !== false ) {
			return $schedule;
		}

		$schedule = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE schedule_id = %d LIMIT 1", array( $schedule_id ) ) );

		wp_cache_set( $cache_key, $schedule );

		return $schedule;
	}

	public static function get_schedules( $post_id, $event_type = '', $limit = 0, $min_date = '' ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$where = array( 'event_id = %d' );
		if ( ( $condition = self::event_type_condition( $event_type, '', '', $min_date ) ) ) {
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

		$schedule = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE event_id = %d ORDER BY start_date ASC, start_time ASC LIMIT 1", array( $post_id ) ) );

		return $schedule;
	}

	public static function get_last_schedule( $post_id ) {
		global $wpdb;

		if ( empty( $post_id ) ) {
			return false;
		}

		$schedule = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " WHERE event_id = %d ORDER BY start_date DESC, start_time DESC LIMIT 1", array( $post_id ) ) );

		return $schedule;
	}

	public static function get_schedules_html( $schedules, $link = true ) {
		global $schedule_links, $aui_bs5;

		if ( empty( $schedules ) ) {
			return NULL;
		}

		if ( empty( $schedule_links ) ) {
			$schedule_links = array();
		}

		$design_style       = geodir_design_style();
		$date_time_format 	= geodir_event_date_time_format();
		$date_format 		= geodir_event_date_format();
		$time_format		= geodir_event_time_format();
		$sep_class = $design_style ? 'd-inline-block px-1' : '';
		$schedule_seperator = apply_filters( 'geodir_event_schedule_start_end_seperator', '<div class="geodir-schedule-sep '.$sep_class.'">-</div>' );
		$gmt_offset			= geodir_gmt_offset();
		$current			= ! empty( $_REQUEST['gde'] ) ? sanitize_text_field( $_REQUEST['gde'] ) : '';
		$count = 0;

		$html		= '';
		foreach ( $schedules as $key => $row ) {
			if ( ! empty( $row->start_date ) && $row->start_date != '0000-00-00' ) {
				$start_date		= $row->start_date;
				$end_date		= ! empty( $row->end_date ) && $row->end_date != '0000-00-00' ? $row->end_date : $start_date;
				$start_time		= ! empty( $row->start_time ) ? $row->start_time : '00:00:00';
				$end_time		= ! empty( $row->end_time ) ? $row->end_time : '00:00:00';
				$all_day		= ! empty( $row->all_day ) ? true : false;
				$count++;

				$start_class = $design_style ? 'd-inline-block' : '';
				$end_class = $design_style ? 'd-inline-block' : '';
				$schedule = '<div class="geodir-schedule-start '.$start_class.'"><i class="fas fa-caret-right"></i> ';
				if ( empty( $all_day ) ) {
					if ( $start_date == $end_date && $start_time == $end_time && $end_time == '00:00:00' ) {
						$end_date = date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . $start_time . ' +1 day' ) );
					}

					if ( $start_date == $end_date ) {
						$schedule .= date_i18n( $date_format, strtotime( $start_date ) );
						$date_time_sep = apply_filters('geodir_event_date_time_separator', ', ');
						$schedule .= $date_time_sep . date_i18n( $time_format, strtotime( $start_time ) );
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end '.$end_class.'">';
						$schedule .= date_i18n( $time_format, strtotime( $end_time ) );
					} else {
						$schedule .= date_i18n( $date_time_format, strtotime( $start_date . ' '. $start_time ) );
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end '.$end_class.'">';
						$schedule .= date_i18n( $date_time_format, strtotime( $end_date . ' '. $end_time ) );
					}
					$meta_startDate = $start_date . 'T' . date_i18n( 'H:i:s', strtotime( $start_time ) );
					$meta_endDate = $end_date . 'T' . date_i18n( 'H:i:s', strtotime( $end_time ) );
				} else {
					$schedule .= date_i18n( $date_format, strtotime( $start_date ) );
					if ( $start_date != $end_date ) {
						$schedule .= '</div>' . $schedule_seperator . '<div class="geodir-schedule-end '.$end_class.'">';
						$schedule .= date_i18n( $date_format, strtotime( $end_date ) );
						$meta_endDate = $end_date . 'T00:00:00';
					} else {
						$meta_endDate = date_i18n( 'Y-m-d', strtotime( $start_date . ' 00:00:00 +1 day' ) ) . 'T00:00:00';
					}
					$meta_startDate = $start_date . 'T00:00:00';
				}
				$schedule .= '</div>';

				if ( $link ) {
					// Don't show link for preview.
					if ( is_preview() ) {
						$schedule_url = '#';
					} else {
						if ( ! empty( $schedule_links[ $row->event_id ] ) ) {
							$schedule_url = $schedule_links[ $row->event_id ];
						} else {
							$schedule_url = get_permalink( $row->event_id );
							$schedule_links[ $row->event_id ] = $schedule_url;
						}

						$schedule_url = add_query_arg( array( 'gde' => $start_date ), $schedule_url );
					}

					$schedule_url = apply_filters( 'geodir_event_recurring_schedule_url', $schedule_url, $row->event_id, $row );

					$schedule = '<a href="' . esc_url( $schedule_url ) . '">' . $schedule . '</a>';
				}

				$class = $current == $start_date ? ' geodir-schedule-current' : '';
				$class .= $count > 5 && $design_style ? ' collapse' : '';
				$html .= '<div class="geodir-schedule' . $class . '"><meta itemprop="startDate" content="' . $meta_startDate . $gmt_offset . '"><meta itemprop="endDate" content="' . $meta_endDate . $gmt_offset . '">' . $schedule . '</div>';
			}
		}
		if ( ! empty( $html ) ) {
			$wrap_class = $design_style ? 'd-inline-block' : '';
			$html = '<div class="geodir-schedules '.$wrap_class.'">' . $html . '</div>';
			if($design_style && $count > 5){
				$badge_class = $aui_bs5 ? 'text-bg-primary' : 'badge-primary';
				$html .= '<button onclick="if(jQuery(this).text()==\'' . __( 'More', 'geodirevents' ) . '\'){jQuery(this).text(\'' . __( 'Less', 'geodirevents' ) . '\')}else{jQuery(this).text(\'' . __( 'More', 'geodirevents' ) . '\')}" class="badge ' . $badge_class . ' d-block mx-auto mt-2" type="button" data-toggle="collapse" data-target=".geodir-schedule.collapse" >' . __( 'More', 'geodirevents' ) . '</button>';
			}
		}

		return $html;
	}

	public static function event_type_condition( $event_type, $alias = NULL, $date = '', $min_date = '' ) {
		// Maybe abort early
		if ( false === $event_type ) {
			return '1=1 ';
		}

		if ( $alias === NULL ) {
			$alias = GEODIR_EVENT_SCHEDULES_TABLE;
		}

		if ( ! empty( $alias ) ) {
			$alias = $alias . '.';
		}

		$now = $date;
		if ( empty( $date ) ) {
			$date = date_i18n( 'Y-m-d' );
			$now = date_i18n( 'Y-m-d H:i:s' );
		}

		// Set end of the week
		$day 			 = date( 'l', strtotime( $date ));
		$sunday			 = date( 'Y-m-d', strtotime( 'this sunday'));
		if ( $day == 'sunday' ) {
			$sunday = $date;
		}

		// Prepare durations
		$tomorrow				= date( 'Y-m-d', strtotime( $date. ' + 1 days' ) );
		$next_7_days			= date( 'Y-m-d', strtotime( $tomorrow. ' + 6 days' ) );
		$next_30_days			= date( 'Y-m-d', strtotime( $tomorrow. ' + 29 days' ) );
		$last_day_month			= date( 'Y-m-t' );
		$first_day_next_week	= date( 'Y-m-d', strtotime( 'next week monday' ) );
		$last_day_next_week		= date( 'Y-m-d', strtotime( 'next week sunday' ) );
		$first_day_next_month	= date( 'Y-m-d', strtotime( 'first day of next month' ) );
		$last_day_next_month	= date( 'Y-m-d', strtotime( 'last day of next month' ) );

		// Get this weekend days
		if ( in_array( $day, explode( ' ', 'saturday sunday' ) ) ) {
			// Is this a weekend day
			$weekend_start = $date;
		} else {
			// This is a weekday
			$weekend_start = date( 'Y-m-d', strtotime( 'this saturday' ) );
		}

		$filters = array(
			'past'			=> "{$alias}start_date < '$date' ",
			'upcoming'		=> "CONCAT( {$alias}start_date, ' ', {$alias}start_time ) > '{$now}' ",
			'ongoing'		=> "( CONCAT( {$alias}start_date, ' ', {$alias}start_time ) <= '{$now}' ) AND ( CONCAT( {$alias}end_date, ' ', {$alias}end_time ) > '{$now}' OR CONCAT( {$alias}end_date, ' ', {$alias}end_time ) = '" . date_i18n( 'Y-m-d', strtotime( $now ) ) . " 00:00:00' ) ",
			'ongoing_upcoming' => "( CONCAT( {$alias}start_date, ' ', {$alias}start_time ) >= '{$now}' OR ( CONCAT( {$alias}end_date, ' ', {$alias}end_time ) > '{$now}' OR CONCAT( {$alias}end_date, ' ', {$alias}end_time ) = '" . date_i18n( 'Y-m-d', strtotime( $now ) ) . " 00:00:00' ) ) ",
			'today'			=> "( {$alias}start_date = '$date' OR ( {$alias}start_date <= '" . $date . "' AND {$alias}end_date >= '" . $date . "' ) ) ",
			'tomorrow'  	=> "( {$alias}start_date = '$tomorrow' OR ( {$alias}start_date <= '" . $tomorrow . "' AND {$alias}end_date >= '" . $tomorrow . "' ) ) ",
			'next_7_days'   => "( ( {$alias}start_date BETWEEN '" . $tomorrow . "' AND '" . $next_7_days . "' ) OR ( {$alias}end_date BETWEEN '" . $tomorrow . "' AND '" . $next_7_days . "' ) OR ( '" . $tomorrow . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $next_7_days . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'next_30_days'  => "( ( {$alias}start_date BETWEEN '" . $tomorrow . "' AND '" . $next_30_days . "' ) OR ( {$alias}end_date BETWEEN '" . $tomorrow . "' AND '" . $next_30_days . "' ) OR ( '" . $tomorrow . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $next_30_days . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'this_week'  	=> "( ( {$alias}start_date BETWEEN '" . $date . "' AND '" . $sunday . "' ) OR ( {$alias}end_date BETWEEN '" . $date . "' AND '" . $sunday . "' ) OR ( '" . $date . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $sunday . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'this_weekend'  => "( ( {$alias}start_date BETWEEN '" . $weekend_start . "' AND '" . $sunday . "' ) OR ( {$alias}end_date BETWEEN '" . $weekend_start . "' AND '" . $sunday . "' ) OR ( '" . $weekend_start . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $sunday . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'this_month'  	=> "( ( {$alias}start_date BETWEEN '" . $date . "' AND '" . $last_day_month . "' ) OR ( {$alias}end_date BETWEEN '" . $date . "' AND '" . $last_day_month . "' ) OR ( '" . $date . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $last_day_month . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'next_month'  	=> "( ( {$alias}start_date BETWEEN '" . $first_day_next_month . "' AND '" . $last_day_next_month . "' ) OR ( {$alias}end_date BETWEEN '" . $first_day_next_month . "' AND '" . $last_day_next_month . "' ) OR ( '" . $first_day_next_month . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $last_day_next_month . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ",
			'next_week'  	=> "( ( {$alias}start_date BETWEEN '" . $first_day_next_week . "' AND '" . $last_day_next_week . "' ) OR ( {$alias}end_date BETWEEN '" . $first_day_next_week . "' AND '" . $last_day_next_week . "' ) OR ( '" . $first_day_next_week . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $last_day_next_week . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) "
		);

		// Include ongoing events in upcoming events.
		if ( geodir_get_option( 'event_include_ongoing' ) ) {
			$filters['upcoming'] = $filters['ongoing_upcoming'];
		}

		/**
		 * Filter event type query conditions.
		 *
		 * @since 2.1.1.7
		 *
		 * @param array  $filters Event type query conditions.
		 * @param string $event_type Event type.
		 * @param string $alias Event schedule table alias.
		 * @param string $date Filter date.
		 */
		$filters = apply_filters( 'geodir_event_type_query_filters', $filters, $event_type, $alias, $date );

		// If the filter is provided, filter the events
		if ( ! empty( $filters[ $event_type ] ) ) {
			$filter = $filters[ $event_type ];
		} else {
			// Default filter to just return all events.
			$filter = '1=1 ';

			// Handle the special between filter where dates are separated by |
			$dates = explode( '|', strtolower( $event_type ) );

			// If there are two dates provided...
			if ( 2 === count( $dates ) ) {
				$date1  = date( 'Y-m-d', strtotime( $dates[0] ) );
				$date2  = date( 'Y-m-d', strtotime( $dates[1] ) );
				$filter = "( ( {$alias}start_date BETWEEN '" . $date1 . "' AND '" . $date2 . "' ) OR ( {$alias}end_date BETWEEN '" . $date1 . "' AND '" . $date2 . "' ) OR ( '" . $date1 . "' BETWEEN {$alias}start_date AND {$alias}end_date ) OR ( '" . $date2 . "' BETWEEN {$alias}start_date AND {$alias}end_date ) ) ";

				// Set min start date.
				if ( ! empty( $min_date ) ) {
					$filter = "{$alias}start_date >= '{$min_date}' AND " . $filter;
				}
			}
		}

		/**
		 * Filter the event type query condition.
		 *
		 * @since 2.1.1.7
		 *
		 * @param string $filter Event type query condition.
		 * @param string $event_type Event type.
		 * @param string $alias Event schedule table alias.
		 * @param string $date Filter date.
		 */
		return apply_filters( 'geodir_event_type_query_filter', $filter, $event_type, $alias, $date );
	}

	public static function has_schedule( $post_id, $date ) {
		$schedule = self::get_upcoming_schedule( $post_id, $date );
		return ! empty( $schedule ) ? true : false;
	}

	public static function location_term_counts( $sql, $term_id, $taxonomy, $post_type, $location_type, $loc, $count_type, $where ) {
		global $wpdb, $geodir_event_query_vars;

		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$table = geodir_db_cpt_table( $post_type );
			$event_type = geodir_get_option( 'event_default_filter', 'upcoming' );
			$single_event = false;

			if ( ! empty( $geodir_event_query_vars ) ) {
				if ( isset( $geodir_event_query_vars['event_type'] ) ) {
					$event_type = $geodir_event_query_vars['event_type'];
				}

				if ( isset( $geodir_event_query_vars['single_event'] ) ) {
					$single_event = (bool) $geodir_event_query_vars['single_event'];
				}
			}

			$join = "LEFT JOIN " . GEODIR_EVENT_SCHEDULES_TABLE . " ON " . GEODIR_EVENT_SCHEDULES_TABLE . ".event_id = post_id";
			$condition = self::event_type_condition( $event_type );

			if ( $count_type == 'review_count' ) {
				$sql = "SELECT COALESCE(SUM(rating_count),0) FROM {$table} {$join} WHERE post_status = 'publish' $where";
			} else {
				$sql = "SELECT COUNT( " . ( $single_event ? "DISTINCT " : "" ) . "post_id ) FROM {$table} {$join} WHERE post_status = 'publish' $where";
			}

			if ( geodir_taxonomy_type( $taxonomy ) == 'tag' ) {
				$term = get_term( (int) $term_id, $taxonomy );

				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$sql .= $wpdb->prepare( "AND FIND_IN_SET( %s, post_tags )", array( $term->name ) );
				} else {
					$sql .= $wpdb->prepare( "AND FIND_IN_SET( %d, post_tags )", array( $term_id ) );
				}
			} else {
				$sql .= $wpdb->prepare( "AND FIND_IN_SET( %d, post_category )", array( $term_id ) );
			}

			$sql .= " AND {$condition}";
		}

		return $sql;
	}

	/**
	 * Limit event created schedules.
	 *
	 * @since 2.1.1.5
	 *
	 * @param  int $post_id Post ID. Default 0.
	 * @return int Allowed max no. of event schedules.
	 */
	public static function max_schedules( $post_id = 0 ) {
		global $wpdb;

		$max_schedules = absint( geodir_get_option( 'event_max_schedules' ) );

		/**
		 * Filter event created schedules limit.
		 *
		 * @since 2.1.1.5
		 *
		 * @param int $max_schedules Allowed max no. of event schedules.
		 * @param int $post_id Post ID.
		 */
		return apply_filters( 'geodir_event_allow_max_schedules', $max_schedules, $post_id );
	}

	/**
	 * Handle past events.
	 *
	 * @since 2.1.1.6
	 *
	 * @param string $post_type The post type to process expired events.
	 * @return int No. of past events processed.
	 */
	public static function handle_past_events( $post_type ) {
		global $wpdb;

		$processed = 0;

		if ( empty( $post_type ) ) {
			return $processed;
		}

		$post_type_obj = geodir_post_type_object( $post_type );

		if ( ! ( ! empty( $post_type_obj ) && ! empty( $post_type_obj->past_event ) ) ) {
			return $processed;
		}

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return $processed;
		}

		/**
		 * Fires action before past events processed.
		 *
		 * @since 2.1.1.6
		 *
		 * @param string $post_type The post type.
		 */
		do_action( 'geodir_event_handle_past_events_before', $post_type );

		$days = absint( $post_type_obj->past_event_days );
		$status = ! empty( $post_type_obj->past_event_status ) ? $post_type_obj->past_event_status : 'pending';
		$cut_off_date = date_i18n( 'Y-m-d', strtotime( date_i18n( 'Y-m-d' ) ) - ( DAY_IN_SECONDS * $days ) );

		$sql = $wpdb->prepare( "SELECT p.ID FROM `" . GEODIR_EVENT_SCHEDULES_TABLE . "` AS s LEFT JOIN `{$wpdb->posts}` AS p ON p.ID = s.event_id WHERE p.post_type = %s AND p.post_status != %s GROUP BY s.event_id HAVING MAX( s.end_date ) < %s", $post_type, $status, $cut_off_date );
		$results = $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				if ( self::handle_past_event( $row->ID, $status ) ) {
					$processed++;
				}
			}
		}

		/**
		 * Fires action after past events processed.
		 *
		 * @since 2.1.1.6
		 *
		 * @param string $post_type The post type.
		 */
		do_action( 'geodir_event_handle_past_events_after', $post_type );

		return $processed;
	}

	/**
	 * Handle past event post.
	 *
	 * @since 2.1.1.6
	 *
	 * @param int    $post_ID The post ID.
	 * @param string $status Apply status to event post on expire.
	 * @return bool True on success else False.
	 */
	public static function handle_past_event( $post_ID, $status ) {
		/**
		 * Check to process past event or not.
		 *
		 * @since 2.1.1.6
		 *
		 * @param bool   $result True on success else False.
		 * @param int    $post_ID The post ID.
		 * @param string $status Apply status to event post on expire.
		 */
		$skip = apply_filters( 'geodir_event_handle_past_event_skip', false, $post_ID, $status );

		if ( $skip ) {
			return false;
		}

		/**
		 * Fires action before past event processed.
		 *
		 * @since 2.1.1.6
		 *
		 * @param int    $post_ID The post ID.
		 * @param string $status Apply status to event post on expire.
		 * @param bool   $result True on success else False.
		 */
		do_action( 'geodir_event_handle_past_event_before', $post_ID, $status );

		if ( $status == 'trash' ) {
			// Trash post.
			$result = wp_trash_post( $post_ID );
		} else if ( $status == 'delete' ) {
			// Delete post.
			$result = wp_delete_post( $post_ID, true );
		} else {
			$post_data = array();
			$post_data['ID'] = $post_ID;
			$post_data['post_status'] = $status;

			/**
			 * Filter post data to update past event post.
			 *
			 * @since 2.1.1.6
			 *
			 * @param array  $post_data Post data.
			 * @param int    $post_ID The post ID.
			 * @param string $status Apply status to event post on expire.
			 */
			$post_data = apply_filters( 'geodir_event_handle_past_event_data', $post_data, $post_ID, $status );

			// Update post.
			$result = wp_update_post( $post_data );
		}

		/**
		 * Fires action after past event processed.
		 *
		 * @since 2.1.1.6
		 *
		 * @param int    $post_ID The post ID.
		 * @param string $status Apply status to event post on expire.
		 * @param bool   $result True on success else False.
		 */
		do_action( 'geodir_event_handle_past_event_after', $post_ID, $status, $result );

		return $result;
	}

	/**
	 * Get templates to display event schedules.
	 *
	 * @since 2.1.1.11
	 *
	 * @return array Template.
	 */
	public static function get_schedule_templates() {
		$template = array(
			'{start_date} @ {start_time} - {end_date} @ {end_time}',
			'{start_date} - {end_date} @ {start_time} - {end_time}',
			'{start_date} @ {start_time} {br} {end_date} @ {end_time}',
			'{start_date} @ {start_time}',
			'{start_date}, {start_time} - {end_date}, {end_time}',
			'{start_date} - {end_date}, {start_time} - {end_time}',
			'{start_date}, {start_time} {br} {end_date}, {end_time}',
			'{start_date}, {start_time}',
		);

		return apply_filters( 'geodir_event_get_schedule_templates', $template );
	}

	/**
	 * Filter SEOPress sitemap single url.
	 *
	 * @since 2.3.2
	 *
	 * @param array  $seopress_url Sitemap url set.
	 * @param object $post Post object.
	 * @return array Sitemap url set.
	 */
	public static function seopress_sitemaps_single_url( $seopress_url, $post ) {
		if ( ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) && geodir_get_option( 'seopress_recurring_schedules' ) && ! empty( $seopress_url['loc'] ) && GeoDir_Post_types::supports( $post->post_type, 'events' ) ) {
			if ( ! empty( $post->recurring ) && ! empty( $post->start_date ) ) {
				$seopress_url['loc'] = add_query_arg(
					'gde',
					$post->start_date,
					$seopress_url['loc']
				);
			}
		}

		return $seopress_url;
	}

	/*
	 * Append event recurring schedule date to url.
	 *
	 * @since 2.3.20
	 *
	 * @param mixed  $value Tag value.
	 * @param string $key Tag key.
	 * @param object $ele_tag Tag object.
	 * @return string Filtered url.
	 */
	public static function elementor_tag_url_render_value( $value, $key, $ele_tag ) {
		global $gd_post;

		if ( $key == 'post_url' && $value && ! empty( $gd_post->post_type ) && ! empty( $gd_post->recurring ) && ! empty( $gd_post->start_date ) && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
			$value = add_query_arg( 'gde', $gd_post->start_date, $value );
		}

		return $value;
	}
}