<?php
/**
 * GeoDirectory Events template functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

function geodir_event_yui_calendar_params() {
	$params = array(
		'month_long_1' => __( 'January','geodirevents' ),
		'month_long_2' => __( 'February','geodirevents' ),
		'month_long_3' => __( 'March','geodirevents' ),
		'month_long_4' => __( 'April','geodirevents' ),
		'month_long_5' => __( 'May','geodirevents' ),
		'month_long_6' => __( 'June','geodirevents' ),
		'month_long_7' => __( 'July','geodirevents' ),
		'month_long_8' => __( 'August','geodirevents' ),
		'month_long_9' => __( 'September','geodirevents' ),
		'month_long_10' => __( 'October' ,'geodirevents'),
		'month_long_11' => __( 'November' ,'geodirevents'),
		'month_long_12' => __( 'December' ,'geodirevents'),
		'month_s_1' => __( 'Jan','geodirevents' ),
		'month_s_2' => __( 'Feb','geodirevents' ),
		'month_s_3' => __( 'Mar','geodirevents' ),
		'month_s_4' => __( 'Apr' ,'geodirevents'),
		'month_s_5' => __( 'May' ,'geodirevents'),
		'month_s_6' => __( 'Jun' ,'geodirevents'),
		'month_s_7' => __( 'Jul','geodirevents' ),
		'month_s_8' => __( 'Aug' ,'geodirevents'),
		'month_s_9' => __( 'Sep' ,'geodirevents'),
		'month_s_10' => __( 'Oct' ,'geodirevents'),
		'month_s_11' => __( 'Nov','geodirevents' ),
		'month_s_12' => __( 'Dec','geodirevents' ),
		'day_s1_1' => __( 'S' ,'geodirevents'),
		'day_s1_2' => __( 'M' ,'geodirevents'),
		'day_s1_3' => __( 'T' ,'geodirevents'),
		'day_s1_4' => __( 'W' ,'geodirevents'),
		'day_s1_5' => __( 'T' ,'geodirevents'),
		'day_s1_6' => __( 'F' ,'geodirevents'),
		'day_s1_7' => __( 'S' ,'geodirevents'),
		'day_s2_1' => __( 'Su' ,'geodirevents'),
		'day_s2_2' => __( 'Mo' ,'geodirevents'),
		'day_s2_3' => __( 'Tu','geodirevents' ),
		'day_s2_4' => __( 'We','geodirevents' ),
		'day_s2_5' => __( 'Th','geodirevents' ),
		'day_s2_6' => __( 'Fr' ,'geodirevents'),
		'day_s2_7' => __( 'Sa' ,'geodirevents'),
		'day_s3_1' => __( 'Sun','geodirevents' ),
		'day_s3_2' => __( 'Mon' ,'geodirevents'),
		'day_s3_3' => __( 'Tue' ,'geodirevents'),
		'day_s3_4' => __( 'Wed','geodirevents' ),
		'day_s3_5' => __( 'Thu' ,'geodirevents'),
		'day_s3_6' => __( 'Fri' ,'geodirevents'),
		'day_s3_7' => __( 'Sat' ,'geodirevents'),
		'day_s5_1' => __( 'Sunday','geodirevents' ),
		'day_s5_2' => __( 'Monday' ,'geodirevents'),
		'day_s5_3' => __( 'Tuesday','geodirevents' ),
		'day_s5_4' => __( 'Wednesday','geodirevents' ),
		'day_s5_5' => __( 'Thursday','geodirevents' ),
		'day_s5_6' => __( 'Friday' ,'geodirevents'),
		'day_s5_7' => __( 'Saturday' ,'geodirevents'),
		's_previousMonth' => __( 'Previous Month' ),
		's_nextMonth' => __( 'Next Month' ),
		's_close' => __( 'Close' )
	);

	return apply_filters( 'geodir_event_yui_calendar_params', $params );
}
 
function geodir_event_params() {
	$input_date_format = geodir_event_field_date_format();
	$display_date_format = geodir_event_date_format();
	$jqueryui_date_format = geodir_event_date_format_php_to_jqueryui( $input_date_format );

	$params = array(
		'text_to' => __( 'to', 'geodirevents' ),
		'input_date_format' => $input_date_format,
		'display_date_format' => $display_date_format,
		'jqueryui_date_format' => $jqueryui_date_format,
		'week_start_day' => apply_filters( 'geodir_event_calendar_week_start_day', '0' ),
		'monthsArray' => '["' . __( 'January' ) . '", "' . __( 'February' ) . '", "' . __( 'March' ) . '", "' . __( 'April' ) . '", "' . __( 'May' ) . '", "' . __( 'June' ) . '", "' . __( 'July' ) . '", "' . __( 'August' ) . '", "' . __( 'September' ) . '", "' . __( 'October' ) . '", "' . __( 'November' ) . '", "' . __( 'December' ) . '"]'
    );

    return apply_filters( 'geodir_event_params', $params );
}

function geodir_event_admin_params() {
	$params = array(
    );

    return apply_filters( 'geodir_event_admin_params', $params );
}

function geodir_event_display_filter_options(){
	
	global $wp_query, $geodir_post_type, $paged;
	
	$filter_by = '';
	$filter_field_options = '';
	
	if(isset($_REQUEST['etype'])) $filter_by = $_REQUEST['etype'];
	
	$event_filters_opt = apply_filters( 'geodir_event_filter_options', array(
		'all' => __('All Events', 'geodirevents'),
		'today' => __('Today', 'geodirevents'),
		'upcoming' => __('Upcoming', 'geodirevents'),
		'past' => __('Past', 'geodirevents'),
	) );
	
	if($filter_by == '')
		$filter_by = geodir_get_option( 'event_defalt_filter' );

	//$current_link = esc_url(get_pagenum_link());
	$current_link = esc_url(geodir_curPageURL());
	$current_link = str_replace('#038;', '&', esc_url( add_query_arg(array('etype'=>'all'), $current_link )));
	
	
	if(!empty($event_filters_opt)){
	
		foreach($event_filters_opt as $key => $opts){
			
			($filter_by == $key) ? $selected = 'selected="selected"' :  $selected = '';	
			
			$filter_field_options .= '<option '.$selected.' value="'. esc_url( add_query_arg( array('etype'=>$key),$current_link ) ).'">'.$event_filters_opt[$key].'</option>';
			
		}
	
	}
	
	if($filter_field_options != ''){ ?>
		
		<div class="geodir-event-filter">
		
			<select name="etype" id="etype" onchange="javascript:window.location=this.value;">
				<?php echo $filter_field_options;?>
			</select>
		
		</div>
		<div style="clear:both"></div> <?php
	
	}
	
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
	$event_time_array = array();
	for ( $i = 0; $i < 24; $i++ ) {
		 for ( $j = 0; $j < 60; $j += $time_increment ) {
		 	$time_hr_abs = $i;
		 	$time_am_pm = ' AM';

			if ( $i >= 12) {
				$time_am_pm = ' PM';
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

		 	$event_time_array[ $time_hr_index  . ":" . $time_min ] = $time_hr . ":" . $time_min . $time_am_pm;
		 }
	}

	return apply_filters( 'geodir_event_schedule_times' , $event_time_array);
}

function geodir_event_show_event_fields_html( $recuring_data = array() ) {
	global $post;

	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $post );
	
	$format = geodir_event_field_date_format();
	$default_start = date_i18n( $format, current_time( 'timestamp' ) );

	//fix date formats
	if ($format!='Y-m-d' && !isset($_REQUEST['backandedit'])) {
		if (isset($recuring_data['event_start']) && $recuring_data['event_start']) {
			$recuring_data['event_start'] = geodir_date($recuring_data['event_start'], $format, '', true);
		}
		
		if (isset($recuring_data['event_end']) && $recuring_data['event_end']) {
			$recuring_data['event_end'] = geodir_date($recuring_data['event_end'], $format, '', true);
		}
		
		if (isset($recuring_data['repeat_end']) && $recuring_data['repeat_end']) {
			$recuring_data['repeat_end'] = geodir_date($recuring_data['repeat_end'], $format, '', true);
		}
	}
	
	$recurring = false;
	$event_recurring_dates = false;
	$different_times = '';
	$starttime = '';
	$endtime = '';
	$event_start = $default_start;
	$event_end = '';
	$repeat_x = 1;
	$repeat_type = '';
	$duration_x = 1;
	$repeat_end_type = 0;
	$max_repeat = 2;
	$repeat_end = '';
	$all_day = false;
	$repeat_days = array();
	$repeat_weeks = array();
	
	$recurring = empty( $recuring_data ) || ( isset( $recuring_data['recurring'] ) && empty( $recuring_data['recurring'] ) ) ? false : true;
	$recurring = $recurring_pkg && $recurring ? true : false;
		
	if ( !empty( $recuring_data ) ) {
		$event_recurring_dates = isset( $recuring_data['event_recurring_dates'] ) ? $recuring_data['event_recurring_dates'] : $event_recurring_dates;
		$event_start = isset( $recuring_data['event_start'] ) ? $recuring_data['event_start'] : '';
		$event_end = isset( $recuring_data['event_end'] ) ? $recuring_data['event_end'] : '';
		$all_day = isset( $recuring_data['all_day'] ) && !empty( $recuring_data['all_day'] ) ? true : false;
		$starttime = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
    	$endtime = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
		
		if ( !geodir_event_is_date( $event_start ) ) {
			$event_start = is_array( $event_recurring_dates ) && !empty( $event_recurring_dates[0] ) && geodir_event_is_date( $event_recurring_dates[0] ) ? $event_recurring_dates[0] : $default_start;
		} 
		
		if ( $recurring ) {
			$repeat_x = isset( $recuring_data['repeat_x'] ) && (int)$recuring_data['repeat_x'] > 0 ? (int)$recuring_data['repeat_x'] : 1;
			$repeat_type = isset( $recuring_data['repeat_type'] ) ? $recuring_data['repeat_type'] : 'custom';
			$duration_x = isset( $recuring_data['duration_x'] ) ? $recuring_data['duration_x'] : 1;
			$repeat_end_type = isset( $recuring_data['repeat_end_type'] ) ? (int)$recuring_data['repeat_end_type'] : '';
			$max_repeat = isset( $recuring_data['max_repeat'] ) ? $recuring_data['max_repeat'] : 2;
			$repeat_end = isset( $recuring_data['repeat_end'] ) ? $recuring_data['repeat_end'] : '';
			$different_times = isset( $recuring_data['different_times'] ) && !empty( $recuring_data['different_times'] ) && $repeat_type == 'custom' ? true : false;
			
			// week days
			if ( $repeat_type == 'week' || $repeat_type == 'month' ) {
				$repeat_days = isset( $recuring_data['repeat_days'] ) ? $recuring_data['repeat_days'] : $repeat_days;
			}
			
			// by week
			if ( $repeat_type == 'month' ) {
				$repeat_weeks = isset( $recuring_data['repeat_weeks'] ) ? $recuring_data['repeat_weeks'] : $repeat_weeks;
			}
			
			$event_end = '';
		}
				
		if ( $repeat_end_type == 1 ) {
			$max_repeat = '';
		} else {
			$repeat_end = '';
		}
	}
	
	$duration_x = $duration_x > 0 ? $duration_x : 1;
		
	$list_recurring_dates = '';
	
	$jqueryui_format = geodir_event_date_format_php_to_jqueryui( $format );
	
	$dates_selected = array();
	$differnttimes_selected = '';
	if ( isset( $event_recurring_dates ) && $event_recurring_dates != '' ) {
		$dates_array = explode( ',', $event_recurring_dates );
		
		$exttimes = isset( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
		$extendtimes = isset( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
		
		foreach ( $dates_array as $key => $date ) {
			$dates_selected[] = geodir_date( $date, 'm/d/Y' );
			
			$date_formated = geodir_date( $date, $format, '', true );
			$list_recurring_dates .= '<span>' . $date_formated . '</span>';
			
			if ( $different_times ) {
				$exttimes = $recuring_data['starttimes'];
				$extendtimes = $recuring_data['endtimes'];
				
				$exttimes_display = isset( $exttimes[$key] ) ? $exttimes[$key] : '';
				$extendtimes_display = isset( $extendtimes[$key] ) ? $extendtimes[$key] : '';
					
				$differnttimes_selected .= '<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">' . $date_formated . '</label><label class="event-multiple-start">' . __( 'Start', 'geodirevents' ) . '</label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="starttimes[]" class="chosen_select">' . geodir_event_time_options( $exttimes_display ) . '</select></div><label class="event-multiple-end">'.__( 'End', 'geodirevents' ) . '</label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="endtimes[]" class="chosen_select" style="width:90px;">' . geodir_event_time_options( $extendtimes_display ) . '</select></div></div>';
			}
		}
		
		$dates_selected = array_unique( $dates_selected );
	}
	$dates_selected = !empty( $dates_selected ) ? ", selected:'" . implode( ',', $dates_selected ) . "'" : '';
	
	$show_recurring = $recurring ? 'display:block' : 'display:none';
	$show_recurring_dates = $list_recurring_dates != '' ? 'display:block' : 'display:none';
	$show_times = $different_times != '' ? 'display:block' : 'display:none';
	
	if ( $repeat_type != 'custom' ) {
		$dates_selected = '';
		$differnttimes_selected = '';
	}
		
	$show_event_time = $all_day ? "display:none" : "display:block";
	?>
	<?php if ( !is_admin() ) { ?><h5><?php _e( 'Event Schedule', 'geodirevents' );?></h5><?php } ?>
	<?php if ( $recurring_pkg ) { ?>
	<div class="geodir_form_row clearfix">
	  <label><?php _e( 'Recurring event?', 'geodirevents' );?></label>
	  <div class="geodir-category_label">
			<div class="geodir-form_cat">
			  <input type="radio" class="geodir-checkbox" name="recurring" id="recurring_n" value="0" <?php checked( $recurring, false );?> />
			  <?php _e( 'No', 'geodirevents' );?>
			  <input type="radio" class="geodir-checkbox" name="recurring" id="recurring_y" value="1" <?php checked( $recurring, true );?> />
			  <?php _e( 'Yes', 'geodirevents' );?>
			</div>
			<span class="geodir_message_error" id="gd_recurring_span"></span>
		</div>
		<span class="geodir_message_note"><?php _e( 'Check "Yes" for recurring event.', 'geodirevents' );?></span>
	</div>
	<?php } ?>
	<div class="required_field geodir_form_row gd-start-edate clearfix gd-date-fields">
		<label><?php _e( 'Event start date', 'geodirevents' );?><span> *</span></label>
		<input type="text" value="<?php echo $event_start;?>" class="geodir_textfield gd-date-field" id="event_start" name="event_start" placeholder="<?php echo date_i18n( $format );?>" />
		<span class="geodir_message_error"><?php _e( 'Please check start date of event', 'geodirevents' );?></span>
	</div>
	<div class="geodir_form_row clearfix gd-date-fields gd-event-end-div gd-custom-hide">
		<label><?php _e( 'Event end date', 'geodirevents' );?></label>
		<input type="text" value="<?php echo $event_end;?>" class="geodir_textfield gd-date-field" id="event_end" name="event_end" placeholder="<?php echo date_i18n( $format );?>" />
	</div>
	<?php if ( $recurring_pkg ) { ?>
	<div class="geodir_form_row clearfix gd-date-fields gd-event-duration gd-custom-hide">
		<label><?php _e( 'Event duration', 'geodirevents' );?></label>
		<input type="text" value="<?php echo $duration_x;?>" class="geodir_textfield geodir-text-small autofill" id="duration_x" name="duration_x"><label for="duration_x" class="gd-lbl-none"><?php _e( 'day(s)', 'geodirevents' );?></label>
	</div>
	<div class="required_field geodir_form_row clearfix gd-repeat-type gd-recurring" style=" <?php echo $show_recurring;?>">
	  <label><?php _e( 'Repeats', 'geodirevents' );?><span> *</span></label>
		<div class="gd-chosen-outer">
		<select option-ajaxChosen="false" id="repeat_type" name="repeat_type" class="chosen_select">
		  <option value="" <?php selected( $repeat_type, '' );?>><?php _e( 'Select Recurring Type', 'geodirevents' );?></option>
		  <option value="day" <?php selected( $repeat_type, 'day' );?> data-title="<?php echo esc_attr( __( 'days', 'geodirevents' ) );?>"><?php _e( 'Daily', 'geodirevents' );?></option>
		  <option value="week" <?php selected( $repeat_type, 'week' );?> data-title="<?php echo esc_attr( __( 'weeks', 'geodirevents' ) );?>"><?php _e( 'Weekly', 'geodirevents' );?></option>
		  <option value="month" <?php selected( $repeat_type, 'month' );?> data-title="<?php echo esc_attr( __( 'months', 'geodirevents' ) );?>"><?php _e( 'Monthly', 'geodirevents' );?></option>
		  <option value="year" <?php selected( $repeat_type, 'year' );?> data-title="<?php echo esc_attr( __( 'years', 'geodirevents' ) );?>"><?php _e( 'Yearly', 'geodirevents' );?></option>
		  <option value="custom" <?php selected( $repeat_type, 'custom' );?>><?php _e( 'Custom', 'geodirevents' );?></option>
		</select>
		</div>
		<span class="geodir_message_error"><?php _e( 'Please select recurring type', 'geodirevents' );?></span>
	</div>
	<div class="geodir_form_row clearfix gd-custom-hide gd-repeat-x gd-recurring" style=" <?php echo $show_recurring;?>">
	  <label><?php _e( 'Repeat every', 'geodirevents' );?></label>
		<div class="gd-chosen-outer">
		<select option-ajaxChosen="false" id="repeat_x" name="repeat_x" class="chosen_select">
		  <?php for ( $i = 1; $i <= 30; $i++ ) { ?>
		  <option value="<?php echo $i;?>" <?php selected( $repeat_x, $i );?>><?php echo $i;?></option>
		  <?php } ?>
		</select>
		</div>
	</div>
	<div class="geodir_form_row clearfix gd-custom-hide gd-repeat-days gd-recurring" style=" <?php echo $show_recurring;?>">
	  <label><?php _e( 'Repeat on', 'geodirevents' );?></label>
		<div class="gd-chosen-outer">
		<select option-ajaxchosen="false" id="repeat_days" name="repeat_days[]" class="chosen_select" multiple="multiple" data-placeholder="<?php echo esc_attr( __( 'Select days', 'geodirevents' ) );?>">
		   <option value="1" <?php selected( true, in_array( 1, $repeat_days ) );?>><?php _e( 'Mon', 'geodirevents' );?></option>
		   <option value="2" <?php selected( true, in_array( 2, $repeat_days ) );?>><?php _e( 'Tue', 'geodirevents' );?></option>
		   <option value="3" <?php selected( true, in_array( 3, $repeat_days ) );?>><?php _e( 'Wed', 'geodirevents' );?></option>
		   <option value="4" <?php selected( true, in_array( 4, $repeat_days ) );?>><?php _e( 'Thu', 'geodirevents' );?></option>
		   <option value="5" <?php selected( true, in_array( 5, $repeat_days ) );?>><?php _e( 'Fri', 'geodirevents' );?></option>
		   <option value="6" <?php selected( true, in_array( 6, $repeat_days ) );?>><?php _e( 'Sat', 'geodirevents' );?></option>
		   <option value="0" <?php selected( true, in_array( 0, $repeat_days ) );?>><?php _e( 'Sun', 'geodirevents' );?></option>
		</select>
		</div>
	</div>
	<div class="geodir_form_row clearfix gd-custom-hide gd-repeat-weeks gd-recurring" style=" <?php echo $show_recurring;?>">
	  <label><?php _e( 'Repeat by', 'geodirevents' );?></label>
		<div class="gd-chosen-outer">
		<select option-ajaxchosen="false" id="repeat_weeks" name="repeat_weeks[]" class="chosen_select" multiple="multiple" data-placeholder="<?php echo esc_attr( __( 'Select weeks', 'geodirevents' ) );?>">
		   <option value="1" <?php selected( true, in_array( 1, $repeat_weeks ) );?>><?php _e( '1st week', 'geodirevents' );?></option>
		   <option value="2" <?php selected( true, in_array( 2, $repeat_weeks ) );?>><?php _e( '2nd week', 'geodirevents' );?></option>
		   <option value="3" <?php selected( true, in_array( 3, $repeat_weeks ) );?>><?php _e( '3rd week', 'geodirevents' );?></option>
		   <option value="4" <?php selected( true, in_array( 4, $repeat_weeks ) );?>><?php _e( '4th week', 'geodirevents' );?></option>
		   <option value="5" <?php selected( true, in_array( 5, $repeat_weeks ) );?>><?php _e( '5th week', 'geodirevents' );?></option>
		</select>
		</div>
	</div>
	<?php } ?>
	<?php if ( $recurring_pkg ) { ?>
		<div class="geodir_form_row clearfix gd-recurring-for gd-recurring gd-custom-hide" style=" <?php echo $show_recurring;?>">
			<label><?php _e( 'Recurring ends', 'geodirevents' );?><span> *</span></label>
			<div class="geodir-form_cat">
		  		<input type="radio" class="geodir-checkbox" name="repeat_end_type" id="repeat_end_type_m" value="0" <?php checked( $repeat_end_type, 0 );?> /><label for="repeat_end_type_m"><?php _e( 'After', 'geodirevents' );?></label> <input type="text" value="<?php echo $max_repeat;?>" class="geodir_textfield geodir-text-small autofill" id="max_repeat" name="max_repeat"><label for="repeat_end_type_m"><?php _e( 'occurrences', 'geodirevents' );?></label>&nbsp;<input type="radio" class="geodir-checkbox" name="repeat_end_type" id="repeat_end_type_u" value="1" <?php checked( $repeat_end_type, 1 );?> /><label for="repeat_end_type_u"><?php _e( 'On', 'geodirevents' );?></label> <input type="text" value="<?php echo $repeat_end;?>" class="geodir_textfield geodir-text-med autofill" id="repeat_end" name="repeat_end" />
		  </div>
			<span class="geodir_message_note"><?php _e( 'Recurring event duration.', 'geodirevents' );?></span>
		</div>
		<div id="gd_custom_recurring" class="gd-recurring gd-custom-show" style=" <?php echo $show_recurring;?>">
			<div class="required_field geodir_form_row clearfix">
				<label><?php _e( 'Event Date(s)', 'geodirevents' );?></label>
				<div id="doc2" class="yui-skin-sam yui-t2">
				  <div class="fullitem">
					<div class="yui-calcontainer multi" id="multiCalContainer1">
					  <div class="yui-calcontainer groupcal first-of-type" id="multiCalContainer1_0"></div>
					  <div class="yui-calcontainer groupcal last-of-type" id="multiCalContainer1_1"></div>
					</div>
					<div style="z-index:2;visibility:visible;left:1139px;top:347px;" id="help1_c" class="yui-panel-container shadow">
					  <div id="help1" class="popup yui-module yui-overlay yui-panel" style=" <?php echo $show_recurring_dates;?>">
						<div id="help1_h" style="cursor:move;" class="hd">
						  <?php _e( 'Selected Dates', 'geodirevents' );?>
						</div>
						<div class="bd">
						  <div id="multiDisplay1"><?php echo $list_recurring_dates;?></div>
						</div>
						<div class="ft"></div>
					  </div>
					  <div class="underlay"></div>
					</div>
					<input name="event_recurring_dates" class="event_recurring_dates" id="dates" value="<?php echo $event_recurring_dates;?>" type="hidden">
				  </div>
				</div>
				<span class="geodir_message_note"><?php _e( 'Click on each day your event will be held. You may choose more than one day. Selected dates appear in blue and can be unselected by clicking on them.', 'geodirevents' );?></span>
				<span class="geodir_message_error" style="display:none;"><?php _e( 'Please select at least one event date.', 'geodirevents' );?></span>
			</div>
		</div>
	<?php } ?>
	<div class="required_field geodir_form_row gd-all-day clearfix">
	  <label for="all_day"><?php _e( 'All day', 'geodirevents' );?></label>
	  <input type="checkbox" value="1" name="all_day" id="all_day" class="all_day" <?php checked( $all_day, true );?> />
	  <span class="geodir_message_note"><?php _e( 'Checked to set event for all day', 'geodirevents' ); ?></span>
	</div>
	<div class="required_field clearfix geodir_form_row clearfix gd-event-time-row" style=" <?php echo $show_event_time;?>">
	  <label><?php _e( 'Event Time', 'geodirevents' );?></label>
	  <label class="eventdateto"><?php _e( 'Start', 'geodirevents' );?></label>
	  <div class="event-single-dateto-inner">
		<select option-ajaxChosen="false" id="starttime" name="starttime" class="chosen_select">
		  <?php echo geodir_event_time_options( $starttime );?>
		</select>
	  </div>
	  <label class="eventdateto"><?php _e( 'End', 'geodirevents' );?></label>
	  <div class="event-single-dateto-inner">
		<select option-ajaxChosen="false" id="endtime" name="endtime" class="chosen_select" style="width:90px;">
		  <?php echo geodir_event_time_options( $endtime );?>
		</select>
	  </div>
	</div>
	<?php if ( $recurring_pkg ) { ?>
	<div id="gd_diff_times" class="geodir_form_row clearfix gd-recurring gd-custom-show" style=" <?php echo $show_recurring;?>">
	  <label><?php _e( 'Different Event Times', 'geodirevents' );?></label>
	  <input name="different_times" class="different_times" value="1" type="checkbox" <?php checked( $different_times, true );?> />
	  <span class="geodir_message_note"><?php _e('Checked to different dates have different start and end times', 'geodirevents')?></span>
	</div>
	<div class="geodir_form_row show_times_div clearfix gd-custom-show" style=" <?php echo $show_times;?>">
	  <label></label>
	  <!--<input type="button" class="show_different_times" value="Click to set times" />-->
	  <div class="show_different_times_div"><?php echo $differnttimes_selected;?></div>
	</div>
	<?php } ?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery( "#event_start" ).datepicker({
				dateFormat:'<?php echo $jqueryui_format;?>',
				minDate: 0,
				onClose: function( selectedDate ) {
					jQuery( "#repeat_end" ).datepicker( "option", "minDate", selectedDate );
					jQuery( "#event_end" ).datepicker( "option", "minDate", selectedDate );
				}
			});
			jQuery( "#event_end" ).datepicker({
				dateFormat:'<?php echo $jqueryui_format;?>',
				minDate: 0,
				onClose: function( selectedDate ) {
					var esDate = jQuery( "#event_start" ).val();
					if (esDate) {
						jQuery( "#repeat_end" ).datepicker( "option", "minDate", esDate );
						jQuery( "#event_end" ).datepicker( "option", "minDate", esDate );
					}
				}
			});
			jQuery('#all_day').change(function() {
				gd_on_all_day();
			});
			<?php if ( $recurring_pkg ) { ?>
			jQuery( "#repeat_end" ).datepicker({
				dateFormat:'<?php echo $jqueryui_format;?>',
				minDate: 0,
				onClose: function( selectedDate ) {
					if (jQuery( "#event_start" ).val()) {
						jQuery( "#repeat_end" ).datepicker( "option", "minDate", jQuery( "#event_start" ).val() );
					}
				}
			});

			jQuery('input[name=recurring]').click(function() {
				jQuery('input[name=recurring]').each(function() {
					if (jQuery(this).is(':checked') == true) {
						if (jQuery(this).val() == '1') {
							gd_on_recurring(this);
						} else {
							gd_on_norecurring(this);
						}
					}
				});
			});
			jQuery('input[name=recurring]').each(function() {
				if (jQuery(this).is(':checked') == true) {
					jQuery(this).click();
				}
			});
			jQuery('#repeat_type').change(function() {
				gd_on_repeat_type();
			});
			<?php } ?>
			
			jQuery('.different_times').change(function() {
				if (jQuery(this).is(':checked') == false) {
					jQuery('.show_times_div').hide();
				} else {
					jQuery('.show_times_div').show();
				}
				var event_dates = jQuery('#dates').val();
				if (event_dates != '') {
					var spdates = event_dates.split(",");
					var total_dates = spdates.length;
					var total_dates_selected = '';
					for (i = 0; i < total_dates; i++) {
						total_dates_selected += '<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">' + spdates[i] + '</label><label class="event-multiple-start"><?php _e('Start ', 'geodirevents');?></label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="starttimes[]" class="chosen_select"><?php echo geodir_event_time_options(); ?></select></div><label class="event-multiple-end"><?php echo _e('End ', 'geodirevents');?></label><div class="event-multiple-dateto-inner"><select option-ajaxChosen="false" name="endtimes[]" class="chosen_select" style="width:90px;"><?php echo geodir_event_time_options(); ?></select></div></div>';
					}
					jQuery('.show_different_times_div').html('');
					jQuery('.show_different_times_div').append(total_dates_selected);
					jQuery(".show_different_times_div .chosen_select").chosen();
				} else {
					jQuery('.show_different_times_div').html('');
				}
			});
		});
		
		function gd_event_date_format(date) {
			var format = '<?php echo $format;?>';
			var formatted_date = format;
			var monthNames = [
			"<?php _e('January'); ?>",
			"<?php _e('February'); ?>",
			"<?php _e('March'); ?>",
			"<?php _e('April'); ?>",
			"<?php _e('May'); ?>",
			"<?php _e('June'); ?>",
			"<?php _e('July'); ?>",
			"<?php _e('August'); ?>",
			"<?php _e('September'); ?>",
			"<?php _e('October'); ?>",
			"<?php _e('November'); ?>",
			"<?php _e('December'); ?>"
			];
			formatted_date = formatted_date.replace("Y", date.getFullYear());
			formatted_date = formatted_date.replace("m", (date.getMonth() + 1));
			formatted_date = formatted_date.replace("d", date.getDate());
			formatted_date = formatted_date.replace("j", date.getDate());
			formatted_date = formatted_date.replace("F", monthNames[date.getMonth()]);
			return formatted_date;
		}

    jQuery(function() {
		multicalInit1 = function() {
			cal = new YAHOO.widget.CalendarGroup("multiCal1", "multiCalContainer1", {
				pages: 2,
				MULTI_SELECT: true <?php echo $dates_selected;?>,
				start_weekday: <?php echo apply_filters( 'geodir_calendar_start_weekday', '0' );?>
			});
			cal.render();
			var updated = function() {
				var selectedDates = cal.getSelectedDates();
				var txt = "";
				var val = "";
				for (var ii = 0; ii < selectedDates.length; ii++) {
					var date = selectedDates[ii];
					var current = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate();
					var current_display = gd_event_date_format(date);
					if (ii > 0) {
						val += ",";
					}
					val += current;
					txt += "<span>" + current_display + "</span>";
				}
				if (txt == '') {
					jQuery('#help1').hide();
					<?php if (!is_admin()) { ?>
					jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').show();
					jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').html(error_message);
					<?php } ?>
				} else {
					jQuery('#help1').show();
					jQuery('.event_recurring_dates').closest('.required_field').find('.geodir_message_error').hide();
				}
				document.getElementById('multiDisplay1').innerHTML = txt;
				document.getElementById('dates').value = val;
				if (jQuery('.different_times').is(':checked') == true) {
					jQuery('.different_times').change();
				}
				calChanged(cal);
			}
			cal.selectEvent.subscribe(updated, cal, true);
			cal.deselectEvent.subscribe(updated, cal, true);
		}
		YAHOO.util.Event.onDOMReady(multicalInit1);
    });
		
		function gd_on_all_day(custom) {
			if (jQuery('select[name=repeat_type]').val() == 'custom') {
				jQuery('#gd_diff_times').show();
				jQuery('.show_times_div').show();
			} else {
				jQuery('#gd_diff_times').hide();
				jQuery('.show_times_div').hide();
			}
				
			if(jQuery('input[name=all_day]').is(":checked")) {
				jQuery('.gd-event-time-row').hide();
				jQuery('#gd_diff_times').hide();
				jQuery('.show_times_div').hide();
			} else {
				jQuery('.gd-event-time-row').show();
			}
		}
		<?php if ( $recurring_pkg ) { ?>
		function gd_on_repeat_type() {
			var repeat_type = jQuery('select[name=repeat_type]').val();
			if(repeat_type == 'custom') {
				jQuery('.gd-repeat-x').hide();
				jQuery('.gd-event-duration').hide();
				jQuery('.gd-recurring-for').hide();
				jQuery('.gd-start-edate').hide();
				jQuery('.gd-repeat-days').hide();
				jQuery('.gd-repeat-weeks').hide();
				
				jQuery('#gd_custom_recurring').show();
				jQuery('#gd_diff_times').show();
				jQuery('#show_times_div').show();
				jQuery('.gd-all-day').show();
				jQuery('.gd-event-time-row').show();
				if(jQuery('input[name=all_day]').is(":checked")) {
					jQuery('.gd-event-time-row').hide();
				} else {
					jQuery('.gd-event-time-row').show();
				}
			} else if(repeat_type == 'year' || repeat_type == 'month' || repeat_type == 'week' || repeat_type == 'day') {
				jQuery('.gd-repeat-x').show();
				jQuery('.gd-event-duration').show();
				jQuery('.gd-recurring-for').show();
				jQuery('.gd-start-edate').show();
				jQuery('.gd-all-day').show();
				if(jQuery('input[name=all_day]').is(":checked")) {
					jQuery('.gd-event-time-row').hide();
				} else {
					jQuery('.gd-event-time-row').show();
				}
				
				if (repeat_type == 'month' || repeat_type == 'week' ) {
					jQuery('.gd-repeat-weeks').show();
					jQuery('.gd-repeat-days').show();
					if (repeat_type == 'week') {
						jQuery('.gd-repeat-weeks').hide();
					}					
				} else {
					jQuery('.gd-repeat-days').hide();
					jQuery('.gd-repeat-weeks').hide();
				}
				
				jQuery('#gd_custom_recurring').hide();
				jQuery('#gd_diff_times').hide();
				jQuery('#show_times_div').hide();
			} else {
				jQuery('.gd-repeat-x').hide();
				jQuery('.gd-recurring-for').hide();
				jQuery('#gd_custom_recurring').hide();
				jQuery('.gd-all-day').hide();
				jQuery('.gd-event-time-row').hide();
				jQuery('#gd_diff_times').hide();
				jQuery('.show_times_div').hide();
			}
			var repeat_text = jQuery('#repeat_type').find('option:selected').attr('data-title');
						
			if (repeat_type!='') {
				gd_on_all_day(true);
			}
		}
		function gd_on_recurring(el) {
			jQuery('.gd-event-duration').show();
			jQuery('.gd-repeat-type').show();
			jQuery('.gd-repeat-x').show();
			jQuery('.gd-recurring-for').show();
			
			jQuery('.gd-event-end-div').hide();
			
			gd_on_repeat_type();		
			
			jQuery("select.chosen_select").chosen().trigger("chosen:updated");
			
			gd_onchange_recurring(el, 'on');
		}
		
		function gd_on_norecurring(el) {
			jQuery('.gd-event-duration').hide();
			jQuery('.gd-repeat-type').hide();
			jQuery('.gd-repeat-x').hide();
			jQuery('.gd-recurring-for').hide();
			
			jQuery('.gd-repeat-days').hide();
			jQuery('.gd-repeat-weeks').hide();
			
			jQuery('#gd_custom_recurring').hide();
			jQuery('#gd_diff_times').hide();
			jQuery('#show_times_div').hide();
			
			jQuery('.gd-start-edate').show();
			jQuery('.gd-event-end-div').show();
			jQuery('.gd-all-day').show();
			if(jQuery('input[name=all_day]').is(":checked")) {
				jQuery('.gd-event-time-row').hide();
			} else {
				jQuery('.gd-event-time-row').show();
			}
						
			gd_onchange_recurring(el, 'off');
		}
		
		function gd_onchange_recurring(el, type) {
		}
		<?php } ?>
	</script>
	<?php   
}

/**
 *
 * @since 1.4.6 Same day events should just show date and from - to time.
 */
function geodir_event_show_shedule_date() {
    global $post, $geodir_date_time_format, $geodir_date_format, $geodir_time_format;
    
    if ( geodir_is_page( 'preview' ) ) {
        $recuring_data = (array)$post;
        $input_format = geodir_event_field_date_format();
            
        if (isset($recuring_data['event_start']) && $recuring_data['event_start']) {
            $recuring_data['event_start'] = geodir_date($recuring_data['event_start'], 'Y-m-d', $input_format);
        }

        if (isset($recuring_data['event_end']) && $recuring_data['event_end']) {
            $recuring_data['event_end'] = geodir_date($recuring_data['event_end'], 'Y-m-d', $input_format);
        }
        
        if (isset($recuring_data['repeat_end']) && $recuring_data['repeat_end']) {
            $recuring_data['repeat_end'] = geodir_date($recuring_data['repeat_end'], 'Y-m-d', $input_format);
        }
    } else {
        $recuring_data = !empty( $post->event_dates ) ? maybe_unserialize( $post->event_dates ) : NULL;
    }

    if ( !empty( $recuring_data ) && ( isset( $recuring_data['event_recurring_dates'] ) && $recuring_data['event_recurring_dates'] != '' ) || ( isset( $post->recurring ) && !empty( $post->recurring ) ) ) {
        $geodir_num_dates = 0;
        $starttimes = '';
        $endtimes = '';
        $astarttimes = array();
        $aendtimes = array();
        
        // Check recurring enabled
        $recurring_pkg = geodir_event_recurring_pkg( $post );
        
        $hide_past_dates = geodir_get_option( 'event_hide_past_dates' );
        
        if ( $post->recurring && $recurring_pkg ) {
            if ( !isset( $recuring_data['repeat_type'] ) ) {
                $recuring_data['repeat_type'] = 'custom';
            }
            
            $repeat_type = isset( $recuring_data['repeat_type'] ) && in_array( $recuring_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $recuring_data['repeat_type'] : 'year'; // day, week, month, year, custom
            
            $different_times = isset( $recuring_data['different_times'] ) && !empty( $recuring_data['different_times'] ) ? true : false;
            
            if ( geodir_is_page( 'preview' ) ) {
                $start_date = geodir_event_is_date( $recuring_data['event_start'] ) ? $recuring_data['event_start'] : date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
                $end_date = isset( $recuring_data['event_end'] ) ? trim( $recuring_data['event_end'] ) : '';
                $all_day = isset( $recuring_data['all_day'] ) && !empty( $recuring_data['all_day'] ) ? true : false;
                $starttime = isset( $recuring_data['starttime'] ) && !$all_day ? trim( $recuring_data['starttime'] ) : '';
                $endtime = isset( $recuring_data['endtime'] ) && !$all_day ? trim( $recuring_data['endtime'] ) : '';

                $starttimes = isset( $recuring_data['starttimes'] ) && !$all_day ? $recuring_data['starttimes'] : '';
                $endtimes = isset( $recuring_data['endtimes'] ) && !$all_day ? $recuring_data['endtimes'] : '';
            
                $repeat_x = isset( $recuring_data['repeat_x'] ) ? trim( $recuring_data['repeat_x'] ) : '';
                $duration_x = isset( $recuring_data['duration_x'] ) ? trim( $recuring_data['duration_x'] ) : 1;
                $repeat_end_type = isset( $recuring_data['repeat_end_type'] ) ? trim( $recuring_data['repeat_end_type'] ) : 0;
            
                $max_repeat = $repeat_end_type != 1 && isset( $recuring_data['max_repeat'] ) ? (int)$recuring_data['max_repeat'] : 0;
                $repeat_end = $repeat_end_type == 1 && isset( $recuring_data['repeat_end'] ) ? $recuring_data['repeat_end'] : '';
                                         
                if ( geodir_event_is_date( $end_date ) && strtotime( $end_date ) < strtotime( $start_date ) ) {
                    $end_date = $start_date;
                }
                
                $repeat_x = $repeat_x > 0 ? (int)$repeat_x : 1;
                $duration_x = $duration_x > 0 ? (int)$duration_x : 1;
                $max_repeat = $max_repeat > 0 ? (int)$max_repeat : 1;
                
                if ( $repeat_end_type == 1 && !geodir_event_is_date( $repeat_end ) ) {
                    $repeat_end = '';
                }
                
                if ( $repeat_type == 'custom' ) {
                    $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                } else {
                    // week days
                    $repeat_days = array();
                    if ( $repeat_type == 'week' || $repeat_type == 'month' ) {
                        $repeat_days = isset( $recuring_data['repeat_days'] ) ? $recuring_data['repeat_days'] : $repeat_days;
                    }
                    
                    // by week
                    $repeat_weeks = array();
                    if ( $repeat_type == 'month' ) {
                        $repeat_weeks = isset( $recuring_data['repeat_weeks'] ) ? $recuring_data['repeat_weeks'] : $repeat_weeks;
                    }
            
                    $event_recurring_dates = geodir_event_date_occurrences( $repeat_type, $start_date, $end_date, $repeat_x, $max_repeat, $repeat_end, $repeat_days, $repeat_weeks );
                }
            } else {
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
            }

            if ( empty( $recuring_data['all_day'] ) ) {
                if ( $repeat_type == 'custom' && $different_times ) {
                    $astarttimes = isset( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
                    $aendtimes = isset( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
                } else {
                    $starttimes = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                    $endtimes = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                }
            }
            
            $output = '';
            $output .= '<div class="geodir_event_schedule">';

            foreach( $event_recurring_dates as $key => $date ) {
                $geodir_num_dates++;
                
                if ( $repeat_type == 'custom' && $different_times ) {
                    if ( !empty( $astarttimes ) && isset( $astarttimes[$key] ) ) {
                        $starttimes = $astarttimes[$key];
                        $endtimes = $aendtimes[$key];
                    } else {
                        $starttimes = '';
                        $endtimes = '';
                    }
                }
                
                $duration = isset( $recuring_data['duration_x'] ) && (int)$recuring_data['duration_x'] > 0 ? (int)$recuring_data['duration_x'] : 1;
                $duration--;
                $enddate = date_i18n( 'Y-m-d', strtotime( $date . ' + ' . $duration . ' day' ) );
                
                // Hide past dates
                if ( $hide_past_dates && strtotime( $enddate ) < strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ) ) {
                    $geodir_num_dates--;
                    continue;
                }
                        
                $sdate = strtotime( $date . ' ' . $starttimes );
                $edate = strtotime( $enddate . ' ' . $endtimes );
                            
                $start_date = date_i18n( $geodir_date_time_format, $sdate );
                $end_date = date_i18n( $geodir_date_time_format, $edate );
                
                $same_day = false;
                $full_day = false;
                $same_datetime = false;
                
                if ( $starttimes == $endtimes && ( $starttimes == '' || $starttimes == '00:00:00' || $starttimes == '00:00' ) ) {
                    $full_day = true;
                }
                
                if ( $start_date == $end_date && $full_day ) {
                    $same_datetime = true;
                }

                $link_date = date_i18n( 'Y-m-d', $sdate );
                $title_date = date_i18n( $geodir_date_format, $sdate );
                if ( $full_day ) {
                    $start_date = $title_date;
                    $end_date = date_i18n( $geodir_date_format, $edate );
                }
                
                // recuring event title
                $recurring_event_title = $post->post_title . ' - ' . $title_date;
                $recurring_event_title = apply_filters( 'geodir_event_recurring_event_link', $recurring_event_title, $post->ID );
                
                // recuring event link
                $recurring_event_link = geodir_getlink( get_permalink( $post->ID ), array( 'gde' => $link_date ) );
                $recurring_event_link = esc_url( apply_filters( 'geodir_event_recurring_event_link', $recurring_event_link, $post->ID ) );
                
                $recurring_class = 'gde-recurr-link';
                $recurring_class_cont = 'gde-recurring-cont';
                if ( isset( $_REQUEST['gde'] ) && $_REQUEST['gde'] == $link_date ) {
                    $recurring_event_link = 'javascript:void(0);';
                    $recurring_class .= ' gde-recurr-act';
                    $recurring_class_cont .= ' gde-recurr-cont-act';
                }
                
                if ( !$same_datetime && !$full_day && date_i18n( 'Y-m-d', $sdate ) == date_i18n( 'Y-m-d', $edate ) ) {
                    $same_day = true;
                    
                    $start_date .= ' - ' . date_i18n( $geodir_time_format, $edate );
                }
                
                $output .= '<p class="' . $recurring_class_cont . '">';
                $output .= '<a class="' . $recurring_class . '" href="' . $recurring_event_link . '" title="' . esc_attr( $recurring_event_title ) . '">';
                $output .= '<span class="geodir_schedule_start"><i class="fa fa-caret-right"></i>' . $start_date . '</span>';
                if ( !$same_day && !$same_datetime ) {
                    $output .= '<br />';
                    $output .= '<span class="geodir_schedule_end"><i class="fa fa-caret-left"></i>' . $end_date . '</span>';
                }
                $output .= '</a>';
                $output .= '</p>';
            }
            $output .= '</div>';
            
            if ( !$geodir_num_dates > 0 ) {
                return;
            }
            
            $geodir_date_count = $geodir_num_dates > 1 ? __( 'Recurring Dates', 'geodirevents' ) : __( 'Date','geodirevents' );
        } else {
            $geodir_num_dates = 0;
            
            if ( isset( $recuring_data['recurring'] ) ) {
                $start_date = isset( $recuring_data['event_start'] ) ? $recuring_data['event_start'] : '';
                $end_date = isset( $recuring_data['event_end'] ) ? $recuring_data['event_end'] : $start_date;
                $all_day = isset( $recuring_data['all_day'] ) && !empty( $recuring_data['all_day'] ) ? true : false;
                $starttime = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                $endtime = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                $starttimes = isset( $recuring_data['starttimes'] ) && !empty( $recuring_data['starttimes'] ) ? $recuring_data['starttimes'] : array();
                $endtimes = isset( $recuring_data['endtimes'] ) && !empty( $recuring_data['endtimes'] ) ? $recuring_data['endtimes'] : array();
                
                if ( !geodir_event_is_date( $start_date ) && !empty( $event_recurring_dates ) ) {
                    $start_date = $event_recurring_dates[0];
                }
                            
                if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
                    $end_date = $start_date;
                }
                
                if ( $starttime == '' && !empty( $starttimes ) ) {
                    $starttime = $starttimes[0];
                    $endtime = $endtimes[0];
                }
                
                $same_day = false;
                $one_day = false;
                if ( $start_date == $end_date && $all_day ) {
                    $one_day = true;
                }

                if ( $all_day ) {
                    $start_datetime = strtotime( $start_date );
                    $end_datetime = strtotime( $end_date );
                    
                    $start_date = date_i18n( $geodir_date_format, $start_datetime );
                    $end_date = date_i18n( $geodir_date_format, $end_datetime );
                    if ( $start_date == $end_date ) {
                        $one_day = true;
                    }
                } else {
                    if ( $start_date == $end_date && $starttime == $endtime ) {
                        $end_date = date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . $starttime . ' +1 day' ) );
                        $one_day = false;
                    }
                    $start_datetime = strtotime( $start_date . ' ' . $starttime );
                    $end_datetime = strtotime( $end_date . ' ' . $endtime );
                    
                    $start_date = date_i18n( $geodir_date_time_format, $start_datetime );
                    $end_date = date_i18n( $geodir_date_time_format, $end_datetime );
                }
                $output = '<div class="geodir_event_schedule">';
                
                $title_start_date = date_i18n( 'Y-m-d H:i:s', $start_datetime ) . ' ' . date_i18n( 'T+H:i', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
                if ( !$one_day ) {
                    $title_start_date .=  ' - ' . date_i18n( 'Y-m-d H:i:s', $end_datetime ) . ' ' . date_i18n( 'T+H:i', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
                }
                                            
                $output .= '<p title="' . esc_attr( $title_start_date ) . '">';
   
                if ( !$one_day && date_i18n( 'Y-m-d', $start_datetime ) == date_i18n( 'Y-m-d', $end_datetime ) ) {
                    $same_day = true;
                    
                    $start_date .= ' - ' . date_i18n( $geodir_time_format, $end_datetime );
                }
                
                $output .= '<span class="geodir_schedule_start"><i class="fa fa-caret-right"></i>' . $start_date. '</span>';
                
                if ( !$same_day && !$one_day ) {
                    $output .= '<br />';
                    $output .= '<span class="geodir_schedule_end"><i class="fa fa-caret-left"></i>' . $end_date. '</span>';
                }
                $output .= '</p>';
                $output .= '</div>';
            } else { // older event dates
                $event_recurring_dates = explode( ',', $recuring_data['event_recurring_dates'] );
                $starttimes = isset( $recuring_data['starttime'] ) ? $recuring_data['starttime'] : '';
                $endtimes = isset( $recuring_data['endtime'] ) ? $recuring_data['endtime'] : '';
                
                $output = '';
                $output .= '<div class="geodir_event_schedule">';
                
                foreach( $event_recurring_dates as $key => $date ) {
                    $geodir_num_dates++;
                
                    if ( isset( $recuring_data['different_times'] ) && $recuring_data['different_times'] == '1' ) {
                        $starttimes = isset( $recuring_data['starttimes'][$key] ) ? $recuring_data['starttimes'][$key] : '';
                        $endtimes = isset( $recuring_data['endtimes'][$key] ) ? $recuring_data['endtimes'][$key] : '';
                    }
                    
                    $sdate = strtotime( $date . ' ' . $starttimes );
                    $edate = strtotime( $date . ' ' . $endtimes );
                    
                    if ( $starttimes > $endtimes ) {
                        $edate = strtotime( $date . ' ' . $endtimes . " +1 day" );
                    }
                    
                    // Hide past dates
                    if ( $hide_past_dates && strtotime( date_i18n( 'Y-m-d', $edate ) ) < strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ) ) {
                        $geodir_num_dates--;
                        continue;
                    }
                    
                    $start_date = date_i18n( $geodir_date_time_format, $sdate );
                    
                    $same_day = false;
                    if ( $sdate != $edate && date_i18n( 'Y-m-d', $sdate ) == date_i18n( 'Y-m-d', $edate ) ) {
                        $same_day = true;
                        
                        $start_date .= ' - ' . date_i18n( $geodir_time_format, $edate );
                    }
                
                    $output .= '<p>';
                    $output .= '<span class="geodir_schedule_start"><i class="fa fa-caret-right"></i>' . $start_date. '</span>';
                    if ( !$same_day && $sdate != $edate ) {
                        $output .= '<br />';
                        $output .= '<span class="geodir_schedule_end"><i class="fa fa-caret-left"></i>' . date_i18n( $geodir_date_time_format, $edate ). '</span>';
                    }
                    $output .= '</p>';
                }
                $output .= '</div>';
                
                if ( !$geodir_num_dates > 0 ) {
                    return;
                }
            }
            
            $geodir_date_count = $geodir_num_dates > 1 ? __( 'Dates', 'geodirevents' ) : __( 'Date','geodirevents' );
        } 
        
        $geodir_event_dates_display = $geodir_num_dates > 5 ? 'geodir_event_dates_display' : '';
                
        ob_start();
        echo '<div class="geodir-company_info ' . $geodir_event_dates_display . '">';
        
        if ( $geodir_num_dates == 1 ) {
            echo '<span class="geodir-event-dates"><i class="fa fa-calendar"></i>' . $geodir_date_count . ' : ';
        } else {
            echo '<span class="geodir-event-dates"><i class="fa fa-calendar"></i>' . $geodir_date_count . ' : ';
        }
        echo $output;
        echo '</span></div>';
        
        echo $datehtml = ob_get_clean();
    }
}


function geodir_event_show_business_fields_html() {
	global $post,$wpdb,$current_user,$post_info, $gd_session;
	
	$package_info = array();
	$package_info = geodir_post_package_info( $package_info , $post );
	
	if( !isset( $package_info->post_type ) || $package_info->post_type != 'gd_event' ) {
		return false;
	}
		
	$geodir_link_business = '';
	
	if ( isset( $_REQUEST['backandedit'] ) ) {
		$post = (object)$gd_session->get('listing');
		$geodir_link_business = isset($post->geodir_link_business) ? $post->geodir_link_business : '';
	} else if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
		$geodir_link_business = geodir_get_post_meta( $_REQUEST['pid'], 'geodir_link_business' );
	} else if ( isset( $post->geodir_link_business ) ) {
		$geodir_link_business = $post->geodir_link_business;
	} else if ( isset( $post_info->geodir_link_business ) ) {
		$geodir_link_business = $post_info->geodir_link_business;
	}
	
	if( $geodir_link_business == '' && isset( $post->ID ) ) {
		$geodir_link_business = geodir_get_post_meta( $post->ID, 'geodir_link_business' );
	}

	$geodir_link_business = apply_filters('geodir_link_business_id', $geodir_link_business, $package_info->post_type);
		
	if( isset( $package_info->link_business_pkg ) && $package_info->link_business_pkg  == '1' ) {
		
		if(!is_admin())
			echo '<h5>'.__('Businesses', 'geodirevents').'</h5>';?>
		<div id="geodir_link_business_row" class="geodir_form_row clearfix">
		  <label>
		  <?php _e( 'Link Business', 'geodirevents' );?>
		  </label>
		  <div class="geodir_link_business_chosen_div" style="width:60%;float:left;margin-bottom:7px">
			<input type="hidden" name="geodir_link_business_val" value="<?php echo $geodir_link_business;?>" />
			<select id="geodir_link_business" name="geodir_link_business" class="geodir_link_business_chosen" data-location_type="link_business"data-placeholder="<?php echo esc_attr( __( 'Search business&hellip;', 'geodirevents' ) );?>" data-ajaxchosen="1"  data-addsearchtermonnorecord="0" data-autoredirect="0">
			  <?php
				$selected = $geodir_link_business == '' || !$geodir_link_business > 0 ? 'selected="selected"' : '';
				$options = '<option ' . $selected . ' value=""> </option>';
				if( $geodir_link_business > 0 ) {
					$listing_info = get_post( $geodir_link_business );
					if( !empty( $listing_info ) ) {
						$options .= '<option selected="selected" value="' . $geodir_link_business . '">' . $listing_info->post_title . '</option>';
					}
				}
				echo $options;
			?>
			</select>
			<?php $geodir_link_business = wp_create_nonce( 'geodir_link_business_autofill_nonce' );?>
			<input type="hidden" name="geodir_link_business_nonce" value="<?php echo $geodir_link_business;?>">
		  </div>
		  <input type="button" id="geodir_link_business_autofill" class="geodir_button button-primary" value="<?php echo __('Fill in Business Details','geodirevents'); ?>" style="float:none;margin-left:30%;" />
		</div>
		<?php
	}
}


function geodir_add_search_fields($fields,$stype){

echo geodir_get_current_posttype();
	  if($stype == 'gd_event' )
	   $fields[]= array('field_type'=>'datepicker',
	                    'site_title'=>'Search By Date ',
	                    'htmlvar_name'=>'event',
	                    'data_type'=>'DATE',
	                    'field_icon' => 'fa fa-calendar'
	   );
	 return $fields;
}


function geodir_event_add_event_features() {
	global $post, $gd_session;
	
	$event_reg_desc = '';
	$event_reg_fees = '';
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if (!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
	
	if (isset($_REQUEST['backandedit']) &&  $_REQUEST['backandedit'] && $ses_listing = $gd_session->get('listing')) { 
		$post = $ses_listing;
		$event_reg_desc = isset($post['event_reg_desc']) ? $post['event_reg_desc'] : '';
		$event_reg_fees = isset($post['event_reg_fees']) ? $post['event_reg_fees'] : '';
	} else if( isset($_REQUEST['pid']) && $_REQUEST['pid']!='' && $post_info = geodir_get_post_info($_REQUEST['pid'])) { 
		$event_reg_desc = isset($post_info->event_reg_desc) ? $post_info->event_reg_desc : '';
		$event_reg_fees = isset($post_info->event_reg_fees) ? $post_info->event_reg_fees : '';
	}
	 
	if($event_reg_desc == '' && isset($post->ID))
		$event_reg_desc = geodir_get_post_meta( $post->ID, 'event_reg_desc');
		
	if($event_reg_fees == '' && isset($post->ID))
		$event_reg_fees = geodir_get_post_meta($post->ID, 'event_reg_fees');
			
	if (isset($package_info->reg_desc_pkg) && $package_info->reg_desc_pkg  == '1') { ?>
		<div id="geodir_event_reg_desc_row" class="geodir_form_row clearfix">
			<label><?php _e('How to Register', 'geodirevents');?></label>
			<?php
			$show_editor = geodir_get_option('geodir_tiny_editor_event_reg_on_add_listing');
			if (!empty($show_editor) && $show_editor=='yes') {
				$editor_settings = array('media_buttons'=>false, 'textarea_rows'=>10);
			?>
				<div class="editor" field_id="event_reg_desc" field_type="editor">
				<?php wp_editor( stripslashes($event_reg_desc), "event_reg_desc", $editor_settings ); ?>
				</div>
			<?php } else { ?>
				<textarea field_type="textarea" name="event_reg_desc" id="event_reg_desc" class="geodir_textarea" ><?php echo esc_attr(stripslashes($event_reg_desc)); ?></textarea>
			<?php } ?>
			<span class="geodir_message_note"><?php _e('Basic HTML tags are allowed', 'geodirevents');?></span>
			<span class="geodir_message_error"></span>
		</div>
		<?php
	}
	
	if (isset($package_info->reg_fees_pkg) && $package_info->reg_fees_pkg  == '1') {
		$currency = geodir_get_option('geodir_currency');
		$currency = $currency ? $currency : 'USD';
		
		$sym = geodir_get_option('geodir_currencysym');
		$sym = $sym ? $sym : '$';
		?>
		<div id="geodir_event_reg_fees_row" class="geodir_form_row clearfix">
			<label><?php _e('Registration Fees', 'geodirevents');?></label>
			<input type="text" field_type="text" name="event_reg_fees" id="event_reg_fees" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($event_reg_fees)); ?>"  />
			<span class="geodir_message_note"><?php echo wp_sprintf(__('Enter Registration Fees, in %s eg. : %s50', 'geodirevents'), $currency, $sym);?></span>
			<span class="geodir_message_error"></span>
		</div>
		<?php
	}
}

function geodir_event_before_description() {
	global $post;
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
		
	$event_reg_desc = ''; 
	$event_reg_fees = '';
	
	if(isset($package_info->reg_desc_pkg) && $package_info->reg_desc_pkg  == '1'){
		$event_reg_desc = isset($post->event_reg_desc) ? $post->event_reg_desc : '';
	 
		if($event_reg_desc == '' && isset($post->ID))
			$event_reg_desc = geodir_get_post_meta( $post->ID, 'event_reg_desc');
	}
	
	if(isset($package_info->reg_fees_pkg) && $package_info->reg_fees_pkg  == '1'){
		$event_reg_fees = isset($post->event_reg_fees) ? $post->event_reg_fees : '';
	 
		if($event_reg_desc == '' && isset($post->ID))
			$event_reg_fees = geodir_get_post_meta( $post->ID, 'event_reg_fees');

	}
	
	if($event_reg_desc != '' || $event_reg_fees != ''){
		
		echo '<div class="geodir-company_info field-group">';
		
		if($event_reg_desc != ''){
			echo '<h3>'.__('How to Register', 'geodirevents').'</h3>';
			echo wpautop(stripslashes($event_reg_desc));
		}
		
		if($event_reg_fees != ''){
			echo '<p class="" style="clear:both;"><span class="geodir-i-text" style="">' . __( 'Fees:', 'geodirevents' ) .' </span>'.$event_reg_fees.'</p>';
		}
		
		echo '</div>';
	
	}
	
}



function geodir_get_post_feature_events($query_args = array(), $layout='gridview_onehalf'){
	
	global $gridview_columns;
	
	$character_count = (isset($query_args['character_count']) && !empty($query_args['character_count'])) ? $query_args['character_count'] : '';
	
	$gd_event_type = $query_args['gd_event_type'];
	
	if($gd_event_type == 'all' || empty($gd_event_type) || (is_array($gd_event_type) && (in_array('all',$gd_event_type) || in_array('feature',$gd_event_type)))){
		
		$query_args['gd_event_type'] = 'feature';
		
	}else{
		return false;
	}
	

	$all_events = query_posts( $query_args );
	
	
	$all_events = query_posts( $query_args );
	
	if(!empty($all_events)){
	
		if(strstr($layout,'gridview')){
			
			$listing_view_exp = explode('_',$layout);
			
			$gridview_columns = $layout;
			
			$layout = $listing_view_exp[0];
			
		}
		
		
			
			$template = apply_filters( "geodir_template_part-feature-event-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		
		
		
		
			?>
			<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo apply_filters('geodir_widget_feature_event_title', __('Events', 'geodirevents'));?></h3>
			</div><?php
			include( $template );
			?> </div> <?php
	
	}
	
	wp_reset_query();
			

}
			
function geodir_get_post_past_events($query_args = array(), $layout='gridview_onehalf'){
	
	global $gridview_columns;
	
	$character_count = (isset($query_args['character_count']) && !empty($query_args['character_count'])) ? $query_args['character_count'] : '';

	$gd_event_type = $query_args['gd_event_type'];
	
	if($gd_event_type == 'all' || empty($gd_event_type) || (is_array($gd_event_type) && (in_array('all',$gd_event_type) || in_array('past',$gd_event_type)))){
		
		$query_args['gd_event_type'] = 'past';
		
	}else{
		return false;
	}
	
	$all_events = query_posts( $query_args );
	
	if(!empty($all_events)){
	
		if(strstr($layout,'gridview')){
			
			$listing_view_exp = explode('_',$layout);
			
			$gridview_columns = $layout;
			
			$layout = $listing_view_exp[0];
			
		}
		
	
			
			$template = apply_filters( "geodir_template_part-past-event-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
		
		
		
			?>
			<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo apply_filters('geodir_widget_past_event_title', __('Past Events', 'geodirevents'));?></h3>
			</div> <?php
			
			include( $template );
			?> </div> <?php
		
	}
	 
	wp_reset_query();

}

function geodir_get_post_widget_events( $query_args = array(), $layout = 'gridview_onehalf' ) {
	global $gridview_columns_widget, $geodir_event_widget_listview, $character_count;
	
	$character_count = ( isset( $query_args['character_count'] ) && $query_args['character_count'] != '' ) ? $query_args['character_count'] : 20;
	$listing_width = isset($query_args['listing_width']) && $query_args['listing_width'] > 0 ? $query_args['listing_width'] : '';
	$gd_event_type = $query_args['gd_event_type'];
	
	$geodir_widget_title = __( 'Related Events', 'geodirevents' );
	if (empty($query_args['event_related_id'])) {
		switch ( $gd_event_type ) {
			case 'feature' :
				$geodir_widget_title = __( 'Feature Events', 'geodirevents' );
			break;
			case 'past' :
				$geodir_widget_title = __( 'Past Events', 'geodirevents' );
			break;
			case 'upcoming' :
				$geodir_widget_title = __( 'Upcoming Events', 'geodirevents' );
			break;
		}
	}
	$geodir_widget_title = apply_filters( 'geodir_widget_past_event_title', $geodir_widget_title );
	
	$widget_events = geodir_event_get_widget_events( $query_args );

	if( !empty( $widget_events ) ) {
		if( strstr( $layout, 'gridview' ) ) {
			$listing_view_exp = explode( '_', $layout );
            $gridview_columns_widget = $layout;
			$layout = $listing_view_exp[0];
		} else if($layout == 'list') {
			$gridview_columns_widget = '';
		}
		
		$template = apply_filters( "geodir_event_template_widget_listview", WP_PLUGIN_DIR . '/geodir_event_manager/gdevents_widget_listview.php' );
				
		global $post;
		$current_post = $post;
		$geodir_event_widget_listview = true;
		ob_start();
		?>
		<div class="geodir_locations geodir_location_listing">
			<div class="locatin_list_heading clearfix">
				<h3><?php echo $geodir_widget_title;?></h3> 
			</div>
			<?php include( $template ); ?>
		</div>
		<?php
		$GLOBALS['post'] = $current_post;
		setup_postdata( $current_post );
		$geodir_event_widget_listview = false;	
		
		$content = ob_get_clean();
		return $content;	
	}
	return NULL;
}

function geodir_event_get_widget_events( $query_args, $count_only = false ) {
	global $wpdb, $plugin_prefix;
	$GLOBALS['gd_query_args'] = $query_args;
	$GLOBALS['gd_query_args_widgets'] = $query_args;
    $gd_query_args_widgets = $query_args;
	
	$post_type = 'gd_event';
	$table = $plugin_prefix . $post_type . '_detail';
	
	$fields = $wpdb->posts . ".*, " . $table . ".*, " . GEODIR_EVENT_SCHEDULES_TABLE . ".*";
	$fields = apply_filters( 'geodir_event_filter_widget_events_fields', $fields );
	
	$join = "INNER JOIN " . $table ." ON (" . $table .".post_id = " . $wpdb->posts . ".ID)";
	$join .= " INNER JOIN " . GEODIR_EVENT_SCHEDULES_TABLE ." ON (" . GEODIR_EVENT_SCHEDULES_TABLE .".event_id = " . $wpdb->posts . ".ID)";
	
	########### WPML ###########
    $lang_code = '';
    if (geodir_wpml_is_post_type_translated($post_type)) {
        global $sitepress;
        $lang_code = ICL_LANGUAGE_CODE;
        if ($lang_code) {
            $join .= " JOIN " . $wpdb->prefix . "icl_translations icl_t ON icl_t.element_id = " . $wpdb->posts . ".ID";
        }
    }
    ########### WPML ###########
	
	$join = apply_filters( 'geodir_event_filter_widget_events_join', $join );
	
	$post_status = is_super_admin() ? " OR " . $wpdb->posts . ".post_status = 'private'" : '';
    $where = " AND ( " . $wpdb->posts . ".post_status = 'publish' " . $post_status . " ) AND " . $wpdb->posts . ".post_type = '" . $post_type . "'";
	
	########### WPML ###########
    if (geodir_wpml_is_post_type_translated($post_type)) {
        if ($lang_code) {
            $where .= " AND icl_t.language_code = '$lang_code' AND icl_t.element_type = 'post_$post_type' ";
        }
    }
    ########### WPML ###########
	
	$where = apply_filters( 'geodir_event_filter_widget_events_where', $where );
	$where = $where != '' ? " WHERE 1=1 " . $where : '';
						
	if ($count_only) {
		$sql = "SELECT COUNT(DISTINCT(CONCAT(" . $wpdb->posts . ".ID, '-', " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date))) AS total FROM " . $wpdb->posts . "
			" . $join . "
			" . $where;
		$rows = (int)$wpdb->get_var($sql);
	} else {	
		$groupby = " GROUP BY CONCAT(" . $wpdb->posts . ".ID, '-', " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date)";
		$groupby = apply_filters( 'geodir_event_filter_widget_events_groupby', $groupby );
	
		$orderby = geodir_event_widget_events_get_order( $query_args );
		$orderby = apply_filters( 'geodir_event_filter_widget_events_orderby', $orderby );
		$orderby .= $wpdb->posts . ".post_title ASC";
		$orderby = $orderby != '' ? " ORDER BY " . $orderby : '';
			
		$limit = !empty( $query_args['posts_per_page'] ) ? $query_args['posts_per_page'] : 5;
		$limit = apply_filters( 'geodir_event_filter_widget_events_limit', $limit );
		
		$page = !empty($query_args['pageno']) ? absint($query_args['pageno']) : 1;
		if ( !$page )
			$page = 1;
		
		$limit = (int)$limit > 0 ? " LIMIT " . absint( ( $page - 1 ) * (int)$limit ) . ", " . (int)$limit : "";
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS " . $fields . " FROM " . $wpdb->posts . "
			" . $join . "
			" . $where . "
			" . $groupby . "
			" . $orderby . "
			" . $limit;	
		$rows = $wpdb->get_results($sql);
	}
	
	unset($GLOBALS['gd_query_args_widgets']);
    unset($gd_query_args_widgets);
		
	return $rows;
}
function geodir_event_widget_events_get_order( $query_args ) {
	global $wpdb, $plugin_prefix, $gd_query_args, $table;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $wpdb->posts . ".post_date DESC, ";
	}
	
	$table = $plugin_prefix . 'gd_event_detail';
	$current_date = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	
	$sort_by = !empty( $query_args['order_by'] ) ? $query_args['order_by'] : '';
	
	switch ( $sort_by ) {
		case 'latest':
		case 'newest':
			$orderby = $wpdb->posts . ".post_date DESC, ";
		break;
		case 'high_review':
			$orderby = $table . ".rating_count DESC, " . $table . ".overall_rating DESC, ";
		break;
		case 'high_rating':
			$orderby = $table . ".overall_rating DESC, ";
		break;
		case 'random':
			$orderby = "RAND(), ";
		break;
		case 'upcoming':
			$orderby = "(CASE WHEN DATEDIFF(DATE(" . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date), '" . $current_date . "') < 0 THEN 1 ELSE 0 END), ABS(DATEDIFF(DATE(" . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date), '" . $current_date . "')) ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC, ";
		break;
		case 'rsvp_count':
			$orderby = $table . ".rsvp_count DESC, ";
		break;
		default:
			$orderby = $wpdb->posts . ".post_title ASC, ";
		break;
	}
	
	if ( $orderby != 'upcoming' ) {
		$orderby .= "(CASE WHEN DATEDIFF(DATE(" . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date), '" . $current_date . "') < 0 THEN 1 ELSE 0 END), ABS(DATEDIFF(DATE(" . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date), '" . $current_date . "')) ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC, ";
	}
	
	return $orderby;
}

add_filter( 'geodir_event_filter_widget_events_orderby', 'geodir_event_function_widget_events_orderby' );
function geodir_event_function_widget_events_orderby( $orderby ) {
	global $gd_query_args, $table;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $orderby;
	}
	
	$table = GEODIR_EVENT_DETAIL_TABLE;
	
	$orderby .= GEODIR_EVENT_SCHEDULES_TABLE . ".start_date ASC, " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_time ASC, ";
	
	return $orderby;
}

add_filter( 'geodir_event_filter_widget_events_where', 'geodir_event_function_widget_events_where' );
function geodir_event_function_widget_events_where( $where ) {
	global $wpdb, $plugin_prefix, $gd_query_args;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $where;
	}
	
	$table = $plugin_prefix . 'gd_event_detail';
	
	$date_now = date_i18n( 'Y-m-d' );
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['event_related_id'] ) ) {
		$where .= " AND " . $table . ".geodir_link_business = " . (int)$gd_query_args['event_related_id'];
	}
	
	if ( !empty( $gd_query_args ) && isset( $gd_query_args['gd_event_type'] ) ) {
		if ( $gd_query_args['gd_event_type'] == 'feature' ) {
			$where .= " AND ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date >= '" . $date_now . "' OR ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date <= '" . $date_now . "' AND " . GEODIR_EVENT_SCHEDULES_TABLE . ".end_date >= '" . $date_now . "' ) ) ";
		}
		
		if ( $gd_query_args['gd_event_type'] == 'past' ) {
			$where .= " AND " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date < '" . $date_now . "' ";
		}
		
		if ( $gd_query_args['gd_event_type'] == 'upcoming' ) {
			$where .= " AND ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date >= '" . $date_now . "' OR ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date <= '" . $date_now . "' AND " . GEODIR_EVENT_SCHEDULES_TABLE . ".end_date >= '" . $date_now . "' ) ) ";
		}
		
		if ( $gd_query_args['gd_event_type'] == 'today' ) {
			$where .= " AND ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date LIKE '" . $date_now . "%%' OR ( " . GEODIR_EVENT_SCHEDULES_TABLE . ".start_date <= '" . $date_now . "' AND " . GEODIR_EVENT_SCHEDULES_TABLE . ".end_date >= '" . $date_now . "' ) ) ";
		}		
	}
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['gd_location'] ) && function_exists( 'geodir_default_location_where' ) ) {
		$where = geodir_default_location_where( $where,$table );
	}
	
	return $where;
}

add_filter( 'geodir_event_filter_widget_events_limit', 'geodir_event_function_widget_events_limit' );
function geodir_event_function_widget_events_limit( $limit ) {
	global $wpdb, $plugin_prefix, $gd_query_args;
	
	if ( empty( $gd_query_args ) || empty( $gd_query_args['is_geodir_loop'] ) ) {
		return $limit;
	}
	
	if ( !empty( $gd_query_args ) && !empty( $gd_query_args['posts_per_page'] ) ) {
		$limit = (int)$gd_query_args['posts_per_page'];
	}
	
	return $limit;
}

function geodir_calender_event_details_after_post_title() {
	global $post, $wpdb, $condition_date, $geodir_post_type, $geodir_is_link_business, $geodir_event_widget_listview;

	$is_post_type_event = !empty( $post ) && isset( $post->post_type ) && $post->post_type == 'gd_event' && isset( $post->start_date ) && $post->start_date ? true : false;
	
	if ( ( ( is_main_query() && $geodir_post_type == 'gd_event' ) || get_query_var( 'geodir_event_listing_filter' ) || ( isset( $geodir_is_link_business ) && $geodir_is_link_business ) || ( isset( $geodir_event_widget_listview ) && $geodir_event_widget_listview ) ) && $is_post_type_event ) {
		global $geodir_date_time_format, $geodir_date_format, $geodir_time_format;
	?>
	<p style="clear:both;" class="geodir-event-meta">
		<span class="geodir-i-datepicker"><i class="fa fa-calendar-o"></i> <?php _e( 'Date:', 'geodirevents' ); ?></span>
		<?php echo date_i18n( $geodir_date_format, strtotime( $post->start_date ) );

        if($post->end_date!='' && $post->end_date!='0000-00-00' && date_i18n( $geodir_date_format, strtotime( $post->end_date) )!= date_i18n( $geodir_date_format, strtotime( $post->start_date ) )){
            echo ' - '. date_i18n( $geodir_date_format, strtotime( $post->end_date ) );
        }
        ?>
	</p>
	<?php if ( ( isset( $post->all_day ) && $post->all_day != '1' ) && ( ( isset( $post->start_time ) && !empty( $post->start_time ) ) || ( isset( $post->end_time ) && !empty( $post->end_time ) ) ) ) {  ?>
	<p style="clear:both;" class="geodir-event-meta">
		<span class="geodir-i-time"><i class="fa fa-clock-o"></i> <?php _e('Time:', 'geodirevents'); ?> </span>
		<?php 
		if ( isset( $post->start_time ) && !empty( $post->start_time ) ) {
			echo date_i18n( $geodir_time_format, strtotime( $post->start_time ) ). ' ' ;
		}	
		if ( isset( $post->end_time ) && !empty( $post->end_time ) ) {
			echo __( ' to', 'geodirevents' ).' '. date_i18n( $geodir_time_format, strtotime( $post->end_time ) );
		}											
		?>
	</p>
		<?php
		}
	}			
}

function geodir_event_get_link_business( $listings = array() ) {
	global $post, $gridview_columns, $geodir_is_link_business, $character_count, $gd_session;
	
	if( empty ( $listings ) ) {
		return NULL;
	}

	$template = apply_filters( "geodir_template_part-link-business-listview", WP_PLUGIN_DIR . '/geodir_event_manager/link-business-listview.php', 'gd_event' );
	
	$event_type = geodir_get_option( 'geodir_event_linked_event_type' );
		
	$view_all_params = array();
	if ( $event_type == 'today' || $event_type == 'past' || $event_type == 'upcoming' ) {
		$view_all_params['etype'] = $event_type;
	}
	$venue_name = $post->post_title;
	$view_all_params['venue'] = $post->ID . '-' . $venue_name;
	$view_all_url = geodir_getlink( get_post_type_archive_link( 'gd_event' ), $view_all_params );
	$view_all_url = apply_filters( 'geodir_event_linked_event_link', $view_all_url, $post->ID );
	
	$gd_listing_view_set = $gd_session->get('gd_listing_view') ? true : false;
	$gd_listing_view = '';
	if ( $gd_listing_view_set ) {
		$gd_listing_view = $gd_session->get('gd_listing_view');
		$gd_session->un_set('gd_listing_view');
	}
	
	ob_start();
	?>
	<div class="geodir_locations geodir_location_listing">
		<a class="geodir-viewall geodir-viewall-linked clearfix" href="<?php echo esc_url( $view_all_url );?>" title="<?php echo esc_attr( sprintf( __('View all events in "%s"', 'geodirevents' ), $venue_name ) );?>"><?php echo sprintf( __('View all events in "%s"', 'geodirevents' ), $venue_name ); ?></a>
	<?php
	if ( !$character_count ) {
		$character_count = 20;
	}
	$linked_listings = $listings;
	$related_posts = true;
	$current_post = $post;
	$geodir_is_link_business = true;
	include( $template );
	$GLOBALS['post'] = $current_post;
	setup_postdata( $current_post );
	$geodir_is_link_business = false;
	
	if ( $gd_listing_view_set ) {
		$gd_session->set('gd_listing_view', $gd_listing_view);
	}
	?>
	</div>
	<?php
	wp_reset_query();
	
	$content = ob_get_clean();
	
	return $content;
}

function geodir_event_filter_title_meta_vars($settings) {
    foreach($settings as $index => $setting) {
        if (!empty($setting['id']) && $setting['id'] == 'geodir_meta_vars' && !empty($setting['type']) && $setting['type']== 'sectionstart') {
            $settings[$index]['desc'] = $setting['desc'] . ', %%event_type_archive%%, %%event_start_date%%, %%event_end_date%%, %%event_start_time%%, %%event_end_time%%, %%event_start_to_end_date%%, %%event_start_to_end_time%%';
        }
    }
    return $settings;
}

function geodir_event_search_calendar_day_filter_title($filters = array()) {
    global $geodir_date_format;
    
    $event_calendar = !empty($_REQUEST['event_calendar']) ? sanitize_text_field($_REQUEST['event_calendar']) : '';
    
    if ($event_calendar) {
        $title = '<label class="gd-adv-search-label gd-adv-search-date gd-adv-search-event_calendar" data-name="event_calendar">' . date_i18n($geodir_date_format, strtotime($event_calendar)) . '</label>';
        $filters[] = apply_filters('geodir_event_search_calendar_day_filter_title', $title, $filters);
    }
    
    return $filters;
}

function geodir_event_filter_title_variables_vars($title, $location_array, $gd_page, $sep) {    
    if (strpos($title, '%%event_') !== false) {
        $event_title_vars = array();
        
        $event_type = '';
        if (!empty($_REQUEST['etype'])) {
            $event_filter = $_REQUEST['etype'];
            
            if ($event_filter == 'past') {
                $event_type = __('Past','geodirevents');
            } else if ($event_filter == 'today') {
                $event_type = __('Today','geodirevents');
            } else if ($event_filter == 'upcoming') {
                $event_type = __('Upcoming','geodirevents');
            }
        }
        $event_title_vars['%%event_type_archive%%'] = $event_type;
        
        $event_start_date = '';
        $event_end_date = '';
        $event_start_to_end_date = '';
        $event_start_time = '';
        $event_end_time = '';
        $event_start_to_end_time = '';
        
        if (is_single()) {
            global $post, $preview, $geodir_date_format, $geodir_time_format;
        
            if (!empty($post) && $post->post_type == 'gd_event') {
                $schedule_dates = geodir_event_get_schedule_dates($post->ID, $preview, '');

                if (!empty($schedule_dates)) {
                    $today = strtotime(date_i18n('Y-m-d', current_time('timestamp')));
                    
                    foreach ($schedule_dates as $date) {
                        $event_start_date = date_i18n($geodir_date_format, strtotime($date['start_date']));
                        $event_end_date = date_i18n($geodir_date_format, strtotime($date['end_date']));
                        $event_start_time = date_i18n($geodir_time_format, strtotime($date['start_time']));
                        $event_end_time = date_i18n($geodir_time_format, strtotime($date['end_time']));
                        $event_start_to_end_date = $event_start_date;
                        if ($event_start_date !== $event_end_date) {
                            $event_start_to_end_date .= ' - ' . $event_end_date;
                        }
                        $event_start_to_end_time = $event_start_time . ' ' . __('to', 'geodirevents') . ' ' . $event_end_time;

                        if (isset($_REQUEST['gde']) && $_REQUEST['gde'] == $date['start_date']) {
                            break;
                        } else if (!isset($_REQUEST['gde']) && ((strtotime($date['start_date']) >= $today) || (!empty($date['end_date']) && strtotime($date['end_date']) >= $today))) {
                            break;
                        }
                    }
                }
            }
        }

        $event_title_vars['%%event_start_date%%'] = $event_start_date;
        $event_title_vars['%%event_end_date%%'] = $event_end_date;
        $event_title_vars['%%event_start_to_end_date%%'] = $event_start_to_end_date;
        $event_title_vars['%%event_start_time%%'] = $event_start_time;
        $event_title_vars['%%event_end_time%%'] = $event_end_time;
        $event_title_vars['%%event_start_to_end_time%%'] = $event_start_to_end_time;
        
        $title = str_replace(array_keys($event_title_vars), array_values($event_title_vars), $title);
    }        
        
    return $title;
}

function geodir_event_display_event_type_filter( $post_type ) {
	if ( $post_type != 'gd_event' ) {
		return;
	}

	$event_types = apply_filters( 'geodir_event_filter_options', array(
		'all' => __( 'All Events', 'geodirevents' ),
		'today' => __( 'Today', 'geodirevents' ),
		'upcoming' => __( 'Upcoming', 'geodirevents' ),
		'past' => __( 'Past', 'geodirevents' ),
	) );

	if ( empty( $event_types ) ) {
		return;
	}

	$event_type 	= ! empty( $_REQUEST['etype'] ) ? sanitize_text_field( $_REQUEST['etype'] ) : geodir_get_option( 'event_defalt_filter' );
	$current_url 	= str_replace( '#038;', '&', geodir_curPageURL() );
	$current_url	= remove_query_arg( array( 'etype' ), $current_url );

	$options = '';
	foreach ( $event_types as $value => $label ) {
		$url = add_query_arg( array( 'etype' => $value ), $current_url );
		$url = apply_filters( 'geodir_event_type_filter_url', $url, $value );

		$options .= '<option ' . selected( $value, $event_type, false ) . ' value="' . esc_url( $url ) . '">' . $label . '</option>';
	}

	$content = '<div class="geodir-event-filter">';
	$content .= '<select name="etype" id="etype" class="geodir-select" onchange="javascript:window.location=this.value;">' . $options . '</select>';
	$content .= '</div>';

	echo $content;
}
add_action( 'geodir_extra_loop_actions', 'geodir_event_display_event_type_filter', 10, 1 );