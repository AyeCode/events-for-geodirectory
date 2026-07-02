<?php
/**
 * Event Countdown widget
 *
 * @package GeoDir_Event_Manager
 * @since 2.3.31
 */

defined( 'ABSPATH' ) || exit;

/**
 * GeoDir_Event_Widget_Countdown class.
 */
class GeoDir_Event_Widget_Countdown extends WP_Super_Duper {

	/**
	 * Widget arguments.
	 *
	 * @var array
	 */
	public $arguments;

	/**
	 * Sets up a widget instance.
	 */
	public function __construct() {
		$options = array(
			'textdomain'     => 'geodirevents',
			'block-icon'     => 'clock',
			'block-category' => 'geodirectory',
			'block-keywords' => "['event','countdown','timer','date']",
			'class_name'     => __CLASS__,
			'base_id'        => 'geodir_event_countdown',
			'name'           => __( 'GD > Event Countdown', 'geodirevents' ),
			'widget_ops'     => array(
				'classname'       => 'geodir-event-countdown-widget ' . geodir_bsui_class(),
				'description'     => esc_html__( 'Displays a live countdown to the event start date.', 'geodirevents' ),
				'geodirectory'    => true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-detail' ),
			),
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 */
	public function set_arguments() {
		$arguments = array(
			'title'        => array(
				'title'    => __( 'Title:', 'geodirevents' ),
				'desc'     => __( 'The widget title.', 'geodirevents' ),
				'type'     => 'text',
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
			),
			'id'           => array(
				'title'       => __( 'Post ID:', 'geodirevents' ),
				'desc'        => __( 'Leave blank to use the current post id.', 'geodirevents' ),
				'type'        => 'number',
				'placeholder' => __( 'Leave blank to use the current post id.', 'geodirevents' ),
				'default'     => '',
				'desc_tip'    => true,
				'advanced'    => true,
			),
			'finished_action' => array(
				'title'    => __( 'When event has started:', 'geodirevents' ),
				'desc'     => __( 'What to do once the event start date has passed.', 'geodirevents' ),
				'type'     => 'select',
				'options'  => array(
					'text' => __( 'Show finished text', 'geodirevents' ),
					'hide' => __( 'Hide the countdown', 'geodirevents' ),
				),
				'default'  => 'text',
				'desc_tip' => true,
				'advanced' => false,
			),
			'finished_text' => array(
				'title'    => __( 'Finished text:', 'geodirevents' ),
				'desc'     => __( 'Shown once the event start date has passed.', 'geodirevents' ),
				'type'     => 'text',
				'default'  => __( 'Event started!', 'geodirevents' ),
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%finished_action%]=="text"',
			),
			'box_bg_color'   => array(
				'title'    => __( 'Box background color:', 'geodirevents' ),
				'desc'     => __( 'Background color of the countdown boxes.', 'geodirevents' ),
				'type'     => 'color',
				'default'  => '#f44336',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Design', 'geodirevents' ),
			),
			'box_text_color' => array(
				'title'    => __( 'Box text color:', 'geodirevents' ),
				'desc'     => __( 'Text color of the countdown boxes.', 'geodirevents' ),
				'type'     => 'color',
				'default'  => '#ffffff',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Design', 'geodirevents' ),
			),
		);

		// Wrapper background.
		$arguments['bg'] = geodir_get_sd_background_input( 'bg' );

		// Text alignment.
		$arguments['text_align'] = geodir_get_sd_text_align_input();

		// Margins.
		$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
		$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
		$arguments['mb'] = geodir_get_sd_margin_input( 'mb' );
		$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

		// Padding.
		$arguments['pt'] = geodir_get_sd_padding_input( 'pt' );
		$arguments['pr'] = geodir_get_sd_padding_input( 'pr' );
		$arguments['pb'] = geodir_get_sd_padding_input( 'pb' );
		$arguments['pl'] = geodir_get_sd_padding_input( 'pl' );

		// Border.
		$arguments['border']       = geodir_get_sd_border_input( 'border' );
		$arguments['rounded']      = geodir_get_sd_border_input( 'rounded' );
		$arguments['rounded_size'] = geodir_get_sd_border_input( 'rounded_size' );

		// Shadow.
		$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );

		// CSS class.
		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}

	/**
	 * The Super block output.
	 *
	 * @param array  $instance The instance settings.
	 * @param array  $args     The widget arguments.
	 * @param string $content  The shortcode content argument.
	 * @return string
	 */
	public function output( $instance = array(), $args = array(), $content = '' ) {
		global $post, $gd_post;

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'          => '',
				'id'             => '',
				'finished_action' => 'text',
				'finished_text'  => __( 'Event started!', 'geodirevents' ),
				'box_bg_color'  => '#f44336',
				'box_text_color' => '#ffffff',
			)
		);

		$post_id = absint( $instance['id'] );

		if ( $post_id > 0 ) {
		} elseif ( ! empty( $gd_post ) ) {
			$post_id = $gd_post->ID;
		} elseif ( ! empty( $post ) ) {
			$post_id = $post->ID;
		}

		$demo = geodir_is_block_demo();

		if ( ! ( $post_id && GeoDir_Post_types::supports( get_post_type( $post_id ), 'events' ) ) && ! $demo ) {
			return '';
		}

		$target = $demo ? date( 'Y-m-d', strtotime( '+10 days' ) ) . 'T00:00:00' . geodir_event_normalize_tz_offset( geodir_gmt_offset() ) : self::get_target_datetime( $post_id );

		if ( empty( $target ) ) {
			return '';
		}

		$finished_action = $instance['finished_action'] === 'hide' ? 'hide' : 'text';
		$finished_text   = $finished_action === 'hide' ? '' : trim( (string) $instance['finished_text'] );

		$style_parts = array();
		if ( ! empty( $instance['box_bg_color'] ) && ( $bg_color = sanitize_hex_color( $instance['box_bg_color'] ) ) ) {
			$style_parts[] = '--gd-countdown-bg:' . $bg_color;
		}
		if ( ! empty( $instance['box_text_color'] ) && ( $text_color = sanitize_hex_color( $instance['box_text_color'] ) ) ) {
			$style_parts[] = '--gd-countdown-color:' . $text_color;
		}
		$style_attr = ! empty( $style_parts ) ? ' style="' . esc_attr( implode( ';', $style_parts ) ) . '"' : '';

		$wrap_class = trim( sd_build_aui_class( $instance ) );
		$wrap_class = $wrap_class !== '' ? ' ' . esc_attr( $wrap_class ) : '';

		$units = array(
			'days'    => __( 'Days', 'geodirevents' ),
			'hours'   => __( 'Hours', 'geodirevents' ),
			'minutes' => __( 'Minutes', 'geodirevents' ),
			'seconds' => __( 'Seconds', 'geodirevents' ),
		);

		wp_enqueue_style( 'geodir-event-countdown' );
		wp_enqueue_script( 'geodir-event-countdown' );

		$output  = '<div class="gd-event-countdown' . $wrap_class . '"' . $style_attr . ' data-countdown-to="' . esc_attr( $target ) . '" data-finished-action="' . esc_attr( $finished_action ) . '" data-finished="' . esc_attr( $finished_text ) . '" role="timer" aria-live="polite">';

		foreach ( $units as $key => $label ) {
			$output .= '<div class="gd-event-countdown__unit" data-countdown-unit="' . esc_attr( $key ) . '">';
			$output .= '<span class="gd-event-countdown__number" data-countdown-number>00</span>';
			$output .= '<span class="gd-event-countdown__label">' . esc_html( $label ) . '</span>';
			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Build the ISO 8601 start datetime (with timezone offset) for an event.
	 *
	 * @param int $post_id Event post id.
	 * @return string Empty string when the event has no start date.
	 */
	public static function get_target_datetime( $post_id ) {
		$schedule = GeoDir_Event_Schedules::get_start_schedule( $post_id );

		if ( empty( $schedule ) ) {
			$schedule = geodir_event_schedule_from_post( geodir_get_post_info( $post_id ) );
		}

		if ( empty( $schedule->start_date ) || $schedule->start_date === '0000-00-00' ) {
			return '';
		}

		$all_day    = ! empty( $schedule->all_day );
		$start_time = $all_day || empty( $schedule->start_time ) ? '00:00:00' : date( 'H:i:s', strtotime( $schedule->start_time ) );

		$gd_post  = ! empty( $GLOBALS['gd_post'] ) && $GLOBALS['gd_post']->ID == $post_id ? $GLOBALS['gd_post'] : geodir_get_post_info( $post_id );
		$timezone = geodir_gmt_offset();

		if ( ! empty( $gd_post->timezone_offset ) ) {
			$timezone = $gd_post->timezone_offset;
		}

		$timezone = geodir_event_normalize_tz_offset( $timezone );

		return $schedule->start_date . 'T' . $start_time . $timezone;
	}
}
