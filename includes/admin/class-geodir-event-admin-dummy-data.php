<?php
/**
 * GeoDirectory Events Admin Dummy Data
 *
 * @class    GeoDir_Event_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDir_Event_Manager/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_Admin_Dummy_Data class.
 */
class GeoDir_Event_Admin_Dummy_Data {

	/**
	 * Constructor.
	 */
	public function __construct() {

	}

	/**
	 * The types of dummy data available.
	 *
	 * @return array
	 */
	public static function dummy_data_types( $types, $post_type ) {
		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return $types;
		}

		$types = array(
			'standard_events' => array(
				'name'  => __( 'Default', 'geodirevents' ),
				'count' => 22
			),
			'recurring_events'   => array(
				'name'  => __( 'With Recurring Events', 'geodirevents' ),
				'count' => 22
			)
		);

		return $types;
	}

	public static function include_file( $post_type, $data_type, $type, $item_index ) {
		global $dummy_image_url, $dummy_categories, $dummy_custom_fields, $dummy_posts;

		if ( ! GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			return;
		}

		if ( $data_type == 'standard_events' ) {
			add_filter( 'geodir_extra_custom_fields', 'geodir_event_extra_custom_fields_' . $data_type, 10, 3 );

			/**
			 * Contains dummy post content.
			 *
			 * @since 2.0.0
			 * @package GeoDirectory
			 */
			include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/dummy-data/standard_events.php' );
		} elseif ( $data_type == 'recurring_events' ) {
			add_filter( 'geodir_extra_custom_fields', 'geodir_event_extra_custom_fields_' . $data_type, 10, 3 );

			/**
			 * Contains dummy property for sale post content.
			 *
			 * @since 2.0.0
			 * @package GeoDirectory
			 */
			include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/dummy-data/recurring_events.php' );
		}
	}

}

return new GeoDir_Event_Admin_Dummy_Data();