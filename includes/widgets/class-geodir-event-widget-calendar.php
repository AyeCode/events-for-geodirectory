<?php

/**
 * GeoDir_Event_Widget_Calendar class.
 *
 * @since 2.0.0
 */
class GeoDir_Event_Widget_Calendar extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     	=> 'geodirevents',
			'block-icon'     	=> 'calendar-alt',
			'block-category' 	=> 'common',
			'block-keywords' 	=> "['calendar','event','event calendar']",
			'class_name'     	=> __CLASS__,
			'base_id'        	=> 'geodir_event_calendar',
			'name'           	=> __( 'GD > Events Calendar', 'geodirevents' ),
			'widget_ops'     	=> array(
				'classname'     => 'geodir-wgt-event-calendar',
				'description'   => esc_html__( 'Displays the events calendar.', 'geodirevents' ),
				'geodirectory'  => true,
				'gd_show_pages' => array(),
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {
		$arguments = array(
			'title' => array(
				'type' 		=> 'text',
                'title' 	=> __( 'Title:', 'geodirevents' ),
                'desc' 		=> __( 'The widget title.', 'geodirevents' ),
                'default'  	=> '',
                'desc_tip' 	=> true,
                'advanced' 	=> false
            ),
            'week_start_day' => array(
				'type' 		=> 'select',
                'title' 	=> __( 'Week start day:', 'geodirevents' ),
                'desc' 		=> __( 'Week start day of the calendar.', 'geodirevents' ),
                'options'   =>  array(
                    '1'	=>  __( 'Monday', 'geodirevents' ),
                    '0'	=>  __( 'Sunday', 'geodirevents' ),
                ),
                'default'  => '1',
                'desc_tip' => true,
                'advanced' => true
            ),
            'week_day_format' => array(
				'type' 		=> 'select',
                'title' 	=> __( 'Start day:', 'geodirevents' ),
                'desc' 		=> __( 'Start day of the calendar.', 'geodirevents' ),
                'options'   =>  array(
                    '0'	=>  __( 'M', 'geodirevents' ),
                    '1'	=>  __( 'Mo', 'geodirevents' ),
					'2'	=>  __( 'Mon', 'geodirevents' ),
					'3'	=>  __( 'Monday', 'geodirevents' ),
                ),
                'default'  => '0',
                'desc_tip' => true,
                'advanced' => true
            ),
            'add_location_filter' => array(
                'title' 	=> __( 'Enable location filter?', 'geodirevents' ),
                'type' 		=> 'checkbox',
                'desc_tip' 	=> true,
                'value'  	=> '1',
                'default'  	=> '0',
                'advanced' 	=> true
            )
		);

		return $arguments;
	}

	public function output( $instance = array(), $args = array(), $content = '' ) {
		extract( $args, EXTR_SKIP );

		ob_start();

		GeoDir_Event_Calendar::display_calendar( $args, $instance );

		$output = ob_get_clean();

		return $output;
	}	
}

