<?php
/**
 * Main calendar feed Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main calendar feed class.
 *
 * This class handles the initialization of the iCal feed and exports iCal data.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @since 1.0.0
 */
class GeoDir_Event_Ical_Feed {
	/**
	 * The one true instance of GeoDir_Event_Ical_Feed.
	 *
	 * @var GeoDir_Event_Ical_Feed
	 */
	private static $instance;

	/**
	 * Constructor.
	 * Hooks into WordPress init action to initialize the iCal feed.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init_ical_feed' ) );
	}

	/**
	 * Get the one true instance of GeoDir_Event_Ical_Feed.
	 *
	 * @since 1.0
	 * @return GeoDir_Event_Ical_Feed
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes the iCal feed by adding a custom feed.
	 *
	 * @since 1.0.0
	 */
	public function init_ical_feed() {
		add_feed( 'gdevent.ics', array( $this, 'export_ics' ) );
	}

	/**
	 * Export iCal data.
	 *
	 * Retrieves the event ID from the request and exports the iCal data for the specified event.
	 *
	 * @since 1.0.0
	 */
	public function export_ics() {
		$event_id = isset( $_REQUEST['event_id'] ) ? absint( (int) $_REQUEST['event_id'] ) : 0;

		if ( empty( $event_id ) ) {
			return;
		}

		$event = geodir_get_post_info( $event_id );
		if ( ! $event ) {
			return;
		}

		$exporter = new GeoDir_Event_Ical_Exporter();
		$exporter->export( $event );
	}
}
