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

if ( ! class_exists( 'GeoDir_Event_Settings_CPT_General', false ) ) :

	/**
	 * GeoDir_Location_Settings_General.
	 */
	class GeoDir_Event_Settings_CPT_General extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'event_general';
			$this->label = __( 'General', 'geodirevents' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 19 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );

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
				''	=> __( 'General Settings', 'geodirevents' ),
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

			$settings = apply_filters( 'geodir_event_settings_cpt_general_options', 
				array(
					array( 
						'name' => __( 'Listings', 'geodirevents' ), 
						'type' => 'title', 
						'desc' => '', 
						'id' => 'geodir_event_listing_settings' 
					),
					array(
						'type' => 'select',
						'id' => 'event_defalt_filter',
						'name' => __( 'Default event filter', 'geodirevents' ),
						'desc' => __( 'Set the default filter view of event on listing page.', 'geodirevents' ),
						'class' => 'geodir-select',
						'default'  => 'upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array( 
							'all' => __( 'All', 'geodirevents' ),
							'today' => __( 'Today', 'geodirevents' ),
							'upcoming' => __( 'Upcoming', 'geodirevents' ),
							'past' => __( 'Past', 'geodirevents' )
						),
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
						'class' => 'geodir-select',
						'default'  => 'upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array( 
							'all' => __( 'All', 'geodirevents' ),
							'today' => __( 'Today', 'geodirevents' ),
							'upcoming' => __( 'Upcoming', 'geodirevents' ),
							'past' => __( 'Past', 'geodirevents' )
						),
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'sectionend', 
						'id' => 'geodir_event_listing_settings'
					),
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
						'class' => 'geodir-select',
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
						'class' => 'geodir-select',
						'default'  => get_option( 'date_format' ),
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array_unique( $display_date_options ),
						'desc_tip' => true,
						'advanced' => false,
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_use_custom_format',
						'name' => '',
						'desc' => __( 'OR use custom date form setting for display event dates.', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'text',
						'id'   => 'event_custom_date_format',
						'name' => '',
						'desc' => __( 'Set the custom date format to display event dates.', 'geodirevents' ),
						'css'  => 'min-width:300px;',
						'default'  => '',
						'desc_tip' => true,
						'advanced' => true
					),
					array(
						'type' => 'sectionend', 
						'id' => 'geodir_event_date_format_settings'
					),
					array( 
						'name' => __( 'Link Business', 'geodirevents' ), 
						'type' => 'title',
						'desc' => '', 
						'id' => 'geodir_event_link_business_settings',					
					),
					array(
						'type' => 'checkbox',
						'id'   => 'event_use_custom_format',
						'name' => __( 'Any linking Author?', 'geodirevents' ),
						'desc' => __( 'Allow linking to any post not just users own posts?', 'geodirevents' ),
						'default' => '0',
						'advanced' => true
					),
					array(
						'type' => 'number',
						'id' => 'geodir_event_linked_count',
						'name' => __('Posts to show', 'geodirevents'),
						'desc' => __('The number of posts to show by default.', 'geodirevents'),
						'default'  => '5',
						'desc_tip' => true,
						'advanced' => false
					),
					array(
						'type' => 'select',
						'id' => 'geodir_event_linked_event_type',
						'name' => __( 'Event filter', 'geodirevents' ),
						'desc' => __( 'Set the event filter.', 'geodirevents' ),
						'class' => 'geodir-select',
						'default'  => 'upcoming',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array( 
							'all' => __( 'All', 'geodirevents' ),
							'today' => __( 'Today', 'geodirevents' ),
							'upcoming' => __( 'Upcoming', 'geodirevents' ),
							'past' => __( 'Past', 'geodirevents' )
						),
						'desc_tip' => true,
						'advanced' => true,
					),
					array(
						'type' => 'select',
						'id' => 'geodir_event_linked_sortby',
						'name' => __( 'Sort by', 'geodirevents' ),
						'desc' => __( 'Set the linked event listing sorting.', 'geodirevents' ),
						'class' => 'geodir-select',
						'default'  => 'latest',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options' => array( 
							'az' => __( 'A-Z', 'geodirevents' ),
							'latest' => __( 'Latest', 'geodirevents' ),
							'featured' => __( 'Featured', 'geodirevents' ),
							'high_review' => __( 'Review', 'geodirevents' ),
							'high_rating' => __( 'Rating', 'geodirevents' ),
							'random' => __( 'Random', 'geodirevents' ),
							'upcoming' => __( 'Upcoming', 'geodirevents' ),
						),
						'desc_tip' => true,
						'advanced' => true,
					),
					array(
						'type' => 'select',
						'name' => __('Layout', 'geodirevents'),
						'id' => 'geodir_event_linked_listing_view',
						'desc' => __('How the listings should laid out by default.', 'geodirevents'),
						'class' => 'geodir-select',
						'default'  => 'gridview_onehalf',
						'placeholder' => __( 'Select', 'geodirevents' ),
						'options'   =>  array(
							"gridview_onehalf" => __('Grid View (Two Columns)', 'geodirevents'),
							"gridview_onethird" => __('Grid View (Three Columns)', 'geodirevents'),
							"gridview_onefourth" => __('Grid View (Four Columns)', 'geodirevents'),
							"gridview_onefifth" => __('Grid View (Five Columns)', 'geodirevents'),
							"list" => __('List view', 'geodirevents'),
						),
						'desc_tip' => true,
						'advanced' => true
					),
					array(
						'type' => 'sectionend', 
						'id' => 'geodir_event_link_business_settings',
						'advanced' => true	
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
	}

endif;

return new GeoDir_Event_Settings_CPT_General();
