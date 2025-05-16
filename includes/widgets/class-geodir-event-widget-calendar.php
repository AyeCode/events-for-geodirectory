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
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['calendar','event','event calendar']",
			'class_name'    => __CLASS__,
			'base_id'       => 'geodir_event_calendar',
			'name'          => __( 'GD > Events Calendar', 'geodirevents' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-event-calendar '.geodir_bsui_class(),
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
		$design_style = geodir_design_style();

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
					'0'	=>  __( 'Sunday' ),
					'1'	=>  __( 'Monday' ),
					'2'	=>  __( 'Tuesday' ),
					'3'	=>  __( 'Wednesday' ),
					'4'	=>  __( 'Thursday' ),
					'5'	=>  __( 'Friday' ),
					'6'	=>  __( 'Saturday' )
				),
				'default'  => get_option( 'start_of_week' ),
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

		if ( $design_style ) {
			$arguments['size'] = array(
				'type' 		=> 'select',
				'title' 	=> __( 'Calendar Size', 'geodirevents' ),
				'desc' 		=> __( 'Small is best used in sidebars and small spaces.', 'geodirevents' ),
				'options'   =>  array(
					'small'	=>  __( 'Small', 'geodirevents' ),
					'medium'	=>  __( 'Medium', 'geodirevents' ),
				),
				'default'  => 'small',
				'desc_tip' => true,
				'advanced' => true
			);

			$arguments['disable_lazyload'] = array(
				'title' 	=> __( 'Disable Lazyload', 'geodirevents' ),
				'type' 		=> 'checkbox',
				'desc_tip' 	=> true,
				'value'  	=> '1',
				'default'  	=> '0',
				'advanced' 	=> true
			);
		}

		return $arguments;
	}

	/**
	 * The Super block output function.
	 *
	 * @param array $instance
	 * @param array $args
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
				'week_start_day' => get_option( 'start_of_week' ),
				'week_day_format' => '0',
				'use_viewing_post_type' => '0',
				'size'  =>  'small',
				'disable_lazyload'  =>  '0'
			)
		);

		$is_preview = $this->is_preview();
		$block_preview = $this->is_block_content_call();

		if ( $is_preview  || $block_preview ) {
			$instance['is_preview'] = $is_preview;
			$instance['block_preview'] = $block_preview;
		} else {
			add_action( 'wp_footer', array( $this,'script' ) );
		}

		ob_start();

		$this->output_html( $args, $instance );

		return ob_get_clean();
	}

	/**
	 * Generates popular postview HTML.
	 *
	 * @param array $args               Display arguments including before_title, after_title, before_widget, and
	 *                                         after_widget.
	 * @param array $instance           The settings for the particular instance of the widget.
	 */
	public function output_html( $args = array(), $instance = array() ) {
		$use_viewing_post_type = ! empty( $instance['use_viewing_post_type'] ) ? true : false;

		if ( $use_viewing_post_type ) {
			$current_post_type = geodir_get_current_posttype();

			if ( $current_post_type != '' && $current_post_type != $instance['post_type'] ) {
				$instance['post_type'] = $current_post_type;
			}
		}

		ob_start();

		GeoDir_Event_Calendar::display_calendar( $args, $instance );

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

	/**
	 * JS for the widget output.
	 */
	public static function script(){
		?>
		<script>
			function geodir_event_get_calendar($container, params) {
				var $calendar,data;
				$calendar = jQuery('.geodir_event_calendar', $container);
				$calendar.addClass('geodir-calendar-loading');
				params = params.replace(/&#038;/g, "&");
				data = 'action=geodir_ajax_calendar' + params + geodir_event_params.calendar_params;
				jQuery.ajax({
					type: "GET",
					url: geodir_params.gd_ajax_url,
					data: data,
					beforeSend: function() {
						jQuery('.gd-event-cal-title', $container).html('');
						$calendar.find('.gd-div-loader').show();
					},
					success: function(html) {
						$calendar.removeClass('geodir-calendar-loading').html(html);
						$calendar.find('.gd-div-loader').hide();
						jQuery('#cal_title', $container).appendTo(".gd-event-cal-title");
						aui_init();
					}
				});
			}
		</script>
		<?php
	}
	}

