<?php
/**
 * Main logger Class.
 *
 * @package GeoDirectory
 * @subpackage Booking
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles logging for GeoDirectory bookings.
 *
 * @since 1.0.0
 */
class GeoDir_Event_Ical_Logger {

	/**
	 * The single instance of GeoDir_Event_Ical_Logger.
	 *
	 * @var GeoDir_Event_Ical_Logger|null
	 */
	private static $instance = null;

	/**
	 * The queue ID.
	 *
	 * @var string
	 */
	protected $queue_id = '';

	/**
	 * GeoDir_Event_Ical_Logger constructor.
	 *
	 * @param string $queue_id Optional. The queue ID.
	 */
	public function __construct( $queue_id = '' ) {
		$this->set_queue_id( $queue_id );
	}

	/**
	 * Retrieves the single instance of GeoDir_Event_Ical_Logger.
	 *
	 * @return GeoDir_Event_Ical_Logger
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets the queue ID.
	 *
	 * @param string $queue_id The queue ID.
	 */
	public function set_queue_id( $queue_id ) {
		$this->queue_id = sanitize_key( $queue_id );
	}

	/**
	 * Retrieves the queue ID.
	 *
	 * @return string The queue ID.
	 */
	public function get_queue_id() {
		return $this->queue_id;
	}

	/**
	 * Logs a success message.
	 *
	 * @param string $message The success message to log.
	 */
	public function success( $message ) {
		$this->log( 'success', $message );
	}

	/**
	 * Logs an informational message.
	 *
	 * @param string $message The informational message to log.
	 */
	public function info( $message ) {
		$this->log( 'info', $message );
	}

	/**
	 * Logs a warning message.
	 *
	 * @param string $message The warning message to log.
	 */
	public function warning( $message ) {
		$this->log( 'warning', $message );
	}

	/**
	 * Logs an error message.
	 *
	 * @param string $message The error message to log.
	 */
	public function error( $message ) {
		$this->log( 'error', $message );
	}

	/**
	 * Logs a message.
	 *
	 * @param string $status  The status of the log message (success, info, warning, error).
	 * @param string $message The message content to log.
	 */
	public function log( $status, $message ) {
		if ( empty( $this->queue_id ) ) {
			return;
		}

		$logs   = get_option( "gdevent_sync_logs_{$this->queue_id}", array() );
		$logs[] = array(
			'status'    => $status,
			'message'   => $message,
			'timestamp' => current_time( 'mysql' ),
		);

		update_option( "gdevent_sync_logs_{$this->queue_id}", $logs );
	}

	/**
	 * Retrieves logs associated with the current queue ID, optionally skipping a specified number of records.
	 *
	 * @param int $skip_count Optional. The number of records to skip.
	 * @param int $limit Optional. The maximum number of logs to retrieve.
	 * @return array An array of logs.
	 */
	public function get_logs( $skip_count = 0, $limit = 400000000 ) {
		$logs = get_option( "gdevent_sync_logs_{$this->queue_id}", array() );
		return array_slice( $logs, $skip_count, $limit );
	}

	/**
	 * Clears all logs associated with the current queue ID.
	 */
	public function clear() {
		if ( ! empty( $this->queue_id ) ) {
			delete_option( "gdevent_sync_logs_{$this->queue_id}" );
		}
	}

	/**
	 * Counts the number of logs associated with the current queue ID.
	 *
	 * @return int The number of logs.
	 */
	public function count_logs() {
		$logs = get_option( "gdevent_sync_logs_{$this->queue_id}", array() );
		return count( $logs );
	}

	/**
	 * Deletes logs associated with multiple queue IDs provided in the array $queue_ids.
	 *
	 * @param array $queue_ids An array containing queue IDs.
	 */
	public function delete_queues( $queue_ids ) {
		foreach ( $queue_ids as $queue_id ) {
			delete_option( "gdevent_sync_logs_{$queue_id}" );
		}
	}
}
