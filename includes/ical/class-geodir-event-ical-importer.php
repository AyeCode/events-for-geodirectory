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

if ( ! class_exists( 'GeoDir_Background_Process', false ) ) {
	include_once GEODIRECTORY_PLUGIN_DIR . 'includes/abstracts/class-geodir-background-process.php';
}

/**
 * Main calendar feed class.
 *
 * This class handles the initialization of the iCal feed and exports iCal data.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @since 1.0.0
 */
class GeoDir_Event_Ical_Importer extends GeoDir_Background_Process {
	/**
	 * The one true instance of GeoDir_Event_Ical_Importer.
	 *
	 * @var GeoDir_Event_Ical_Importer
	 */
	private static $instance;

	protected $action = 'import';

	/**
	 * The batch size for processing tasks.
	 */
	const BATCH_SIZE = 1000;

	/**
	 * Action identifier for parsing calendar data.
	 */
	const ACTION_PARSE = 'parse';

	/**
	 * Action identifier for importing booking data.
	 */
	const ACTION_IMPORT = 'import';

	/**
	 * Maximum request timeout duration.
	 */
	const MAX_REQUEST_TIMEOUT = 30;

	/**
	 * Prefix for background worker.
	 *
	 * @var string
	 */
	protected $prefix = 'geodir_event_ical';

	/**
	 * Instance of the statistics handler.
	 *
	 * @var GeoDir_Event_Ical_Stats
	 */
	public $stats;

	/**
	 * Instance of the logger.
	 *
	 * @var GeoDir_Event_Ical_Logger
	 */
	public $logger;

	/**
	 * Maximum execution time for the background process.
	 *
	 * @var int
	 */
	protected $max_execution_time = 0;

	/**
	 * Constructor method.
	 * Adds the blog ID to the prefix, initializes options, logger, importer, stats, and sets the maximum execution time.
	 */
	public function __construct() {
		// Add blog ID to the prefix (only for multisites and only for IDs 2, 3 and so on).
		$blog_id = get_current_blog_id();
		if ( $blog_id > 1 ) {
			$this->prefix .= '_' . $blog_id;
		}

		parent::__construct();

		$this->stats              = new GeoDir_Event_Ical_Stats();
		$this->logger             = new GeoDir_Event_Ical_Logger();
		$this->max_execution_time = intval( ini_get( 'max_execution_time' ) );
	}

	/**
	 * Retrieves the one true instance of GeoDir_Event_Ical_Importer.
	 *
	 * @since 1.0
	 * @return GeoDir_Event_Ical_Importer
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks if the background process is in progress.
	 *
	 * @return bool
	 */
	public function is_in_progress() {
		return $this->is_process_running() || ! $this->is_queue_empty();
	}

	/**
	 * Touches the background process to restart if needed.
	 */
	public function touch() {
		if ( ! $this->is_process_running() && ! $this->is_queue_empty() ) {
			// Background process down, but was not finished. Restart it.
			$this->dispatch();
		}
	}

	/**
	 * Calculates time left for the background process.
	 *
	 * @return int
	 */
	protected function time_left() {
		if ( $this->max_execution_time > 0 ) {
			return $this->start_time + $this->max_execution_time - time();
		} else {
			return self::MAX_REQUEST_TIMEOUT;
		}
	}

	/**
	 * Handles completion of background process.
	 */
	protected function complete() {
		parent::complete();

		do_action( $this->identifier . '_complete' );
	}

	/**
	 * Retrieves the progress of the background process.
	 *
	 * @return int
	 */
	public function get_progress() {
		$stats = $this->stats->get_stats();

		$total     = $stats['total'];
		$processed = $stats['succeed'] + $stats['skipped'] + $stats['failed'];

		if ( $total == 0 ) {
			return $this->is_in_progress() ? 0 : 100;
		} else {
			return min( round( $processed / $total * 100 ), 100 );
		}
	}

	/**
	 * Handles each task of the background process.
	 *
	 * @param array $task
	 * @return array|false
	 */
	protected function task( $task ) {
		if ( ! isset( $task['action'] ) ) {
			return false;
		}

		if ( ! empty( $task['queue_id'] ) ) {
			$this->logger->set_queue_id( $task['queue_id'] );
			$this->stats->set_queue_id( $task['queue_id'] );
		}

		switch ( $task['action'] ) {
			case self::ACTION_PARSE:
				$task = $this->task_parse( $task );
				break;
			case self::ACTION_IMPORT:
				$task = $this->task_import( $task );
				break;
		}

		return $task;
	}

	/**
	 * Parses new events immediately and adds new "import" tasks.
	 *
	 * @param string $calendar_uri The URI of the calendar
	 */
	public function parse_calendar_upload( $task ) {
		$task['calendar_source'] = 'upload';
		$this->task_parse( $task );
	}

	/**
	 * Parses new events immediately and adds new "import" tasks.
	 *
	 * @param array $workload The URI of the calendar
	 */
	public function parse_calendar_urls( $workload ) {
		$tasks = array(
			array_merge(
				$workload,
				array(
					'action'          => self::ACTION_PARSE,
					'calendar_source' => 'url',
				)
			),
		);

		$this->add_tasks( $tasks );
	}

	/**
	 * Adds import tasks to the background process.
	 *
	 * @param array $workloads [[events], ...]
	 */
	public function add_import_tasks( $workloads ) {
		$tasks = array_map(
			function ( $workload ) {
				$workload['action'] = self::ACTION_IMPORT;
				return $workload;
			},
			$workloads
		);

		$this->add_tasks( $tasks );
	}

	/**
	 * Parses the task for parsing calendar data.
	 *
	 * This method parses the calendar data from the provided URI, extracts events, and performs necessary actions based on the parsed data.
	 *
	 * @param array $task The task containing listing ID, calendar URI, sync ID, and queue ID.
	 * @return array|false Returns an array of tasks or false if the parsing encounters an error.
	 */
	protected function task_parse( $task ) {
		if ( ! empty( $task['queue_id'] ) ) {
			$this->logger->set_queue_id( $task['queue_id'] );
			$this->stats->set_queue_id( $task['queue_id'] );
		}

		$calendar_source = $task['calendar_source'];
		$calendar_uri    = $task['calendar_uri'];

		try {
			$calendar_name = $this->retrieve_calendar_name_from_source( $calendar_uri );

			if ( 'upload' === $calendar_source ) {
				$calendar_content = $this->retrieve_calendar_content_from_upload( $calendar_uri );
			} else {
				$calendar_content = $this->retrieve_calendar_content_from_url( $calendar_uri );
			}

			$ical         = new GeoDir_Event_Ical( $calendar_content );
			$events       = $ical->get_events_data();
			$events_count = count( $events );

			if ( 0 < $events_count ) {
				$this->logger->info(
					sprintf(
						_nx(
							'%1$d event found in calendar %2$s',
							'%1$d events found in calendar %2$s',
							$events_count,
							'%s - calendar URI or calendar filename',
							'geodirevents'
						),
						$events_count,
						$calendar_name
					)
				);

				$import_tasks = array_map(
					function ( $event ) use ( $task ) {
						return array(
							'event'       => $event,
							'post_author' => $task['post_author'],
							'queue_id'    => $task['queue_id'],
						);
					},
					$events
				);

				$this->add_import_tasks( $import_tasks );

				$this->stats->increase_imports_total( $events_count );

			} elseif ( empty( $calendar_content ) ) {

				$this->logger->info(
					sprintf(
						_x(
							'Calendar source is empty (%s)',
							'%s - calendar URI or calendar filename',
							'geodirevents'
						),
						$calendar_name
					)
				);

			} else {

				$this->logger->info(
					sprintf(
						_x(
							'Calendar file is not empty, but there are no events in %s',
							'%s - calendar URI or calendar filename',
							'geodirevents'
						),
						$calendar_name
					)
				);
			}
		} catch ( GeoDir_Event_Exception_Execution_Time $e ) {
			// Stop executing ACTION_PARSE task, restart the process, and give more time to request files.
			add_filter( $this->identifier . '_time_exceeded', '__return_true' );

			/**
			 * There might be problems on hosts with a low max_execution_time:
			 *
			 * WP Background Processing library does not check the execution time option and always schedules 20 seconds for every handle cycle.
			 * Process can fall and restart only by cron (only every 5 minutes).
			 * Process can go into an infinite loop, restarting every time because of a negative timeout.
			 */

			return $task;

		} catch ( GeoDir_Event_Exception_Request $e ) {
			$this->logger->error( sprintf( __( 'Error while loading calendar (%1$s): %2$s', 'geodirevents' ), $calendar_uri, $e->getMessage() ) );
		} catch ( Exception $e ) {
			$this->logger->error( sprintf( _x( 'Parse error. %s', '%s - error description', 'geodirevents' ), $e->getMessage() ) );
		}

		return false;
	}

	protected function task_import( $task ) {
		$event       = $task['event'];
		$post_author = isset( $task['post_author'] ) ? (int) $task['post_author'] : get_current_user_id();

		$uid         = sanitize_text_field( $event['uid'] );
		$title       = isset( $event['summary'] ) ? sanitize_text_field( $event['summary'] ) : '';
		$description = isset( $event['description'] ) ? wp_kses_post( $event['description'] ) : '';
		$location    = isset( $event['location'] ) ? sanitize_text_field( $event['location'] ) : '';
		$categories  = isset( $event['categories'] ) ? (array) $event['categories'] : array();
		$geo         = isset( $event['geo'] ) ? (array) $event['geo'] : array();
		$post_type   = 'gd_event';

		// Check if event already exists.
		$existing_event = get_posts(
			array(
				'post_type'      => $post_type,
				'meta_key'       => '_event_uid',
				'meta_value'     => $uid,
				'posts_per_page' => 1,
			)
		);

		try {
			$dtstart = new DateTime( $event['dtstart'] );
			$dtend   = new DateTime( $event['dtend'] );
		} catch ( Exception $e ) {
			$this->logger->error(
				sprintf(
					'Failed to import event: Invalid date format. Start date: %s, End date: %s',
					$event['dtstart'] ?? 'undefined',
					$event['dtend'] ?? 'undefined'
				)
			);
			$this->stats->increase_failed_imports( 1 );
			return false;
		}

		$event_data = array(
			'post_title'   => $title,
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => $post_type,
			'post_author'  => (int) $post_author,
		);

		$new_event = false;
		if ( $existing_event ) {
			$event_data['ID'] = $existing_event[0]->ID;
			$event_id         = wp_update_post( $event_data );
		} else {
			$new_event = true;
			$event_id  = wp_insert_post( $event_data );
		}

		if ( is_wp_error( $event_id ) ) {
			$this->logger->error(
				sprintf(
					'Failed to %s event "%s": %s',
					$new_event ? 'create' : 'update',
					$title,
					$event_id->get_error_message()
				)
			);

			$this->stats->increase_failed_imports( 1 );
			return false;
		}

		$gd_event = geodir_get_post_info( (int) $event_id );

		update_post_meta( $gd_event->ID, '_event_uid', $uid );

		if ( ! empty( $categories ) ) {
			foreach ( $categories as $index => $category ) {
				$event_category = $this->create_taxonomy( $post_type, array( 'name' => sanitize_text_field( $category ) ) );

				if ( false !== $event_category ) {
					$post_category = wp_parse_id_list( array( $event_category ) );
					geodir_save_post_meta( $gd_event->ID, 'post_category', $post_category );
					wp_set_object_terms( $gd_event->ID, $post_category, $gd_event->post_type . 'category', true );

					if ( 0 === $index ) {
						geodir_save_post_meta( $gd_event->ID, 'default_category', $event_category );
					}
				}
			}
		}

		// Check if the event is "all day".
		$default_date = date_i18n( 'Y-m-d' );
		$start_date   = $dtstart->format( 'Y-m-d' );
		$end_date     = $dtend->format( 'Y-m-d' );
		$all_day      = $this->is_all_day_event( $dtstart, $dtend );

		if ( ! geodir_event_is_date( $start_date ) ) {
			$start_date = $default_date;
		}

		if ( strtotime( $end_date ) < strtotime( $start_date ) ) {
			$end_date = $start_date;
		}

		$schedule = array(
			'event_id'   => (int) $gd_event->ID,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'start_time' => $dtstart->format( 'H:i A' ),
			'end_time'   => $dtend->format( 'H:i A' ),
			'all_day'    => (int) $all_day,
			'recurring'  => 0,
		);

		// Save event schedules and meta.
		$event_dates = maybe_serialize( $schedule );
		GeoDir_Event_Schedules::create_schedules( array( $schedule ), $gd_event->ID );
		geodir_save_post_meta( $gd_event->ID, 'event_dates', $event_dates );

		// Save geolocation.
		if ( isset( $geo['latitude'] ) && ! empty( $geo['latitude'] ) ) {
			geodir_save_post_meta( $gd_event->ID, 'latitude', sanitize_text_field( $geo['latitude'] ) );
		}

		if ( isset( $geo['longitude'] ) && ! empty( $geo['longitude'] ) ) {
			geodir_save_post_meta( $gd_event->ID, 'longitude', sanitize_text_field( $geo['longitude'] ) );
		}

		// Save location details.
		$location = $this->parse_event_location( $location );

		if ( isset( $location['street'], $location['city'], $location['region'], $location['country'], $location['zip'] ) ) {
			geodir_save_post_meta( $gd_event->ID, 'street', sanitize_text_field( $location['street'] ) );
			geodir_save_post_meta( $gd_event->ID, 'city', sanitize_text_field( $location['city'] ) );
			geodir_save_post_meta( $gd_event->ID, 'region', sanitize_text_field( $location['region'] ) );
			geodir_save_post_meta( $gd_event->ID, 'country', sanitize_text_field( $location['country'] ) );
			geodir_save_post_meta( $gd_event->ID, 'zip', sanitize_text_field( $location['zip'] ) );
		}

		// Handle featured image.
		if ( isset( $event['attachment'] ) && ! empty( $event['attachment'] ) ) {
			$attachment = sanitize_text_field( $event['attachment'] );
			// Delete any existing media.
			GeoDir_Media::delete_files( $gd_event->ID, 'post_images' );

			geodir_save_post_meta( $gd_event->ID, 'featured_image', $attachment );
			GeoDir_Media::insert_attachment( $gd_event->ID, 'post_images', $attachment, $title, '', 1, 1, true );
		}

		if ( $new_event ) {
			$this->logger->success(
				sprintf(
					__( 'New event %1$s. Event scheduled from %2$s to %3$s.', 'geodirevents' ),
					$gd_event->post_title,
					$start_date,
					$end_date
				)
			);
		} else {
			$this->logger->info(
				sprintf(
					__( 'Success. Event %s updated with new data.', 'geodirevents' ),
					$gd_event->post_title
				)
			);
		}

		$this->stats->increase_succeed_imports( 1 );

		return false;
	}

	/**
	 * Determine if an event is an all-day event.
	 *
	 * @param DateTime $dtstart Start date/time.
	 * @param DateTime $dtend End date/time.
	 * @return bool
	 */
	protected function is_all_day_event( DateTime $dtstart, DateTime $dtend ) {
		return ( $dtstart->format( 'H:i:s' ) === '00:00:00' && $dtend->format( 'H:i:s' ) === '00:00:00' && $dtstart->format( 'Y-m-d' ) !== $dtend->format( 'Y-m-d' ) )
			|| ( $dtstart->format( 'H:i:s' ) === '00:00:00' && $dtend->format( 'H:i:s' ) === '23:59:59' );
	}

	/**
	 * Adds tasks to the background process.
	 *
	 * @param array $tasks
	 */
	protected function add_tasks( $tasks ) {
		// Save new batches
		$batch_size = apply_filters( "{$this->identifier}_batch_size", self::BATCH_SIZE );
		$batches    = array_chunk( $tasks, $batch_size );

		foreach ( $batches as $batch ) {
			$this->data( $batch )->save();
		}

		$this->touch();
	}

	/**
	 * Retrieves the calendar name from the source.
	 *
	 * @param string $calendar_uri The URI of the calendar
	 * @return string The calendar name
	 */
	protected function retrieve_calendar_name_from_source( $calendar_uri ) {
		if ( isset( $_FILES['import'] ) && isset( $_FILES['import']['name'] ) ) {
			return sanitize_text_field( wp_unslash( $_FILES['import']['name'] ) );
		} else {
			return $calendar_uri;
		}
	}

	/**
	 * Retrieves the calendar content from the source.
	 *
	 * @param string $calendar_uri The URI of the calendar
	 * @return string The calendar content
	 */
	protected function retrieve_calendar_content_from_upload( $calendar_uri ) {
		$calendar_content = @file_get_contents( $calendar_uri );
		if ( $calendar_content === false ) {
			$this->logger->error( __( 'Cannot read uploaded file', 'geodirevents' ) );
			return '';
		} else {
			return $calendar_content;
		}
	}

	/**
	 * Retrieves the calendar content from the source.
	 *
	 * @param string $calendar_uri Link to the external calendar.
	 * @return string The calendar content
	 *
	 * @throws GeoDir_Event_Exception_Execution_Time
	 * @throws GeoDir_Event_Exception_Request
	 */
	protected function retrieve_calendar_content_from_url( $calendar_uri ) {
		// Time left until script termination.
		$time_left = $this->time_left();

		// Leave 5 seconds for parsing/batching/logging.
		$timeout = min( $time_left - 5, self::MAX_REQUEST_TIMEOUT );

		if ( $timeout <= 0 ) {
			throw new GeoDir_Event_Exception_Execution_Time( sprintf( esc_attr__( 'Maximum execution time is set to %d seconds.', 'geodirevents' ), $timeout ) );
		}

		$request_args = array(
			'timeout'    => $timeout,
			'user-agent' => geodir_event_site_domain() . '/' . GEODIR_EVENT_VERSION,
		);

		$request_args = apply_filters( "{$this->identifier}_retrieve_calendar_request_args", $request_args, $calendar_uri );

		$response = wp_remote_get( $calendar_uri, $request_args );

		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			throw new GeoDir_Event_Exception_Request( $response->get_error_message() );
		}

		$calendar_content = wp_remote_retrieve_body( $response );

		return $calendar_content;
	}

	/**
	 * Create a taxonomy term if it doesn't already exist.
	 *
	 * @param string $post_type The post type to which the taxonomy belongs.
	 * @param array  $category The category data, including 'name' and optionally 'parent-name'.
	 * @param string $taxonomy The taxonomy to which the category belongs (default is 'category').
	 * @return int|WP_Error The term ID if successful, WP_Error otherwise.
	 */
	protected function create_taxonomy( $post_type, $category, $taxonomy = 'category' ) {
		if ( ! taxonomy_exists( $post_type . $taxonomy ) ) {
			GeoDir_Post_types::register_taxonomies();
		}

		$existing_category = term_exists( $category['name'], $post_type . $taxonomy );

		if ( ! $existing_category ) {
			$args = array();

			// Add parent ID if present.
			if ( isset( $category['parent-name'] ) ) {
				$parent = get_term_by( 'name', $category['parent-name'], $post_type . $taxonomy );
				if ( ! empty( $parent->term_id ) ) {
					$args['parent'] = absint( $parent->term_id );
				}
			}

			// Insert the category.
			$new_category = wp_insert_term( $category['name'], $post_type . $taxonomy, $args );

			if ( is_wp_error( $new_category ) ) {
				return $new_category;
			}

			return (int) $new_category['term_id'];
		} else {
			return (int) $existing_category['term_id'];
		}
	}

	/**
	 * Parses a location string into structured components.
	 *
	 * @param string $location The location string in the format "Venue, Street, City, Region, Zip, Country".
	 * @return array Associative array containing 'street', 'city', 'region', 'country', and 'zip'.
	 */
	protected function parse_event_location( $location ) {
		// Split the string by commas and trim any extra whitespace.
		$parts = array_map( 'trim', explode( ',', $location ) );

		return array(
			'street'  => isset( $parts[1] ) ? $parts[1] : '',
			'city'    => isset( $parts[2] ) ? $parts[2] : '',
			'region'  => isset( $parts[3] ) ? $parts[3] : '',
			'zip'     => isset( $parts[4] ) ? $parts[4] : '',
			'country' => isset( $parts[5] ) ? $parts[5] : '',
		);
	}

	/**
	 * Retrieves the details including logs and statistics.
	 *
	 * @param int $skip_logs Optional. How many logs to skip. Defaults to 0.
	 * @return array An array containing logs and stats
	 */
	public function get_details( $skip_logs = 0 ) {
		return array(
			'logs'  => $this->logger->get_logs( $skip_logs ),
			'stats' => $this->stats->get_stats(),
		);
	}

	/**
	 * Deletes old log messages and stats.
	 *
	 * This function removes log messages and stats that are older than 1 hour.
	 * It also cleans up orphaned stat entries.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return int The number of options deleted.
	 */
	public function delete_ghosts() {
		global $wpdb;

		$deleted_count = 0;
		$current_time  = time();
		$one_hour_ago  = $current_time - HOUR_IN_SECONDS;

		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( 'gdevent_sync_logs_' ) . '%',
				$wpdb->esc_like( 'gdevent_sync_stats_' ) . '%'
			)
		);

		if ( ! $options ) {
			return $deleted_count;
		}

		foreach ( $options as $option ) {
			$is_log  = 0 === strpos( $option->option_name, 'gdevent_sync_logs_' );
			$is_stat = 0 === strpos( $option->option_name, 'gdevent_sync_stats_' );

			if ( $is_log ) {
				$logs = maybe_unserialize( $option->option_value );
				if ( ! is_array( $logs ) || empty( $logs ) ) {
					$this->delete_log_and_stat_option( $option->option_name );
					++$deleted_count;
					continue;
				}

				$last_log = end( $logs );
				if ( isset( $last_log['timestamp'] ) ) {
					$log_time = strtotime( $last_log['timestamp'] );
					if ( $log_time && $log_time < $one_hour_ago ) {
						$this->delete_log_and_stat_option( $option->option_name );
						++$deleted_count;
					}
				}
			} elseif ( $is_stat ) {
				$stats = maybe_unserialize( $option->option_value );
				if ( ! is_array( $stats ) || empty( $stats ) ) {
					delete_option( $option->option_name );
					++$deleted_count;
					continue;
				}

				$log_option_name = str_replace( 'gdevent_sync_stats_', 'gdevent_sync_logs_', $option->option_name );
				if ( ! get_option( $log_option_name ) ) {
					delete_option( $option->option_name );
					++$deleted_count;
				}
			}
		}

		return $deleted_count;
	}

	/**
	 * Deletes both the log and stat options for a given queue ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $log_option_name The name of the log option.
	 */
	private function delete_log_and_stat_option( $log_option_name ) {
		$stat_option_name = str_replace( 'gdevent_sync_logs_', 'gdevent_sync_stats_', $log_option_name );
		delete_option( $log_option_name );
		delete_option( $stat_option_name );
	}
}
