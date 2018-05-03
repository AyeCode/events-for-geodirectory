<?php
/**
 * GeoDirectory Events widget functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

 /**
 * Register Widgets.
 *
 * @since 2.0.0
 */
function goedir_event_register_widgets() {
    if ( get_option( 'geodir_event_version' )) {
        register_widget( 'GeoDir_Event_Widget_Calendar' );
        register_widget( 'GeoDir_Event_Widget_AYI' );

		// Non Widgets
        new GeoDir_Event_Widget_Linked_Events();
    }
}
add_action( 'widgets_init', 'goedir_event_register_widgets' );