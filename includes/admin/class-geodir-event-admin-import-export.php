<?php
/**
 * Handle import and exports.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory_Event_manager/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * GeoDir_Event_Admin_Import_Export Class.
 */
class GeoDir_Event_Admin_Import_Export {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'geodir_export_posts', array( __CLASS__, 'export_events' ), 10, 2 );
		add_filter( 'geodir_import_validate_post', array( __CLASS__, 'import_validate_post' ), 10, 2 );
	}

	public static function import_validate_post( $post_info, $row ) {
		if ( ! empty( $post_info ) && is_array( $post_info ) && GeoDir_Post_types::supports( $post_info['post_type'], 'events' ) ) {
			$default_start_date = date_i18n( 'Y-m-d' );
			$mapping = array(
				'start_date' 				=> 'start_date',
				'end_date' 					=> 'end_date',
				'start_time' 				=> 'start_time',
				'end_time' 					=> 'end_time',
				'is_all_day_event' 			=> 'all_day',
				'recurring_duration_days' 	=> 'duration_x',
				'recurring_type' 			=> 'repeat_type',
				'recurring_interval' 		=> 'repeat_x',
				'recurring_week_days' 		=> 'repeat_days',
				'recurring_week_numbers' 	=> 'repeat_weeks',
				'recurring_limit' 			=> 'max_repeat',
				'recurring_end_date' 		=> 'repeat_end',
				'recurring_custom_dates' 	=> 'recurring_dates',
				'recurring_different_times' => 'different_times',
				'recurring_start_times' 	=> 'start_times',
				'recurring_end_times' 		=> 'end_times',
			);

			$post_info['recurring'] 		= ! empty( $post_info['recurring'] ) ? 1 : 0;

			$event_data = array( 'recurring' => $post_info['recurring'] );
			foreach ( $post_info as $key => $value ) {
				if ( isset( $mapping [ $key ] ) ) {
					$event_data[ $mapping [ $key ] ] = $value;

					unset( $post_info[ $key ] );
				}
			}

			// Don't update event schedules when event date/time fields are included in import.
			$event_keys = array_keys( $event_data );
			if ( ! ( in_array( 'start_date', $event_keys ) && in_array( 'start_time', $event_keys ) && in_array( 'end_date', $event_keys ) && in_array( 'end_time', $event_keys ) ) ) {
				unset( $post_info['recurring'] );

				if ( isset( $post_info['event_dates'] ) ) {
					unset( $post_info['event_dates'] );
				}

				return $post_info;
			}

			$defaults = array(
				'recurring'			=> '',
				'start_date'		=> '',
				'end_date'			=> '',
				'all_day'			=> '',
				'start_time'		=> '',
				'end_time'			=> '',
				'duration_x'		=> '',
				'repeat_type'		=> '',
				'repeat_x'			=> '',
				'repeat_end_type'	=> '',
				'max_repeat'		=> '',
				'repeat_end'		=> '',
				'recurring_dates'	=> '',
				'different_times'	=> '',
				'start_times'		=> '',
				'end_times'			=> '',
				'repeat_days'		=> '',
				'repeat_weeks'		=> ''
			);
			$event_data = wp_parse_args( $event_data, $defaults );
			
			if ( ! empty( $event_data['all_day'] ) ) {
				$event_data['start_time'] 	= '';
				$event_data['end_time'] 	= '';
				$event_data['start_times'] 	= array();
				$event_data['end_times'] 	= array();
			}

			if ( ! empty( $event_data['start_date'] ) ) {
				$event_data['start_date'] 	= self::parse_import_date( $event_data['start_date'] );
			} else {
				$event_data['start_date'] 	= $default_start_date;
			}

			if ( ! empty( $event_data['start_time'] ) ) {
				$event_data['start_time'] 	= date_i18n( 'H:i', strtotime( $event_data['start_time'] ) );
			}
			if ( ! empty( $event_data['end_time'] ) ) {
				$event_data['end_time'] 	= date_i18n( 'H:i', strtotime( $event_data['end_time'] ) );
			}

			if ( ! empty( $event_data['recurring'] ) ) {
				$event_data['repeat_type'] 		= in_array( $event_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $event_data['repeat_type'] : 'custom';
				$event_data['duration_x'] 		= absint( $event_data['duration_x'] ) > 0 ? absint( $event_data['duration_x'] ) : 1;

				if ( $event_data['repeat_type'] == 'custom' ) {
					if ( ! empty( $event_data['start_times'] ) ) {
						$event_data['start_times'] 	= GeoDir_Event_Fields::parse_array( $event_data['start_times'] );
						$parse_times 	= array();
						if ( ! empty( $event_data['start_times'] ) ) {
							foreach ( $event_data['start_times'] as $key => $time ) {
								if ( ! empty( $time ) ) {
									$parse_times[] = date_i18n( 'H:i', strtotime( $time ) );
								}
							}
						}
						$event_data['start_times'] = $parse_times;
					}
					if ( ! empty( $event_data['end_times'] ) ) {
						$event_data['end_times'] 	= GeoDir_Event_Fields::parse_array( $event_data['end_times'] );
						$parse_times 	= array();
						if ( ! empty( $event_data['end_times'] ) ) {
							foreach ( $event_data['end_times'] as $key => $time ) {
								if ( ! empty( $time ) ) {
									$parse_times[] = date_i18n( 'H:i', strtotime( $time ) );
								}
							}
						}
						$event_data['end_times'] = $parse_times;
					}
					$event_data['different_times'] 	= !empty( $event_data['start_times'] ) ? true : false;
					
					$parse_dates 		= GeoDir_Event_Fields::parse_array( $event_data['recurring_dates'] );
					$recurring_dates 	= array();
					if ( ! empty( $parse_dates ) ) {
						foreach ( $parse_dates as $key => $date ) {
							if ( ! empty( $date ) ) {
								$recurring_dates[] = self::parse_import_date( $date );
							}
						}
					}
					if ( empty( $recurring_dates ) ) {
						$recurring_dates = array( $event_data['start_date'] );
					}
					$repeat_days 					= array();
					$repeat_weeks 					= array();
					$event_data['start_date'] 		= '';
					$event_data['repeat_x'] 		= 1;
					$event_data['repeat_end_type'] 	= 0;
					$event_data['max_repeat'] 		= 1;
					$event_data['repeat_end'] 		= '';
				} else {
					// week days
					$repeat_days = array();
					if ( ( $event_data['repeat_type'] == 'week' || $event_data['repeat_type'] == 'month' ) && ! empty( $event_data['repeat_days'] ) ) {
						$event_data['repeat_days'] = GeoDir_Event_Fields::parse_array( $event_data['repeat_days'] );
						$week_day_nos = self::week_days( true );

						$repeat_days = array();
						foreach( $event_data['repeat_days'] as $key => $vaue ) {
							$vaue = ! empty( $vaue ) ? strtolower( $vaue ) : '';
							if ( $vaue && isset( $week_day_nos[ $vaue ] ) ) {
								$repeat_days[] = $week_day_nos[ $vaue ];
							}
						}
					}

					// by week
					$repeat_weeks = array();
					if ( $event_data['repeat_type'] == 'month' && ! empty( $event_data['repeat_weeks'] ) ) {
						$repeat_weeks = GeoDir_Event_Fields::parse_array( $event_data['repeat_weeks'] );
					}
					$event_data['repeat_x'] 			= absint( $event_data['repeat_x'] ) > 0 ? absint( $event_data['repeat_x'] ) : 1;
					if ( ! empty( $event_data['repeat_end'] ) ) {
						$event_data['repeat_end'] 		= self::parse_import_date( $event_data['repeat_end'] );
					}
					if ( $event_data['repeat_end'] ) {
						$event_data['repeat_end_type'] 	= 1;
						$event_data['max_repeat'] 		= 0;
					} else {
						$event_data['repeat_end_type'] 	= 0;
						$event_data['max_repeat'] 		= absint( $event_data['max_repeat'] ) > 0 ? absint( $event_data['max_repeat'] ) : 1;
					}
					$recurring_dates 	= array();
				}
				$event_data['end_date'] 		= '';
				$event_data['repeat_days'] 		= $repeat_days;
				$event_data['repeat_weeks'] 	= $repeat_weeks;
				$event_data['recurring_dates'] 	= $recurring_dates;
			} else {
				if ( ! empty( $event_data['end_date'] ) ) {
					$event_data['end_date'] 	= self::parse_import_date( $event_data['end_date'] );
				} else {
					$event_data['end_date'] 	= $event_data['start_date'];
				}
				if ( strtotime( $event_data['end_date'] ) < strtotime( $event_data['start_date'] ) ) {
					$event_data['end_date'] = $event_data['start_date'];
				}

				$event_data['duration_x']		= 1;
				$event_data['repeat_type']		= '';
				$event_data['repeat_x']			= '';
				$event_data['repeat_end_type']	= '';
				$event_data['max_repeat']		= '';
				$event_data['repeat_end']		= '';
				$event_data['recurring_dates']	= '';
				$event_data['different_times']	= false;
				$event_data['start_times']		= '';
				$event_data['end_times']		= '';
			}
			$post_info['event_dates'] = $event_data;
		}
		return $post_info;
	}

	public static function export_events( $results, $post_type ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) && ! empty( $results ) ) {
			$is_recurring_active	= geodir_event_is_recurring_active();
			$week_day_nos 			= self::week_days();

			foreach ( $results as $key => $row ) {
				$event_data = ! empty( $row['event_dates'] ) ? maybe_unserialize( $row['event_dates'] ) : array();

				// v1 event data
				if ( ! empty( $event_data ) && isset( $event_data['event_recurring_dates'] ) && isset( $event_data['event_start'] ) && ! isset( $event_data['recurring_dates'] ) ) {
					$event_data['start_date'] = $event_data['event_start'];
					$event_data['end_date'] = $event_data['event_end'];
					$event_data['recurring_dates'] = $event_data['event_recurring_dates'];
					$event_data['start_time'] = $event_data['starttime'];
					$event_data['end_time'] = $event_data['endtime'];
					$event_data['start_times'] = $event_data['starttimes'];
					$event_data['end_times'] = $event_data['endtimes'];
				}

				$data = array();
				$data['start_date'] 			= ! empty( $event_data['start_date'] ) ? $event_data['start_date'] : '';
				$data['end_date'] 				= ! empty( $event_data['end_date'] ) ? $event_data['end_date'] : '';
				$data['start_time'] 			= ! empty( $event_data['start_time'] ) ? $event_data['start_time'] : '';
				$data['end_time'] 				= ! empty( $event_data['end_time'] ) ? $event_data['end_time'] : '';
				$data['is_all_day_event'] 		= ! empty( $event_data['all_day'] ) ? '1' : '';
				if ( $is_recurring_active ) { // Recurring disabled
					$recurring = ! empty( $row['recurring'] ) ? true : false;

					if ( $recurring ) {
						$week_days = '';
						if ( ! empty( $event_data['repeat_days'] ) ) {
							$repeat_days = is_array( $event_data['repeat_days'] ) ? $event_data['repeat_days'] : explode( ',', $event_data['repeat_days'] );
							$week_days = array();
							if ( ! empty( $repeat_days ) ) {
								foreach ( $repeat_days as $day_no ) {
									if ( isset( $week_day_nos[ $day_no ] ) ) {
										$week_days[] = $week_day_nos[ $day_no ];
									}
								}
							}
							$week_days = ! empty( $week_days ) ? implode( ',', array_unique( $week_days ) ) : '';
						}
						if ( ! empty( $event_data['repeat_end_type'] ) ) {
							$recurring_limit 	= '';
							$recurring_end_date = ! empty( $event_data['repeat_end'] ) ? $event_data['repeat_end'] : '';
						} else {
							$recurring_limit 	= ! empty( $event_data['max_repeat'] ) ? absint( $event_data['max_repeat'] ) : 1;
							$recurring_end_date = '';
						}

						if ( ! empty( $event_data['repeat_type'] ) && $event_data['repeat_type'] == 'custom' ) {
							$data['start_date']				= '';
						}
						$data['end_date']					= '';
						$data['recurring_duration_days'] 	= ! empty( $event_data['duration_x'] ) ? absint( $event_data['duration_x'] ) : '';
						$data['recurring_type'] 			= ! empty( $event_data['repeat_type'] ) ? $event_data['repeat_type'] : 'custom';
						$data['recurring_interval'] 		= ! empty( $event_data['repeat_x'] ) ? absint( $event_data['repeat_x'] ) : '';
						$data['recurring_week_days'] 		= $week_days;
						$data['recurring_week_numbers'] 	= is_array( $event_data['repeat_weeks'] ) ? implode( ',', $event_data['repeat_weeks'] ) : $event_data['repeat_weeks'];
						$data['recurring_limit'] 			= $recurring_limit;
						$data['recurring_end_date'] 		= $recurring_end_date;
						$data['recurring_custom_dates'] 	= is_array( $event_data['recurring_dates'] ) ? implode( ',', $event_data['recurring_dates'] ) : $event_data['recurring_dates'];
						$data['recurring_different_times']	= ! empty( $event_data['different_times'] ) ? '1' : '';
						$data['recurring_start_times'] 		= is_array( $event_data['start_times'] ) ? implode( ',', $event_data['start_times'] ) : $event_data['start_times'];
						$data['recurring_end_times'] 		= is_array( $event_data['end_times'] ) ? implode( ',', $event_data['end_times'] ) : $event_data['end_times'];
					} else {
						$data['recurring_duration_days'] 	= '';
						$data['recurring_type'] 			= '';
						$data['recurring_interval'] 		= '';
						$data['recurring_week_days'] 		= '';
						$data['recurring_week_numbers'] 	= '';
						$data['recurring_limit'] 			= '';
						$data['recurring_end_date'] 		= '';
						$data['recurring_custom_dates'] 	= '';
						$data['recurring_different_times'] 	= '';
						$data['recurring_start_times'] 		= '';
						$data['recurring_end_times'] 		= '';
					}
				} else {
					unset( $row['recurring'] );
				}
				$row = geodir_event_array_insert( $row, array_search( 'event_dates', array_keys( $row ) ), $data );

				unset( $row['event_dates'] );

				$results[ $key ] = $row;
			}
		}
		return $results;
	}

	public static function week_days( $flip = false ) {
		$week_days = array( 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' );

		if ( $flip ) {
			$week_days = array_flip( $week_days );
		}

		return $week_days;
	}

	public static function parse_import_date( $date, $format = 'm/d/Y' ) {
		if ( strpos( $date, '/' ) === false ) {
			return date_i18n( 'Y-m-d', strtotime( $date ) );
		}

		return geodir_event_date_to_ymd( $date, $format );
	}
}

new GeoDir_Event_Admin_Import_Export();