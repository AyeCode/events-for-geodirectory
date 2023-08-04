<?php
/**
 * GeoDirectory Event Manager Settings
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDir_Event_Manager/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Event_Settings_Events', false ) ) :

	/**
	 * GeoDir_Event_Settings_Events.
	 */
	class GeoDir_Event_Settings_Events extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'events';
			$this->label = __( 'Events', 'geodirevents' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
			//add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

			add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );

			add_action( 'geodir_settings_form_method_tab_' . $this->id, array( $this, 'form_method' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				''	=> __( 'General', 'geodirevents' ),
			);

			return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			GeoDir_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );

			GeoDir_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$input_date_formats = geodir_event_input_date_formats();
			$input_date_options = array();
			foreach ( $input_date_formats as $format ) {
				$input_date_options[$format] = $format . ' ( ' . date_i18n( $format, time() ) . ' )';
			}

			$display_date_formats = geodir_event_display_date_formats();
			$display_date_options = array();
			foreach ( $display_date_formats as $format ) {
				$display_date_options[$format] = $format . ' ( ' . date_i18n( $format, time() ) . ' )';
			}

			$event_options = geodir_event_filter_options();

			$settings = apply_filters( 'geodir_event_settings_general_options', 
				array(
					array( 
						'name' => __( 'Date Format', 'geodirevents' ), 
						'type' => 'title',
						'desc' => '', 
						'id' => 'geodir_event_date_format_settings' 
					),
					array(
						'type' => 'select',
						'id' => 'event_field_date_format',
						'name' => __( 'Input date format', 'geodirevents' ),
						'desc' => __( 'Date format to use in the add event form.', 'geodirevents' ),
						'default'  => 'Y-m-d',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array_unique( $input_date_options ),
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'select',
						'id' => 'event_display_date_format',
						'name' => __( 'Display date format', 'geodirevents' ),
						'desc' => __( 'Date format to display event dates.', 'geodirevents' ),
						'default'  => get_option( 'date_format' ),
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array_unique( $display_date_options ),
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_use_custom_format',
						'name' => ' ',
						'desc' => __( 'OR use custom date form setting for display event dates.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'text',
						'id'   => 'event_custom_date_format',
						'name' => __( 'Custom Date Format', 'geodirevents' ),
						'desc' => __( 'Set the custom date format to display event dates.', 'geodirevents' ),
						'css'  => 'min-width:300px;',
						'default' => '',
						'desc_tip' => true,
						'advanced' => true,
						'element_require' => '[%event_use_custom_format%:checked]'
					),
					array(
						'type' => 'sectionend', 
						'id' => 'geodir_event_date_format_settings'
					),
					array( 
						'name' => __( 'Listings', 'geodirevents' ), 
						'type' => 'title', 
						'desc' => '', 
						'id' => 'geodir_event_listing_settings' 
					),
					array(
						'type' => 'select',
						'id' => 'event_default_filter',
						'name' => __( 'Default event filter', 'geodirevents' ),
						'desc' => __( 'Set the default filter view of event on listing page.', 'geodirevents' ),
						'default'  => 'upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => $event_options,
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'select',
						'id' => 'event_map_filter',
						'name' => __( 'Directory Map Event Filter', 'geodirevents' ),
						'desc' => __( 'Set the event type filter to show on the events directory map.', 'geodirevents' ),
						'default'  => 'ongoing_upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => $event_options,
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_disable_recurring',
						'name' => __( 'Disable recurring feature?', 'geodirevents' ),
						'desc' => __( 'This allows to disable recurring event feature.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_hide_past_dates',
						'name' => __( 'Hide event past schedules?', 'geodirevents' ),
						'desc' => __( 'Hide past event schedules on the event listings.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_include_ongoing',
						'name' => __( 'Include Ongoing', 'geodirevents' ),
						'desc' => __( 'Include ongoing events in upcoming events.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_remove_title_date',
						'name' => __( 'Remove Date From Title', 'geodirevents' ),
						'desc' => __( 'Remove the recurring date from recurring event title.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'number',
						'id' => 'event_max_schedules',
						'name' => __( 'Limit Event Occurrences', 'geodirevents' ),
						'desc' => __( 'Sometimes people set the end date like 50 years from now and it creates like 50 extra pages of events. This will help to limit max number of event occurrences for the event. Leave blank or use 0 to allow all possible occurrences.', 'geodirevents' ),
						'placeholder' => __( 'No Limit', 'geodirevents' ),
						'default' => '',
						'desc_tip' => true,
						'advanced' => true
					),
					array(
						'type' => 'number',
						'id'   => 'event_map_popup_count',
						'name' => __( 'Schedules in map popup', 'geodirevents' ),
						'desc' => __( 'No. of schedule dates to display for event marker info window on the map. Default: 1', 'geodirevents' ),
						'default'  => '1',
						'desc_tip' => true,
						'advanced' => true
					),
					array(
						'type' => 'select',
						'id' => 'event_map_popup_dates',
						'name' => __( 'Display dates in map popup', 'geodirevents' ),
						'desc' => __( 'Set the filter to view schedule dates for event marker info window on the map.', 'geodirevents' ),
						'default'  => 'upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => $event_options,
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'sectionend', 
						'id' => 'geodir_event_listing_settings'
					)
				)
			);

			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
		}
		
		/**
		 * Form method.
		 *
		 * @param  string $method
		 *
		 * @return string
		 */
		public function form_method( $method ) {
			global $current_section;

			return 'post';
		}

		public function get_sort_options($post_type = 'gd_event'){
			$options = array(
				"az"			=>  __('A-Z', 'geodirevents'),
				"latest"        =>  __('Latest', 'geodirevents'),
				"high_review"   =>  __('Most reviews', 'geodirevents'),
				"high_rating"   =>  __('Highest rating', 'geodirevents'),
				"random"        =>  __('Random', 'geodirevents'),
				"distance_asc" 	=>  __('Distance to current post', 'geodirevents'),
			);

			if ( ! get_option( 'geodirectory_version' ) ) {
				return $options;
			}

			$sort_options = geodir_get_sort_options( $post_type );
			if(!empty($sort_options)){
				foreach($sort_options as $sort_option){
					if(!empty($sort_option->sort_asc) && !empty($sort_option->asc_title)){
						$options[$sort_option->htmlvar_name."_asc"] = __($sort_option->asc_title,'geodirectory');
					}
					if(!empty($sort_option->sort_desc) && !empty($sort_option->desc_title)){
						$options[$sort_option->htmlvar_name."_desc"] = __($sort_option->desc_title,'geodirectory');
					}
				}
			}

			return $options;
		}
	}

endif;

return new GeoDir_Event_Settings_Events();
