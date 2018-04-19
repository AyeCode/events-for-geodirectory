<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    GeoDir_Event_Manager
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
