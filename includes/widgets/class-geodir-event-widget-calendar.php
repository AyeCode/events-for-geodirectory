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
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'calendar-alt',
            'block-category'=> 'widgets',
            'block-keywords'=> "['calendar','event','event calendar']",
            'class_name'    => __CLASS__,
            'base_id'       => 'geodir_event_calendar',
            'name'          => __( 'GD > Events Calendar', 'geodirevents' ),
            'widget_ops'    => array(
                'classname'   => 'geodir-event-calendar',
                'description' => esc_html__( 'Displays the events calendar for event post type.', 'geodirevents'),
                'customize_selective_refresh' => true,
                'geodirectory' => true,
            ),
        );

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {
		$arguments = array(
			'title'  => array(
                'title' => __('Title:', 'geodirevents'),
                'desc' => __('The widget title.', 'geodirevents'),
                'type' => 'text',
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false
            ),
            'post_type'  => array(
                'title' => __( 'Default Post Type:', 'geodirevents' ),
                'desc' => __( 'Select post type to filter posts on calendar.', 'geodirevents' ),
                'type' => 'select',
                'options' => self::post_type_options(),
                'default' => 'gd_event',
                'desc_tip' => true,
                'advanced' => true
            ),
			'week_start_day' => array(
				'type' 		=> 'select',
                'title' 	=> __( 'Week start day:', 'geodirevents' ),
                'desc' 		=> __( 'Week start day of the calendar.', 'geodirevents' ),
                'options'   =>  array(
                    '1'	=>  __( 'Monday' ),
                    '0'	=>  __( 'Sunday' ),
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
                    '0'	=>  __( 'M', 'Monday initial' ),
                    '1'	=>  __( 'Mo', 'geodirevents' ),
					'2'	=>  __( 'Mon' ),
					'3'	=>  __( 'Monday' ),
                ),
                'default'  => '0',
                'desc_tip' => true,
                'advanced' => true
            ),
            'use_viewing_post_type'  => array(
                'title' => __( 'Use current viewing post type?', 'geodirevents'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '0',
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

	/**
     * The Super block output function.
     *
     * @param array $instance
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output( $instance = array(), $args = array(), $content = '' ) {

        $instance = wp_parse_args(
			(array)$instance,
            array(
				'title' => '',
				'post_type' => 'gd_event',
				'week_start_day' => '1',
				'week_day_format' => '0',
				'use_viewing_post_type' => '0'
            )
        );

        ob_start();

        $this->output_html( $args, $instance );

        return ob_get_clean();
    }

	/**
     * Generates popular postview HTML.
     *
     * @param array|string $args               Display arguments including before_title, after_title, before_widget, and
     *                                         after_widget.
     * @param array|string $instance           The settings for the particular instance of the widget.
     */
    public function output_html( $args = '', $instance = '' ) {
		$use_viewing_post_type = ! empty( $instance['use_viewing_post_type'] ) ? true : false;

        if ( $use_viewing_post_type ) {
            $current_post_type = geodir_get_current_posttype();

            if ( $current_post_type != '' && $current_post_type != $instance['post_type'] ) {
                $instance['post_type'] = $current_post_type;
            }
        }

		ob_start();

		echo GeoDir_Event_Calendar::display_calendar( $args, $instance );

		$output = ob_get_clean();

		echo $output;
	}

	public static function post_type_options() {
		$post_types = geodir_get_posttypes( 'options-plural' );
		$event_post_types = GeoDir_Event_Post_Type::get_event_post_types();

		$options = $post_types;
		foreach ( $post_types as $post_type => $name ) {
			if ( ! in_array( $post_type, $event_post_types ) ) {
				unset( $options[$post_type] );
			}
		}

		return $options;
	}
}

