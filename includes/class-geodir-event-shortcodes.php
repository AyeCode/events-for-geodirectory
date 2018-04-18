<?php
/**
 * Plugin shortcodes class
 *
 *
 * @link       https://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    GeoDir_Event_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_Shortcodes class
 *
 * @class       GeoDir_Event_Shortcodes
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 */
class GeoDir_Event_Shortcodes {

    /**
     * Init shortcodes.
     */
    public static function init() {
        $shortcodes = array(
        );

        foreach ( $shortcodes as $shortcode => $function ) {
            add_shortcode( apply_filters( 'geodir_event_shortcode_tag_' . $shortcode, $shortcode ), $function );
        }
    }
}
