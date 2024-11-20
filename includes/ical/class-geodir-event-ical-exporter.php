<?php
/**
 * Main calendar exporter Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

use GeoDir\Events\Libraries\iCalendar\ZCiCal;
use GeoDir\Events\Libraries\iCalendar\ZCiCalNode;
use GeoDir\Events\Libraries\iCalendar\ZCiCalDataNode;

/**
 * Main calendar exporter class for exporting iCal data.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */
class GeoDir_Event_Ical_Exporter {
	/**
	 * Export iCal data for a given event ID.
	 *
	 * @param WP_Post $event The ID of the event to export iCal data for.
	 */
	public function export( $event ) {
		// Time when calendar was created. Format: "Ymd\THis\Z".
		$datestamp = ZCiCal::fromUnixDateTime() . 'Z';

		// Create calendar.
		$calendar = new GeoDir_Event_Ical();
		$calendar->remove_method_property(); // Remove property METHOD.

		// Change default PRODID.
		$prodid = '-//' . geodir_event_site_domain() . '//GeoDirectory Events ' . GEODIR_EVENT_VERSION;
		$calendar->set_prodid( $prodid );

		$schedule = geodir_event_get_dates( $event );

		$event_uid = md5( $event->ID ) . '@' . geodir_event_site_domain();

		$featured_image   = get_the_post_thumbnail_url( $event->ID, 'full' );
		$location         = geodir_event_location( $event );
		$event_url        = get_the_permalink( $event->ID );
		$summary          = trim( $event->post_title );
		$description      = wp_strip_all_tags( $event->post_content );
		$last_modified    = ( new DateTime( $event->post_modified ) )->format( 'Ymd\THis\Z' );
		$categories       = get_the_category( $event->ID );
		$event_categories = implode( ',', wp_list_pluck( $categories, 'name' ) );
		$timezone         = wp_timezone();

		// Fill the calendar with events.
		$ical = new ZCiCalNode( 'VEVENT', $calendar->curnode );
		$ical->addNode( new ZCiCalDataNode( 'UID:' . $event_uid ) );
		$ical->addNode( new ZCiCalDataNode( 'DTSTAMP:' . $datestamp ) );

		if ( ! empty( $featured_image ) ) {
			$ical->add_node( new ZCiCalDataNode( 'ATTACH:' . esc_url( $featured_image ) ) );
		}

		$ical->addNode( new ZCiCalDataNode( 'SUMMARY:' . $summary ) );
		$ical->addNode( new ZCiCalDataNode( 'DESCRIPTION:' . $description ) );
		$ical->addNode( new ZCiCalDataNode( 'URL:' . $event_url ) );
		$ical->addNode( new ZCiCalDataNode( 'LOCATION:' . $location ) );
		$ical->addNode( new ZCiCalDataNode( 'GEO:' . $event->latitude . ';' . $event->longitude ) );

		if ( ! empty( $schedule ) ) {
			$start_date = $schedule['start_date'];
			$end_date   = $schedule['end_date'];
			$timezone   = $schedule['timezone'];

			$ical->addNode( new ZCiCalDataNode( 'DTSTART;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $start_date->format( 'Y-m-d H:i a' ) ) ) );
			$ical->addNode( new ZCiCalDataNode( 'DTEND;VALUE=DATE:' . ZCiCal::fromSqlDateTime( $end_date->format( 'Y-m-d H:i a' ) ) ) );

			if ( ! empty( $timezone ) ) {
				$ical->addNode( new ZCiCalDataNode( 'TZID:' . $timezone ) );
			}
		}

		if ( ! empty( $event_categories ) ) {
			$ical->addNode( new ZCiCalDataNode( 'CATEGORIES:' . $event_categories ) );
		}

		$ical->addNode( new ZCiCalDataNode( 'CREATED:' . $last_modified ) );
		$ical->addNode( new ZCiCalDataNode( 'LAST-MODIFIED:' . $last_modified ) );

		$post_name = get_post_field( 'post_name', $event->ID, 'raw' );
		$filename  = geodir_event_site_domain() . '-' . $post_name . '-' . date( 'Ymd' ) . '.ics';

		header( 'Content-type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: inline; filename=' . $filename );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $calendar->export();
	}
}
