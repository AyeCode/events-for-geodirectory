<?php
/**
 * Main iCal Handler Class.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @version 1.0.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main ical display class.
 *
 * Handles display of iCal import and export actions for events.
 *
 * @package GeoDir_Event
 * @subpackage iCal
 * @since 1.0.0
 */
class GeoDir_Event_Ical_Handler {
	/**
	 * The one true instance of GeoDir_Event_Ical_Handler.
	 *
	 * @var GeoDir_Event_Ical_Handler
	 */
	private static $instance;

	/**
	 * Retrieves the one true instance of GeoDir_Event_Ical_Handler.
	 *
	 * @since 1.0
	 * @return GeoDir_Event_Ical_Handler
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Display iCal actions for events.
	 *
	 * Renders export or import buttons based on the current context.
	 *
	 * @param string  $listing_type Current listing type.
	 * @param WP_Post $post        Current post object.
	 */
	public function display_ical_actions( $listing_type, $post ) {
		if ( 'gd_event' !== $listing_type ) {
			return;
		}

		$parent_id = isset( $_REQUEST['pid'] ) ? absint( $_REQUEST['pid'] ) : 0;

		if ( $parent_id && $parent_id === $post->post_parent ) {
			$this->render_export_calendar_actions( $post );
		} else {
			$this->render_import_calendar_actions();
		}
	}

	/**
	 * Render export calendar actions modal.
	 *
	 * @param WP_Post $post Current post object.
	 */
	private function render_export_calendar_actions( $post ) {
		// Generate iCal export URL.
		$query_args = array(
			'feed'     => 'gdevent.ics',
			'event_id' => absint( $_REQUEST['pid'] ),
		);
		$ics_url    = add_query_arg( $query_args, site_url( '/' ) );

		// Get calendar links.
		$event_links = GeoDir_Event_Ical_Links::instance()->calendar_links( $post );

		aui()->button(
			array(
				'type'             => 'button',
				'class'            => 'float-end btn btn-sm btn-primary mb-2 js_export-calendar',
				'content'          => __( 'Export Calendar', 'geodirevents' ),
				'icon'             => 'fa fa-calendar-plus',
				'extra_attributes' => array(
					'data-id'     => absint( $_REQUEST['pid'] ),
					'data-toggle' => 'geodir-modal',
					'data-action' => '#geodir-evert-export-calendar',
				),
			),
			true
		);

		$this->render_export_modal_template( $ics_url, $event_links );
	}

	/**
	 * Render export modal template.
	 *
	 * @param string $ics_url      iCal export URL.
	 * @param array  $event_links  Calendar service links.
	 */
	private function render_export_modal_template( $ics_url, $event_links ) {
		?>
		<script id="geodir-evert-export-calendar" type="text/template">
			<div class="modal-header">
				<h5 class="modal-title"><?php esc_html_e( 'Export Calendar', 'geodirevents' ); ?></h5>
				<span class="modal-close" data-dismiss="modal" aria-label="Close">
					<i class="fa fa-times"></i>
				</span>
			</div>

			<div class="modal-body">
				<?php $this->render_ical_url_section( $ics_url ); ?>
				<?php $this->render_calendar_action_buttons( $ics_url, $event_links ); ?>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<?php esc_html_e( 'Cancel', 'geodirevents' ); ?>
				</button>
			</div>
		</script>
		<?php
	}

	/**
	 * Render iCal URL section with copy functionality.
	 *
	 * @param string $ics_url iCal export URL.
	 */
	private function render_ical_url_section( $ics_url ) {
		?>
		<div class="code-block position-relative mb-4">
			<h6 class="overline-title title mb-0">
				<?php esc_html_e( 'iCal URL', 'geodirevents' ); ?>
			</h6>
			<button class="btn btn-sm clipboard-init position-absolute end-0" 
				title="<?php esc_attr_e( 'Copy to clipboard', 'geodirevents' ); ?>"
				data-clipboard-target="#exportIcalLink" 
				data-clip-success="<?php esc_attr_e( 'Copied', 'geodirevents' ); ?>" 
				data-clip-text="<?php esc_attr_e( 'Copy', 'geodirevents' ); ?>" 
				style="top:-5px">
				<span class="clipboard-text">
					<?php esc_html_e( 'Copy', 'geodirevents' ); ?>
				</span>
			</button>
			<div class="bg-light mt-3 p-2 rounded-2" id="exportIcalLink">
				<?php echo esc_url( $ics_url ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render calendar action buttons.
	 *
	 * @param string $ics_url      iCal export URL.
	 * @param array  $event_links  Calendar service links.
	 */
	private function render_calendar_action_buttons( $ics_url, $event_links ) {
		$calendar_buttons = array(
			array(
				'url'   => $ics_url,
				'icon'  => 'fa-download',
				'label' => __( 'Download Calendar', 'geodirevents' ),
				'class' => 'btn-primary',
			),
			array(
				'url'   => $event_links['google'],
				'icon'  => 'fa-calendar-plus',
				'label' => __( 'Add to Google Calendar', 'geodirevents' ),
				'class' => 'btn-outline-primary',
			),
			array(
				'url'   => $event_links['apple'],
				'icon'  => 'fa-link',
				'label' => __( 'Add to Apple Calendar', 'geodirevents' ),
				'class' => 'btn-outline-primary',
			),
			array(
				'url'   => $event_links['outlook_live'],
				'icon'  => 'fa-link',
				'label' => __( 'Add to Outlook Live', 'geodirevents' ),
				'class' => 'btn-outline-primary',
			),
		);

		foreach ( $calendar_buttons as $button ) {
			printf(
				'<div class="mb-4">
                    <a class="btn btn-lg d-block w-100 %1$s" href="%2$s">
                        <i class="fa %3$s"></i>
                        <span class="ml-2">%4$s</span>
                    </a>
                </div>',
				esc_attr( $button['class'] ),
				esc_url( $button['url'] ),
				esc_attr( $button['icon'] ),
				esc_html( $button['label'] )
			);
		}
	}

	/**
	 * Render import calendar actions modal.
	 */
	private function render_import_calendar_actions() {
		aui()->button(
			array(
				'class'            => 'float-end btn btn-sm btn-primary mb-2 js_import-calendar',
				'content'          => __( 'Import iCal Event', 'geodirevents' ),
				'icon'             => 'fa fa-calendar-plus',
				'extra_attributes' => array(
					'data-toggle' => 'geodir-modal',
					'data-action' => '#geodir-event-import-calendar',
				),
			),
			true
		);

		$this->render_import_modal_template();
	}

	/**
	 * Render import modal template.
	 */
	private function render_import_modal_template() {
		$logs_handler    = new GeoDir_Event_Ical_Logs_Handler();
		$process_details = array(
			'logs'  => array(),
			'stats' => GeoDir_Event_Ical_Stats::instance()->empty_stats(),
		);

		?>
		<script id="geodir-event-import-calendar" type="text/template">
			<?php $this->render_import_modal_content( $logs_handler, $process_details ); ?>
		</script>

		<style type="text/css">
			.upload-area.dragover {
				border-color: var(--bs-primary) !important;
				background-color: var(--bs-primary-bg-subtle) !important;
			}
			.file-name {
				max-width: 200px;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
		</style>
		<?php
	}

	/**
	 * Render import modal content.
	 *
	 * @param GeoDir_Event_Ical_Logs_Handler $logs_handler    Logs handler instance.
	 * @param array                          $process_details Process details.
	 */
	private function render_import_modal_content( $logs_handler, $process_details ) {
		?>
		<form class="js_event-import" action="geodir_event_import_event" method="POST" novalidate>
			<div class="modal-header">
				<h5 class="modal-title"><?php esc_html_e( 'Import Event', 'geodirevents' ); ?></h5>
				<span class="modal-close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'geodirevents' ); ?>">
					<i class="fa fa-times"></i>
				</span>
			</div>

			<div class="modal-body">
				<?php $this->render_file_upload_section(); ?>

				<div class="mb-3 upload-area__url">
					<label for="ical-url" class="form-label"><?php esc_html_e( 'iCal URL (optional)', 'geodirevents' ); ?></label>
					<input type="url" class="form-control" id="ical-url" name="ical_url" placeholder="https://example.com/calendar.ics">
					<div class="invalid-feedback"><?php esc_html_e( 'Please enter a valid URL.', 'geodirevents' ); ?></div>
				</div>

				<div class="upload-area__process p-3 rounded bg-light d-none">
					<?php
					$logs_handler->display( $process_details );
					$logs_handler->display_progress();
					?>
				</div>

				<div class="alert alert-success mt-3 d-none" role="alert"></div>
				<div class="alert alert-danger mt-3 d-none" role="alert"></div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<?php esc_html_e( 'Cancel', 'geodirevents' ); ?>
				</button>
				<button type="submit" class="btn btn-primary">
					<?php esc_html_e( 'Import', 'geodirevents' ); ?>
				</button>
			</div>
		</form>
		<?php
	}

	/**
	 * Render file upload section for iCal import.
	 */
	private function render_file_upload_section() {
		?>
		<div class="mb-3 upload-area__import">
			<div class="upload-area rounded-3 border border-2 border-dashed p-4 text-center bg-light cursor-pointer" id="gdevents-upload-area">
				<div class="upload-area__content">
					<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16">
						<path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383z"/>
						<path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3z"/>
					</svg>
					<p class="mb-2"><?php esc_html_e( 'Drag and drop here or click', 'geodirevents' ); ?></p>
					<input type="file" class="d-none js_import-file" accept=".ics,.ical,.icalendar">
					<button type="button" class="btn btn-primary px-4 js_browse-btn">
						<?php esc_html_e( 'Browse Files', 'geodirevents' ); ?>
					</button>
				</div>

				<div class="upload-area__file d-none">
					<div class="d-flex align-items-center">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark" viewBox="0 0 16 16">
							<path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
						</svg>
						<span class="file-name ms-3 me-auto text-body"></span>
						<button type="button" class="btn btn-link text-danger p-0 remove-file" aria-label="<?php esc_attr_e( 'Remove file', 'geodirevents' ); ?>">
							<i class="fa fa-trash"></i>
						</button>
					</div>
				</div>
			</div>

			<div class="mt-2 text-secondary small">
				<i class="bi bi-info-circle me-1"></i>
				<?php esc_html_e( 'Accepted formats: .ics, .ical, .icalendar', 'geodirevents' ); ?>
			</div>
		</div>
		<?php
	}
}
