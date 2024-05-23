<?php
/**
 * Upgrade related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory Event Manager
 * @global object $wpdb WordPress Database object.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $wpdb;

if ( get_option( 'geodir_event_db_version' ) != GEODIR_EVENT_VERSION ) {
    /**
     * Include custom database table related functions.
     *
     * @since 1.0.0
     * @package GeoDir_Event_Manager
     */
    add_action( 'plugins_loaded', 'geodir_event_upgrade_all', 10 );

    // Upgrade old options to new options before loading the rest GD options.
    if ( GEODIR_EVENT_VERSION <= '2.0.0' ) {
        add_action( 'init', 'geodir_event_upgrade_200' );
    }
}

/**
 * Handles upgrade for all GeoDirectory Event Manager versions.
 *
 * @since 1.0.0
 * @package GeoDir_Event_Manager
 */
function geodir_event_upgrade_all() {
}

/**
 * Handles upgrade for all geodirectory versions.
 *
 * @since 2.0.0
 * @package GeoDir_Event_Manager
 */
function geodir_event_upgrade_200() {
}