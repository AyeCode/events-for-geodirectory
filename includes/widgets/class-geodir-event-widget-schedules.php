<?php
/**
 * Event Schedules widget
 *
 * @package GeoDir_Event_Manager
 * @since 2.0.0.16
 */

/**
 * GeoDir_Event_Widget_Schedules class.
 */
class GeoDir_Event_Widget_Schedules extends WP_Super_Duper {
	/**
	 * Widget attributes.
	 *
	 * @since 2.0.0.16
	 * @var array
	 */
	public $arguments;

	/**
	 * Sets up a widget instance.
	 *
	 * @since 2.0.0.16
	 */
	public function __construct() {
		$options = array(
			'textdomain'    => 'geodirevents',
			'block-icon'    => 'calendar-alt',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['event','event schedules','schedule']",
			'class_name'    => __CLASS__,
			'base_id'       => 'geodir_event_schedules',
			'name'          => __( 'GD > Event Schedules', 'geodirevents' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-event-schedules '.geodir_bsui_class(),
				'description' => esc_html__( 'Displays the event schedules.', 'geodirevents' ),
				'geodirectory' => true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-detail' ),
			),
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 * @since 2.0.0.16
	 */
	public function set_arguments() {
		$arguments = array(
			'title' => array(
				'title' => __( 'Title:', 'geodirevents' ),
				'desc' => __( 'The widget title.', 'geodirevents' ),
				'type' => 'text',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'id' => array(
				'title' => __( 'Post ID:', 'geodirevents' ),
				'desc' => __( 'Leave blank to use current post id.', 'geodirevents' ),
				'type' => 'number',
				'placeholder' => __( 'Leave blank to use current post id.', 'geodirevents' ),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'type'  => array(
				'title' => __( 'Event Type:', 'geodirevents' ),
				'desc' => __( 'Select event type to show schedules.', 'geodirevents' ),
				'type' => 'select',
				'options' => geodir_event_filter_options(),
				'default' => 'upcoming',
				'desc_tip' => true,
				'advanced' => false
			),
			'number' => array(
				'title' => __( 'Number of schedules to show:', 'geodirevents' ),
				'desc' => __( 'Set number of schedules to show. Default: 5', 'geodirevents'),
				'type' => 'number',
				'placeholder' => 5,
				'default' => 5,
				'desc_tip' => true,
				'advanced' => true
			),
			'show_past' => array(
				'title' => __( 'Show past schedules when no upcoming schedule.', 'geodirevents' ),
				'type' => 'checkbox',
				'value' => '1',
				'default' => '0',
				'desc_tip' => true,
				'advanced' => true,
				'element_require' => '[%type%]!="past" && [%type%]!="all"'
			),
			'date_format' => array(
				'title' => __( 'Date Format:', 'geodirevents' ),
				'desc' => __( 'Set the date format. Leave blank to use default date format.', 'geodirevents'),
				'type' => 'text',
				'placeholder' => geodir_event_date_format(),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'time_format' => array(
				'title' => __( 'Time Format:', 'geodirevents' ),
				'desc' => __( 'Set the time format. Leave blank to use default time format.', 'geodirevents'),
				'type' => 'text',
				'placeholder' => geodir_event_time_format(),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'template'  => array(
				'title' => __( 'Schedule Template:', 'geodirevents' ),
				'desc' => __( 'Select the template to show schedules.', 'geodirevents' ),
				'type' => 'select',
				'options' => self::get_schedule_template_options(),
				'default' => '',
				'desc_tip' => true,
				'advanced' => true
			),
			'use_current' => array(
				'title' => __( 'Tick to use current schedule date to get schedules if schedule is already set on event listings.', 'geodirevents' ),
				'type' => 'checkbox',
				'value' => '1',
				'default' => '0',
				'desc_tip' => true,
				'advanced' => true,
			),
			'hide_current' => array(
				'title' => __( 'Tick to hide current schedule date in schedules on event listings.', 'geodirevents' ),
				'type' => 'checkbox',
				'value' => '1',
				'default' => '0',
				'desc_tip' => true,
				'advanced' => true,
				'element_require' => '[%use_current%]=="1" || [%type%]==1'
			)
		);

		return $arguments;
	}

	/**
	 * The widget output.
	 *
	 * @since 2.0.0.16
	 *
	 * @param array $instance
	 * @param array $args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		global $post, $gd_post;

		$output = '';

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
				'id' => '',
				'type' => 'upcoming',
				'number' => 5,
				'show_past' => '0',
				'date_format' => '',
				'time_format' => '',
				'template' => '',
				'use_current' => '0',
				'hide_current' => '0',
			)
		);

		$post_id = apply_filters( 'geodir_event_widget_schedules_id', absint( $instance['id'] ), $instance, $this->id_base );
		$type = apply_filters( 'geodir_event_widget_schedules_type', $instance['type'], $instance, $this->id_base );
		$number = apply_filters( 'geodir_event_widget_schedules_number', absint( $instance['number'] ), $instance, $this->id_base );
		$show_past = apply_filters( 'geodir_event_widget_schedules_past', (int) $instance['show_past'] == 1, $instance, $this->id_base );
		$date_format = apply_filters( 'geodir_event_widget_schedules_date_format', $instance['date_format'], $instance, $this->id_base );
		$time_format = apply_filters( 'geodir_event_widget_schedules_time_format', $instance['time_format'], $instance, $this->id_base );
		$template = apply_filters( 'geodir_event_widget_schedules_template', $instance['template'], $instance, $this->id_base );
		$use_current = apply_filters( 'geodir_event_widget_schedules_use_current', (int) $instance['use_current'] == 1, $instance, $this->id_base );
		$hide_current = apply_filters( 'geodir_event_widget_schedules_show_current', (int) $instance['hide_current'] == 1, $instance, $this->id_base );

		if ( ! in_array( $type, array_keys( geodir_event_filter_options() ) ) ) {
			$type = 'upcoming';
		}
		if ( ! (int) $number > 0 ) {
			$number = 5;
		}

		if ( $post_id > 0 ) {
		} elseif ( ! empty( $gd_post ) ) {
			$post_id = $gd_post->ID;
		} elseif ( ! empty( $post ) ) {
			$post_id = $post->ID;
		}

		$demo = geodir_is_block_demo();
		if ( ! ( $post_id && GeoDir_Post_types::supports( get_post_type( $post_id ), 'events' ) ) ) {
			if ( ! $demo ) {
				return $output;
			}
		}

		// Use current schedule to retrieve schedules.
		$date = '';
		if ( $use_current ) {
			if ( ! empty( $gd_post->start_date ) ) {
				$date = $gd_post->start_date;
			} elseif ( ! empty( $post->start_date ) && $post->ID == $gd_post->ID ) {
				$date = $post->start_date;
			} elseif ( geodir_is_page( 'single' ) ) {
				if ( ! empty( $_REQUEST['gde'] ) ) {
					$date = sanitize_text_field( $_REQUEST['gde'] );
				} else {
					$schedules = GeoDir_Event_Schedules::get_schedules( $gd_post->ID, $type, 1 );

					if ( ! empty( $schedules ) && ! empty( $schedules[0]->start_date ) ) {
						$date = $schedules[0]->start_date;
					}
				}
			}

			if ( $date ) {
				$type =  $hide_current ? date_i18n( 'Y-m-d', strtotime( $date . ' +1 day' ) ) : $date;
				$type .= '|2038-01-01'; // Y2K38
			}
		}

		if ( $demo ) {
			$schedules = self::get_demo_schedules( $number );
		} else {
			$schedules = GeoDir_Event_Schedules::get_schedules( $post_id, $type, $number, $date );
			if ( empty( $schedules ) && ! in_array( $type, array( 'past', 'all' ) ) && $show_past ) {
				$schedules = GeoDir_Event_Schedules::get_schedules( $post_id, 'all', $number );
			}
		}

		if ( empty( $schedules ) ) {
			return $output;
		}

		$output = self::get_schedules_html( $schedules, $template, $date_format, $time_format );

		return $output;
	}

	/**
	 * The schedule template options.
	 *
	 * @since 2.0.0.16
	 *
	 * @return array
	 */
	public static function get_schedule_template_options() {
		$templates = array_values( GeoDir_Event_Schedules::get_schedule_templates() );

		return array_combine( $templates, $templates );
	}

	/**
	 * The schedule default template.
	 *
	 * @since 2.0.0.16
	 *
	 * @return string
	 */
	public static function get_schedule_template() {
		$templates = GeoDir_Event_Schedules::get_schedule_templates();

		return $templates[0];
	}

	/**
	 * Get schedules html.
	 *
	 * @since 2.0.0.16
	 *
	 * @return string
	 */
	public static function get_schedules_html( $schedules, $template = '', $date_format = '', $time_format = '' ) {
		if ( empty( $schedules ) ) {
			return NULL;
		}

		if ( empty( $date_format ) ) {
			$date_format = geodir_event_date_format();
		}
		if ( empty( $time_format ) ) {
			$time_format = geodir_event_time_format();
		}
		if ( empty( $template ) ) {
			$template = self::get_schedule_template();
		}

		$design_style = geodir_design_style();

		$_schedules = array();
		foreach ( $schedules as $key => $row ) {
			if ( ! ( ! empty( $row->start_date ) && $row->start_date != '0000-00-00' ) ) {
				continue;
			}

			$start_date		= $row->start_date;
			$end_date		= ! empty( $row->end_date ) && $row->end_date != '0000-00-00' ? $row->end_date : $start_date;
			$start_time		= ! empty( $row->start_time ) ? $row->start_time : '00:00:00';
			$end_time		= ! empty( $row->end_time ) ? $row->end_time : '00:00:00';
			$all_day		= ! empty( $row->all_day ) ? true : false;

			$_schedule = array();
			$_schedule['start_date'] = date_i18n( $date_format, strtotime( $start_date ) );
			$_schedule['end_date'] = '';
			$_schedule['start_time'] = '';
			$_schedule['end_time'] = '';

			if ( empty( $all_day ) ) {
				if ( $start_date == $end_date && $start_time == $end_time && $end_time == '00:00:00' ) {
					$end_date = date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . $start_time . ' +1 day' ) );
				}
				$_schedule['start_time'] = date_i18n( $time_format, strtotime( $start_time ) );
				$_schedule['end_time'] = date_i18n( $time_format, strtotime( $end_time ) );
				$_schedule['has_times'] = true;
			} else {
				$_schedule['all_day'] = true;
			}

			if ( $start_date != $end_date ) {
				$_schedule['end_date'] = date_i18n( $date_format, strtotime( $end_date ) );
			}

			$row_class = $design_style ? 'list-group-item px-0 py-2' : '';
			$schedule_row = self::schedule_parse_template( $_schedule, $row, $template, $date_format, $time_format );
			$schedule_row = '<div class="geodir-schedule-row '.$row_class.'">' . $schedule_row . '</div>';
			$schedule_row = apply_filters( 'geodir_event_widget_schedule_html', $schedule_row, $row, $template, $date_format, $time_format );

			if ( ! empty( $schedule_row ) ) {
				$_schedules[] = $schedule_row;
			}
		}

		$wrap_class = $design_style ? 'list-group list-group-flush' : '';
		$html = ! empty( $_schedules ) ? '<div class="geodir-schedule-rows '.$wrap_class.'">' . implode( '', $_schedules ) . '</div>' : '';

		return apply_filters( 'geodir_event_widget_schedules_html', $html, $_schedules, $schedules, $template, $date_format, $time_format );
	}

	/**
	 * Parse schedule template.
	 *
	 * @since 2.0.0.16
	 *
	 * @return string
	 */
	public static function schedule_parse_template( $item, $schedule = array(), $template = '', $date_format = '', $time_format = '' ) {
		if ( empty( $item ) || ! is_array( $item ) ) {
			return NULL;
		}

		if ( empty( $template ) ) {
			$template = self::get_schedule_template();
		}

		if ( strpos( $template, '{start_date}' ) === false && ! empty( $item['start_date'] ) && empty( $item['end_date'] ) ) {
			$item['end_date'] = $item['start_date'];
		}

		$content = $template;
		if ( ! empty( $item['has_times'] ) ) {
			if ( empty( $item['end_date'] ) ) {
				if ( strpos( $content, '{start_date} - {end_date}' ) !== false ) {
					$content = str_replace( '{start_date} - {end_date}', '{start_date}', $content );
				}
			}
		}

		foreach ( $item as $key => $value ) {
			if ( $value !== '' ) {
				$content = str_replace( '{' . $key .'}', $value, $content );
			}
		}
		$content = normalize_whitespace( $content );

		if ( ! empty( $item['has_times'] ) ) {
			if ( empty( $item['end_date'] ) ) {
				if ( strpos( $content, '{br} {end_date}' ) !== false ) {
					$item['end_date'] = $item['start_date'];
				}
			}
		}

		$item['br'] = '<br>';

		foreach ( $item as $key => $value ) {
			$content = str_replace( '{' . $key .'}', $value, $content );
		}

		$content = apply_filters( 'geodir_event_widget_schedules_parse_template', $content, $item, $schedule, $template, $date_format, $time_format );

		return self::normalize_chars( $content, $item );
	}

	/**
	 * Normalize template.
	 *
	 * @since 2.0.0.16
	 *
	 * @return string
	 */
	public static function normalize_chars( $content, $item ) {
		$custom_vars = array( 'br' );

		$content = trim( $content, '@-, ' );
		$content = normalize_whitespace( $content );
		$content = str_replace( array( '- @', '- ,', '@ -', ', -', '@ <br>', ', <br>', '- <br>' ), array( '-', '-', '-', '-', '<br>', '<br>', '<br>' ), $content );
		foreach( $custom_vars as $var ) {
			if ( isset( $item[ $var ] ) && $item[ $var ] === substr( $content, -1 * strlen( $item[ $var ] ) ) ) {
				$content = substr( $content, 0, -1 * strlen( $item[ $var ] ) );
			}
		}

		return trim( $content );
	}

	/**
	 * The schedule template options.
	 *
	 * @since 2.0.0.16
	 *
	 * @return array
	 */
	public static function get_demo_schedules( $number = 0 ) {
		if ( ! $number > 0 ) {
			$number = 5;
		}

		$recurring = ! geodir_get_option( 'event_disable_recurring' ) ? 1 : 0;

		if ( ! $recurring ) {
			$number = 1;
		}

		$today = date_i18n( 'Y-m-d' );

		$schedules = array();
		for ( $i = 0; $i < $number; $i++ ) {
			$schedule = array(
				'schedule_id' => ( $i + 1 ) * 10000000000,
				'event_id' => ( $i + 1 ) * 10000000000,
				'start_date' => date_i18n( 'Y-m-d', strtotime( $today . ' +' . ( $i * 7 ) . ' day' ) ),
				'end_date' => date_i18n( 'Y-m-d', strtotime( $today . ' +' . ( $i * 7 ) . ' day' ) ),
				'start_time' => '09:00:00',
				'end_time' => '17:00:00',
				'all_day' => 0,
				'recurring' => $recurring
			);

			$schedules[] = (object) $schedule;
		}

		return $schedules;
	}
}
