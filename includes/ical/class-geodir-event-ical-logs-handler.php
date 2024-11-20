<?php
/**
 * Main logs handler Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the display of process logs and statistics.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */
class GeoDir_Event_Ical_Logs_Handler {

	/**
	 * Displays the process details including logs and statistics.
	 *
	 * @param array $process_details An array containing logs and statistics.
	 */
	public function display( array $process_details ): void {
		$logs  = $process_details['logs'];
		$stats = $process_details['stats'];

		$this->display_title();

		$this->display_stats( $stats );

		$this->display_logs( $logs );
	}

	/**
	 * Displays the title for the process information section.
	 */
	public function display_title(): void {
		echo '<h6 class="mb-2">';
		esc_html_e( 'Import Process', 'geodirevents' );
		echo '</h6>';
	}

	/**
	 * Displays the statistics for the process.
	 *
	 * @param array $stats An array containing various statistics.
	 */
	public function display_stats( array $stats ): void {
		echo '<p class="geodir-events-import-stats fw-medium">';
		printf( esc_html__( 'Total events: %s', 'geodirevents' ), '<span class="geodir-events-total float-end">' . esc_html( $stats['import_total'] ) . '</span>' );
		echo '<br />';
		printf( esc_html__( 'Success events: %s', 'geodirevents' ), '<span class="geodir-events-succeed float-end">' . esc_html( $stats['import_succeed'] ) . '</span>' );
		echo '<br />';
		printf( esc_html__( 'Skipped events: %s', 'geodirevents' ), '<span class="geodir-events-skipped float-end">' . esc_html( $stats['import_skipped'] ) . '</span>' );
		echo '<br />';
		printf( esc_html__( 'Failed events: %s', 'geodirevents' ), '<span class="geodir-events-failed float-end">' . esc_html( $stats['import_failed'] ) . '</span>' );
		echo '</p>';
	}

	/**
	 * Displays the logs associated with the process.
	 *
	 * @param array $logs An array containing log entries.
	 */
	public function display_logs( array $logs = array() ): void {
		echo '<ul class="geodir-events-logs list-unstyled">';
		foreach ( $logs as $log ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->log_to_html( $log );
		}
		echo '</ul>';
	}

	/**
	 * Displays the progress of the process.
	 */
	public function display_progress(): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="progress geodir-events-progress">';
		echo '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success geodir-events-progress__bar" role="progressbar">';
		echo '<span class="geodir-events-progress__text">0%</span>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Converts a log entry into HTML format.
	 *
	 * @param array $log Log entry ["status", "message"].
	 * @param bool  $inline Indicates whether the log should be displayed inline.
	 * @return string HTML representation of the log entry.
	 */
	public function log_to_html( array $log, bool $inline = false ): string {
		$log += array(
			'status'  => 'info',
			'message' => '',
		);

		$html = '';

		if ( ! empty( $log['message'] ) && ! $inline ) {
			$html .= '<li class="mb-1">';
			$html .= '<p class="alert p-2 fs-sm alert-' . esc_attr( $log['status'] ) . '">';
			$html .= esc_html( $log['message'] );
			$html .= '</p>';
			$html .= '</li>';
		} else {
			$html .= esc_html( $log['message'] );
		}

		return $html;
	}

	/**
	 * Converts an array of logs into HTML format.
	 *
	 * @param array $logs An array of log entries.
	 * @param bool  $inline Indicates whether the logs should be displayed inline.
	 * @return array HTML representations of the log entries.
	 */
	public function logs_to_html( array $logs, bool $inline = false ): array {
		$logs_html = array();
		foreach ( $logs as $log ) {
			$logs_html[] = $this->log_to_html( $log, $inline );
		}
		return $logs_html;
	}

	/**
	 * Builds a notice message based on the number of successful and failed events.
	 *
	 * @param int $succeed_count The number of successful events.
	 * @param int $failed_count The number of failed events.
	 * @return string The HTML representation of the notice message.
	 */
	public function build_notice( int $succeed_count, int $failed_count ): string {
		$message  = _n(
			'All done! %1$d event was successfully added.',
			'All done! %1$d events were successfully added.',
			$succeed_count,
			'geodirevents'
		);
		$message .= _n(
			' There was %2$d failure.',
			' There were %2$d failures.',
			$failed_count,
			'geodirevents'
		);
		$message  = sprintf( $message, $succeed_count, $failed_count );

		return $message;
	}
}
