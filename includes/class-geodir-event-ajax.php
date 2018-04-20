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
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// geodirectory_EVENT => nopriv
		$ajax_events = array(
			'ayi_action'			=> false,
			'ajax_calendar'			=> true,
			'json_search_business'	=> true,
			'event_fill_business'	=> true,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_geodir_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GeoDir AJAX can be used for frontend ajax requests.
				add_action( 'geodir_event_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public static function ayi_action() {
		GeoDir_Event_AYI::ajax_ayi_action();
		exit;
	}

	public static function ajax_calendar() {
		GeoDir_Event_Calendar::ajax_calendar();
		exit;
	}

	public static function json_search_business( $term = '' ) {
		check_ajax_referer( 'search-business', 'security' );

		if ( $term == '' && isset( $_POST['term'] ) ) {
			 $term = stripslashes( $_POST['term'] );
		}
		$term = geodir_clean( $term );
		if ( $term == '' ) {
			wp_die();
		}

		$limit = ! empty( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 20;
		$include = ! empty( $_POST['include'] ) ? absint( $_POST['include'] ) : 0;
		$post_type = apply_filters( 'geodir_event_search_business_post_type', 'gd_place' );

		$items = geodir_event_get_my_listings( $post_type, $term, $limit );

		$results = array();
		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				if ( $include == $item->ID ) {
					$include = 0;
				}
				$results[ $item->ID ] = rawurldecode( $item->post_title );
			}
		}
		if ( ! empty( $include ) && ( $title = get_the_title( $include ) ) ) {
			$results[ $include ] = rawurldecode( $title );
		}

		wp_send_json( apply_filters( 'geodir_event_json_search_found_business', $results ) );
	}

	public static function event_fill_business() {
		check_ajax_referer( 'fill-business', 'security' );
		
		try {
			$post = ! empty( $_POST['id'] ) && (int)$_POST['id'] > 0 ? geodir_get_post_info( (int)$_POST['id'] ) : array();
			if ( empty( $post ) || ! ( ! empty( $post->ID ) && $post->post_status == 'publish' ) ) {
				throw new Exception( __( 'Business does not found!', 'geodirevents' ) );
			}

			$post_types = geodir_get_posttypes();

			if ( ( $key = array_search( 'gd_event', $post_types ) ) !== false ) {
				unset( $post_types[ $key ] );
			}
			if ( empty( $post_types ) || ( ! empty( $post_types ) && ! in_array( $post->post_type, $post_types ) ) ) {
				throw new Exception( __( 'Business not to available link with event!', 'geodirevents' ) );
			}
			$post_id = $post->ID;
			$package_id = ! empty( $post->package_id ) ? $post->package_id : '';

			$custom_fields = geodir_post_custom_fields( $package_id, 'all', $post->post_type );
			
			$fields = array();
			foreach ( $custom_fields as $key => $field ) {
				if ( apply_filters( 'geodir_event_fill_business_skip_field', false, $field, $post ) ) {
					continue;
				}

				$name = $field['htmlvar_name'];
				if ( empty( $name ) ) {
					continue;
				}
				$post_keys = array_keys( (array)$post );

				$type = '';
				$value = '';
				switch( $field['type'] ) {
					case 'phone':
					case 'email':
					case 'text':
					case 'url':
						if ( in_array( $name, $post_keys ) ) {
							$type = 'text';
							$value = $post->{$name};
						}
						break;
					case 'html':
					case 'textarea':
						if ( in_array( $name, $post_keys ) ) {
							$type = 'textarea';
							$value = $post->{$name};
						}
						break;
					case 'address':
						$address_fields = array( 'street', 'country', 'region', 'city', 'zip', 'latitude', 'longitude', 'neighbourhood' );
						foreach ( $address_fields as $address_field ) {
							if ( ! apply_filters( 'geodir_event_fill_business_skip_address_field', false, $address_field, $post ) ) {
								if ( in_array( $address_field, $post_keys ) ) {
									$data = array( 'key' => 'text', 'value' => $post->{$address_field} );
									$fields[ $address_field ] = apply_filters( 'geodir_event_fill_business_address_field_data', $data, $address_field, $post );
								}
							}
						}
						$type = '';
						break;
					case 'multiselect':
						if ( in_array( $name, $post_keys ) ) {
							$type = $field['type'];
							$value = $post->{$name};
							$value = $value != '' ? explode( ",", $value ) : array();
						}
					break;
					default:
						if ( in_array( $name, $post_keys ) ) {
							$type = $field['type'];
							$value = maybe_unserialize( $post->{$name} );
						}
						break;
				}
				if ( $type ) {
					$data = array( 'key' => $type, 'value' => $value );
					$fields[ $name ] = apply_filters( 'geodir_event_fill_business_field_data', $data, $field, $post );
				}
			}
			//geodir_error_log( $fields, 'fields', __FILE__, __LINE__ );

			$data = array( 'fields' => $fields );
			wp_send_json_success( $data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}