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
		add_filter( 'geodir_default_custom_fields', array( __CLASS__, 'default_custom_fields' ), 10, 3 );
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

		// Set default sort.
		add_filter( 'geodir_get_posts_default_sort_by', array( __CLASS__, 'set_event_default_sort' ), 10, 3 );

		// Get input value
		add_filter( 'geodir_get_cf_value', array( __CLASS__, 'event_dates_cf_value' ), 10, 2 );

		// Output event fields
		add_filter( 'geodir_custom_field_output_event', array( __CLASS__, 'cf_event' ), 10, 5 );
		add_filter( 'geodir_custom_field_output_event_var_event_dates', array( __CLASS__, 'output_event_dates' ), 10, 5 ); // Schedules
		add_filter( 'geodir_custom_field_output_event_loc_listing', array( __CLASS__, 'output_event_date' ), 10, 4 ); // Single date

		// Search
		add_filter( 'geodir_search_fields_setting_allow_var_event_dates', '__return_true', 10, 3 );
		add_filter( 'geodir_advance_search_field_in_main_search_bar', array( __CLASS__, 'field_in_main_search_bar' ), 10, 3 );
		add_filter( 'geodir_search_cpt_search_setting_field', array( __CLASS__, 'cpt_search_setting_field' ), 10, 2 );
		add_filter( 'geodir_search_output_to_main_event', array( __CLASS__, 'search_bar_output_event' ), 10, 3 );
		add_filter( 'geodir_search_filter_field_output_event', array( __CLASS__, 'search_output_event' ), 10, 3 );
		add_action( 'geodir_before_search_form', array( __CLASS__, 'search_form_inputs' ), 10, 1 );

		// Post meta custom fields
		add_action( 'geodir_post_meta_standard_fields', array( __CLASS__, 'post_meta_standard_fields' ), 10, 2 );

		$schedule_keys = array( 'event_start_date', 'event_start_time', 'event_start_date_time', 'event_end_date', 'event_end_time', 'event_end_date_time', 'event_first_start_date', 'event_first_start_time', 'event_first_start_date_time', 'event_first_end_date', 'event_first_end_time', 'event_first_end_date_time', 'event_last_start_date', 'event_last_start_time', 'event_last_start_date_time', 'event_last_end_date', 'event_last_end_time', 'event_last_end_date_time' );

		foreach ( $schedule_keys as $schedule_key ) {
			add_filter( 'geodir_custom_field_output_custom_var_' . $schedule_key, array( __CLASS__, 'custom_field_output_event_date_time' ), 10, 5 );
		}

		// Post badge
		add_filter( 'geodir_badge_conditions', array( __CLASS__, 'post_badge_conditions' ), 20, 1 );
		add_filter( 'geodir_post_badge_check_match_found', array( __CLASS__, 'post_badge_check_match_found' ), 20, 3 );
		add_filter( 'geodir_dynamic_content_check_match_found', array( __CLASS__, 'post_badge_check_match_found' ), 20, 3 );

		// Elementor custom fields.
		add_filter( 'geodir_elementor_tag_text_fields', array( __CLASS__, 'elementor_tag_text_fields' ), 20, 2 );
		add_filter( 'geodir_elementor_tag_text_render_value', array( __CLASS__, 'elementor_tag_text_render_value' ), 20, 3 );
	}

	public static function event_custom_fields( $post_type, $package_id ) {
		$package = is_array( $package_id ) && ! empty( $package_id ) ? $package_id : ( $package_id !== '' ? array( $package_id ) : '');

		$fields = array();
		$fields[] = array(
			'post_type' => $post_type,
			'data_type' => 'VARCHAR',
			'field_type' => 'event',
			'admin_title' => __( 'Recurring Event?', 'geodirevents' ),
			'frontend_desc' => __( 'Tick "Yes" for recurring event.', 'geodirevents' ),
			'frontend_title' => __( 'Recurring Event?', 'geodirevents' ),
			'htmlvar_name' => 'recurring',
			'sort_order' => '1',
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
			'single_use' => true,
			'cat_sort' => true
		);

		return $fields;
	}

	public static function default_custom_fields( $fields, $post_type, $package_id ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) && ! ( ! empty( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'cpt' && isset( $_REQUEST['prev_supports_events'] ) ) ) {
			$event_fields = self::event_custom_fields( $post_type, $package_id );

			if ( ! empty( $event_fields ) ) {
				foreach ( $event_fields as $key => $field ) {
					$fields[] = $field;
				}
			}
		}

		return $fields;
	}

	public static function predefined_fields( $custom_fields, $post_type ) {
		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
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

		// Event Status
		$custom_fields['event_status'] = array(
			'field_type'  => 'select',
			'class'       => 'gd-event-status',
			'icon'        => 'fas fa-exclamation-circle',
			'name'        => __( 'Event Disruption', 'geodirevents' ),
			'description' => __( 'Adds a input for a event status in-case of disruption.', 'geodirevents' ),
			'single_use'  => 'event_status',
			'defaults'    => array(
				'data_type'          => 'VARCHAR',
				'admin_title'        => __('Event Disruption','geodirevents'),
				'frontend_title'     => __('Event Disruption','geodirevents'),
				'frontend_desc'      => __("If your event has been disrupted please set its status here. If the event has been postponed to a later date, but the date isn't known yet, set this to Postponed. Once the new date is known and changed, then set this to Rescheduled.",'geodirevents'),
				'htmlvar_name'       => 'event_status',
				'is_active'          => true,
				'for_admin_use'      => false,
				'default_value'      => '',
				'db_default'         => '',
				'show_in'            => '[detail],[listing],[mapbubble]',
				'is_required'        => false,
				'option_values'      => __( 'Select Disruption Status/,No Disruption/scheduled,Cancelled/cancelled,Postponed/postponed,Rescheduled/rescheduled,Moved Online/moved-online', 'geodirevents' ),
				'validation_pattern' => '',
				'validation_msg'     => '',
				'required_msg'       => '',
				'field_icon'         => 'fas fa-exclamation-circle',
				'css_class'          => '',
				'cat_sort'           => true,
				'cat_filter'         => true,
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
			if ( ! empty( $field->htmlvar_name ) && ( $field->htmlvar_name == 'recurring' || $field->htmlvar_name == 'event_dates' ) ) {
				$delete = false;
			}
		}
		return $delete;
	}

	public static function input_recurring( $post_type, $package_id, $field ) {
        global $gd_post,$geodir_label_type;

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return;
		}

		if ( ! geodir_event_recurring_pkg( $gd_post ) ) { // Recurring is disabled
			return;
		}

		if ( has_filter( 'geodir_event_custom_field_input_event_recurring' ) ) {
			$html = apply_filters( 'geodir_event_custom_field_input_event_recurring', '', $field, $package_id );

			if ( ! empty( $html ) ) {
				return $html;
			}
		}

		$value 					= geodir_get_cf_value( $field );
		$field_title 			= ! empty( $field['frontend_title'] ) ? __( $field['frontend_title'], 'geodirectory' ) : '';
		$field_desc 			= ! empty( $field['desc'] ) ? __( $field['desc'], 'geodirectory' ) : '';
		$required_msg 			= ! empty( $field['required_msg'] ) ? __( $field['required_msg'], 'geodirectory' ) : '';
		$htmlvar_name 			= $field['htmlvar_name'];
		$design_style           = geodir_design_style();


		ob_start();
		if ( $design_style ) {
			// Help text
			$help_text = $field['desc'] != '' ? __( $field['desc'], 'geodirectory' ) : '';

			$conditional_attrs = geodir_conditional_field_attrs( $field, '', 'radio' );

			echo aui()->radio(
				array(
					'id'                => $htmlvar_name,
					'name'              => $htmlvar_name,
					'type'              => "radio",
					'label'             => esc_attr__($field_title, 'geodirevents').' <span class="text-danger">*</span>',
					'label_type'        => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'help_text'         => $help_text,
					'class'             => '',
					'value'             => $value,
					'options'           => array(
						'1' =>  __( 'Yes', 'geodirevents' ),
						'0' =>  __( 'No', 'geodirevents' ),
					),
					'wrap_attributes'  => $conditional_attrs
				)
			);
		}else{
			?>
			<div id="<?php echo esc_attr( $htmlvar_name ); ?>_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field">
				<label><?php echo $field_title . ' <span>*</span>'; ?></label>
				<span class="gd-radios"><input name="<?php echo esc_attr( $htmlvar_name ); ?>" id="<?php echo esc_attr( $htmlvar_name ); ?>" <?php checked( (int) $value, 1 ); ?> value="1" class="gd-checkbox" field_type="radio" type="radio" /><?php echo __( 'Yes', 'geodirevents' ); ?></span>
				<span class="gd-radios"><input name="<?php echo esc_attr( $htmlvar_name ); ?>" id="<?php echo esc_attr( $htmlvar_name ); ?>" <?php checked( (int) $value, 0 ); ?> value="0" class="gd-checkbox" field_type="radio" type="radio" /><?php echo __( 'No', 'geodirevents' ); ?></span>
				<span class="geodir_message_note"><?php echo $field_desc; ?></span>
				<span class="geodir_message_error"><?php echo $required_msg; ?></span>
			</div>
			<?php
		}
		$html = ob_get_clean();

		echo $html;
	}

	public static function input_event_dates( $post_type, $package_id, $field ) {
		global $aui_bs5, $gd_post, $geodir_label_type;

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return;
		}

		if ( has_filter( 'geodir_event_custom_field_input_event_event_dates' ) ) {
			$html = apply_filters( 'geodir_event_custom_field_input_event_event_dates', '', $field, $package_id );

			if ( ! empty( $html ) ) {
				return $html;
			}
		}

		$design_style           = geodir_design_style();

		$htmlvar_name 			= $field['htmlvar_name'];
		$description			= $field['desc'] != '' ? __( $field['desc'], 'geodirectory' ) : '';
		$event_data 			= geodir_get_cf_value( $field );
		$event_data 			= maybe_unserialize( $event_data );

		// v1 event data
		if ( ! empty( $event_data ) && isset( $event_data['event_recurring_dates'] ) && isset( $event_data['event_start'] ) && empty( $event_data['recurring_dates'] ) && empty( $event_data['start_date'] ) ) {
			$event_data['start_date'] = $event_data['event_start'];
			$event_data['end_date'] = $event_data['event_end'];
			$event_data['recurring_dates'] = is_array( $event_data['event_recurring_dates'] ) ? $event_data['event_recurring_dates'] : explode( ',', $event_data['event_recurring_dates'] );
			$event_data['start_time'] = $event_data['starttime'];
			$event_data['end_time'] = $event_data['endtime'];
			$event_data['start_times'] = $event_data['starttimes'];
			$event_data['end_times'] = $event_data['endtimes'];
		}

		$is_recurring_active	= geodir_event_recurring_pkg( $gd_post );
		$format 				= geodir_event_field_date_format();
		$default_start_date 	= $design_style ? date_i18n( 'Y-m-d' ) : date_i18n( $format );

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
		$repeat_days 			= array();
		$repeat_weeks 			= array();

		$recurring_dates_list	= '';
		$custom_dates_list		= '';
		$different_times_list	= '';

		// Event start time
		$timepicker_extras = array();
		$timepicker_extras['data-enable-time'] = 'true';
		$timepicker_extras['data-no-calendar'] = 'true';
		$timepicker_extras['data-alt-input'] = 'true';
		$timepicker_extras['data-date-format'] = 'H:i';
		$timepicker_extras['data-alt-format'] = geodir_event_input_time_format( true );
		$timepicker_extras['data-time_24hr'] = 'false';

		$timepicker_extras = apply_filters( 'geodir_event_aui_start_end_time_attrs', $timepicker_extras );

		if ( $recurring && $is_recurring_active ) {	
			$duration_x 		= ! empty( $event_data['duration_x'] ) && absint( $event_data['duration_x'] ) > 0 ? absint( $event_data['duration_x'] ) : 1;
			$repeat_type 		= isset( $event_data['repeat_type'] ) && in_array( $event_data['repeat_type'], array( 'day', 'week', 'month', 'year', 'custom' ) ) ? $event_data['repeat_type'] : 'custom'; // day, week, month, year, custom
			$repeat_x 			= ! empty( $event_data['repeat_x'] ) && absint( $event_data['repeat_x'] ) > 0 ? absint( $event_data['repeat_x'] ) : 1;
			if ( ( $repeat_type == 'week' || $repeat_type == 'month' ) && ! empty( $event_data['repeat_days'] ) ) {
				$repeat_days = is_array( $event_data['repeat_days'] ) ? $event_data['repeat_days'] : explode( ',', $event_data['repeat_days'] );
				if ( ! empty( $repeat_days ) ) {
					$repeat_days = array_filter( $repeat_days, 'strlen' );
				}
			}
			if ( $repeat_type == 'month' && ! empty( $event_data['repeat_weeks'] ) ) {
				$repeat_weeks = is_array( $event_data['repeat_weeks'] ) ? $event_data['repeat_weeks'] : explode( ',', $event_data['repeat_weeks'] );
			}
			$repeat_end_type 	= isset( $event_data['repeat_end_type'] ) ? absint( $event_data['repeat_end_type'] ) : '';
			$max_repeat 		= ! empty( $event_data['max_repeat'] ) && absint( $event_data['max_repeat'] ) > 0 ? absint( $event_data['max_repeat'] ) : 2;
			if ( ! empty( $event_data['repeat_end'] ) ) {
				$repeat_end = $design_style ? $event_data['repeat_end'] : date_i18n( $format, strtotime( $event_data['repeat_end'] ) );
			}
			if ( $repeat_type == 'custom' ) {
				$different_times 	= ! empty( $event_data['different_times'] ) ? true : false;
				$recurring_dates 	= ! empty( $event_data['recurring_dates'] ) && is_array( $event_data['recurring_dates'] ) ? $event_data['recurring_dates'] : array();
				$start_times 		= ! empty( $event_data['start_times'] ) ? $event_data['start_times'] : array();
				$end_times 			= ! empty( $event_data['end_times'] ) ? $event_data['end_times'] : array();

				$custom_dates_list 	= array();
				if ( ! empty( $recurring_dates ) ) {
					$timepicker_attrs = '';
					foreach ( $timepicker_extras as $_key => $_val ) {
						$timepicker_attrs .= " " . sanitize_html_class( $_key ) . '="' . esc_attr( $_val ) . '"';
					}

					foreach ( $recurring_dates as $key => $date ) {
						$recurring_dates_list .= '<span data-date="' . date_i18n( 'Y-m-d', strtotime( $date ) ) . '">' . date_i18n( $format, strtotime( $date ) ) . '</span>';
						if ( $different_times ) {
							$start_time_selected	= ! empty( $start_times[ $key ] ) ? $start_times[$key] : ( ! empty( $start_time ) ? $start_time : '10:00' );
							$end_time_selected 	= ! empty( $end_times[ $key ] ) ? $end_times[$key] : ( ! empty( $end_time ) ? $end_time : '18:00' );
							if($design_style){
								$different_times_list 	.= '<div data-date="' . date_i18n( 'Y-m-d', strtotime( $date ) ) . '" class="event-multiple-times row pb-1"><div class="col-2"><div class="gd-events-custom-time">' . date_i18n( 'Y-m-d', strtotime( $date ) ) . '</div></div><div class="col-5"><input type="text" name="event_dates[start_times][]" placeholder="' . esc_attr__( 'Start', 'geodirevents' ) . '" value="'.esc_attr($start_time_selected).'" class="form-control bg-initial" ' . trim( $timepicker_attrs ) . ' data-aui-init="flatpickr"></div><div class="col-5"><input type="text" name="event_dates[end_times][]" placeholder="' . esc_attr__( 'End', 'geodirevents' ) . '" value="'.esc_attr($end_time_selected).'" class="form-control bg-initial" ' . trim( $timepicker_attrs ) . ' data-aui-init="flatpickr"></div></div>';

							}else{
								$different_times_list 	.= '<div data-date="' . date_i18n( 'Y-m-d', strtotime( $date ) ) . '" class="event-multiple-times clearfix"><label class="event-multiple-dateto">' . date_i18n( $format, strtotime( $date ) ) . '</label><div class="event-multiple-dateto-inner"><select id="event_start_times" name="event_dates[start_times][]" class="geodir_textfield geodir-select geodir-w110">' . geodir_event_time_options( $start_time_selected ) .  '</select></div><label class="event-multiple-end"> ' . __( 'to', 'geodirevents' ) . ' </label><div class="event-multiple-dateto-inner"><select id="event_end_times" name="event_dates[end_times][]" class="geodir_textfield geodir-select geodir-w110">' . geodir_event_time_options( $end_time_selected ) .  '</select></div></div>';
							}
						}
						$custom_dates_list[] = date_i18n( 'm/d/Y', strtotime( $date ) );
					}
				}

				if($design_style){
					$recurring_dates 	= ! empty( $recurring_dates ) ? implode( ', ', $recurring_dates ) : '';
				}else{
					$recurring_dates 	= ! empty( $recurring_dates ) ? implode( ',', $recurring_dates ) : '';
				}
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
		if ( ! empty( $start_date ) && !$design_style ) {
			$start_date = date_i18n( $format, strtotime( $start_date ) );
		}
		if ( ! empty( $end_date ) && !$design_style ) {
			$end_date = date_i18n( $format, strtotime( $end_date ) );
		}
		
		$recurring_class 			= $recurring ? '' : 'geodir-none';
		$custom_recurring_class 	= $recurring_dates_list != '' ? '' : 'geodir-none';
		$show_time_class 			= $all_day ? 'geodir-none' : '';
		$show_times_class 			= ! empty( $different_times ) ? '' : 'geodir-none';


		ob_start();
		if ( $design_style ) {
			$horizontal = empty( $geodir_label_type ) || $geodir_label_type == 'horizontal' ? true : false;

			// Event dates description.
			if ( $description != '' ) {
				echo '<div id="event_dates_desc_row" class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . ( $horizontal ? ' row' : '' ) . '" >';
				if ( $horizontal ) {
					echo '<label class="col-sm-2 col-form-label form-label"></label><div class="col-sm-10">';
				}
				echo '<div class="form-text text-muted d-block">' . $description . '</div>';
				if ( $horizontal ) {
					echo '</div>';
				}
				echo '</div>';
			}

			// flatpickr attributes
			$extra_attributes = array();
			$extra_attributes['data-alt-input'] = 'true';
			$extra_attributes['data-alt-format'] = geodir_date_format_php_to_aui( $format  );
			$extra_attributes['data-date-format'] = 'Y-m-d';
			$extra_attributes['data-disable-mobile'] = 'true'; // Disable mobile native datetime input.

			// start date
			echo aui()->input(
				array(
					'id'                => "event_start_date",
					'name'              => $htmlvar_name . "[start_date]",
					'required'          => true,
					'label'              => __('Event start date', 'geodirevents').' <span class="text-danger">*</span>',
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'type'              => 'datepicker',
					'placeholder'       => esc_html__( 'Start date', 'geodirevents'),
					'class'             => '',
					'value'             => $start_date,
					'help_text'         => __( 'Choose a start date of the event.', 'geodirevents' ),
					'element_require'   => ( $is_recurring_active ? '!([%recurring%:checked] && [%event_repeat_type%]=="custom")' : '' ),
					'extra_attributes'  => $extra_attributes,
					'wrap_attributes'   => geodir_conditional_field_attrs( array(), 'start_date', 'hidden' )
				)
			);

			// end date
			echo aui()->input(
				array(
					'id'                => "event_end_date",
					'name'              => $htmlvar_name . "[end_date]",
					'required'          => false,
					'label'              => __('Event end date', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'type'              => 'datepicker',
					'placeholder'       => esc_html__( 'End date', 'geodirevents'),
					'class'             => '',
					'value'             => $end_date,
					'help_text'         => __( 'Choose a end date of the event.', 'geodirevents' ),
					'element_require'   => ( $is_recurring_active ? '[%recurring%:checked]=="0"' : '' ),
					'extra_attributes'  => $extra_attributes,
					'wrap_attributes'   => geodir_conditional_field_attrs( array(), 'end_date', 'hidden' )
				)
			);

			if ( $is_recurring_active ) {

				// duration
				echo aui()->input(
					array(
						'id'                => 'event_duration_x',
						'name'              => $htmlvar_name . "[duration_x]",
						'required'          => false,
						'label'              => esc_html__(  'Event duration (days)', 'geodirevents' ),
						'type'              => 'number',
						'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
						'value'             => $duration_x,
						'element_require'   => '[%recurring%:checked]=="1"',
						'extra_attributes'  => array(
							'min'       => 0,
							'lang'      => "EN"
						)
					)
				);

				// repeat every
				echo  aui()->select( array(
					'id'               => "event_repeat_x",
					'name'             => $htmlvar_name . "[repeat_x]",
					'class'            => 'mw-100',
					'label'            => esc_html__('Repeats every', 'geodirevents').' <span class="text-danger">*</span>',
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'value'            => $repeat_x,
					'options'          => array_combine( range( 1, 30 ), range( 1, 30 ) ),
					'help_text'        => __( 'Please select recurring interval', 'geodirevents' ),
					'element_require'  => '[%recurring%:checked]=="1" && [%event_repeat_type%]!="custom"',
				) );

				// repeats
				echo  aui()->select( array(
					'id'               => "event_repeat_type",
					'name'             => $htmlvar_name . "[repeat_type]",
					'class'             => 'mw-100',
					'label'              => esc_html__('Repeats', 'geodirevents').' <span class="text-danger">*</span>',
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'value'            => $repeat_type,
					'options'          => array(
						''      =>  esc_html__( 'Select recurring type', 'geodirevents' ),
						'day'      =>  esc_html__( 'Days', 'geodirevents' ),
						'week'      =>  esc_html__( 'Weeks', 'geodirevents' ),
						'month'      =>  esc_html__( 'Months', 'geodirevents' ),
						'year'      =>  esc_html__( 'Years', 'geodirevents' ),
						'custom'      =>  esc_html__( 'Custom', 'geodirevents' ),
					),
					'help_text'         => __( 'Please select recurring type', 'geodirevents' ),
					'element_require'   => '[%recurring%:checked]=="1"',
				) );

				// repeat on
				echo  aui()->select( array(
					'id'               => "event_repeat_days",
					'name'             => $htmlvar_name . "[repeat_days][]",
					'class'            => 'mw-100',
					'label'            => esc_html__('Select days', 'geodirevents').' <span class="text-danger">*</span>',
					'placeholder'      => esc_html__( 'Select days', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'value'            => $repeat_days,
					'options'          => array(
						'1'      =>  esc_html__( 'Mon', 'geodirevents' ),
						'2'      =>  esc_html__( 'Tue', 'geodirevents' ),
						'3'      =>  esc_html__( 'Wed', 'geodirevents' ),
						'4'      =>  esc_html__( 'Thu', 'geodirevents' ),
						'5'      =>  esc_html__( 'Fri', 'geodirevents' ),
						'6'      =>  esc_html__( 'Sat', 'geodirevents' ),
						'0'      =>  esc_html__( 'Sun', 'geodirevents' ),
					),
					'multiple'          => true,
					'select2'           => true,
					'data-allow-clear'  => false,
					'element_require'   => '[%recurring%:checked]=="1" && ([%event_repeat_type%]=="week" || [%event_repeat_type%]=="month")',
				) );

				// repeat by
				echo  aui()->select( array(
					'id'               => "event_repeat_weeks",
					'name'             => $htmlvar_name . "[repeat_weeks][]",
					'class'            => 'mw-100',
					'label'            => esc_html__('Repeat by', 'geodirevents').' <span class="text-danger">*</span>',
					'placeholder'      => esc_html__( 'Select weeks', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'value'            => $repeat_weeks,
					'options'          => array(
						'1'      =>  esc_html__( '1st week', 'geodirevents' ),
						'2'      =>  esc_html__( '2nd week', 'geodirevents' ),
						'3'      =>  esc_html__( '3rd week', 'geodirevents' ),
						'4'      =>  esc_html__( '4th week', 'geodirevents' ),
						'5'      =>  esc_html__( '5th week', 'geodirevents' ),
					),
					'multiple'          => true,
					'select2'           => true,
					'data-allow-clear'  => false,
					'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]=="month"',
				) );

				echo aui()->radio(
					array(
						'id'                => 'event_repeat_end_type',
						'name'              => $htmlvar_name . "[repeat_end_type]",
						'type'              => "radio",
						'label'             => esc_html__( 'Stop Recurring Events', 'geodirevents' ),
						'label_type'        => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
						'class'             => '',
						'value'             => $repeat_end_type,
						'options'           => array(
							'0' =>  __( 'After a certain number of occurrences', 'geodirevents' ),
							'1' =>  __( 'After a certain date', 'geodirevents' ),
						),
						'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]!="custom"',
					)
				);

				// recurring ends occurrences
				echo aui()->input(
					array(
						'id'                => 'event_max_repeat',
						'name'              => $htmlvar_name . "[max_repeat]",
						'required'          => false,
						'label'              => esc_html__(  'How many times should this event occur?', 'geodirevents' ),
						'type'              => 'number',
						'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
						'value'             => $max_repeat,
						'placeholder'       => esc_html__( 'Set a number of occurrences', 'geodirevents'),
						'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]!="custom" && [%event_repeat_end_type%:checked]!="1"',
						'help_text'         => __( 'Event will stop recurring after this number of instances.', 'geodirevents' ),
						'extra_attributes'  => array(
							'min'       => 0,
							'lang'      => "EN"
						)
					)
				);

				// recurring ends date
				echo aui()->input(
					array(
						'id'                => "event_repeat_end",
						'name'              => $htmlvar_name . "[repeat_end]",
						'required'          => false,
						'label'              => __('Recurring end date', 'geodirevents'),
						'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
						'type'              => 'datepicker',
						'placeholder'       => esc_html__( 'Select a date recurring should stop', 'geodirevents'),
						'class'             => '',
						'value'             => $repeat_end,
						'help_text'         => __( 'Event will stop recurring after this date.', 'geodirevents' ),
						'extra_attributes'  => $extra_attributes,
						'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]!="custom" && [%event_repeat_end_type%:checked]=="1"',
					)
				);

				// recurring custom
				$custom_dates_extra = $extra_attributes;
				$custom_dates_extra['data-mode'] = 'multiple';
				echo aui()->input(
					array(
						'id'                => "event_recurring_dates",
						'name'              => $htmlvar_name . "[recurring_dates]",
						'required'          => false,
						'label'              => __('Event Date(s)', 'geodirevents'),
						'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
						'type'              => 'datepicker',
						'placeholder'       => esc_html__( 'Select one or more custom dates', 'geodirevents'),
						'class'             => '',
						'value'             => $recurring_dates,
//						'help_text'         => __( 'Choose a end date of the event.', 'geodirevents' ),
						'extra_attributes'  => $custom_dates_extra,
						'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]=="custom"',
					)
				);
			}

			// event time all day
			echo aui()->input(array(
				'type'  =>  'checkbox',
				'id'    =>  'event_all_day',
				'name'    =>  $htmlvar_name . "[all_day]",
				'value' =>  '1',
				'checked'   => (int)$all_day,
				'help_text' => esc_html__( 'Tick to set event for all day.', 'geodirevents'),
				'label' => esc_html__( 'All day', 'geodirevents'),
				'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
			));

			echo aui()->input(
				array(
					'id'                => "event_start_time",
					'name'              => $htmlvar_name . "[start_time]",
					'label'              => esc_html__( 'Start time', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'type'              => 'timepicker',
					'placeholder'       => esc_html__( 'Select the event start time', 'geodirevents'),
					'class'             => '',
					'value'             => $start_time,
//						'help_text'         => __($cf['desc'], 'geodirectory'),
					'extra_attributes'  => $timepicker_extras,
					'element_require'   => '![%event_all_day%:checked]',
				)
			);

			// event end time
			echo aui()->input(
				array(
					'id'                => "event_end_time",
					'name'              => $htmlvar_name . "[end_time]",
					'label'              => esc_html__( 'End time', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'type'              => 'timepicker',
					'placeholder'       => esc_html__( 'Select the event end time', 'geodirevents'),
					'class'             => '',
					'value'             => $end_time,
//						'help_text'         => __($cf['desc'], 'geodirectory'),
					'extra_attributes'  => $timepicker_extras,
					'element_require'   => '![%event_all_day%:checked]',
				)
			);

			if ( $is_recurring_active ) {
				// event time all day
				echo aui()->input(array(
					'type'  =>  'checkbox',
					'id'    =>  'event_different_times',
					'name'    =>  $htmlvar_name . "[different_times]",
					'value' =>  '1',
					'checked'   => absint($different_times),
					'help_text' => esc_html__( 'Tick to set separate start and end times for each date.', 'geodirevents'),
					'label' => esc_html__( 'Different Event Times?', 'geodirevents'),
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]=="custom"',
				));
			}

			$output = $different_times_list;

			$wrap_class = ( $aui_bs5 ? 'mb-3' : 'form-group' );
			if ( empty( $geodir_label_type ) ) {
				$wrap_class .= ' rowx';
			}
			echo AUI_Component_Input::wrap(array(
				'content' => $output,
				'class'   => 'geodir_event_times_per_date '.$wrap_class,
				'element_require'   => '[%recurring%:checked]=="1" && [%event_repeat_type%]=="custom" && [%event_different_times%:checked]=="1"',
				'argument_id'  => 'event_times_per_date'
			));
			
		}else{
	        ?>
			<?php if ( $description != '' ) { ?>
			<div id="geodir_event_date_desc_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field">
				<span class="geodir_message_note"><?php echo $description; ?></span>
			</div>
			<?php } ?>
	        <div id="geodir_event_start_date_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field">
	            <label for="event_start_date"><?php echo __( 'Event start date', 'geodirevents' ) . ' <span>*</span>'; ?></label>
				<input type="text" class="geodir_textfield geodir-w200" name="<?php echo esc_attr( $htmlvar_name ); ?>[start_date]" id="event_start_date" value="<?php echo esc_attr( $start_date ); ?>" field_type="text" <?php echo ( wp_is_mobile() ) ?  'readonly="readonly"' : '';?>>
	            <span class="geodir_message_error"><?php _e( 'Choose a start date of the event.', 'geodirevents' );?></span>
	        </div>
			<div id="geodir_event_end_date_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field">
	            <label for="event_end_date"><?php echo __( 'Event end date', 'geodirevents' ); ?></label>
				<input type="text" class="geodir_textfield geodir-w200" name="<?php echo esc_attr( $htmlvar_name ); ?>[end_date]" id="event_end_date" value="<?php echo esc_attr( $end_date ); ?>" field_type="text"  <?php echo ( wp_is_mobile() ) ?  'readonly="readonly"' : '';?>>
	        </div>
			<?php if ( $is_recurring_active ) { ?>
			<div id="geodir_event_duration_x_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label for="event_duration_x"><?php echo __( 'Event duration (days)', 'geodirevents' ); ?></label>
				<input type="number" class="geodir_textfield geodir-w200" name="<?php echo esc_attr( $htmlvar_name ); ?>[duration_x]" id="event_duration_x" value="<?php echo esc_attr( $duration_x ); ?>" min="0" lang="EN" field_type="text">
	        </div>
			<div id="geodir_event_repeat_x_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label for="event_repeat_x"><?php echo __( 'Repeats every', 'geodirevents' ); ?></label>
				<select id="event_repeat_x" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_x]" class="geodir_textfield geodir-select geodir-w200">
					<?php for ( $i = 1; $i <= 30; $i++ ) { ?>
					<option value="<?php echo $i;?>" <?php selected( $repeat_x, $i ); ?>><?php echo $i;?></option>
					<?php } ?>
				</select>
				<span class="geodir_message_error"><?php _e( 'Please select recurring interval', 'geodirevents' );?></span>
	        </div>
			<div id="geodir_event_repeat_type_row" class="required_field geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label for="event_repeat_type"><?php echo __( 'Repeats', 'geodirevents' ) . ' <span>*</span>'; ?></label>
				<select id="event_repeat_type" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_type]" class="geodir_textfield geodir-select geodir-w200" data-placeholder="<?php echo esc_attr_e( 'Select recurring type', 'geodirevents' );?>">
					<option value="" <?php selected( $repeat_type, '' );?>><?php _e( 'Select recurring type', 'geodirevents' );?></option>
					<option value="day" <?php selected( $repeat_type, 'day' );?> data-title="<?php echo esc_attr( __( 'days', 'geodirevents' ) );?>"><?php _e( 'Days', 'geodirevents' );?></option>
					<option value="week" <?php selected( $repeat_type, 'week' );?> data-title="<?php echo esc_attr( __( 'weeks', 'geodirevents' ) );?>"><?php _e( 'Weeks', 'geodirevents' );?></option>
					<option value="month" <?php selected( $repeat_type, 'month' );?> data-title="<?php echo esc_attr( __( 'months', 'geodirevents' ) );?>"><?php _e( 'Months', 'geodirevents' );?></option>
					<option value="year" <?php selected( $repeat_type, 'year' );?> data-title="<?php echo esc_attr( __( 'years', 'geodirevents' ) );?>"><?php _e( 'Years', 'geodirevents' );?></option>
					<option value="custom" <?php selected( $repeat_type, 'custom' );?>><?php _e( 'Custom', 'geodirevents' );?></option>
				</select>
				<span class="geodir_message_error"><?php _e( 'Please select recurring type', 'geodirevents' );?></span>
	        </div>
			<div id="geodir_event_repeat_days_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label for="event_repeat_days"><?php echo __( 'Repeat on', 'geodirevents' ); ?></label>
				<select id="event_repeat_days" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_days][]" class="geodir_textfield geodir-select" multiple="multiple" data-placeholder="<?php echo esc_attr_e( 'Select days', 'geodirevents' );?>">
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
				<select id="event_repeat_weeks" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_weeks][]" class="geodir_textfield geodir-select" multiple="multiple" data-placeholder="<?php echo esc_attr_e( 'Select weeks', 'geodirevents' );?>">
					<option value="1" <?php selected( true, in_array( 1, $repeat_weeks ) ); ?>><?php _e( '1st week', 'geodirevents' ); ?></option>
					<option value="2" <?php selected( true, in_array( 2, $repeat_weeks ) ); ?>><?php _e( '2nd week', 'geodirevents' ); ?></option>
					<option value="3" <?php selected( true, in_array( 3, $repeat_weeks ) ); ?>><?php _e( '3rd week', 'geodirevents' ); ?></option>
					<option value="4" <?php selected( true, in_array( 4, $repeat_weeks ) ); ?>><?php _e( '4th week', 'geodirevents' ); ?></option>
					<option value="5" <?php selected( true, in_array( 5, $repeat_weeks ) ); ?>><?php _e( '5th week', 'geodirevents' ); ?></option>
				</select>
	        </div>
			<div id="geodir_event_recurring_ends_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label><?php echo __( 'Stop Recurring Events', 'geodirevents' ); ?></label>
				<div class="geodir-inline-fields">
					<input type="radio" class="gd-checkbox" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_end_type]" id="event_repeat_end_type_m" value="0" <?php checked( $repeat_end_type, 0 );?> /><label for="event_repeat_end_type_m"><?php _e( 'After', 'geodirevents' );?></label>&nbsp;<input type="number" value="<?php echo $max_repeat;?>" class="geodir_textfield geodir-w110" id="event_max_repeat" name="<?php echo esc_attr( $htmlvar_name ); ?>[max_repeat]" lang="EN">&nbsp;<label for="event_repeat_end_type_m"><?php _e( 'occurrences', 'geodirevents' );?></label>&nbsp;&nbsp;<input type="radio" class="gd-checkbox" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_end_type]" id="event_repeat_end_type_u" value="1" <?php checked( $repeat_end_type, 1 );?> /><label for="event_repeat_end_type_u"><?php _e( 'On', 'geodirevents' );?></label> <input type="text" value="<?php echo $repeat_end;?>" class="geodir_textfield geodir-w200" id="event_repeat_end" name="<?php echo esc_attr( $htmlvar_name ); ?>[repeat_end]" />
				</div>
				<span class="geodir_message_note"><?php _e( 'Event will stop recurring after this number of instances / this date.', 'geodirevents' );?></span>
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
						<input name="<?php echo esc_attr( $htmlvar_name ); ?>[recurring_dates]" id="event_recurring_dates" value="<?php echo $recurring_dates; ?>" type="hidden">
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
	            <input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[all_day]" id="event_all_day" value="<?php echo (int)$all_day; ?>"/>
	            <input value="1" id="event_all_day_chk" class="gd-checkbox" field_type="checkbox" type="checkbox" <?php checked( $all_day, 1 ); ?> onchange="if(this.checked){jQuery('#event_all_day').val('1');} else{ jQuery('#event_all_day').val('0');}" />
				<span class="geodir_message_note"><?php _e( 'Tick to set event for all day.', 'geodirevents' ); ?></span>
	        </div>
			<div id="geodir_event_time_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $show_time_class; ?>">
	            <label for="event_start_time"><?php echo __( 'Event Time', 'geodirevents' ); ?></label>
				<div class="geodir-inline-fields">
					<label for="event_start_time" class="lbl-event-start-time"><?php _e( 'Starts at', 'geodirevents' ); ?></label>
					<select id="event_start_time" name="<?php echo esc_attr( $htmlvar_name ); ?>[start_time]" class="geodir_textfield geodir-select geodir-w110">
						<?php echo geodir_event_time_options( $start_time ); ?>
					</select>
					<label for="event_end_time" class="lbl-event-end-time"><?php _e( 'Ends at', 'geodirevents' ); ?></label>
					<select id="event_end_time" name="<?php echo esc_attr( $htmlvar_name ); ?>[end_time]" class="geodir_textfield geodir-select geodir-w110">
						<?php echo geodir_event_time_options( $end_time ); ?>
					</select>
				</div>
	        </div>
			<?php if ( $is_recurring_active ) { ?>
			<div id="geodir_event_different_times_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $recurring_class; ?>">
	            <label for="event_different_times_chk"><?php echo __( 'Different Event Times?', 'geodirevents' ); ?></label>
	            <input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[different_times]" id="event_different_times" value="<?php echo (int)$different_times; ?>"/>
	            <input value="1" id="event_different_times_chk" class="gd-checkbox" field_type="checkbox" type="checkbox" <?php checked( $different_times, 1 ); ?> onchange="if(this.checked){jQuery('#event_different_times').val('1');} else{ jQuery('#event_different_times').val('0');}" />
				<span class="geodir_message_note"><?php _e( 'Tick to set separate start and end times for each date.', 'geodirevents' ); ?></span>
	        </div>
			<div id="geodir_event_times_row" class="geodir_form_row clearfix gd-fieldset-details geodir-event-field <?php echo $show_times_class; ?>">
				<label></label>
				<div class="show_different_times_div"><?php echo $different_times_list; ?></div>
			</div>
			<?php }

		}
        $html = ob_get_clean();

		echo $html;
	}

	public static function save_event_data( $postarr, $gd_post, $post, $update ) {
		$post_type = ! empty( $post->post_type ) ? $post->post_type : '';

		if ( wp_is_post_revision( $post->ID ) && ( $post_id = wp_get_post_parent_id( $post->ID ) ) ) {
			$post_type = get_post_type( $post_id );
		}

		if ( $post_type && GeoDir_Post_types::supports( $post_type, 'events' ) ) {
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
					$duration_x 		= ! empty( $data['duration_x'] ) && (int) $data['duration_x'] > 0 ? (int) $data['duration_x'] : 1;

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
						$repeat_x 			= 1;
						$repeat_end_type 	= 0;
						$max_repeat 		= 1;
						$repeat_end 		= '';
					} else {
						$repeat_x 			= isset( $data['repeat_x'] ) ? sanitize_text_field( $data['repeat_x'] ) : '';
						$repeat_end_type 	= isset( $data['repeat_end_type'] ) ? sanitize_text_field( $data['repeat_end_type'] ) : 0;
						$repeat_end 		= $repeat_end_type == 1 && isset( $data['repeat_end'] ) ? sanitize_text_field( $data['repeat_end'] ) : '';
						$repeat_x 			= $repeat_x > 0 ? (int)$repeat_x : 1;

						if ( $repeat_end_type == 1 && ! geodir_event_is_date( $repeat_end ) ) {
							$repeat_end_type = 0;
							$repeat_end 	= '';
						}
						$max_repeat 		= $repeat_end_type != 1 && isset( $data['max_repeat'] ) ? (int)$data['max_repeat'] : 1;
						$max_repeat 		= $max_repeat > 0 ? (int)$max_repeat : 1;
						
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

		// Set default value
		if ( ! empty( $gd_post->post_status ) && $gd_post->post_status == 'auto-draft' ) {
			if ( $field_name == 'recurring' && isset( $cf['default'] ) ) {
				$value = ! empty( $cf['default'] ) && geodir_event_recurring_pkg( $gd_post ) ? 1 : 0;
			}
		}

		if ( $field_name != 'event_dates' ) {
			return $value;
		}

		if ( ! GeoDir_Post_types::supports( $cf['post_type'], 'events' ) ) {
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

		if ( $recurring && ! geodir_event_recurring_pkg( $gd_post ) ) {
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

		return apply_filters( 'geodir_event_dates_cf_value', $event_data, $value, $cf, $gd_post );
	}

	public static function cf_event( $html, $location, $cf, $p = '', $output = '' ) {
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
			$html = apply_filters( "geodir_custom_field_output_event_loc_{$location}", $html, $cf, $output, $gd_post );
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
			$html = apply_filters( "geodir_custom_field_output_event_var_{$html_var}", $html, $location, $cf, $output, $gd_post );
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
			$html = apply_filters( "geodir_custom_field_output_event_key_{$cf['field_type_key']}", $html, $location, $cf, $output, $gd_post );
		}

		return $html;
	}
	
	public static function output_event_dates( $html, $location, $cf, $output = '', $the_post = array() ) {
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
			if ( $event_type == 'upcoming' && empty( $schedules ) ) {
				$schedules 	= GeoDir_Event_Schedules::get_schedules( $the_post->ID, 'all', $count ); // Show all schedules for past events
			}
			$schedules_html = GeoDir_Event_Schedules::get_schedules_html( $schedules, ! empty( $the_post->recurring ) );

			$output = geodir_field_output_process( $output );
			if ( ! empty( $output ) && isset( $output['raw'] ) ) {
				// Database value.
				return $the_post->{$htmlvar_name};
			} elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
				// Stripped value.
				return $schedules_html;
			}

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

				$html = '<div class="geodir_post_meta geodir-field-' . $htmlvar_name . ' ' . trim( $date_class ) . '" style="clear:both;">';
				if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
				if ( ( $output == '' || isset( $output['label'] ) ) && $field_label != '' ) $html .= $field_label . ': ';
				if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';
				if ( $output == '' || isset( $output['value'] ) ) $html .= $schedules_html;
				$html .= '</div>';
			} else {
				$html = '';
			}
		}

		return $html;
	}

	public static function output_event_date( $html, $cf, $output = '', $the_post = array() ) {
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
				$value = GeoDir_Event_Schedules::get_schedules_html( array( (object)$schedule ), false );

				$field_icon = geodir_field_icon_proccess( $cf );
				$output = geodir_field_output_process( $output );
				if ( strpos( $field_icon, 'http' ) !== false ) {
					$field_icon_af = '';
				} elseif ( $field_icon == '' ) {
					$field_icon_af = '<i class="fas fa-calendar-alt"></i>';
				} else {
					$field_icon_af = $field_icon;
					$field_icon = '';
				}

				if ( ! empty( $output ) && isset( $output['raw'] ) ) {
					// Database value.
					return '';
				} elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
					// Stripped value.
					return $value;
				}

				$date_class = $cf['css_class'];
				$date_class .= 'geodir-schedule-meta geodir-edate-' . $cf['css_class'];
				$field_label = '';

				$html = '<div class="geodir_post_meta geodir-field-' . $htmlvar_name . ' ' . trim( $date_class ) . '" style="clear:both;">';
				if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
				if ( ( $output == '' || isset( $output['label'] ) ) && $field_label != '' ) $html .= $field_label . ': ';
				if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';
				if ( $output == '' || isset( $output['value'] ) ) $html .= $value;
				$html .= '</div>';
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

		if ( ! empty( $value ) ) {
			$value = array_filter( $value, 'strlen' );
		}

		return $value;
	}

	public static function field_in_main_search_bar( $value, $field, $cf ) {
		if ( ! empty( $field ) && GeoDir_Post_types::supports( $field->post_type, 'events' ) ) {
			if ( $field->htmlvar_name == 'event_dates' ) {
				$value = true;
			}
		}

		return $value;
	}

	public static function cpt_search_setting_field( $field, $cf ) {
		if ( ! empty( $field ) && GeoDir_Post_types::supports( $field->post_type, 'events' ) ) {
			if ( $field->htmlvar_name == 'event_dates' ) {
				$field->input_type = 'DATE';
				$field->date_type = 'DATE';
			}
		}

		return $field;
	}

	public static function search_bar_output_event( $html, $cf, $post_type ) {
		if ( geodir_design_style() ) {
			return self::search_bar_output_event_aui( $html, $cf, $post_type );
		}

		if ( ! ( ! empty( $cf ) && $cf->htmlvar_name == 'event_dates' ) ) {
			return $html;
		}

		$pt_name = geodir_post_type_singular_name( $post_type, true );
		$htmlvar_name = $cf->htmlvar_name;
		$event_dates = isset( $_REQUEST[ $htmlvar_name ] ) ? geodir_event_sanitize_text_field( $_REQUEST[ $htmlvar_name ] ) : '';
		$field_label = $cf->frontend_title ? stripslashes( __( $cf->frontend_title, 'geodirectory' ) ) : '';

		$date_format = geodir_event_date_format();

		// Convert to jQuery UI datepicker format.
		$jqueryui_date_format  = geodir_date_format_php_to_jqueryui( $date_format  );

		ob_start();
		if ( $cf->search_condition == 'FROM' ) {
			$field_value_from = '';
			$field_value_to = '';
			$field_value_from_display = '';
			$field_value_to_display = '';

			if ( is_array( $event_dates ) && ! empty( $event_dates ) ) {
				if ( ! empty( $event_dates['from'] ) ) {
					$field_value_from = sanitize_text_field( $event_dates['from'] );
					$field_value_from_display = date_i18n( $date_format, strtotime( $field_value_from ) );
				}

				if ( ! empty( $event_dates['to'] ) ) {
					$field_value_to = sanitize_text_field( $event_dates['to'] );
					$field_value_to_display = date_i18n( $date_format, strtotime( $field_value_to ) );
				}
			}

			$field_label_from = ! empty( $field_label ) ? wp_sprintf( __( 'From: %s', 'geodirevents' ), $field_label ) : wp_sprintf( __( '%s Start Date', 'geodirevents' ), $pt_name );
			$field_label_to = ! empty( $field_label ) ? wp_sprintf( __( 'To: %s', 'geodirevents' ), $field_label ) : wp_sprintf( __( '%s End Date', 'geodirevents' ), $pt_name );
			?>
			<div class="gd-search-input-wrapper gd-search-field-cpt gd-search-has-date gd-search-<?php echo $htmlvar_name; ?>-from">
				<input type="text" value="<?php echo esc_attr( $field_value_from_display ); ?>" placeholder="<?php echo esc_attr( $field_label_from ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>[from]" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>" data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>" data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>" aria-label="<?php echo esc_attr( $field_label_from ); ?>"/><input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[from]" value="<?php echo esc_attr( $field_value_from ); ?>">
			</div>
			<div class="gd-search-input-wrapper gd-search-field-cpt gd-search-has-date gd-search-<?php echo $htmlvar_name; ?>-to">
				<input type="text" value="<?php echo esc_attr( $field_value_to_display ); ?>" placeholder="<?php echo esc_attr( $field_label_to ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>[to]" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>"  data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>" data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>" aria-label="<?php echo esc_attr( $field_label_to ); ?>"/><input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[to]" value="<?php echo esc_attr( $field_value_to ); ?>">
			</div>
			<?php
		} else {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Date', 'geodirevents' ), $pt_name );
			}
			$field_value = ! empty( $event_dates ) && ! is_array( $event_dates ) ? sanitize_text_field( $event_dates ) : '';
			$field_value_display = ! empty( $field_value ) ? date_i18n( $date_format, strtotime( $field_value ) ) : '';
			?>
			<div class="gd-search-input-wrapper gd-search-field-cpt gd-search-has-date gd-search-<?php echo $htmlvar_name; ?>">
				<input type="text" value="<?php echo esc_attr( $field_value_display ); ?>" placeholder="<?php echo esc_attr( $field_label ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>"  data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>" data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>" aria-label="<?php echo esc_attr( $field_label ); ?>"><input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>">
			</div>
			<?php			
		}

		$html .= ob_get_clean();

		return apply_filters( 'geodir_event_search_form_output_main_event_dates', $html, $cf, $post_type );
	}

	public static function search_bar_output_event_aui( $html, $cf, $post_type ) {
		global $aui_bs5, $as_fieldset_start;

		if ( ! ( ! empty( $cf ) && $cf->htmlvar_name == 'event_dates' ) ) {
			return $html;
		}

		$pt_name = geodir_post_type_singular_name( $post_type, true );
		$htmlvar_name = $cf->htmlvar_name;
		$event_dates = isset( $_REQUEST[ $htmlvar_name ] ) ? geodir_event_sanitize_text_field( $_REQUEST[ $htmlvar_name ] ) : '';
		$field_label = $cf->frontend_title ? stripslashes( __( $cf->frontend_title, 'geodirectory' ) ) : '';

		$date_format = geodir_event_date_format();

		// Convert to jQuery UI datepicker format.
		$datepicker_format  = geodir_date_format_php_to_aui( $date_format  );
		$wrap_attrs = function_exists( 'geodir_search_conditional_field_attrs' ) ? geodir_search_conditional_field_attrs( $cf ) : '';

		ob_start();
		if ( $cf->search_condition == 'FROM' ) {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Dates', 'geodiradvancesearch' ), $pt_name );
			}
			?>
			<div class="gd-search-has-date gd-search-<?php echo $htmlvar_name; ?> from-to col-auto flex-fill<?php echo ( $aui_bs5 ? ' px-0' : '' ); ?>"<?php echo $wrap_attrs; ?>>
				<?php if ( ! empty( $field_label ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>" class="sr-only visually-hidden"><?php echo $field_label; ?></label>
				<?php }

				// Flatpickr attributes
				$extra_attributes = array();
				$extra_attributes['data-alt-input'] = 'true';
				$extra_attributes['data-alt-format'] = $datepicker_format;
				$extra_attributes['data-date-format'] = 'Y-m-d';
				$extra_attributes['data-disable-mobile'] = 'true'; // Disable mobile native datetime input.

				// Range
				$extra_attributes['data-mode'] = 'range';
				echo aui()->input(
					array(
						'id'                => $htmlvar_name,
						'name'              => $htmlvar_name,
						'type'              => 'datepicker',
						'placeholder'       => $field_label,
						'class'             => '',
						'value'             => esc_attr( $event_dates ),
						'extra_attributes'  => $extra_attributes,
						'form_group_class'  => ( $aui_bs5 ? ' ' : '' ), // Remove bottom margin for BS5
					)
				);
				?>
			</div>
			<?php
		} else {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Date', 'geodirevents' ), $pt_name );
			}
			$event_dates = ! empty( $event_dates ) && ! is_array( $event_dates ) ? sanitize_text_field( $event_dates ) : '';

			?>
			<div class="gd-search-has-date gd-search-<?php echo $htmlvar_name; ?> col-auto flex-fill<?php echo ( $aui_bs5 ? ' px-0' : '' ); ?>"<?php echo $wrap_attrs; ?>>
				<?php if ( ! empty( $field_label ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>" class="sr-only visually-hidden"><?php echo $field_label; ?></label>
				<?php }

				// flatpickr attributes
				$extra_attributes = array();
				$extra_attributes['data-alt-input'] = 'true';
				$extra_attributes['data-alt-format'] = $datepicker_format;
				$extra_attributes['data-date-format'] = 'Y-m-d';
				$extra_attributes['data-disable-mobile'] = 'true'; // Disable mobile native datetime input.
				echo aui()->input(
					array(
						'id'                => $htmlvar_name,
						'name'              => $htmlvar_name,
						'type'              => 'datepicker',
						'placeholder'       => $field_label,
						'class'             => '',
						'value'             => esc_attr( $event_dates ),
						'extra_attributes'  => $extra_attributes,
						'form_group_class'  => ( $aui_bs5 ? ' ' : '' ), // Remove bottom margin for BS5
					)
				);
				?>
			</div>
			<?php
		}

		$html .= ob_get_clean();

		return apply_filters( 'geodir_event_search_form_output_main_event_dates', $html, $cf, $post_type );
	}

	public static function search_output_event( $html, $cf, $post_type ) {
		global $as_fieldset_start;

		if ( geodir_design_style() ) {
			return self::search_output_event_aui( $html, $cf, $post_type );
		}

		if ( ! ( ! empty( $cf ) && $cf->htmlvar_name == 'event_dates' ) ) {
			return $html;
		}

		$pt_name = geodir_post_type_singular_name( $post_type, true );
		$htmlvar_name = $cf->htmlvar_name;
		$event_dates = isset( $_REQUEST[ $htmlvar_name ] ) ? geodir_event_sanitize_text_field( $_REQUEST[ $htmlvar_name ] ) : NULL;
		$field_label = $cf->frontend_title ? stripslashes( __( $cf->frontend_title, 'geodirectory' ) ) : '';

		$date_format = geodir_event_date_format();

		// Convert to jQuery UI datepicker format.
		$jqueryui_date_format  = geodir_date_format_php_to_jqueryui( $date_format  );
		
		$html .= GeoDir_Adv_Search_Fields::field_wrapper_start( $cf );

		ob_start();
		?><li class="gd-search-row-<?php echo $htmlvar_name; ?>"><?php
		if ( $cf->search_condition == 'FROM' ) {
			$field_value_from = '';
			$field_value_to = '';
			$field_value_from_display = '';
			$field_value_to_display = '';

			if ( is_array( $event_dates ) && ! empty( $event_dates ) ) {
				if ( ! empty( $event_dates['from'] ) ) {
					$field_value_from = sanitize_text_field( $event_dates['from'] );
					$field_value_from_display = date_i18n( $date_format, strtotime( $field_value_from ) );
				}

				if ( ! empty( $event_dates['to'] ) ) {
					$field_value_to = sanitize_text_field( $event_dates['to'] );
					$field_value_to_display = date_i18n( $date_format, strtotime( $field_value_to ) );
				}
			}

			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Dates', 'geodirevents' ), $pt_name );
			}
			$field_label_from = ! empty( $field_label ) ? wp_sprintf( __( 'From: %s', 'geodirevents' ), $field_label ) : wp_sprintf( __( '%s Start Date', 'geodirevents' ), $pt_name );
			$field_label_to = ! empty( $field_label ) ? wp_sprintf( __( 'To: %s', 'geodirevents' ), $field_label ) : wp_sprintf( __( '%s End Date', 'geodirevents' ), $pt_name );
			$aria_label_from = empty( $as_fieldset_start ) ? ' aria-label="' . esc_attr( $field_label_from ) . '"' : '';
			$aria_label_to = empty( $as_fieldset_start ) ? ' aria-label="' . esc_attr( $field_label_to ) . '"' : '';
			?>
			<div class="gd-search-has-date gd-search-<?php echo $htmlvar_name; ?> from-to">
				<?php if ( ! empty( $as_fieldset_start ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>_from"><?php echo $field_label; ?></label>
				<?php } ?>
				<input type="text" id="<?php echo esc_attr( $htmlvar_name ); ?>_from" value="<?php echo esc_attr( $field_value_from_display ); ?>" placeholder="<?php echo esc_attr( $field_label_from ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>[from]" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>" data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>"  data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>"<?php echo $aria_label_from; ?>/>
				<input type="text" id="<?php echo esc_attr( $htmlvar_name ); ?>_to" value="<?php echo esc_attr( $field_value_to_display ); ?>" placeholder="<?php echo esc_attr( $field_label_to ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>[to]" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>" data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>"  data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>"<?php echo $aria_label_to; ?>/>
				<input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[from]" value="<?php echo esc_attr( $field_value_from ); ?>"><input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>[to]" value="<?php echo esc_attr( $field_value_to ); ?>">
			</div>
			<?php
		} else {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Date', 'geodirevents' ), $pt_name );
			}
			$aria_label = empty( $as_fieldset_start ) ? ' aria-label="' . esc_attr( $field_label ) . '"' : '';
			$field_value = ! empty( $event_dates ) && ! is_array( $event_dates ) ? sanitize_text_field( $event_dates ) : '';
			$field_value_display = ! empty( $field_value ) ? date_i18n( $date_format, strtotime( $field_value ) ) : '';
			?>
			<div class="gd-search-has-date gd-search-<?php echo $htmlvar_name; ?>">
				<?php if ( ! empty( $as_fieldset_start ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>"><?php echo $field_label; ?></label>
				<?php } ?>
				<input type="text" id="<?php echo esc_attr( $htmlvar_name ); ?>" value="<?php echo esc_attr( $field_value_display ); ?>" placeholder="<?php echo esc_attr( $field_label ); ?>" class="cat_input gd-search-date-input" field_type="text" data-alt-field="<?php echo esc_attr( $htmlvar_name ); ?>" data-date-format="<?php echo esc_attr( $jqueryui_date_format ); ?>"  data-alt-format="<?php echo esc_attr( geodir_date_format_php_to_jqueryui( 'Y-m-d' ) ); ?>" data-field-key="<?php echo esc_attr( $htmlvar_name ); ?>"<?php echo $aria_label; ?>/><input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>" value="<?php echo esc_attr( $field_value ); ?>">
			</div>
			<?php
		}
		?></li><?php

		$html .= ob_get_clean();

		$html .= GeoDir_Adv_Search_Fields::field_wrapper_end( $cf );

		return apply_filters( 'geodir_event_search_form_output_event_dates', $html, $cf, $post_type );
	}

	public static function search_output_event_aui( $html, $cf, $post_type ) {
		global $as_fieldset_start;

		if ( ! ( ! empty( $cf ) && $cf->htmlvar_name == 'event_dates' ) ) {
			return $html;
		}

		$pt_name = geodir_post_type_singular_name( $post_type, true );
		$htmlvar_name = $cf->htmlvar_name;
		$event_dates = isset( $_REQUEST[ $htmlvar_name ] ) ? geodir_event_sanitize_text_field( $_REQUEST[ $htmlvar_name ] ) : NULL;
		$field_label = $cf->frontend_title ? stripslashes( __( $cf->frontend_title, 'geodirectory' ) ) : '';

		$date_format = geodir_event_date_format();

		// Convert to jQuery UI datepicker format.
		$datepicker_format  = geodir_date_format_php_to_aui( $date_format  );
		
		$html .= GeoDir_Adv_Search_Fields::field_wrapper_start( $cf );

		ob_start();
		if ( $cf->search_condition == 'FROM' ) {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Dates', 'geodirevents' ), $pt_name );
			}
			?>
			<div class="gd-search-has-date gd-search-<?php echo esc_attr( $htmlvar_name ); ?> from-to gd-search-row-<?php echo esc_attr( $htmlvar_name ); ?>">
				<?php if ( ! empty( $as_fieldset_start ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>" class="text-muted"><?php echo $field_label; ?></label>
				<?php }

				// Flatpickr attributes
				$extra_attributes = array();
				$extra_attributes['data-alt-input'] = 'true';
				$extra_attributes['data-alt-format'] = $datepicker_format;
				$extra_attributes['data-date-format'] = 'Y-m-d';
				$extra_attributes['data-disable-mobile'] = 'true'; // Disable mobile native datetime input.

				// Range
				$extra_attributes['data-mode'] = 'range';
				echo aui()->input(
					array(
						'id'                => $htmlvar_name,
						'name'              => $htmlvar_name,
						'type'              => 'datepicker',
						'placeholder'       => $field_label,
						'class'             => '',
						'value'             => esc_attr( $event_dates ),
						'extra_attributes'  => $extra_attributes
					)
				);
				?>
			</div>
			<?php
		} else {
			if ( empty( $field_label ) ) {
				$field_label = wp_sprintf( __( '%s Date', 'geodirevents' ), $pt_name );
			}
			$event_dates = ! empty( $event_dates ) && ! is_array( $event_dates ) ? sanitize_text_field( $event_dates ) : '';
			?>
			<div class="gd-search-has-date gd-search-<?php echo esc_attr( $htmlvar_name ); ?> gd-search-row-<?php echo esc_attr( $htmlvar_name ); ?>">
				<?php if ( ! empty( $as_fieldset_start ) ) { ?>
					<label for="<?php echo esc_attr( $htmlvar_name ); ?>"><?php echo $field_label; ?></label>
				<?php }

				// Flatpickr attributes
				$extra_attributes = array();
				$extra_attributes['data-alt-input'] = 'true';
				$extra_attributes['data-alt-format'] = $datepicker_format;
				$extra_attributes['data-date-format'] = 'Y-m-d';
				$extra_attributes['data-disable-mobile'] = 'true'; // Disable mobile native datetime input.
				echo aui()->input(
					array(
						'id'                => $htmlvar_name,
						'name'              => $htmlvar_name,
						'type'              => 'datepicker',
						'placeholder'       => $field_label,
						'class'             => '',
						'value'             => esc_attr( $event_dates ),
						'extra_attributes'  => $extra_attributes
					)
				);

				?>
			</div>
			<?php
		}

		$html .= ob_get_clean();

		$html .= GeoDir_Adv_Search_Fields::field_wrapper_end( $cf );

		return apply_filters( 'geodir_event_search_form_output_event_dates', $html, $cf, $post_type );
	}

	/**
	 * Add event fields to post meta keys.
	 *
	 * @since 2.0.1.0
	 *
	 * @param array $fields Post meta custom keys.
	 * @param string $post_type Post type.
	 * @return array Post meta keys.
	 */
	public static function post_meta_standard_fields( $fields, $post_type ) {
		$fields['event_start_date'] = array(
			'name' => 'event_start_date',
			'htmlvar_name' => 'event_start_date',
			'frontend_title' => __( 'Event Start Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_start_time'] = array(
			'name' => 'event_start_time',
			'htmlvar_name' => 'event_start_time',
			'frontend_title' => __( 'Event Start Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_start_date_time'] = array(
			'name' => 'event_start_date_time',
			'htmlvar_name' => 'event_start_date_time',
			'frontend_title' => __( 'Event Starts On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		$fields['event_end_date'] = array(
			'name' => 'event_end_date',
			'htmlvar_name' => 'event_end_date',
			'frontend_title' => __( 'Event End Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_end_time'] = array(
			'name' => 'event_end_time',
			'htmlvar_name' => 'event_end_time',
			'frontend_title' => __( 'Event End Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_end_date_time'] = array(
			'name' => 'event_end_date_time',
			'htmlvar_name' => 'event_end_date_time',
			'frontend_title' => __( 'Event Ends On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		$fields['event_first_start_date'] = array(
			'name' => 'event_first_start_date',
			'htmlvar_name' => 'event_first_start_date',
			'frontend_title' => __( 'First Schedule Start Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_first_start_time'] = array(
			'name' => 'event_first_start_time',
			'htmlvar_name' => 'event_first_start_time',
			'frontend_title' => __( 'First Schedule Start Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_first_start_date_time'] = array(
			'name' => 'event_first_start_date_time',
			'htmlvar_name' => 'event_first_start_date_time',
			'frontend_title' => __( 'First Schedule Starts On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		$fields['event_first_end_date'] = array(
			'name' => 'event_first_end_date',
			'htmlvar_name' => 'event_first_end_date',
			'frontend_title' => __( 'First Schedule End Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_first_end_time'] = array(
			'name' => 'event_first_end_time',
			'htmlvar_name' => 'event_first_end_time',
			'frontend_title' => __( 'First Schedule End Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_first_end_date_time'] = array(
			'name' => 'event_first_end_date_time',
			'htmlvar_name' => 'event_first_end_date_time',
			'frontend_title' => __( 'First Schedule Ends On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		$fields['event_last_start_date'] = array(
			'name' => 'event_last_start_date',
			'htmlvar_name' => 'event_last_start_date',
			'frontend_title' => __( 'Last Schedule Start Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_last_start_time'] = array(
			'name' => 'event_last_start_time',
			'htmlvar_name' => 'event_last_start_time',
			'frontend_title' => __( 'Last Schedule Start Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_last_start_date_time'] = array(
			'name' => 'event_last_start_date_time',
			'htmlvar_name' => 'event_last_start_date_time',
			'frontend_title' => __( 'Last Schedule Starts On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		$fields['event_last_end_date'] = array(
			'name' => 'event_last_end_date',
			'htmlvar_name' => 'event_last_end_date',
			'frontend_title' => __( 'Last Schedule End Date', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_format() ),
		);

		$fields['event_last_end_time'] = array(
			'name' => 'event_last_end_time',
			'htmlvar_name' => 'event_last_end_time',
			'frontend_title' => __( 'Last Schedule End Time', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-clock',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_time_format() ),
		);

		$fields['event_last_end_date_time'] = array(
			'name' => 'event_last_end_date_time',
			'htmlvar_name' => 'event_last_end_date_time',
			'frontend_title' => __( 'Last Schedule Ends On', 'geodirevents' ),
			'type' => 'custom',
			'field_icon' => 'fas fa-calendar-alt',
			'field_type_key' => '',
			'css_class' => '',
			'extra_fields' => array( 'date_format' => geodir_event_date_time_format() ),
		);

		return $fields;
	}

	/**
	 * Filter event fields to custom field output.
	 *
	 * @since 2.0.0.20
	 *
	 * @param string $html The html to filter.
	 * @param string $location The location to output the html.
	 * @param array $cf The custom field array.
	 * @param string $output The output string that tells us what to output.
	 * @return string The html to output.
	 */
	public static function custom_field_output_event_date_time( $html, $location, $cf, $output, $_gd_post ) {
		global $post;

		if ( ! empty( $_gd_post ) ) {
			$gd_post = $_gd_post;
		} else {
			global $gd_post;
		}

		if ( ! ( ! empty( $gd_post->post_type ) && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) ) {
			return $html;
		}

		$schedule = array();

		if ( ! empty( $gd_post->recurring ) ) {
			if ( strpos( $cf['htmlvar_name'], 'event_first_start' ) === 0 || strpos( $cf['htmlvar_name'], 'event_first_end' ) === 0 ) {
				$schedule = GeoDir_Event_Schedules::get_start_schedule( $gd_post->ID );
			} else if ( strpos( $cf['htmlvar_name'], 'event_last_start' ) === 0 || strpos( $cf['htmlvar_name'], 'event_last_end' ) === 0 ) {
				$schedule = GeoDir_Event_Schedules::get_last_schedule( $gd_post->ID );
			}
		}

		if ( empty( $schedule ) ) {
			if ( isset( $gd_post->start_date ) ) {
				$schedule = $gd_post;
			} elseif ( ! empty( $post ) && $gd_post->ID == $post->ID && isset( $post->start_date ) ) {
				$schedule = $post;
			} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $gd_post->ID, 'upcoming', 1 ) ) ) {
				$schedule = $schedules[0];
			} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $gd_post->ID, '', 1 ) ) ) {
				$schedule = $schedules[0];
			}
		}

		if ( empty( $schedule ) ) {
			return $html;
		}

		$htmlvar_name = $cf['htmlvar_name'];

		if ( ! empty( $schedule->start_date ) && $schedule->start_date != '0000-00-00' ) {
			$date_format = geodir_event_date_format();
			$time_format = geodir_event_time_format();
			$date_time_format = geodir_event_date_time_format();

			$start_date = $schedule->start_date;
			$start_time = $schedule->start_time;
			$start_date_time = $start_date . ' ' . $start_time;
			$end_date = $schedule->end_date;
			$end_time = $schedule->end_time;
			$end_date_time = $end_date . ' ' . $end_time;

			$format = '';
			if ( $cf['extra_fields'] != '' ) {
				$_date_format = maybe_unserialize( $cf['extra_fields'] );

				if ( ! empty( $_date_format['date_format'] ) ) {
					$format = $_date_format['date_format'];
				}
			}

			$value = '';
			$value_raw = '';

			switch ( $htmlvar_name ) {
				case 'event_start_date':
				case 'event_first_start_date':
				case 'event_last_start_date':
					if ( empty( $format ) ) {
						$format = $date_format;
					}

					$value = $start_date_time;
					$value_raw = $start_date;
					break;
				case 'event_start_time':
				case 'event_first_start_time':
				case 'event_last_start_time':
					$format = $time_format;

					$value = $start_date_time;
					$value_raw = $start_time;
					break;
				case 'event_start_date_time':
				case 'event_first_start_date_time':
				case 'event_last_start_date_time':
					if ( empty( $format ) ) {
						$format = $date_time_format;
					}

					$value = $start_date_time;
					$value_raw = $start_date_time;
					break;
				case 'event_end_date':
				case 'event_first_end_date':
				case 'event_last_end_date':
					if ( empty( $format ) ) {
						$format = $date_format;
					}

					$value = $end_date_time;
					$value_raw = $end_date;
					break;
				case 'event_end_time':
				case 'event_first_end_time':
				case 'event_last_end_time':
					$format = $time_format;

					$value = $end_date_time;
					$value_raw = $end_time;
					break;
				case 'event_end_date_time':
				case 'event_first_end_date_time':
				case 'event_last_end_date_time':
					if ( empty( $format ) ) {
						$format = $date_time_format;
					}

					$value = $end_date_time;
					$value_raw = $end_date_time;
					break;
			}

			$output = geodir_field_output_process( $output );

			if ( ! empty( $output ) && isset( $output['raw'] ) ) {
				// Database value.
				return $value_raw;
			} else if ( ! empty( $output ) && isset( $output['strip'] ) ) {
				// Stripped value.
				return $value;
			}

			$value = date_i18n( $format, strtotime( $value ) );

			$field_icon = geodir_field_icon_proccess( $cf );

			if ( strpos( $field_icon, 'http' ) !== false ) {
				$field_icon_af = '';
			} elseif ( $field_icon == '' ) {
				$field_icon_af = '<i class="fas fa-calendar" aria-hidden="true"></i>';
			} else {
				$field_icon_af = $field_icon;
				$field_icon = '';
			}

			$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

			 if ( $output=='' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
			if ( $output=='' || isset( $output['label'] ) ) $html .= trim( $cf['frontend_title'] ) != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': </span>' : '';
			if ( $output=='' || isset( $output['icon'] ) ) $html .= '</span>';
			if ( $output=='' || isset( $output['value'] ) ) $html .= $value;

			$html .= '</div>';
		}

		return $html;
	}

	/**
	 * Filter badge conditions.
	 * 
	 * @since 2.1.0.2
	 * 
	 * @global object $post The post object.
	 *
	 * @param array $conditions Badge conditions.
	 * @return array Filtered conditions.
	 */
	public static function post_badge_conditions( $conditions ) {
		$conditions['is_past_event'] = __( 'is past event (for events only)', 'geodirevents' );
		$conditions['is_ongoing_event'] = __( 'is ongoing event (for events only)', 'geodirevents' );
		$conditions['is_upcoming_event'] = __( 'is upcoming event (for events only)', 'geodirevents' );

		return $conditions;
	}

	/**
	 * Filter event dates post badge match value.
	 * 
	 * @since 2.1.0.2
	 * 
	 * @param bool $match_found True if match found else False.
	 * @param array $args Badge arguments.
	 * @param object $gd_post The GD post object.
	 * @return bool
	 */
	public static function post_badge_check_match_found( $match_found, $args, $gd_post ) {
		global $post;

		if ( ! empty( $args['condition'] ) && in_array( $args['condition'], array( 'is_past_event', 'is_ongoing_event', 'is_upcoming_event' ) ) && GeoDir_Post_types::supports( $gd_post->post_type, 'events' ) ) {
			if ( $args['condition'] == 'is_past_event' ) {
				$today = strtotime( date_i18n( 'Y-m-d' ) );
				$is_past = false;

				if ( ! empty( $gd_post->end_date ) && $gd_post->end_date != '0000-00-00' ) {
					$is_past = strtotime( $gd_post->end_date ) < $today;
				} elseif ( ! empty( $post ) && $gd_post->ID == $post->ID && ! empty( $post->end_date ) && $post->end_date != '0000-00-00' ) {
					$is_past = strtotime( $post->end_date ) < $today;
				} elseif ( ( GeoDir_Event_Schedules::get_schedules( $gd_post->ID, 'upcoming', 1 ) ) ) {
					$is_past = false;
				} elseif ( ( GeoDir_Event_Schedules::get_schedules( $gd_post->ID, 'past', 1 ) ) ) {
					$is_past = true;
				}

				$match_found = $is_past;
			} elseif ( $args['condition'] == 'is_ongoing_event' ) {
				$now = strtotime( date_i18n( 'Y-m-d H:i:s' ) );
				$is_ongoing = false;

				$schedule = array();
				if ( ! empty( $gd_post->start_date ) ) {
					$schedule = $gd_post;
				} elseif ( ! empty( $post ) && $gd_post->ID == $post->ID && ! empty( $post->start_date ) ) {
					$schedule = $post;
				} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $gd_post->ID, 'today', 1 ) ) ) {
					$schedule = $schedules[0];
				}

				if ( ! empty( $schedule ) ) {
					$start_time = ! empty( $schedule->all_day ) ? '00:00:00' : $schedule->start_time;
					$end_time = ( $schedule->end_time == '00:00:00' || empty( $schedule->end_time ) ) && $schedule->start_date == $schedule->end_date ? '23:59:59' : $schedule->end_time;

					if ( strtotime( $schedule->start_date . ' ' . $start_time ) <= $now && $now <= strtotime( $schedule->end_date . ' ' . $end_time ) ) {
						$is_ongoing = true;
					}
				}

				$match_found = $is_ongoing;
			} elseif ( $args['condition'] == 'is_upcoming_event' ) {
				$now = strtotime( date_i18n( 'Y-m-d H:i:s' ) );
				$is_upcoming = false;

				$schedule = array();
				if ( ! empty( $gd_post->start_date ) ) {
					$schedule = $gd_post;
				} elseif ( ! empty( $post ) && $gd_post->ID == $post->ID && ! empty( $post->start_date ) ) {
					$schedule = $post;
				} elseif ( ( $schedules = GeoDir_Event_Schedules::get_schedules( $gd_post->ID, 'upcoming', 1 ) ) ) {
					$schedule = $schedules[0];
				}

				if ( ! empty( $schedule ) ) {
					$start_time = ! empty( $schedule->all_day ) ? '00:00:00' : $schedule->start_time;

					if ( strtotime( $schedule->start_date . ' ' . $start_time ) > $now ) {
						$is_upcoming = true;
					}
				}

				$match_found = $is_upcoming;
			}
		}

		return $match_found;
	}

	/**
	 * Set post default sort option for event type CPT.
	 *
	 * @since 2.2.1
	 *
	 * @param string $default_sort Default sort.
	 * @param string $post_type The post type.
	 * @param object $field Field object.
	 * @return string Default sort field.
	 */
	public static function set_event_default_sort( $default_sort, $post_type, $field ) {
		if ( empty( $default_sort ) && GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$default_sort = 'event_dates_asc';
		}

		return $default_sort;
	}

	/*
	 * Filter element tag text custom fields.
	 *
	 * @since 2.2.6
	 *
	 * @param array  $groups Field groups.
	 * @param object $tag Tag object.
	 * @return array Fields groups.
	 */
	public static function elementor_tag_text_fields( $groups, $tag ) {
		$event_fields = self::post_meta_standard_fields( array(), 'gd_event' );

		foreach ( $event_fields as $key => $field ) {
			$groups[0]['options'][ $key ] = $key;
		}

		return $groups;
	}

	/*
	 * Render the elementor tag event field value.
	 *
	 * @since 2.2.6
	 *
	 * @param mixed  $value Tag value.
	 * @param string $key Tag key.
	 * @param object $tag Tag object.
	 * @return mixed Rendered value.
	 */
	public static function elementor_tag_text_render_value( $value, $key, $tag ) {
		if ( strpos( $key, 'event_' ) === 0 ) {
			$event_fields = array_keys( self::post_meta_standard_fields( array(), 'gd_event' ) );

			if ( in_array( $key, $event_fields ) ) {
				$show = $tag->get_settings( 'show' );

				$value = do_shortcode( '[gd_post_meta key="' . esc_attr( $key ) . '" show="' . esc_attr( $show ) . '" no_wrap=1]' );
			}
		}

		return $value;
	}

	/**
	 * Search form inputs.
	 *
	 * @since 2.3.17
	 *
	 * @param array $args Input args.
	 */
	public static function search_form_inputs( $args ) {
		if ( ! empty( $_REQUEST['event_calendar'] ) && geodir_is_page( 'search' ) ) {
			echo '<input type="hidden" name="event_calendar" value="' . esc_attr( $_REQUEST['event_calendar'] ) . '">';
		}
	}
}