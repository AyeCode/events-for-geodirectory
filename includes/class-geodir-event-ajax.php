<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Events AJAX class.
 *
 * AJAX Event Handler.
 *
 * @class    GeoDir_Event_AJAX
 * @package  GeoDirectory_Event_Manager/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_Event_AJAX {
	/**
	 * GeoDirectory Event Manager instance.
	 *
	 * @var GeoDir_Event_AJAX
	 * @access private
	 * @since  2.0.0
	 */
	private static $instance = null;

	/**
	 * Name of the nonce used for security verification.
	 *
	 * @var string
	 */
	protected $nonce_name = 'geodir_nonce';

	/**
	 * Prefix used for AJAX action names.
	 *
	 * @var string
	 */
	protected $action_prefix = 'geodir_';

	/**
	 * List of AJAX actions along with their details.
	 *
	 * @var array
	 */
	protected $ajax_actions = array(
		'ayi_action'                     => array(
			'method' => 'POST',
		),
		'calendar'                       => array(
			'method'  => 'GET',
			'no_priv' => true,
		),
		'widget_post_type_field_options' => array(
			'method' => 'POST',
		),
	);

	/**
	 * UWPM_Ajax constructor.
	 */
	public function __construct() {
		foreach ( $this->ajax_actions as $action => $details ) {
			$no_priv = isset( $details['no_priv'] ) ? $details['no_priv'] : false;
			$this->add_ajax_action( $action, $no_priv );
		}
	}

	/**
	 * Get the one true instance of GeoDir_Event_AJAX.
	 *
	 * @since 1.0.0
	 * @return GeoDir_Event_AJAX
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Retrieves input data for processing AJAX requests.
	 *
	 * @param string $action The name of the AJAX action without the 'wp' prefix.
	 * @return array An array containing input data for the AJAX request.
	 */
	protected function get_request_input( $action ) {
		$method = isset( $this->ajax_actions[ $action ]['method'] ) ? $this->ajax_actions[ $action ]['method'] : '';

		switch ( $method ) {
			case 'GET':
				$input = $_GET;
				break;
			case 'POST':
				$input = $_POST;
				break;
			default:
				$input = $_REQUEST;
		}

		return $input;
	}

	/**
	 * Retrieve nonces for AJAX actions.
	 *
	 * @return array Nonces for AJAX actions.
	 */
	public function get_nonces() {
		$nonces = array();
		foreach ( $this->ajax_actions as $action_name => $details ) {
			$nonces[ $this->action_prefix . $action_name ] = wp_create_nonce( $this->action_prefix . $action_name );
		}

		return $nonces;
	}

	/**
	 * Add AJAX action hooks.
	 *
	 * @param string $action  AJAX action name.
	 * @param bool   $no_priv Whether the action is available for non-logged in users.
	 */
	public function add_ajax_action( $action, $no_priv = false ) {
		add_action( 'wp_ajax_' . $this->action_prefix . $action, array( $this, $action ) );

		if ( $no_priv ) {
			add_action( 'wp_ajax_nopriv_' . $this->action_prefix . $action, array( $this, $action ) );
		}

		// GeoDir AJAX can be used for frontend ajax requests.
		add_action( 'geodir_ajax_' . $this->action_prefix . $action, array( $this, $action ) );
	}

	/**
	 * Check the validity of the nonce.
	 *
	 * @param string $action AJAX action name.
	 * @return bool True if the nonce is valid, otherwise false.
	 */
	protected function check_nonce( $action ) {
		if ( ! isset( $this->ajax_actions[ $action ] ) ) {
			return false;
		}

		$input = $this->get_request_input( $action );

		$nonce = isset( $input[ $this->nonce_name ] ) ? $input[ $this->nonce_name ] : '';

		return wp_verify_nonce( $nonce, $this->action_prefix . $action );
	}

	/**
	 * Verify the validity of the nonce.
	 *
	 * @param string $action AJAX action name.
	 */
	protected function verify_nonce( $action ) {
		if ( ! $this->check_nonce( $action ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Request does not pass security verification. Please refresh the page and try one more time.', 'geodirevents' ),
				)
			);
		}
	}

	/**
	 * Create a new GetPaid item.
	 */
	public function ayi_action() {
		$this->verify_nonce( __FUNCTION__ );

		GeoDir_Event_AYI::ajax_ayi_action();
		exit;
	}

	/**
	 * Create a new GetPaid item.
	 */
	public function calendar() {
		$this->verify_nonce( __FUNCTION__ );

		GeoDir_Event_Calendar::ajax_calendar();
		exit;
	}

	/**
	 * Create a new GetPaid item.
	 */
	public function widget_post_type_field_options() {
		$this->verify_nonce( __FUNCTION__ );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		try {
			$post_type = ! empty( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';

			$category_options = '';
			if ( $categories = geodir_category_options( $post_type ) ) {
				foreach ( $categories as $value => $name ) {
					$category_options .= '<option value="' . $value . '">' . $name . '</option>';
				}
			}

			$sort_by_options = '';
			if ( $sort_by = geodir_sort_by_options( $post_type ) ) {
				foreach ( $sort_by as $value => $name ) {
					$sort_by_options .= '<option value="' . $value . '">' . $name . '</option>';
				}
			}

			$data = array(
				'category' => array(
					'options' => $category_options,
				),
				'sort_by'  => array(
					'options' => $sort_by_options,
				),
			);

			$data = apply_filters( 'geodir_widget_post_type_field_options', $data, $post_type );

			wp_send_json_success( $data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}