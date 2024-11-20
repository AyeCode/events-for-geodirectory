<?php
/**
 * Main booking stats Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main booking stats class.
 *
 * @since 1.0.0
 */
class GeoDir_Event_Ical_Stats {

	/**
	 * The one true instance of GeoDir_Event_Ical_Stats.
	 *
	 * @var GeoDir_Event_Ical_Stats
	 */
	private static $instance;

	/**
	 * The queue ID.
	 *
	 * @var string
	 */
	protected $queue_id = '';

	/**
	 * Initializes a new instance of the Stats class with an optional queue ID.
	 *
	 * @param string $queue_id Optional. The queue ID.
	 */
	public function __construct( $queue_id = '' ) {
		$this->set_queue_id( $queue_id );
	}

	/**
	 * Get the one true instance of GeoDir_Event_Ical_Stats.
	 *
	 * @return GeoDir_Event_Ical_Stats
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets the queue ID for the current instance of the Stats class.
	 *
	 * @param string $queue_id The queue ID.
	 */
	public function set_queue_id( $queue_id ) {
		$this->queue_id = sanitize_key( $queue_id );
	}

	/**
	 * Retrieves the queue ID for the current instance of the Stats class.
	 *
	 * @return string The queue ID.
	 */
	public function get_queue_id() {
		return $this->queue_id;
	}

	/**
	 * Increases the total count of imports by the specified increment.
	 *
	 * @param int $increment The amount by which to increase the total imports count.
	 */
	public function increase_imports_total( $increment ) {
		$this->increase_field( 'import_total', $increment );
	}

	/**
	 * Increases the count of successful imports by the specified increment.
	 *
	 * @param int $increment The amount by which to increase the successful imports count.
	 */
	public function increase_succeed_imports( $increment ) {
		$this->increase_field( 'import_succeed', $increment );
	}

	/**
	 * Increases the count of skipped imports by the specified increment.
	 *
	 * @param int $increment The amount by which to increase the skipped imports count.
	 */
	public function increase_skipped_imports( $increment ) {
		$this->increase_field( 'import_skipped', $increment );
	}

	/**
	 * Increases the count of failed imports by the specified increment.
	 *
	 * @param int $increment The amount by which to increase the failed imports count.
	 */
	public function increase_failed_imports( $increment ) {
		$this->increase_field( 'import_failed', $increment );
	}

	/**
	 * Increases the value of a specific field by the specified increment in the option.
	 *
	 * @param string $field The name of the field to increase.
	 * @param int    $increment The amount by which to increase the field's value.
	 */
	protected function increase_field( $field, $increment ) {
		if ( empty( $this->queue_id ) ) {
			return;
		}

		$stats = get_option( "gdevent_sync_stats_{$this->queue_id}", $this->empty_stats() );
		if ( isset( $stats[ $field ] ) ) {
			$stats[ $field ] += $increment;
		} else {
			$stats[ $field ] = $increment;
		}
		update_option( "gdevent_sync_stats_{$this->queue_id}", $stats );
	}

	/**
	 * Retrieves the statistics for the current queue ID.
	 *
	 * @return array An array containing statistics (total, succeed, skipped, failed).
	 */
	public function get_stats() {
		$stats = get_option( "gdevent_sync_stats_{$this->queue_id}", $this->empty_stats() );
		return array(
			'total'   => $stats['import_total'] ?? 0,
			'succeed' => $stats['import_succeed'] ?? 0,
			'skipped' => $stats['import_skipped'] ?? 0,
			'failed'  => $stats['import_failed'] ?? 0,
		);
	}

	/**
	 * Returns an array representing empty statistics, with all counts initialized to 0.
	 *
	 * @return array An array containing empty statistics.
	 */
	public function empty_stats() {
		return array(
			'import_total'   => 0,
			'import_succeed' => 0,
			'import_skipped' => 0,
			'import_failed'  => 0,
		);
	}

	/**
	 * Retrieves statistics for multiple queue IDs provided in the array $queue_ids.
	 *
	 * @param array $queue_ids An array containing queue IDs.
	 * @return array An array containing statistics for each queue ID.
	 */
	public function select_stats( $queue_ids ) {
		$results = array();
		foreach ( $queue_ids as $queue_id ) {
			$stats                = get_option( "gdevent_sync_stats_{$queue_id}", $this->empty_stats() );
			$results[ $queue_id ] = array(
				'total'   => $stats['import_total'] ?? 0,
				'succeed' => $stats['import_succeed'] ?? 0,
				'skipped' => $stats['import_skipped'] ?? 0,
				'failed'  => $stats['import_failed'] ?? 0,
			);
		}
		return $results;
	}

	/**
	 * Resets the statistics for a specific queue ID, setting all counts to 0.
	 *
	 * @param string $queue_id The queue ID.
	 */
	public function reset_stats( $queue_id ) {
		update_option( "gdevent_sync_stats_{$queue_id}", $this->empty_stats() );
	}

	/**
	 * Deletes the statistics for a specific queue ID from the options.
	 *
	 * @param string $queue_id The queue ID.
	 */
	public function delete_queue( $queue_id ) {
		delete_option( "gdevent_sync_stats_{$queue_id}" );
	}

	/**
	 * Deletes the statistics for multiple queue IDs provided in the array $queue_ids from the options.
	 *
	 * @param array $queue_ids An array containing queue IDs.
	 */
	public function delete_queues( $queue_ids ) {
		foreach ( $queue_ids as $queue_id ) {
			delete_option( "gdevent_sync_stats_{$queue_id}" );
		}
	}
}
