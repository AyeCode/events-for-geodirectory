<?php
/**
 * Main calendar links Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main calendar exporter class for links iCal data.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */
class GeoDir_Event_Ical_Links {
	/**
	 * GeoDirectory Event Manager instance.
	 *
	 * @var GeoDir_Event_Ical_Links
	 * @access private
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * Get the one true instance of GeoDir_Event_Ical_Links.
	 *
	 * @since 1.0.0
	 * @return GeoDir_Event_Ical_Links
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Formats event details for consistent output across calendars
	 */
	protected function format_event_details( object $event ): array {
		$schedule    = geodir_event_get_dates( $event );
		$location    = geodir_event_location( $event );
		$description = wp_strip_all_tags( $event->post_content );

		if ( empty( $schedule ) ) {
			return array();
		}

		$start_date = $schedule['start_date'];
		$end_date   = $schedule['end_date'];
		$timezone   = $schedule['timezone'];

		$formatted = array();

		if ( $start_date ) {
			$formatted['start_date'] = $start_date;
		}

		if ( $end_date ) {
			$formatted['end_date'] = $end_date;
		}

		if ( $timezone ) {
			$formatted['timezone'] = $timezone;
		}

		if ( $location ) {
			$formatted['location'] = $location;
		}

		// Format date and time strings if dates exist.
		if ( isset( $formatted['start_date'] ) && isset( $formatted['end_date'] ) ) {
			$time_str = sprintf(
				'%s – %s',
				$start_date->format( 'g:i' ),
				$end_date->format( 'g:i a' )
			);

			$details = sprintf(
				'<strong>Date:</strong> %s<br><br>' .
				'<strong>Time:</strong> %s<br><br>' .
				'<strong>Address:</strong> %s<br><br>' .
				'<br><br>' .
				'%s',
				$start_date->format( 'l, F j' ),
				$time_str,
				$location,
				$description
			);

			$formatted['details'] = $details;
		}

		return $formatted;
	}

	/**
	 * Generates Google Calendar link
	 */
	public function google_calendar_link( object $event ): string {
		$event_details = $this->format_event_details( $event );
		$query_args    = array();

		if ( ! empty( $event_details ) ) {
			$query_args['action'] = 'TEMPLATE';

			if ( isset( $event_details['start_date'] ) && isset( $event_details['end_date'] ) ) {
				$query_args['dates'] = sprintf(
					'%s/%s',
					$event_details['start_date']->format( 'Ymd\THis' ),
					$event_details['end_date']->format( 'Ymd\THis' )
				);
			}

			if ( isset( $event->post_title ) ) {
				$query_args['text'] = rawurlencode( $event->post_title );
			}

			if ( isset( $event_details['details'] ) ) {
				$query_args['details'] = rawurlencode( $event_details['details'] );
			}

			if ( isset( $event_details['location'] ) ) {
				$query_args['location'] = rawurlencode( $event_details['location'] );
			}

			$query_args['trp'] = 'false';

			if ( isset( $event_details['timezone'] ) ) {
				$query_args['ctz'] = $event_details['timezone'];
			}

			if ( isset( $event->ID ) ) {
				$query_args['sprop'] = 'website:' . get_the_permalink( $event->ID );
			}
		}

		return add_query_arg(
			$query_args,
			'https://www.google.com/calendar/event'
		);
	}

	/**
	 * Generates Apple Calendar link (webcal protocol)
	 */
	public function apple_calendar_link( object $event ): string {
		$calendar_url = get_the_permalink( $event->ID );

		return sprintf(
			'webcal://%s/?ical=1',
			$calendar_url
		);
	}

	/**
	 * Generates Outlook Live link
	 */
	public function outlook_live_link( object $event ): string {
		$event_details = $this->format_event_details( $event );
		$query_args    = array(
			'path' => '/calendar/action/compose',
			'rrv'  => 'addevent',
		);

		if ( ! empty( $event_details ) ) {
			if ( isset( $event_details['start_date'] ) ) {
				$query_args['startdt'] = $event_details['start_date']->format( 'Y-m-d\TH:i:s' );
			}

			if ( isset( $event_details['end_date'] ) ) {
				$query_args['enddt'] = $event_details['end_date']->format( 'Y-m-d\TH:i:s' );
			}

			if ( isset( $event_details['location'] ) ) {
				$query_args['location'] = rawurlencode( $event_details['location'] );
			}

			if ( isset( $event->post_title ) ) {
				$query_args['subject'] = $event->post_title;
			}

			if ( isset( $event_details['details'] ) ) {
				$query_args['body'] = rawurlencode( $event_details['details'] );
			}
		}

		return add_query_arg(
			$query_args,
			'https://outlook.live.com/owa/'
		);
	}

	/**
	 * Helper function to generate calendar buttons/links for all supported platforms.
	 *
	 * @param object $event Event object containing post and meta data.
	 * @return array Array of calendar links with their labels.
	 */
	public function calendar_links( object $event ): array {
		return array(
			'google'       => $this->google_calendar_link( $event ),
			'apple'        => $this->apple_calendar_link( $event ),
			'outlook_live' => $this->outlook_live_link( $event ),
		);
	}
}
