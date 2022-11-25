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

	public static function display_calendar( $args = array(), $instance = array() ) {
		global $geodirectory, $post;

		$id_base 				= !empty($args['widget_id']) ? $args['widget_id'] : 'geodir_event_listing_calendar';
		$design_style           = geodir_design_style();
		$post_type 				= apply_filters('geodir_event_calendar_widget_post_type_filter', empty( $instance['post_type'] ) ? 'gd_event' : $instance['post_type'], $instance, $id_base);
		if ( isset( $instance['week_start_day'] ) ) {
			$week_start_day = absint( $instance['week_start_day'] );
		} else {
			$week_start_day = absint( get_option( 'start_of_week' ) );
		}
		$week_start_day 		= apply_filters('widget_day', $week_start_day, $instance, $id_base);
		$week_day_format 		= apply_filters('widget_week_day_format', empty($instance['week_day_format']) ? 0 : $instance['week_day_format'], $instance, $id_base);
		$add_location_filter 	= apply_filters('geodir_event_calendar_widget_add_location_filter', empty($instance['add_location_filter']) ? 0 : 1, $instance, $id_base);
		$identifier 			= str_replace("-","_", sanitize_html_class($id_base) . '_' . uniqid() );
		$function_name 			= 'geodir_event_call_calendar_' . uniqid();
		if ( ! ( ! empty( $post_type ) && in_array( $post_type, GeoDir_Event_Post_Type::get_event_post_types() ) ) ) {
			$post_type = 'gd_event';
		}
		
		// Set location for detail page
		$location_id = 0;
		$location_params = '';

		// @todo: move this to location manager during new calendar features.
		if ( $add_location_filter && defined( 'GEODIRLOCATION_VERSION' ) && ! empty( $geodirectory->location ) ) {
			$location = $geodirectory->location;

			if ( ! empty( $location->country_slug ) ) {
				$location_params .= '&country=' . $location->country_slug;
			}
			if ( ! empty( $location->region_slug ) ) {
				$location_params .= '&region=' . $location->region_slug;
			}
			if ( ! empty( $location->city_slug ) ) {
				$location_params .= '&city=' . $location->city_slug;
			}
			if ( ! empty( $location->neighbourhood_slug ) ) {
				$location_params .= '&neighbourhood=' . $location->neighbourhood_slug;
			}

			if ( $location->type == 'me' && ! empty( $location->latitude ) && ! empty( $location->longitude ) ) {
				$location_params .= '&my_lat=' . $location->latitude;
				$location_params .= '&my_lon=' . $location->longitude;
			} elseif ( empty( $location->type ) && ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
				if ( ! empty( $_REQUEST['snear'] ) ) {
					$location_params .= '&snear=' . sanitize_text_field( $_REQUEST['snear'] );
				}
				$location_params .= '&my_lat=' . sanitize_text_field( $_REQUEST['sgeo_lat'] );
				$location_params .= '&my_lon=' . sanitize_text_field( $_REQUEST['sgeo_lon'] );
			}
		}

		$post_types = geodir_get_posttypes( 'options-plural' );
		$post_type_options = array();
		foreach ( $post_types as $pt => $name ) {
			if ( in_array( $pt, GeoDir_Event_Post_Type::get_event_post_types() ) ) {
				$post_type_options[] = '<option ' . selected( $post_type, $pt, false ) . ' value="' . $pt . '">' . $name . '</option>';
			}
		}

		$tooltip_init = $design_style ? 'data-toggle="tooltip"' : '';
		if ( $design_style ) {
			$cal_size_class = !empty($instance['size']) && $instance['size']=='small' ? ' table-sm ' : '';
			$table_nav_class = 'table table-striped p-0 m-0 border';
			$loader = '<div class="clearfix text-center w-100"><div class="gd-div-loader spinner-border mx-auto m-3" role="status"><span class="sr-only visually-hidden">'.__("Loading...","geodirevents").'</span></div></div>';
		} else {
			$cal_size_class = '';
			$table_nav_class = '';
			$loader = '<div class="gd-div-loader"><i class="fas fa-sync fa-spin"></i></div>';
		}

		$instance['_week_start_day'] = $week_start_day;
		$instance['_week_day_format'] = $week_day_format;

		// Block preview
		$month_title = '';
		if ( ! empty( $instance['is_preview'] ) || ! empty( $instance['block_preview'] ) ) {
			$month_title = date_i18n( 'F Y' );
			$tooltip_init = '';
			ob_start();
			self::ajax_calendar( $args, $instance );
			$loader = ob_get_clean();
		}
		?>
		<div class="geodir_event_cal_widget table-responsive" id="gdwgt_<?php echo $identifier; ?>">
			<?php if ( count( $post_type_options ) > 1 ) { ?>
			<label for="geodir_calendar_post_type" style="margin-bottom:5px;display:block"><select id="geodir_calendar_post_type" class="<?php echo ( $design_style ? 'custom-select form-select w-100 mw-100' : 'geodir-select' ); ?>" style="width:100%;max-width:400px"><?php echo implode("", $post_type_options); ?></select></label>
			<?php } else { ?>
			<input type="hidden" value="<?php echo esc_attr( $post_type); ?>" id="geodir_calendar_post_type">
			<?php } ?>
			<table style="width:100%" class="gd_cal_nav <?php echo $table_nav_class . $cal_size_class;?>">
				<tr align="center" class="title">
					<td style="width:10%" class="title geodir_cal_prev <?php echo $design_style ? 'text-left text-start c-pointer py-2 px-3' : '';?>"><span class="" <?php echo $tooltip_init;?> title="<?php esc_attr_e('prev', 'geodirevents');?>"><i class="fas fa-chevron-left"></i></span></td>
					<td style="vertical-align:top;text-align:center" class="title gd-event-cal-title"><?php echo $month_title; ?></td>
					<td style="width:10%" class="title geodir_cal_next <?php echo $design_style ? 'text-right text-end c-pointer py-2 px-3' : '';?>"><span class="" <?php echo $tooltip_init;?> title="<?php esc_attr_e('next', 'geodirevents');?>"><i class="fas fa-chevron-right"></i></span></td>
				</tr>
			</table>
			<div class="geodir_event_calendar geodir-calendar-loading<?php echo ( $design_style ? ' position-relative' : '' ); ?>"><?php echo $loader;?></div>
		</div>
<?php if ( empty( $instance['is_preview'] ) && empty( $instance['block_preview'] ) ) { ?>
	<script type="text/javascript">
	if (typeof <?php echo $function_name; ?> !== 'function') {
		window.<?php echo $function_name; ?> = function() {
			var $container = jQuery('#gdwgt_<?php echo $identifier;?>');
			var sday = '<?php echo $week_start_day;?>';
			var wday = '<?php echo (int)$week_day_format;?>';
			var gdem_loading = jQuery('.gd_cal_nav .gdem-loading', $container);
			var loc = '&_loc=<?php echo (int)$add_location_filter;?>&_l=<?php echo (int)$location_id;?><?php echo $location_params;?>';
			var size = '&size=<?php echo !empty($instance['size']) && $instance['size']=='small' ? 'small' : '';?>';
			params = "&sday=" + sday + "&wday=" + wday + loc;
			params += '&post_type=' + jQuery('#geodir_calendar_post_type', $container).val();
			params+= size;
			<?php
			if ( $design_style && empty($instance['disable_lazyload'])) {
			// lazy load the cal
			?>
			$gdec_loaded_<?php echo $identifier;?> = false;
			jQuery(document).ready(function(){
				jQuery(window).scroll(function(){
					if (!$gdec_loaded_<?php echo $identifier;?> && $container.aui_isOnScreen()) {
						geodir_event_get_calendar($container, params);
						$gdec_loaded_<?php echo $identifier;?> = true;
					}
				});

				if($container.aui_isOnScreen()){
					geodir_event_get_calendar($container, params);
					$gdec_loaded_<?php echo $identifier;?> = true;
				}
			});
			<?php
			}else{
			?>
			geodir_event_get_calendar($container, params);
			<?php
			}
			?>
			
			var mnth = <?php echo date_i18n("n");?>;
			var year = <?php echo date_i18n("Y");?>;
			
			jQuery(".geodir_cal_next", $container).on('click', function() {
				mnth++;
				if (mnth > 12) {
					year++;
					mnth = 1;
				}
				params = "&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc + size;
				params += '&post_type=' + jQuery('#geodir_calendar_post_type', $container).val();
				geodir_event_get_calendar($container, params);
			});
			
			jQuery(".geodir_cal_prev", $container).on('click', function() {
				mnth--;
				if (mnth < 1) {
					year--;
					mnth = 12;
				}
				params = "&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc + size;
				params += '&post_type=' + jQuery('#geodir_calendar_post_type', $container).val();
				geodir_event_get_calendar($container, params);
			});

			jQuery("#geodir_calendar_post_type", $container).on('change', function() {
				params = "&mnth=" + mnth + "&yr=" + year + "&sday=" + sday + "&wday=" + wday + loc + size;
				params += '&post_type=' + jQuery(this).val();
				geodir_event_get_calendar($container, params);
			});
		};
	}

	document.addEventListener("DOMContentLoaded", function() {
		if (typeof <?php echo $function_name; ?> == 'function') {
			<?php echo $function_name; ?>();
		}
	});
	</script>
<?php } else { ?>
<style>#gdwgt_<?php echo $identifier;?> .gd-div-loader,#gdwgt_<?php echo $identifier;?> #cal_title{display:none;}</style>
<?php } ?>
		<?php
	}

	public static function ajax_calendar( $args = array(), $instance = array() ) {
		global $aui_bs5;

		$is_preview = ! empty( $instance['is_preview'] ) || ! empty( $instance['block_preview'] ) ? true : false;

		$post_type = !empty( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : '';
		if ( ! ( ! empty( $post_type ) && in_array( $post_type, GeoDir_Event_Post_Type::get_event_post_types() ) ) ) {
			$post_type = 'gd_event';
		}
		$design_style = geodir_design_style();
		if ( isset( $_REQUEST['sday'] ) ) {
			$week_start_day = absint( $_REQUEST["sday"] );
		} else if ( isset( $instance['_week_start_day'] ) ) {
			$week_start_day = absint( $instance["_week_start_day"] );
		} else {
			$week_start_day = absint( get_option( 'start_of_week' ) );
		}
		$monthNames = Array(__("January"), __("February"), __("March"), __("April"), __("May"), __("June"), __("July"), __("August"), __("September"), __("October"), __("November"), __("December"));

		if (!isset($_REQUEST["mnth"])) $_REQUEST["mnth"] = date_i18n("n");
		if (!isset($_REQUEST["yr"])) $_REQUEST["yr"] = date_i18n("Y");

		$month = (int)$_REQUEST["mnth"];
		$year = (int)$_REQUEST["yr"];
		$add_location_filter = !empty($_REQUEST['_loc']) && defined('GEODIRLOCATION_VERSION') ? true : false; // @todo LMv2

		$location_params = '&snear=' . (isset($_REQUEST['snear']) ? sanitize_text_field(stripslashes($_REQUEST['snear'])) : '');
		if ($add_location_filter) {
			if (!empty($_REQUEST['my_lat'])&& !empty($_REQUEST['my_lon'])) {
				$location_params .= '&sgeo_lat=' . sanitize_text_field($_REQUEST['my_lat']);
				$location_params .= '&sgeo_lon=' . sanitize_text_field($_REQUEST['my_lon']);
			}
		}
		
		$query_args = array(
			'gd_event_calendar' => strtotime( $year . '-' . $month ),
			'is_geodir_loop' => true,
			'gd_location' => $add_location_filter,
			'post_type' => $post_type,
			'posts_per_page' => 1,
			'post_status' => 'publish'
		);
		if ( $add_location_filter && defined( 'GEODIRLOCATION_VERSION' ) ) {
			if ( ! empty( $_REQUEST['country'] ) ) {
				$query_args['country'] = sanitize_text_field( $_REQUEST['country'] );
				$location_params .= '&country=' . sanitize_text_field( $_REQUEST['country'] );
			}
			if ( ! empty( $_REQUEST['region'] ) ) {
				$query_args['region'] = sanitize_text_field( $_REQUEST['region'] );
				$location_params .= '&region=' . sanitize_text_field( $_REQUEST['region'] );
			}
			if ( ! empty( $_REQUEST['city'] ) ) {
				$query_args['city'] = sanitize_text_field( $_REQUEST['city'] );
				$location_params .= '&city=' . sanitize_text_field( $_REQUEST['city'] );
			}
			if ( ! empty( $_REQUEST['neighbourhood'] ) ) {
				$query_args['neighbourhood'] = sanitize_text_field( $_REQUEST['neighbourhood'] );
				$location_params .= '&neighbourhood=' . sanitize_text_field( $_REQUEST['neighbourhood'] );
			}
		}

		if ( $design_style ) {
			$title_class = 'h6';
			$table_class = 'table table-hoverx table-bordered';
			$td_class = 'text-center position-relative';
			$loader = '<div class="clearfix text-center position-absolute w-100"><div class="gd-div-loader spinner-border mx-auto m-3" role="status"><span class="sr-only visually-hidden">'.__("Loading...","geodirevents").'</span></div></div>';
		} else {
			$title_class = '';
			$table_class = '';
			$td_class = '';
			$loader = '<div class="gd-div-loader"><i class="fas fa-sync fa-spin"></i></div>';
		}

		$week_day_format = isset( $_REQUEST['wday'] ) ? (int)$_REQUEST['wday'] : ( isset( $instance['_week_day_format'] ) ? $instance['_week_day_format'] : 0 );

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
				$day_mon = _x( 'M', 'Monday initial' );
				$day_tue = _x( 'T', 'Tuesday initial' );
				$day_wed = _x( 'W', 'Wednesday initial' );
				$day_thu = _x( 'T', 'Thursday initial' );
				$day_fri = _x( 'F', 'Friday initial' );
				$day_sat = _x( 'S', 'Saturday initial' );
				$day_sun = _x( 'S', 'Sunday initial' );
			break;
		}

		$week_days = array( 
			apply_filters( 'geodir_event_cal_single_day_sunday', $day_sun ), 
			apply_filters( 'geodir_event_cal_single_day_monday', $day_mon ), 
			apply_filters( 'geodir_event_cal_single_day_tuesday', $day_tue ), 
			apply_filters( 'geodir_event_cal_single_day_wednesday', $day_wed ), 
			apply_filters( 'geodir_event_cal_single_day_thursday', $day_thu ), 
			apply_filters( 'geodir_event_cal_single_day_friday', $day_fri ), 
			apply_filters( 'geodir_event_cal_single_day_saturday', $day_sat )
		);

		$_week_days = '';
		for ( $c = 0; $c <= 6; $c++ ) {
			$day_i = $c + $week_start_day;
			if ( $day_i > 6 ) {
				$day_i = $day_i - 7;
			}
			$_week_days .= '<th class="days '.$td_class.'">' . $week_days[ $day_i ] . '</th>';
		}

		if ( ! empty( $_REQUEST['size'] ) ) {
			$size = sanitize_text_field( $_REQUEST['size'] );
		} else if ( ! empty( $instance['size'] ) ) {
			$size = sanitize_text_field( $instance['size'] );
		} else {
			$size = 'small';
		}

		$cal_size_class = $size == 'small' ? ' table-sm ' : '';
		wp_reset_query();

		echo $loader;
	?><span id="cal_title" class="<?php echo $title_class;?>"><strong><?php echo $monthNames[ $month - 1 ] . ' ' . $year; ?></strong></span><table width="100%" border="0" cellpadding="2" cellspacing="2" class="calendar_widget <?php echo $table_class.$cal_size_class;?>"><thead><tr><?php echo $_week_days; ?></tr></thead><tbody>
		<?php
		$today = date_i18n('Y-m-d');
		$timestamp = mktime( 0, 0, 0, $month, 1, $year );
		$maxday = date_i18n( "t", $timestamp );
		$thismonth = getdate( $timestamp );
		$startday = $thismonth['wday'];

		$startday = $startday - $week_start_day;
		if ( $startday < 0 ) {
			$startday = $startday + 7;
		}

		$search_url = geodir_search_page_base_url();
		$search_args = array(
			'geodir_search' => 1,
			'etype' => 'all',
			'stype' => $post_type,
			's' => '',
		);
		$search_url = add_query_arg( $search_args, $search_url ) . $location_params;
		$tr = 0;

		for ( $i = 0; $i < ( $maxday + $startday ); $i++ ) {
			if ( ($i % 7 ) == 0 ) {
				$tr++;
				echo "<tr class='cal_day_nums'>";
			}

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
				if ( $is_preview ) {
					$date_search_url = 'javascript:void(0);';
				} else {
					$date_search_url = add_query_arg( array( 'event_calendar' => "$year$month$day" ), $search_url );
				}

				$today_class = '';
				if( $today == $date ){
					$today_class = 'date_today';
				}

				echo '<td valign="middle" class="gd_cal_nsat ' . $today_class . ' '.$td_class.'">';

				$query_args['gd_event_calendar'] = $year . '-' . $month . '-' . $day;
				if ( $is_preview ) {
					$results = array();
				} else {
					$results = query_posts( $query_args );
				}

				$link_class = '';
				if ( ! empty( $results ) ) {
					$past_class = $date < date("Y-m-d") ? 'event_past' : '';
					if ( $design_style ) {
						$link_class = ' badge stretched-link ';
						$link_class .= $aui_bs5 ? 'text-bg-primary text-decoration-none' : 'badge-primary';
					}
					$tooltip_init = $design_style ? 'data-toggle="tooltip"' : '';
					echo '<a class="event_highlight '.$past_class.$link_class.'" href=" ' . $date_search_url . '" title="' . esc_attr__( 'View events on this date', 'geodirevents' ) . '" '.$tooltip_init.'> ' . (int)$day . '</a>';
				} else {
					if ( $design_style ) {
						$link_class = ' badge text-muted';
					}
					echo '<span class="no_event'.$link_class.'">' . (int)$day . '</span>';
				}
				echo "</td>";
			}

			if ( ( $i % 7 ) == 6 ) {
				echo "</tr>";
			} else if ( $i == ( $maxday + $startday - 1 ) ) {
				echo '<td class="gd_cal_sat" colspan="' . ( ( $tr * 7 ) - $i - 1 ) . '">&nbsp;</td></tr>';
			}
		}
		?></tbody></table><?php
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