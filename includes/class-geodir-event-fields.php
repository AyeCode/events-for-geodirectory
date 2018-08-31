<?php
/**
 * Plugin custom fields class
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
 * GeoDir_Event_Fields class
 *
 * @class       GeoDir_Event_Fields
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 */
class GeoDir_Event_Fields {

    public function __construct() {
    }

	public static function init() {
	
		if ( ! ( ! empty( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'cpt' && isset( $_REQUEST['prev_supports_events'] ) ) ) {
			add_filter( 'geodir_default_custom_fields', array( __CLASS__, 'default_custom_fields' ), 10, 3 );
		}
		add_filter( 'geodir_custom_fields_predefined', array( __CLASS__, 'predefined_fields' ), 10, 2 );

		// Admin cpt cf settings
		add_filter( 'geodir_cfa_is_active_event', array( __CLASS__, 'cfa_is_active' ), 10, 4 );
		add_filter( 'geodir_cfa_for_admin_use_event', array( __CLASS__, 'cfa_for_admin_use' ), 10, 4 );
		add_filter( 'geodir_cfa_is_required_event', array( __CLASS__, 'cfa_is_required' ), 10, 4 );
		add_filter( 'geodir_cfa_can_delete_field', array( __CLASS__, 'cfa_can_delete_field' ), 10, 2 );

		// Add event form
		add_filter( 'geodir_before_custom_form_field_recurring', array( __CLASS__, 'input_recurring' ), 10, 3 );
		add_filter( 'geodir_before_custom_form_field_event_dates', array( __CLASS__, 'input_event_dates' ), 10, 3 );

		// Process value before save
		add_filter( 'geodir_custom_field_value_event', array( __CLASS__, 'sanitize_event_data' ), 10, 6 );

		// Save event data
		add_filter( 'geodir_save_post_data', array( __CLASS__, 'save_event_data' ), 10, 4 );

		// Get input value
		add_filter( 'geodir_get_cf_value', array( __CLASS__, 'event_dates_cf_value' ), 10, 2 );

		// Output event fields
		add_filter( 'geodir_custom_field_output_event', array( __CLASS__, 'cf_event' ), 10, 3 );
		add_filter( 'geodir_custom_field_output_event_var_event_dates', array( __CLASS__, 'output_event_dates' ), 10, 4 ); // Schedules
		add_filter( 'geodir_custom_field_output_event_loc_listing', array( __CLASS__, 'output_event_date' ), 10, 3 ); // Single date
	}

	public static function default_custom_fields( $fields, $post_type, $package_id ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$package = is_array( $package_id ) && ! empty( $package_id ) ? $package_id : ( $package_id !== '' ? array( $package_id ) : '');

			$fields[] = array(
				'post_type' => $post_type,
				'data_type' => 'VARCHAR',
				'field_type' => 'event',
				'admin_title' => __( 'Recurring Event?', 'geodirevents' ),
				'frontend_desc' => __( 'Tick "Yes" for recurring event.', 'geodirevents' ),
				'frontend_title' => __( 'Recurring Event?', 'geodirevents' ),
				'htmlvar_name' => 'recurring',
				'sort_order' => '2',
				'default_value' => '0',
				'option_values' => __( 'Yes', 'geodirevents' ) . '/1,' . __( 'No', 'geodirevents' ) . '/0',
				'is_default' => '1',
				'is_active' => '1',
				'is_required' => '1',
				'show_in' => '',
				'show_on_pkg' => $package,
				'required_msg' => __( 'Choose a type for an event!', 'geodirevents' ),
				'clabels' => __( 'Recurring Event?', 'geodirevents' ),
				'field_icon' => 'fas fa-redo',
				'extra' => array(),
				'single_use' => true
			);
			$fields[] = array(
				'post_type' => $post_type,
				'data_type' => 'TEXT',
				'field_type' => 'event',
				'admin_title' => __( 'Event Dates', 'geodirevents' ),
				'frontend_desc' => '',
				'frontend_title' => __( 'Event Dates', 'geodirevents' ),
				'htmlvar_name' => 'event_dates',
				'sort_order' => '2',
				'default_value' => '',
				'option_values' => '',
				'is_default' => '1',
				'is_active' => '1',
				'is_required' => '1',
				'show_in' => '[detail],[listing],[mapbubble]',
				'show_on_pkg' => $package,
				'required_msg' => __( 'Choose dates for an event!', 'geodirevents' ),
				'clabels' => __( 'Event Dates', 'geodirevents' ),
				'field_icon' => 'fas fa-calendar-alt',
				'extra' => array(),
				'single_use' => true
			);
		}

		return $fields;
	}

	public static function predefined_fields( $custom_fields, $post_type ) {
		if ( $post_type != 'gd_event' ) {
			return $custom_fields;
		}

		// Event Fees
		$custom_fields['event_reg_fees'] = array(
			'field_type'  => 'text',
			'class'       => 'gd-event-reg-fees',
			'icon'        => 'fas fa-dollar-sign',
			'name'        => __( 'Event Registration Fees', 'geodirevents' ),
			'description' => __( 'Adds a input for a event registration fees.', 'geodirevents' ),
			'single_use'  => 'event_reg_fees',
			'defaults'    => array(
				'data_type'          => 'FLOAT',
				'decimal_point'      => '2',
				'admin_title'        => 'Event Registration Fees',
				'frontend_title'     => 'Event Registration Fees',
				'frontend_desc'      => 'Enter the event registration fees in $ (no currency symbol)',
				'htmlvar_name'       => 'event_reg_fees',
				'is_active'          => true,
				'for_admin_use'      => false,
				'default_value'      => '',
				'show_in'            => '[moreinfo]',
				'is_required'        => false,
				'validation_pattern' => '\d+(\.\d{2})?',
				'validation_msg'     => 'Please enter number and decimal only ie: 100.50',
				'required_msg'       => '',
				'field_icon'         => 'fas fa-dollar-sign',
				'css_class'          => '',
				'cat_sort'           => true,
				'cat_filter'         => true,
				'single_use'         => true,
				'extra_fields'       => array(
					'is_price'                  => 1,
					'thousand_separator'        => 'comma',
					'decimal_separator'         => 'period',
					'decimal_display'           => 'if',
					'currency_symbol'           => '$',
					'currency_symbol_placement' => 'left'
				)
			)
		);
		// Event Registration Info
		$custom_fields['event_reg_desc'] = array(
			'field_type'  => 'html',
			'class'       => 'gd-event-reg-desc',
			'icon'        => 'fas fa-ticket-alt',
			'name'        => __( 'Event Registration Info', 'geodirevents' ),
			'description' => __( 'Adds a input for a event registration description.', 'geodirevents' ),
			'single_use'  => 'event_reg_desc',
			'defaults'    => array(
				'data_type'          => 'TEXT',
				'admin_title'        => 'How to register',
				'frontend_title'     => 'Event Registration Info',
				'frontend_desc'      => 'Enter details to register to this event.',
				'htmlvar_name'       => 'event_reg_desc',
				'is_active'          => true,
				'for_admin_use'      => false,
				'default_value'      => '',
				'show_in'            => '[moreinfo]',
				'is_required'        => false,
				'option_values'      => '',
				'validation_pattern' => '',
				'validation_msg'     => '',
				'required_msg'       => '',
				'field_icon'         => 'fas fa-ticket-alt',
				'css_class'          => '',
				'cat_sort'           => false,
				'cat_filter'         => false,
				'single_use'         => true,
			)
		);

		return $custom_fields;
	}

	public static function cfa_is_active( $content, $_id, $cf, $field ) {
		if ( $field->htmlvar_name == 'recurring' || $field->htmlvar_name == 'event_dates' ) {
			$content = '<input id="is_active" name="is_active" value="1" type="hidden">';
		}

		return $content;
	}

	public static function cfa_for_admin_use( $content, $_id, $cf, $field ) {
		if ( $field->htmlvar_name == 'recurring' || $field->htmlvar_name == 'event_dates' ) {
			$content = '<input id="for_admin_use" name="for_admin_use" value="0" type="hidden">';
		}

		return $content;
	}

	public static function cfa_is_required( $content, $_id, $cf, $field ) {
		if ( $field->htmlvar_name == 'recurring' || $field->htmlvar_name == 'event_dates' ) {
			$content = '<input id="is_required" name="is_required" value="1" type="hidden">';
		}

		return $content;
	}

	public static function cfa_can_delete_field( $delete, $field ) {
		if ( ! empty( $field ) && GeoDir_Post_types::supports( $field->post_type, 'events' ) ) {
			if ( $field->htmlvar_name == 'recurring' || $field->htmlvar_name == 'event_dates' ) {
				$delete = false;
			}
		}
		return $delete;
	}

	public static function input_recurring( $post_type, $package_id, $field ) {
        if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return;
		}

		if ( ! geodir_event_is_recurring_active() ) { // Recurring is disabled
			return;
		}
		$value 					= geodir_get_cf_value( $field );
		$field_title 			= ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : '';
		$field_desc 			= ! empty( $field['desc'] ) ? __( $field['desc'], 'geodirectory' ) : '';
		$required_msg 			= ! empty( $field['required_msg'] ) ? __( $field['required_msg'], 'geodirectory' ) : '';
		$htmlvar_name 			= $field['htmlvar_name'];

		ob_start();
        ?>
        <div id="<?php echo $htmlvar_name; ?>_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field">
            <label><?php echo $field_title . ' <span>*</span>'; ?></label>
			<span class="gd-radios"><input name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>" <?php checked( (int) $value, 1 ); ?> value="1" class="gd-checkbox" field_type="radio" type="radio" /><?php echo __( 'Yes', 'geodirevents' ); ?></span>
			<span class="gd-radios"><input name="<?php echo $htmlvar_name; ?>" id="<?php echo $htmlvar_name; ?>" <?php checked( (int) $value, 0 ); ?> value="0" class="gd-checkbox" field_type="radio" type="radio" /><?php echo __( 'No', 'geodirevents' ); ?></span>
            <span class="geodir_message_note"><?php echo $field_desc; ?></span>
            <span class="geodir_message_error"><?php echo $required_msg; ?></span>
        </div>
        <?php
        $html = ob_get_clean();

		echo $html;
	}

	public static function input_event_dates( $post_type, $package_id, $field ) {
		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return;
		}

		$htmlvar_name 			= $field['htmlvar_name'];
		$event_data 			= geodir_get_cf_value( $field );
		$event_data 			= maybe_unserialize( $event_data );

		$is_recurring_active	= geodir_event_is_recurring_active();
		$format 				= geodir_event_field_date_format();
		$default_start_date 	= date_i18n( $format );

		$recurring 				= ! empty( $event_data['recurring'] ) ? true : false;
		$start_date 			= ! empty( $event_data['start_date'] ) ? $event_data['start_date'] : '';
		$end_date 				= ! empty( $event_data['end_date'] ) ? $event_data['end_date'] : '';
		$all_day 				= ! empty( $event_data['all_day'] ) ? true : false;
		$start_time 			= ! $all_day && ! empty( $event_data['start_time'] ) ? $event_data['start_time'] : '10:00';
		$end_time 				= ! $all_day && ! empty( $event_data['end_time'] ) ? $event_data['end_time'] : '18:00';
		$repeat_x 				= 1;
		$repeat_type 			= '';
		$duration_x 			= 1;
		$repeat_end_type 		= 0;
		$max_repeat 			= 2;
		$repeat_end 			= '';
		$recurring_dates 		= '';
		$different_times 		= false;
		$start_times 			= array();
		$end_times 				= array();
		$repeat_days 			= array();
		$repeat_weeks 			= array();

		$recurring_dates_list	= '';
		$custom_dates_list		= '';
		$different_times_list	= '';

		if ( $recurring && $is_recurring_active ) {	
			$duration_x 		= ! empty( $event_data['duration_x'] ) && absint( $event_data['duration_x'] ) > 0 ? absint( $event_data['duration_x'] ) : 1;
			$repeat_type 		= isset( $event_data['repeat_type'] ) && in_array( $event_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $event_data['repeat_type'] : 'custom'; // day, week, month, year, custom
			$repeat_x 			= ! empty( $event_data['repeat_x'] ) && absint( $event_data['repeat_x'] ) > 0 ? absint( $event_data['repeat_x'] ) : 1;
			if ( ( $repeat_type == 'week' || $repeat_type == 'month' ) && ! empty( $event_data['repeat_days'] ) ) {
				$repeat_days = is_array( $event_data['repeat_days'] ) ? $event_data['repeat_days'] : explode( ',', $event_data['repeat_days'] );
			}
			if ( $repeat_type == 'month' && ! empty( $event_data['repeat_weeks'] ) ) {
				$repeat_weeks = is_array( $event_data['repeat_weeks'] ) ? $event_data['repeat_weeks'] : explode( ',', $event_data['repeat_weeks'] );
			}
			$repeat_end_type 	= isset( $event_data['repeat_end_type'] ) ? absint( $event_data['repeat_end_type'] ) : '';
			$max_repeat 		= ! empty( $event_data['max_repeat'] ) && absint( $event_data['max_repeat'] ) > 0 ? absint( $event_data['max_repeat'] ) : 2;
			if ( ! empty( $event_data['repeat_end'] ) ) {
				$repeat_end = date_i18n( $format, strtotime( $event_data['repeat_end'] ) );
			}
			if ( $repeat_type == 'custom' ) {
				$different_times 	= ! empty( $event_data['different_times'] ) ? true : false;
				$recurring_dates 	= ! empty( $event_data['recurring_dates'] ) && is_array( $event_data['recurring_dates'] ) ? $event_data['recurring_dates'] : array();
				$start_times 		= ! empty( $event_data['start_times'] ) ? $event_data['start_times'] : array();
				$end_times 			= ! empty( $event_data['end_times'] ) ? $event_data['end_times'] : array();

				$custom_dates_list 	= array();
				if ( ! empty( $recurring_dates ) ) {
					foreach ( $recurring_dates as $key => $date ) {
						$recurring_dates_list .= '<span>' . date_i18n( $format, strtotime( $date ) ) . '</span>';
						if ( $different_times ) {
							$start_time_selected	= ! empty( $start_times[ $key ] ) ? $start_times[$key] : ( ! empty( $start_time ) ? $start_time : '10:00' );
							$end_time_selected 	= ! empty( $end_times[ $key ] ) ? $end_times[$key] : ( ! empty( $end_time ) ? $end_time : '18:00' );
							$different_times_list 	.= '<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">' . $date . '</label><div class="event-multiple-dateto-inner"><select id="event_start_times" name="event_dates[start_times][]" class="geodir_textfield geodir-select geodir-w110">' . geodir_event_time_options( $start_time_selected ) .  '</select></div><label class="event-multiple-end"> ' . __( 'to', 'geodirevents' ) . ' </label><div class="event-multiple-dateto-inner"><select id="event_end_times" name="event_dates[end_times][]" class="geodir_textfield geodir-select geodir-w110">' . geodir_event_time_options( $end_time_selected ) .  '</select></div></div>';
						}
						$custom_dates_list[] = date_i18n( 'm/d/Y', strtotime( $date ) );
					}
				}

				$recurring_dates 	= ! empty( $recurring_dates ) ? implode( ',', $recurring_dates ) : '';
				$custom_dates_list 	= ! empty( $custom_dates_list ) ? implode( ',', $custom_dates_list ) : '';
			}
		} else {
			if ( empty( $start_date ) ) {
				$start_date = $default_start_date;
			} elseif ( ! geodir_event_is_date( $start_date ) ) {
				$start_date = $default_start_date;
			}
			if ( empty( $end_date ) || ( ! empty( $end_date ) && ! geodir_event_is_date( $end_date ) ) ) {
				$end_date = '';
			}
		}
		if ( ! empty( $start_date ) ) {
			$start_date = date_i18n( $format, strtotime( $start_date ) );
		}
		if ( ! empty( $end_date ) ) {
			$end_date = date_i18n( $format, strtotime( $end_date ) );
		}
		
		$recurring_class 			= $recurring ? '' : 'geodir-none';
		$custom_recurring_class 	= $recurring_dates_list != '' ? '' : 'geodir-none';
		$show_time_class 			= $all_day ? 'geodir-none' : '';
		$show_times_class 			= ! empty( $different_times ) ? '' : 'geodir-none';

		ob_start();
        ?>
        <div id="geodir_event_start_date_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field">
            <label for="event_start_date"><?php echo __( 'Event start date', 'geodirevents' ) . ' <span>*</span>'; ?></label>
			<input type="text" class="geodir_textfield geodir-w200" name="<?php echo $htmlvar_name; ?>[start_date]" id="event_start_date" value="<?php echo $start_date; ?>" field_type="text">
            <span class="geodir_message_error"><?php _e( 'Choose a start date of the event.', 'geodirevents' );?></span>
        </div>
		<div id="geodir_event_end_date_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field">
            <label for="event_end_date"><?php echo __( 'Event end date', 'geodirevents' ); ?></label>
			<input type="text" class="geodir_textfield geodir-w200" name="<?php echo $htmlvar_name; ?>[end_date]" id="event_end_date" value="<?php echo $end_date; ?>" field_type="text">
        </div>
		<?php if ( $is_recurring_active ) { ?>
		<div id="geodir_event_duration_x_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_duration_x"><?php echo __( 'Event duration (days)', 'geodirevents' ); ?></label>
			<input type="number" class="geodir_textfield geodir-w200" name="<?php echo $htmlvar_name; ?>[duration_x]" id="event_duration_x" value="<?php echo $duration_x; ?>" min="0" lang="EN" field_type="text">
        </div>
		<div id="geodir_event_repeat_type_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_repeat_type"><?php echo __( 'Repeats', 'geodirevents' ) . ' <span>*</span>'; ?></label>
			<select id="event_repeat_type" name="<?php echo $htmlvar_name; ?>[repeat_type]" class="geodir_textfield geodir-select geodir-w200" data-placeholder="<?php echo esc_attr_e( 'Select recurring type', 'geodirevents' );?>">
				<option value="" <?php selected( $repeat_type, '' );?>><?php _e( 'Select recurring type', 'geodirevents' );?></option>
				<option value="day" <?php selected( $repeat_type, 'day' );?> data-title="<?php echo esc_attr( __( 'days', 'geodirevents' ) );?>"><?php _e( 'Daily', 'geodirevents' );?></option>
				<option value="week" <?php selected( $repeat_type, 'week' );?> data-title="<?php echo esc_attr( __( 'weeks', 'geodirevents' ) );?>"><?php _e( 'Weekly', 'geodirevents' );?></option>
				<option value="month" <?php selected( $repeat_type, 'month' );?> data-title="<?php echo esc_attr( __( 'months', 'geodirevents' ) );?>"><?php _e( 'Monthly', 'geodirevents' );?></option>
				<option value="year" <?php selected( $repeat_type, 'year' );?> data-title="<?php echo esc_attr( __( 'years', 'geodirevents' ) );?>"><?php _e( 'Yearly', 'geodirevents' );?></option>
				<option value="custom" <?php selected( $repeat_type, 'custom' );?>><?php _e( 'Custom', 'geodirevents' );?></option>
			</select>
			<span class="geodir_message_error"><?php _e( 'Please select recurring type', 'geodirevents' );?></span>
        </div>
		<div id="geodir_event_repeat_x_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_repeat_x"><?php echo __( 'Repeat every', 'geodirevents' ); ?></label>
			<select id="event_repeat_x" name="<?php echo $htmlvar_name; ?>[repeat_x]" class="geodir_textfield geodir-select geodir-w200">
				<?php for ( $i = 1; $i <= 30; $i++ ) { ?>
				<option value="<?php echo $i;?>" <?php selected( $repeat_x, $i ); ?>><?php echo $i;?></option>
				<?php } ?>
			</select>
			<span class="geodir_message_error"><?php _e( 'Please select recurring interval', 'geodirevents' );?></span>
        </div>
		<div id="geodir_event_repeat_days_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_repeat_days"><?php echo __( 'Repeat on', 'geodirevents' ); ?></label>
			<select id="event_repeat_days" name="<?php echo $htmlvar_name; ?>[repeat_days][]" class="geodir_textfield geodir-select" multiple="multiple" data-placeholder="<?php echo esc_attr_e( 'Select days', 'geodirevents' );?>">
				<option value="1" <?php selected( true, in_array( 1, $repeat_days ) ); ?>><?php _e( 'Mon' ); ?></option>
				<option value="2" <?php selected( true, in_array( 2, $repeat_days ) ); ?>><?php _e( 'Tue' ); ?></option>
				<option value="3" <?php selected( true, in_array( 3, $repeat_days ) ); ?>><?php _e( 'Wed' ); ?></option>
				<option value="4" <?php selected( true, in_array( 4, $repeat_days ) ); ?>><?php _e( 'Thu' ); ?></option>
				<option value="5" <?php selected( true, in_array( 5, $repeat_days ) ); ?>><?php _e( 'Fri' ); ?></option>
				<option value="6" <?php selected( true, in_array( 6, $repeat_days ) ); ?>><?php _e( 'Sat' ); ?></option>
				<option value="0" <?php selected( true, in_array( 0, $repeat_days ) ); ?>><?php _e( 'Sun' ); ?></option>
			</select>
        </div>
		<div id="geodir_event_repeat_weeks_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_repeat_weeks"><?php echo __( 'Repeat by', 'geodirevents' ); ?></label>
			<select id="event_repeat_weeks" name="<?php echo $htmlvar_name; ?>[repeat_weeks][]" class="geodir_textfield geodir-select" multiple="multiple" data-placeholder="<?php echo esc_attr_e( 'Select weeks', 'geodirevents' );?>">
				<option value="1" <?php selected( true, in_array( 1, $repeat_weeks ) ); ?>><?php _e( '1st week', 'geodirevents' ); ?></option>
				<option value="2" <?php selected( true, in_array( 2, $repeat_weeks ) ); ?>><?php _e( '2nd week', 'geodirevents' ); ?></option>
				<option value="3" <?php selected( true, in_array( 3, $repeat_weeks ) ); ?>><?php _e( '3rd week', 'geodirevents' ); ?></option>
				<option value="4" <?php selected( true, in_array( 4, $repeat_weeks ) ); ?>><?php _e( '4th week', 'geodirevents' ); ?></option>
				<option value="5" <?php selected( true, in_array( 5, $repeat_weeks ) ); ?>><?php _e( '5th week', 'geodirevents' ); ?></option>
			</select>
        </div>
		<div id="geodir_event_recurring_ends_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label><?php echo __( 'Recurring ends', 'geodirevents' ); ?></label>
			<div class="geodir-inline-fields">
				<input type="radio" class="gd-checkbox" name="<?php echo $htmlvar_name; ?>[repeat_end_type]" id="event_repeat_end_type_m" value="0" <?php checked( $repeat_end_type, 0 );?> /><label for="event_repeat_end_type_m"><?php _e( 'After', 'geodirevents' );?></label>&nbsp;<input type="number" value="<?php echo $max_repeat;?>" class="geodir_textfield geodir-w110" id="event_max_repeat" name="<?php echo $htmlvar_name; ?>[max_repeat]" lang="EN">&nbsp;<label for="event_repeat_end_type_m"><?php _e( 'occurrences', 'geodirevents' );?></label>&nbsp;&nbsp;<input type="radio" class="gd-checkbox" name="<?php echo $htmlvar_name; ?>[repeat_end_type]" id="event_repeat_end_type_u" value="1" <?php checked( $repeat_end_type, 1 );?> /><label for="event_repeat_end_type_u"><?php _e( 'On', 'geodirevents' );?></label> <input type="text" value="<?php echo $repeat_end;?>" class="geodir_textfield geodir-w200" id="event_repeat_end" name="<?php echo $htmlvar_name; ?>[repeat_end]" />
			</div>
			<span class="geodir_message_note"><?php _e( 'Recurring event duration.', 'geodirevents' );?></span>
        </div>
		<div id="geodir_event_custom_recurring_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
			<label><?php echo __( 'Event Date(s)', 'geodirevents' ); ?></label>
			<div id="event_yui_calendar" class="yui-skin-sam yui-t2">
				<div class="fullitem">
					<div id="geodir_event_selected_dates_row_c" class="yui-panel-container shadow">
						<div id="geodir_event_selected_dates_row" class="popup yui-module yui-overlay yui-panel" style="<?php echo $custom_recurring_class; ?>">
							<div id="geodir_event_selected_dates_row_h" class="hd">
								<?php _e( 'Selected Dates', 'geodirevents' );?>
							</div>
							<div class="bd">
								<div id="geodir_event_selected_dates"><?php echo $recurring_dates_list;?></div>
							</div>
							<div class="ft"></div>
						</div>
						<div class="underlay"></div>
					</div>
					<div class="yui-calcontainer multi" id="geodir_event_multi_dates_cal">
						<div class="yui-calcontainer groupcal first-of-type" id="geodir_event_multi_dates_cal_0"></div>
						<div class="yui-calcontainer groupcal last-of-type" id="geodir_event_multi_dates_cal_1"></div>
					</div>
					<input name="<?php echo $htmlvar_name; ?>[recurring_dates]" id="event_recurring_dates" value="<?php echo $recurring_dates; ?>" type="hidden">
					<span style="display:none!important;height:0;width:0" id="geodir_event_default_dates"><?php echo $custom_dates_list; ?></span>
					<span style="display:none!important;height:0;width:0" id="geodir_event_start_time_options"><?php echo geodir_event_time_options( ( ! empty( $start_time ) ? $start_time : '10:00' ) ); ?></span>
					<span style="display:none!important;height:0;width:0" id="geodir_event_end_time_options"><?php echo geodir_event_time_options( ( ! empty( $end_time ) ? $end_time : '18:00' ) ); ?></span>
				</div>
			</div>
			<span class="geodir_message_note"><?php _e( 'Click on each day your event will be held. You may choose more than one day. Selected dates appear in blue and can be unselected by clicking on them.', 'geodirevents' ); ?></span>
			<span class="geodir_message_error" style="display:none;"><?php _e( 'Please select at least one event date.', 'geodirevents' );?></span>
		</div>
		<?php } ?>
		<div id="geodir_event_all_day_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field">
            <label for="event_all_day_chk"><?php echo __( 'All day', 'geodirevents' ); ?></label>
            <input type="hidden" name="<?php echo $htmlvar_name; ?>[all_day]" id="event_all_day" value="<?php echo (int)$all_day; ?>"/>
            <input value="1" id="event_all_day_chk" class="gd-checkbox" field_type="checkbox" type="checkbox" <?php checked( $all_day, 1 ); ?> onchange="if(this.checked){jQuery('#event_all_day').val('1');} else{ jQuery('#event_all_day').val('0');}" />
			<span class="geodir_message_note"><?php _e( 'Tick to set event for all day.', 'geodirevents' ); ?></span>
        </div>
		<div id="geodir_event_time_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $show_time_class; ?>">
            <label for="event_start_time"><?php echo __( 'Event Time', 'geodirevents' ); ?></label>
			<div class="geodir-inline-fields">
				<label for="event_start_time" class="lbl-event-start-time"><?php _e( 'Starts at', 'geodirevents' ); ?></label>
				<select id="event_start_time" name="<?php echo $htmlvar_name; ?>[start_time]" class="geodir_textfield geodir-select geodir-w110">
					<?php echo geodir_event_time_options( $start_time ); ?>
				</select>
				<label for="event_end_time" class="lbl-event-end-time"><?php _e( 'Ends at', 'geodirevents' ); ?></label>
				<select id="event_end_time" name="<?php echo $htmlvar_name; ?>[end_time]" class="geodir_textfield geodir-select geodir-w110">
					<?php echo geodir_event_time_options( $end_time ); ?>
				</select>
			</div>
        </div>
		<?php if ( $is_recurring_active ) { ?>
		<div id="geodir_event_different_times_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
            <label for="event_different_times_chk"><?php echo __( 'Different Event Times?', 'geodirevents' ); ?></label>
            <input type="hidden" name="<?php echo $htmlvar_name; ?>[different_times]" id="event_different_times" value="<?php echo (int)$different_times; ?>"/>
            <input value="1" id="event_different_times_chk" class="gd-checkbox" field_type="checkbox" type="checkbox" <?php checked( $different_times, 1 ); ?> onchange="if(this.checked){jQuery('#event_different_times').val('1');} else{ jQuery('#event_different_times').val('0');}" />
			<span class="geodir_message_note"><?php _e( 'Tick to set separate start and end times for each date.', 'geodirevents' ); ?></span>
        </div>
		<div id="geodir_event_times_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $show_times_class; ?>">
			<label></label>
			<div class="show_different_times_div"><?php echo $different_times_list; ?></div>
		</div>
		<?php } ?>
        <?php
        $html = ob_get_clean();

		echo $html;
	}

	public static function save_event_data( $postarr, $gd_post, $post, $update ) {
		if ( ! empty( $post->post_type ) && GeoDir_Post_types::supports( $post->post_type, 'events' ) ) {
			if ( isset( $postarr['event_dates'] ) ) {
				if ( ! geodir_event_is_recurring_active() ) {
					$postarr['recurring'] = false;
				}
				GeoDir_Event_Schedules::save_schedules( $postarr['event_dates'], $post->ID );
			}
		}

		return $postarr;
	}

	public static function sanitize_event_data( $value, $gd_post, $cf, $post_id, $post, $update ) {
		if ( empty( $cf->htmlvar_name ) ) {
			return $value;
		}

		if ( $cf->htmlvar_name == 'recurring' ) {
			if ( ! geodir_event_is_recurring_active() ) {
				$value = false;
			}
		} elseif ( $cf->htmlvar_name == 'event_dates' ) {
			if ( is_array( $value ) ) {
				$data 				= $value;
				$format 			= geodir_event_field_date_format();
				$default_start_date = date_i18n( 'Y-m-d' );

				if ( !empty( $data['start_date'] ) ) {
					$data['start_date'] = sanitize_text_field( $data['start_date'] );
					if ( $format != 'Y-m-d' ) {
						$data['start_date'] = geodir_event_date_to_ymd( $data['start_date'], $format );
					}
				}
				if ( !empty( $data['end_date'] ) ) {
					$data['end_date'] = sanitize_text_field( $data['end_date'] );
					if ( $format != 'Y-m-d' ) {
						$data['end_date'] = geodir_event_date_to_ymd( $data['end_date'], $format );
					}
				}
				if ( !empty( $data['repeat_end'] ) ) {
					$data['repeat_end'] = sanitize_text_field( $data['repeat_end'] );
					if ( $format != 'Y-m-d' ) {
						$data['repeat_end'] = geodir_event_date_to_ymd( $data['repeat_end'], $format );
					}
				}

				$recurring 				= ! empty( $gd_post['recurring'] ) && geodir_event_is_recurring_active() ? true : false;
				$start_date 			= ! empty( $data['start_date'] ) ? $data['start_date'] : '';
				$end_date 				= ! empty( $data['end_date'] ) ? $data['end_date'] : '';
				$all_day 				= ! empty( $data['all_day'] ) ? true : false;
				$start_time 			= ! $all_day && ! empty( $data['start_time'] ) ? $data['start_time'] : '';
				$end_time 				= ! $all_day && ! empty( $data['end_time'] ) ? $data['end_time'] : '';
				$repeat_days			= array();
				$repeat_weeks			= array();

				if ( $recurring ) {
					$repeat_type 		= isset( $data['repeat_type'] ) && in_array( $data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $data['repeat_type'] : 'custom'; // day, week, month, year, custom
					$different_times 	= !empty( $data['different_times'] ) ? true : false;
					$start_times 		= $different_times && ! $all_day && isset( $data['start_times'] ) ? self::parse_array( $data['start_times'] ) : array();
					$end_times 			= $different_times && ! $all_day && isset( $data['end_times'] ) && !empty( $data['end_times'] ) ? self::parse_array( $data['end_times'] ) : array();

					// week days
					if ( $repeat_type == 'week' || $repeat_type == 'month' ) {
						$repeat_days = isset( $data['repeat_days'] ) ? self::parse_array( $data['repeat_days'] ) : $repeat_days;
					}

					// by week
					if ( $repeat_type == 'month' ) {
						$repeat_weeks = isset( $data['repeat_weeks'] ) ? self::parse_array( $data['repeat_weeks'] ) : $repeat_weeks;
					}

					if ( $repeat_type == 'custom' ) {
						if ( ! geodir_event_is_date( $start_date ) ) {
							$start_date = $default_start_date;
						}

						$recurring_dates = isset( $data['recurring_dates'] ) ? $data['recurring_dates'] : '';
						$recurring_dates = geodir_event_parse_dates( $recurring_dates );
						if ( empty( $recurring_dates ) ) {
							$recurring_dates = array( $start_date );
						}

						if ( $different_times == 1 ) {
							$start_time 	= '';
							$end_time 		= '';
						}

						$start_date 		= '';
						$end_date 			= '';
						$duration_x 		= 1;
						$repeat_x 			= 1;
						$repeat_end_type 	= 0;
						$max_repeat 		= 1;
						$repeat_end 		= '';
					} else {
						$repeat_x 			= isset( $data['repeat_x'] ) ? sanitize_text_field( $data['repeat_x'] ) : '';
						$duration_x 		= isset( $data['duration_x'] ) ? sanitize_text_field( $data['duration_x'] ) : 1;
						$repeat_end_type 	= isset( $data['repeat_end_type'] ) ? sanitize_text_field( $data['repeat_end_type'] ) : 0;
						$max_repeat 		= $repeat_end_type != 1 && isset( $data['max_repeat'] ) ? (int)$data['max_repeat'] : 1;
						$repeat_end 		= $repeat_end_type == 1 && isset( $data['repeat_end'] ) ? sanitize_text_field( $data['repeat_end'] ) : '';			
						$repeat_x 			= $repeat_x > 0 ? (int)$repeat_x : 1;
						$duration_x 		= $duration_x > 0 ? (int)$duration_x : 1;
						$max_repeat 		= $max_repeat > 0 ? (int)$max_repeat : 1;
						
						if ( $repeat_end_type == 1 && ! geodir_event_is_date( $repeat_end ) ) {
							$repeat_end 	= '';
						}	
						
						if ( ! geodir_event_is_date( $start_date ) ) {
							$start_date 	= $default_start_date;
						}
						$end_date 			= '';
						$recurring_dates	= array();
					}
				} else {
					if ( ! geodir_event_is_date( $start_date ) ) {
						$start_date = $default_start_date;
					}
							
					if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
						$end_date = $start_date;
					}
					
					$duration_x			= 1;
					$repeat_type		= '';
					$repeat_x			= '';
					$repeat_end_type	= '';
					$max_repeat			= '';
					$repeat_end			= '';
					$recurring_dates	= '';
					$different_times	= false;
					$start_times		= '';
					$end_times			= '';
				}

				$event_data = array();
				$event_data['recurring'] 		= $recurring;
				$event_data['start_date'] 		= $start_date;
				$event_data['end_date'] 		= $end_date;
				$event_data['all_day'] 			= $all_day;
				$event_data['start_time'] 		= $start_time;
				$event_data['end_time'] 		= $end_time;
				$event_data['duration_x'] 		= $duration_x;
				$event_data['repeat_type'] 		= $repeat_type;
				$event_data['repeat_x'] 		= $repeat_x;
				$event_data['repeat_end_type'] 	= $repeat_end_type;
				$event_data['max_repeat'] 		= $max_repeat;
				$event_data['repeat_end'] 		= $repeat_end;
				$event_data['recurring_dates'] 	= $recurring_dates;
				$event_data['different_times'] 	= $different_times;
				$event_data['start_times'] 		= $start_times;
				$event_data['end_times'] 		= $end_times;
				$event_data['repeat_days'] 		= $repeat_days;
				$event_data['repeat_weeks'] 	= $repeat_weeks;

				$value = maybe_serialize( $event_data );
			}
		}

		return $value;
	}
	
	public static function event_dates_cf_value( $value, $cf ) {
		global $gd_post;

		$field_name = ! empty( $cf['name'] ) ? $cf['name'] : '';

		if ( $field_name != 'event_dates' ) {
			return $value;
		}

		if ( ! ( ! empty( $gd_post->ID ) && ! empty( $gd_post->post_type ) && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) ) {
			return $value;
		}

		$event_data = maybe_unserialize( $value );
		$event_data = maybe_unserialize( $event_data ); // includes\post_functions.php#296

		if ( isset( $gd_post->recurring ) ) {
			$recurring = ! empty( $gd_post->recurring ) ? true : false;
		} elseif ( !empty( $event_data ) && isset( $event_data['recurring'] ) ) {
			$recurring = ! empty( $event_data['recurring'] ) ? true : false;
		} else {
			$recurring = false;
		}

		if ( $recurring && ! geodir_event_is_recurring_active() ) {
			$recurring = false;
		}

		if ( ! is_array( $event_data ) ) {
			$event_data = array();
		}

		$event_data['recurring'] = $recurring;

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

		return apply_filters( 'geodir_event_dates_cf_value	', $event_data, $value, $cf, $gd_post );
	}

	public static function cf_event( $html, $location, $cf, $p = '' ) {
		// check we have the post value
		if ( is_numeric( $p ) ) {
			$gd_post = geodir_get_post_info( $p );
		} else {
			global $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return $html;
		}

		if ( ! is_array( $cf ) && $cf != '' ) {
			$cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );
		}

		if ( empty( $cf['htmlvar_name'] ) ) {
			return $html;
		}

		$html_var = $cf['htmlvar_name'];

		// Check if there is a location specific filter.
		if ( has_filter( "geodir_custom_field_output_event_loc_{$location}" ) ) {
			/**
			 * Filter the event field html by location.
			 *
			 * @param string $html The html to filter.
			 * @param array $cf The custom field array.
			 * @since 2.0.0
			 */
			$html = apply_filters( "geodir_custom_field_output_event_loc_{$location}", $html, $cf, $gd_post );
		}

		// Check if there is a custom field specific filter.
		if ( has_filter( "geodir_custom_field_output_event_var_{$html_var}" ) ) {
			/**
			 * Filter the event field  html by individual custom field.
			 *
			 * @param string $html The html to filter.
			 * @param string $location The location to output the html.
			 * @param array $cf The custom field array.
			 * @since 2.0.0
			 */
			$html = apply_filters( "geodir_custom_field_output_event_var_{$html_var}", $html, $location, $cf, $gd_post );
		}

		// Check if there is a custom field key specific filter.
		if ( has_filter( "geodir_custom_field_output_event_key_{$cf['field_type_key']}" ) ) {
			/**
			 * Filter the event field html by field type key.
			 *
			 * @param string $html The html to filter.
			 * @param string $location The location to output the html.
			 * @param array $cf The custom field array.
			 * @since 2.0.0
			 */
			$html = apply_filters( "geodir_custom_field_output_event_key_{$cf['field_type_key']}", $html, $location, $cf, $gd_post );
		}

		return $html;
	}
	
	public static function output_event_dates( $html, $location, $cf, $the_post = array() ) {
		if ( empty( $the_post ) || empty( $cf ) || $location == 'listing' ) {
			return $html;
		}

		$htmlvar_name = $cf['htmlvar_name'];

		if ( $htmlvar_name == 'event_dates' && empty( $html ) && ! empty( $the_post->{$htmlvar_name} ) ) {
			if ( $location == 'mapbubble' ) {
				$event_type = geodir_get_option( 'event_map_popup_dates', 'upcoming' );
				$count		= geodir_get_option( 'event_map_popup_count' );
			} else {
				$event_type = geodir_get_option( 'event_hide_past_dates' ) ? 'upcoming' : 'all';
				$count		= 0;
			}

			$schedules 		= GeoDir_Event_Schedules::get_schedules( $the_post->ID, $event_type, $count );
			$schedules_html = GeoDir_Event_Schedules::get_schedules_html( $schedules, ! empty( $the_post->recurring ) );

			if ( ! empty( $schedules_html ) ) {
				$field_label = _n( 'Date', 'Dates', count( $schedules ), 'geodirevents' );
				$field_icon = geodir_field_icon_proccess( $cf );
				if ( strpos( $field_icon, 'http' ) !== false ) {
					$field_icon_af = '';
				} elseif ( $field_icon == '' ) {
					$field_icon_af = '<i class="fas fa-calendar-alt"></i>';
				} else {
					$field_icon_af = $field_icon;
					$field_icon = '';
				}

				$date_class = $cf['css_class'];
				$date_class .= ' geodir-edate-' . $cf['css_class'];
				$date_class .= count( $schedules ) > 1 ? ' geodir-schedules-meta' : ' geodir-schedule-meta';

				$html = '<div class="geodir_post_meta geodir-field-' . $htmlvar_name . ' ' . trim( $date_class ) . '" style="clear:both;"><span class="geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
				if ( $field_label != '' ) {
					$html .= $field_label . ': ';
				}
				$html .= '</span>' . $schedules_html . '</div>';
			} else {
				$html = '';
			}
		}

		return $html;
	}

	public static function output_event_date( $html, $cf, $the_post = array() ) {
		global $post;

		if ( empty( $the_post ) || empty( $cf ) ) {
			return $html;
		}

		$htmlvar_name = $cf['htmlvar_name'];

		if ( $htmlvar_name == 'event_dates' && ! empty( $the_post->{$htmlvar_name} ) ) {
			$event_post 	= isset( $the_post->start_date ) ? $the_post : $post;
			$schedule		= array();
			if ( ! empty( $event_post->start_date ) ) {
				$schedule 		= $event_post;
			} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $event_post->ID, 'upcoming', 1 ) ) ) {
				$schedule		= $schedules[0];
			} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $event_post->ID, '', 1 ) ) ) {
				$schedule		= $schedules[0];
			}

			if ( ! empty( $schedule->start_date ) ) {
				$row = array(
					'schedule_id' => ( ! empty( $schedule->schedule_id ) ? $schedule->schedule_id : $event_post->ID ),
					'event_id' => ( ! empty( $schedule->event_id ) ? $schedule->event_id : $event_post->ID ),
					'start_date' => $schedule->start_date,
					'end_date' => ( ! empty( $schedule->end_date ) && $schedule->end_date != '0000-00-00' ? $schedule->end_date : $start_date ),
					'start_time' => ( ! empty( $schedule->start_time ) ? $schedule->start_time : '00:00:00' ),
					'end_time' => ( ! empty( $schedule->end_time ) ? $schedule->end_time : '00:00:00' ),
					'all_day' => ( ! empty( $schedule->all_day ) ? true : false )
				);

				$value = GeoDir_Event_Schedules::get_schedules_html( array( (object)$schedule ), false );

				$field_label = __( 'Date', 'geodirevents' );
				$field_icon = geodir_field_icon_proccess( $cf );
				if ( strpos( $field_icon, 'http' ) !== false ) {
					$field_icon_af = '';
				} elseif ( $field_icon == '' ) {
					$field_icon_af = '<i class="fas fa-calendar-alt"></i>';
				} else {
					$field_icon_af = $field_icon;
					$field_icon = '';
				}

				$date_class = $cf['css_class'];
				$date_class .= 'geodir-schedule-meta geodir-edate-' . $cf['css_class'];
				$field_label = '';

				$html = '<div class="geodir_post_meta geodir-field-' . $htmlvar_name . ' ' . trim( $date_class ) . '" style="clear:both;"><span class="geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
				if ( $field_label != '' ) {
					$html .= $field_label . ': ';
				}
				$html .= '</span>' . $value . '</div>';
			} else {
				$html = '<div style="display:none"></div>';
			}
		}

		return $html;
	}

	public static function parse_array( $value ) {
		if ( ! is_array( $value ) && strlen( $value ) < 1 ) {
			return array();
		}

		if ( ! is_array( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( ! empty( $value ) ) {
			$value = array_map( 'trim', $value );
		}
		
		return $value;
	}
}