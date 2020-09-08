<?php

/**
 * GeoDir_Event_Widget_AYI class.
 *
 * @since 2.0.0
 */
class GeoDir_Event_Widget_AYI extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     	=> 'geodirevents',
			'block-icon'     	=> 'calendar-alt',
			'block-category' 	=> 'geodirectory',
			'block-keywords' 	=> "['ayi','event','geodir event']",
			'class_name'     	=> __CLASS__,
			'base_id'        	=> 'geodir_event_ayi',
			'name'           	=> __( 'GD > Are You Interested?', 'geodirevents' ),
			'widget_ops'     	=> array(
				'classname'     => 'geodir-wgt-event-ayi '.geodir_bsui_class(),
				'description'   => esc_html__( 'Displays "Are You Interested?" widget on front end.', 'geodirevents' ),
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
            )
		);

		return $arguments;
	}

	public function output( $instance = array(), $args = array(), $content = '' ) {
		extract( $args, EXTR_SKIP );

		$instance['is_preview'] = $this->is_preview();

		$output = GeoDir_Event_AYI::geodir_ayi_widget_display( $instance, $args );

		return $output;
	}	
}

