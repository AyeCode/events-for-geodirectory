<?php
/**
 * Event Calendar class
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
 * GeoDir_Event_Calendar class
 *
 * @class       GeoDir_Event_Calendar
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 */
class GeoDir_Event_Calendar {

    public function __construct() {
	}

	public static function display_calendar( $args = '', $instance = '' ) {
		global $post, $gd_session;

		$id_base 				= !empty($args['widget_id']) ? $args['widget_id'] : 'geodir_event_listing_calendar';
		$title 					= apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $id_base);
		$day 					= apply_filters('widget_day', empty($instance['day']) ? '' : $instance['day'], $instance, $id_base);
		$week_day_format 		= apply_filters('widget_week_day_format', empty($instance['week_day_format']) ? 0 : $instance['week_day_format'], $instance, $id_base);
		$add_location_filter 	= apply_filters('geodir_event_calendar_widget_add_location_filter', empty($instance['add_location_filter']) ? 0 : 1, $instance, $id_base);
		$identifier 			= sanitize_html_class($id_base);
		$function_name 			= 'geodir_event_call_calendar_' . rand(100, 999);
		
		// Set location for detail page
		$location_id = 0;
		$location_title = '';
		$backup = array();
		$location_params = '';
		if ($add_location_filter) {
			if (isset($_REQUEST['snear'])) {
				$location_params .= '&snear=' . sanitize_text_field(stripslashes($_REQUEST['snear']));
			}
			if (!empty($_REQUEST['sgeo_lat']) && !empty($_REQUEST['sgeo_lon'])) {
				$location_params .= '&my_lat=' . sanitize_text_field($_REQUEST['sgeo_lat']);
				$location_params .= '&my_lon=' . sanitize_text_field($_REQUEST['sgeo_lon']);
			}
			if (geodir_is_page('detail') && !empty($post) && !empty($post->location_id)) {
				$location_id = $post->location_id;
			}
			$location_id = apply_filters('geodir_event_calendar_widget_location_id', $location_id, $instance, $id_base);
			if ($location_id && function_exists('geodir_get_location_by_id') && $location = geodir_get_location_by_id('', $location_id)) {
				$backup['all_near_me'] = $gd_session->get('all_near_me');
				$backup['gd_multi_location'] = $gd_session->get('gd_multi_location');
				$backup['gd_country'] = $gd_session->get('gd_country');
				$backup['gd_region'] = $gd_session->get('gd_region');
				$backup['gd_city'] = $gd_session->get('gd_city');
				
				$gd_session->set('all_near_me', false);
				$gd_session->set('gd_multi_location', 1);
				$gd_session->set('gd_country', $location->country_slug);
				$gd_session->set('gd_region', $location->region_slug);
				$gd_session->set('gd_city', $location->city_slug);
			}
			if (function_exists('geodir_current_loc_shortcode')) {
				$location_title = geodir_current_loc_shortcode();
			}
		}
		if ($title && strpos($title, '%%location_name%%') !== false) {
			$title = str_replace('%%location_name%%', $location_title, $title);
		}
		?>
		<div class="geodir_event_cal_widget" id="gdwgt_<?php echo $identifier; ?>">
			<?php /* if (trim($title) != '') { ?>
			<div class="geodir_event_cal_widget_title clearfix"><?php echo $args['before_title'] . __($title, 'geodirevents') . $args['after_title'];?></div>
			<?php } */ ?>
			<table style="width:100%" class="gd_cal_nav">
				<tr align="center" class="title">
					<td style="width:10%" class="title"><span class="geodir_cal_prev" title="<?php esc_attr_e('prev', 'geodirevents');?>"><i class="fa fa-chevron-left"></i></span></td>
					<td style="vertical-align:top;text-align:center" class="title"></td>
					<td style="width:10%" class="title"><span class="geodir_cal_next" title="<?php esc_attr_e('next', 'geodirevents');?>"><i class="fa fa-chevron-right"></i></span></td>
				</tr>
			</table>
			<div class="geodir_event_calendar geodir-calendar-loading"><div class="gd-div-loader"><i class="fa fa-refresh fa-spin"></i></div></div>
		</div>
	<script type="text/javascript">
	if (typeof <?php echo $function_name; ?> !== 'function') {
		window.<?php echo $function_name; ?> = function() {
			var $container = jQuery('#gdwgt_<?php echo $identifier;?>');
			var sday = '<?php echo $day;?>';
			var wday = '<?php echo (int)$week_day_format;?>';
			var gdem_loading = jQuery('.gd_cal_nav .gdem-loading', $container);
			var loc = '&_loc=<?php echo (int)$add_location_filter;?>&_l=<?php echo (int)$location_id;?><?php echo $location_params;?>';
			params = "&sday=" + sday + "&wday=" + wday + loc;
			geodir_event_get_calendar($container, params);
			
			var mnth = <?php echo date_i18n("n");?>;
			var year = <?php echo date_i18n("Y");?>;
			
			jQuery(".geodir_cal_next", $container).on('click', function() {
				mnth++;
				if (mnth > 12) {
					year++;
					mnth = 1;
				}
				params = "&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc;
				geodir_event_get_calendar($container, params);
			});
			
			jQuery(".geodir_cal_prev", $container).on('click', function() {
				mnth--;
				if (mnth < 1) {
					year--;
					mnth = 12;
				}
				params = "&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc;
				geodir_event_get_calendar($container, params);
			});
		};
	}

	jQuery(function() {
		if (typeof <?php echo $function_name; ?> == 'function') {
			<?php echo $function_name; ?>();
		}
	});
	</script>
		<?php
		if (!empty($backup)) {
			foreach ($backup as $key => $value) {
				if ($value !== false) {
					$gd_session->set($key, $value);
				} else {
					$gd_session->un_set($key);
				}
			}
		}
	}

	public static function ajax_calendar() {
		global $gd_session;
		$day = $_REQUEST["sday"];
		if ($day == 'monday') {
			$day = '1';
		}
		$monthNames = Array(__("January"), __("February"), __("March"), __("April"), __("May"), __("June"), __("July"), __("August"), __("September"), __("October"), __("November"), __("December"));

		if (!isset($_REQUEST["mnth"])) $_REQUEST["mnth"] = date_i18n("n");
		if (!isset($_REQUEST["yr"])) $_REQUEST["yr"] = date_i18n("Y");

		$month = (int)$_REQUEST["mnth"];
		$year = (int)$_REQUEST["yr"];
		$add_location_filter = !empty($_REQUEST['_loc']) && defined('POST_LOCATION_TABLE') ? true : false;
		$location_id = $add_location_filter && !empty($_REQUEST['_l']) ? (int)$_REQUEST['_l'] : 0;
		
		$location_params = '&snear=' . (isset($_REQUEST['snear']) ? sanitize_text_field(stripslashes($_REQUEST['snear'])) : '');
		if ($add_location_filter) {
			if (!empty($_REQUEST['my_lat'])&& !empty($_REQUEST['my_lon'])) {
				$location_params .= '&sgeo_lat=' . sanitize_text_field($_REQUEST['my_lat']);
				$location_params .= '&sgeo_lon=' . sanitize_text_field($_REQUEST['my_lon']);
			}
			
			if (defined('POST_LOCATION_TABLE')) {
				$gd_ses_country = $gd_session->get('gd_country');
				$gd_ses_region = $gd_session->get('gd_region');
				$gd_ses_city = $gd_session->get('gd_city');
				$gd_ses_neighbourhood = $gd_session->get('gd_neighbourhood');
				
				if ($gd_ses_neighbourhood && geodir_get_option('location_neighbourhoods') && $neighbourhood = geodir_location_get_neighbourhood_by_id($gd_ses_neighbourhood, true)) {
					$location_params .= '&set_location_type=4&set_location_val=' . (int)$neighbourhood->location_id . '&gd_hood_s=' . (int)$neighbourhood->hood_id;
				} else if($gd_ses_country || $gd_ses_region || $gd_ses_city) {
					if ($gd_ses_city) {
						$location_type = 3;
						$type = 'city';
					} else if ($gd_ses_region) {
						$location_type = 2;
						$type = 'region';
					} else if ($gd_ses_country) {
						$location_type = 1;
						$type = 'country';
					}
					
					$location_info = geodir_get_location_by_slug($type, array('city_slug' => $gd_ses_city, 'country_slug' => $gd_ses_country, 'region_slug' => $gd_ses_region));
					if (!empty($location_info)) {
						$location_params .= '&set_location_type=' . $location_type . '&set_location_val=' . (int)$location_info->location_id;
					}
				}
			}
		}
		
		$backup = array();
		if ($location_id && function_exists('geodir_get_location_by_id') && $location = geodir_get_location_by_id('', $location_id)) {
			$backup['all_near_me'] = $gd_session->get('all_near_me');
			$backup['gd_multi_location'] = $gd_session->get('gd_multi_location');
			$backup['gd_country'] = $gd_session->get('gd_country');
			$backup['gd_region'] = $gd_session->get('gd_region');
			$backup['gd_city'] = $gd_session->get('gd_city');
			
			$gd_session->set('all_near_me', false);
			$gd_session->set('gd_multi_location', 1);
			$gd_session->set('gd_country', $location->country_slug);
			$gd_session->set('gd_region', $location->region_slug);
			$gd_session->set('gd_city', $location->city_slug);
		}

		$query_args = array(
			'gd_event_calendar' => strtotime( $year . '-' . $month ),
			'is_geodir_loop' => true,
			'gd_location' => $add_location_filter,
			'post_type' => 'gd_event',
			'posts_per_page' => -1
		);
						
		$all_events = query_posts( $query_args );
		global $wp_query;

		$all_event_dates = array();
		if ( !empty( $all_events ) ) {
			$filter_event_dates = array();
			
			foreach ( $all_events as $event ) {
				if ( $event->schedules != '' ) {
					$event_date_rows = explode( ',', $event->schedules );
					$filter_event_dates = array_merge( $filter_event_dates, $event_date_rows );
				}
			}

			if ( !empty( $filter_event_dates ) ) {
				$filter_event_dates = array_unique($filter_event_dates);
				
				foreach ( $filter_event_dates as $schedule ) {
					if ( $schedule != '' ) {
						$all_event_dates = self::parse_calendar_schedules( $schedule, $all_event_dates );
					}
				}
			}
		}

		$week_day_format = isset( $_REQUEST['wday'] ) ? (int)$_REQUEST['wday'] : 0;

		switch ( $week_day_format ) {
			case 1:
				$day_mon = __( 'Mo', 'geodirevents' );
				$day_tue = __( 'Tu', 'geodirevents' );
				$day_wed = __( 'We', 'geodirevents' );
				$day_thu = __( 'Th', 'geodirevents' );
				$day_fri = __( 'Fr', 'geodirevents' );
				$day_sat = __( 'Sa', 'geodirevents' );
				$day_sun = __( 'Su', 'geodirevents' );
			break;
			case 2:
				$day_mon = __( 'Mon' );
				$day_tue = __( 'Tue' );
				$day_wed = __( 'Wed' );
				$day_thu = __( 'Thu' );
				$day_fri = __( 'Fri' );
				$day_sat = __( 'Sat' );
				$day_sun = __( 'Sun' );
			break;
			case 3:
				$day_mon = __( 'Monday' );
				$day_tue = __( 'Tuesday' );
				$day_wed = __( 'Wednesday' );
				$day_thu = __( 'Thursday' );
				$day_fri = __( 'Friday' );
				$day_sat = __( 'Saturday' );
				$day_sun = __( 'Sunday' );
			break;
			case 0:
			default:
				$day_mon = __( 'M', 'Monday initial' );
				$day_tue = __( 'T', 'Tuesday initial' );
				$day_wed = __( 'W', 'Wednesday initial' );
				$day_thu = __( 'T', 'Thursday initial' );
				$day_fri = __( 'F', 'Friday initial' );
				$day_sat = __( 'S', 'Saturday initial' );
				$day_sun = __( 'S', 'Sunday initial' );
			break;
		}

		wp_reset_query();
	?><div class="gd-div-loader"><i class="fa fa-refresh fa-spin"></i></div><span id="cal_title"><strong><?php echo $monthNames[$month-1].' '.$year; ?></strong></span><table width="100%" border="0" cellpadding="2" cellspacing="2" class="calendar_widget"><tr><?php if ( $day != '1' ) { ?><td align="center" class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_sunday', $day_sun );?></strong></td><?php } ?><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_monday', $day_mon );?></strong></td><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_tuesday', $day_tue );?></strong></td><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_wednesday', $day_wed );?></strong></td><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_thursday', $day_thu );?></strong></td><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_friday', $day_fri );?></strong></td><td class="days"><strong><?php echo apply_filters( 'geodir_event_cal_single_day_saturday', $day_sat );?></strong></td><?php if ( $day == '1' ) { ?><td class="days"><strong><?php echo apply_filters('geodir_event_cal_single_day_sunday', $day_sun );?></strong></td><?php } ?></tr>
		<?php
		$timestamp = mktime( 0, 0, 0, $month, 1, $year );
		$maxday = date_i18n( "t", $timestamp );
		$thismonth = getdate( $timestamp );
		$startday = $thismonth['wday'];
		if ( $day == '1' ) {
			if ( $startday == 0 ) {
				$startday = $startday + 6;
			} else {
				$startday = $startday - 1;
			}
		}

		if ( isset( $_GET['m'] ) ) {
			$m = $_GET['m'];
		}

		$search_url = geodir_search_page_base_url();
		if ( geodir_is_wpml() && wp_doing_ajax() ) {
			global $sitepress;
			$search_url = $sitepress->convert_url( $search_url );
		}
		$search_args = array(
			'geodir_search' => 1,
			'etype' => 'all',
			'stype' => 'gd_event',
			's' => '',
		);
		$search_url = add_query_arg( $search_args, $search_url ) . $location_params;

		for ( $i = 0; $i < ( $maxday + $startday ); $i++ ) {
			if ( ($i % 7 ) == 0 ) echo "<tr class='cal_day_nums'>\n";
			if ( $i < $startday ) {
				echo '<td class="gd_cal_sat">&nbsp;</td>';
			} else {
				$day = $i - $startday + 1;
				if ( strlen( $day ) == 1 ) {
					$day = "0" . $day;
				}
				if ( strlen( $month ) == 1 ) {
					$month = "0" . $month;
				}
				$date = date_i18n( 'Y-m-d', strtotime( $year . '-' . $month . '-' . $day ) );
				$date_search_url = add_query_arg( array( 'event_calendar' => "$year$month$day" ), $search_url );

				echo '<td valign="middle" class="gd_cal_nsat">';
				if ( in_array( $date, $all_event_dates ) ) {
					echo '<a class="event_highlight" href=" ' . $date_search_url . '" title="' . esc_attr__( 'Click to view events on this date', 'geodirevents' ) . '" > ' . (int)$day . '</a>';
				} else {
					echo '<span class="no_event">' . (int)$day . '</span>';
				}
				echo "</td>";
			}
			if ( ( $i % 7 ) == 6 ) {
				echo "</tr>\n";
			}
		}
		?>
	</table><?php
		if (!empty($backup)) {
			foreach ($backup as $key => $value) {
				if ($value !== false) {
					$gd_session->set($key, $value);
				} else {
					$gd_session->un_set($key);
				}
			}
		}
	}

	public static function parse_calendar_schedules( $event_dates, $all_event_dates = array() ) {
		if ( $event_dates != '' ) {
			$start_bound = substr($event_dates, 0, 6);
			$end_bound = substr($event_dates, 6, 6);

			$event_start = $start_bound != '' ? substr(date('Y'), 0, 2) . substr($start_bound, 4, 2) . '-' . substr($start_bound, 2, 2) . '-' . substr($start_bound, 0, 2) : '';
			$event_end = $end_bound != '' ? substr(date('Y'), 0, 2) . substr($end_bound, 4, 2) . '-' . substr($end_bound, 2, 2) . '-' . substr($end_bound, 0, 2) : '';
			
			$event_start_time = strtotime( $event_start );
			$event_end_time = strtotime( $event_end );
			
			if ( $event_start != '' ) {
				$schedule_date = date_i18n( 'Y-m-d', $event_start_time );
				
				if ( !in_array( $schedule_date, $all_event_dates ) ) {
					$all_event_dates[] = $schedule_date;
				}
			}
			
			if ( $event_end != '' && $event_end_time > $event_start_time ) {
				$event_start_time = $event_start_time + DAY_IN_SECONDS;
				
				while ( $event_start_time <= $event_end_time ) {
					$schedule_date = date_i18n( 'Y-m-d', $event_start_time );
					
					if ( !in_array( $schedule_date, $all_event_dates ) ) {
						$all_event_dates[] = $schedule_date;
					}
					
					$event_start_time = $event_start_time + DAY_IN_SECONDS;
				}
			}
		}

		$all_event_dates = !empty( $all_event_dates ) ? array_unique( $all_event_dates ) : $all_event_dates;

		return $all_event_dates;
	}
}

return new GeoDir_Event_Calendar();