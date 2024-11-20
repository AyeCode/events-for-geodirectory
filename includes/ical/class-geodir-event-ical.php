<?php
/**
 * Main iCal Class.
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
 * Main iCal class.
 *
 * This class extends ZCiCal class to handle iCal functionality.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @since 1.0.0
 */
class GeoDir_Event_Ical extends ZCiCal {
	/**
	 * The one true instance of GeoDir_Event_Ical.
	 *
	 * @var GeoDir_Event_Ical|null
	 */
	private static $instance = null;

	/**
	 * Get the one true instance of GeoDir_Event_Ical.
	 *
	 * @since 1.0.0
	 * @return GeoDir_Event_Ical
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieves all events.
	 *
	 * @since 1.0.0
	 * @return ZCiCalNode[] Array of events.
	 */
	public function get_events() {
		if ( 0 === $this->countEvents() ) {
			return array();
		}

		$events = array();

		$event = $this->getFirstEvent();
		while ( $event ) {
			$events[] = $event;
			$event    = $this->getNextEvent( $event );
		}

		return $events;
	}

	/**
	 * Retrieves data of all events.
	 *
	 * @since 1.0.0
	 *
	 * @param array $filters The filters of the event.
	 * @return array Array of event data.
	 */
	public function get_events_data( $filters = array() ) {
		$events        = $this->get_events();
		$calendar_info = $this->get_calendar_info();
		$values        = array();

		foreach ( $events as $event ) {
			$parsed = $this->parse_event( $event );

			// Is parsed valid?
			if ( isset( $parsed['dtstart'] ) && isset( $parsed['dtend'] ) ) {
				$parsed   = array_merge( $calendar_info, $parsed );
				$values[] = $parsed;
			}
		}

		return $values;
	}

	/**
	 * Gets calendar-level information.
	 *
	 * @since 1.0.0
	 * @return array Calendar information
	 */
	private function get_calendar_info() {
		$info = array(
			'prodid'           => $this->get_prodid(),
			'version'          => isset( $this->tree->data['VERSION'] ) ? $this->tree->data['VERSION']->getValues() : '',
			'calname'          => isset( $this->tree->data['X-WR-CALNAME'] ) ? $this->tree->data['X-WR-CALNAME']->getValues() : '',
			'caldesc'          => isset( $this->tree->data['X-WR-CALDESC'] ) ? $this->tree->data['X-WR-CALDESC']->getValues() : '',
			'calscale'         => isset( $this->tree->data['CALSCALE'] ) ? $this->tree->data['CALSCALE']->getValues() : '',
			'method'           => isset( $this->tree->data['METHOD'] ) ? $this->tree->data['METHOD']->getValues() : '',
			'refresh_interval' => isset( $this->tree->data['REFRESH-INTERVAL'] ) ? $this->tree->data['REFRESH-INTERVAL']->getValues() : '',
			'x_published_ttl'  => isset( $this->tree->data['X-PUBLISHED-TTL'] ) ? $this->tree->data['X-PUBLISHED-TTL']->getValues() : '',
			'timezone'         => $this->get_timezone(),
			'timezone_rules'   => $this->get_timezone_rules(),
		);

		return array_filter( $info );
	}

	/**
	 * Gets the timezone information.
	 *
	 * @since 1.0.0
	 * @return string|null Timezone ID
	 */
	private function get_timezone() {
		if ( isset( $this->tree->data['VTIMEZONE'] ) ) {
			$timezone = $this->tree->data['VTIMEZONE']->getFirst( 'TZID' );
			return $timezone ? $timezone->getValues() : null;
		}
		return null;
	}

	/**
	 * Gets detailed timezone rules.
	 *
	 * @since 1.0.0
	 * @return array Timezone rules
	 */
	private function get_timezone_rules() {
		if ( ! isset( $this->tree->data['VTIMEZONE'] ) ) {
			return array();
		}

		$rules     = array();
		$vtimezone = $this->tree->data['VTIMEZONE'];

		// Get standard time rules.
		if ( isset( $vtimezone->data['STANDARD'] ) ) {
			$rules['standard'] = array(
				'offsetfrom' => $vtimezone->data['STANDARD']->getFirst( 'TZOFFSETFROM' )->getValues(),
				'offsetto'   => $vtimezone->data['STANDARD']->getFirst( 'TZOFFSETTO' )->getValues(),
				'tzname'     => $vtimezone->data['STANDARD']->getFirst( 'TZNAME' )->getValues(),
				'dtstart'    => $vtimezone->data['STANDARD']->getFirst( 'DTSTART' )->getValues(),
			);
		}

		// Get daylight savings rules.
		if ( isset( $vtimezone->data['DAYLIGHT'] ) ) {
			$rules['daylight'] = array(
				'offsetfrom' => $vtimezone->data['DAYLIGHT']->getFirst( 'TZOFFSETFROM' )->getValues(),
				'offsetto'   => $vtimezone->data['DAYLIGHT']->getFirst( 'TZOFFSETTO' )->getValues(),
				'tzname'     => $vtimezone->data['DAYLIGHT']->getFirst( 'TZNAME' )->getValues(),
				'dtstart'    => $vtimezone->data['DAYLIGHT']->getFirst( 'DTSTART' )->getValues(),
			);
		}

		return $rules;
	}

	/**
	 * Parses an event.
	 *
	 * @since 1.0.0
	 *
	 * @param ZCiCalNode $event The event to parse.
	 * @return array Event values.
	 */
	private function parse_event( $event ) {
		$values = array(
			'uid'               => null,
			'summary'           => '',
			'description'       => '',
			'location'          => '',
			'geo'               => array(),
			'categories'        => array(),
			'url'               => '',
			'status'            => '',
			'created'           => '',
			'last_modified'     => '',
			'dtstamp'           => '',
			'sequence'          => '',
			'priority'          => '',
			'class'             => '',
			'transp'            => '',
			'attachment'        => '',
			'organizer'         => array(),
			'attendees'         => array(),
			'recurrence'        => array(),
			'exceptions'        => array(),
			'resources'         => '',
			'conference'        => array(),
			'custom_properties' => array(),
		);

		foreach ( $event->data as $name => $node ) {
			$name   = strtoupper( $name );
			$value  = $node->getValues();
			$params = $node->getParameters();

			// Handle dates with timezone.
			if ( in_array( $name, array( 'DTSTART', 'DTEND', 'DTSTAMP', 'CREATED', 'LAST-MODIFIED' ) ) ) {
				$value = $this->parse_datetime( $value, $params );
			}

			switch ( $name ) {
				case 'UID':
					$values['uid'] = $value;
					break;

				case 'DTSTART':
					$values['dtstart'] = $value;
					break;

				case 'DTEND':
					$values['dtend'] = $value;
					break;

				case 'DTSTAMP':
					$values['dtstamp'] = $value;
					break;

				case 'SUMMARY':
					$values['summary'] = $this->sanitize_text( $value );
					break;

				case 'DESCRIPTION':
					$values['description'] = $this->sanitize_text( $value );
					break;

				case 'LOCATION':
					$values['location'] = $this->sanitize_text( $value );
					break;

				case 'GEO':
					$geo_parts = explode( ';', $value );
					if ( count( $geo_parts ) == 2 ) {
						$values['geo'] = array(
							'latitude'  => floatval( $geo_parts[0] ),
							'longitude' => floatval( $geo_parts[1] ),
						);
					}
					break;

				case 'CATEGORIES':
					$values['categories'] = array_map( 'trim', explode( ',', $value ) );
					break;

				case 'URL':
					$values['url'] = esc_url_raw( $value );
					break;

				case 'STATUS':
					$values['status'] = strtolower( $value );
					break;

				case 'CREATED':
					$values['created'] = $value;
					break;

				case 'LAST-MODIFIED':
					$values['last_modified'] = $value;
					break;

				case 'SEQUENCE':
					$values['sequence'] = intval( $value );
					break;

				case 'PRIORITY':
					$values['priority'] = intval( $value );
					break;

				case 'CLASS':
					$values['class'] = strtolower( $value );
					break;

				case 'TRANSP':
					$values['transp'] = strtolower( $value );
					break;

				case 'ATTACH':
					if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
						$values['attachment'] = esc_url_raw( $value );
					}
					break;

				case 'ORGANIZER':
					$values['organizer'] = array_merge(
						array( 'value' => $value ),
						$params
					);
					break;

				case 'ATTENDEE':
					$values['attendees'][] = array_merge(
						array( 'value' => $value ),
						$params
					);
					break;

				case 'RRULE':
					$values['recurrence']['rule'] = $this->parse_recurrence_rule( $value );
					break;

				case 'RDATE':
					$values['recurrence']['dates'][] = $this->parse_datetime( $value, $params );
					break;

				case 'EXDATE':
					$values['exceptions'][] = $this->parse_datetime( $value, $params );
					break;

				case 'RESOURCES':
					$values['resources'] = array_map( 'trim', explode( ',', $value ) );
					break;

				case 'CONFERENCE':
					$values['conference'] = array_merge(
						array( 'value' => $value ),
						$params
					);
					break;

				default:
					// Handle custom X- properties.
					if ( strpos( $name, 'X-' ) === 0 ) {
						$values['custom_properties'][ substr( $name, 2 ) ] = $value;
					}
					break;
			}
		}

		return array_filter(
			$values,
			function ( $value ) {
				return ! empty( $value ) || $value === 0;
			}
		);
	}

	/**
	 * Parses recurrence rule.
	 *
	 * @since 1.0.0
	 *
	 * @param string $rrule Recurrence rule string
	 * @return array Parsed recurrence rule
	 */
	private function parse_recurrence_rule( $rrule ) {
		$parts = explode( ';', $rrule );
		$rule  = array();

		foreach ( $parts as $part ) {
			$piece = explode( '=', $part );
			if ( count( $piece ) == 2 ) {
				$rule[ strtolower( $piece[0] ) ] = $piece[1];
			}
		}

		return $rule;
	}

	/**
	 * Parses datetime value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value DateTime value
	 * @param array  $params Parameters including TZID
	 * @return string Formatted datetime
	 */
	private function parse_datetime( $value, $params = array() ) {
		// Handle dates with timezone.
		if ( isset( $params['tzid'] ) ) {
			try {
				$dt = new DateTime( $value, new DateTimeZone( $params['tzid'] ) );
				return $dt->format( 'Y-m-d H:i:s' );
			} catch ( Exception $e ) {
				// Fallback to basic parsing.
			}
		}

		// Handle UTC dates.
		if ( substr( $value, -1 ) === 'Z' ) {
			try {
				$dt = new DateTime( $value );
				return $dt->format( 'Y-m-d H:i:s' );
			} catch ( Exception $e ) {
				// Fallback to basic parsing.
			}
		}

		// Handle basic date format.
		preg_match( '/(\d{4})(\d{2})(\d{2})(?:T(\d{2})(\d{2})(\d{2}))?/', $value, $parts );
		if ( ! empty( $parts ) ) {
			if ( count( $parts ) > 4 ) {
				// DateTime.
				return sprintf(
					'%s-%s-%s %s:%s:%s',
					$parts[1],
					$parts[2],
					$parts[3],
					$parts[4],
					$parts[5],
					$parts[6]
				);
			} else {
				// Date only.
				return sprintf( '%s-%s-%s', $parts[1], $parts[2], $parts[3] );
			}
		}

		return $value;
	}

	/**
	 * Sanitizes text content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The text to sanitize.
	 * @return string Sanitized text
	 */
	private function sanitize_text( $text ) {
		$text = str_replace( '\n', "\n", $text );
		$text = str_replace( '\,', ',', $text );
		$text = str_replace( '\;', ';', $text );
		$text = str_replace( '\\\\', '\\', $text );
		return wp_kses_post( $text );
	}

	/**
	 * Retrieves the PRODID.
	 *
	 * @since 1.0.0
	 * @return string The PRODID.
	 */
	public function get_prodid() {
		return isset( $this->tree->data['PRODID'] ) ? $this->tree->data['PRODID']->getValues() : '';
	}

	/**
	 * Sets the PRODID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prodid The PRODID to set.
	 */
	public function set_prodid( $prodid ) {
		if ( isset( $this->tree->data['PRODID'] ) ) {
			$prodid_node        = $this->curnode->data['PRODID'];
			$prodid_node->value = array( $prodid );
		} else {
			$prodid_node                                    = new ZCiCalDataNode( 'PRODID:' . $prodid );
			$this->curnode->data[ $prodid_node->getName() ] = $prodid_node;
		}
	}

	/**
	 * Removes the METHOD property.
	 *
	 * @since 1.0.0
	 */
	public function remove_method_property() {
		if ( isset( $this->curnode->data['METHOD'] ) ) {
			unset( $this->curnode->data['METHOD'] );
		}
	}
}
