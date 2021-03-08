<?php
/**
 * Fired during plugin activation/deactivation.
 *
 * @since      2.1.1.1
 *
 * @package    Events_For_Geodirectory
 * @subpackage Events_For_Geodirectory/includes
 */

/**
 * Fired during plugin activation/deactivation.
 *
 * This class defines all code necessary to run during the plugin's activation/deactivation.
 *
 * @since      2.1.1.1
 * @package    Events_For_Geodirectory
 * @subpackage Events_For_Geodirectory/includes
 * @author     AyeCode Ltd
 */
class GeoDir_Event_Activator {

	/**
	 * Fired during plugin activation.
	 *
	 * @since    2.1.1.1
	 *
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
	 *                             or just the current site. Multisite only. Default false.
	 */
	public static function activate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids'
				)
			);

			if ( ! empty( $site_ids ) ) {
				foreach ( $site_ids  as $blog_id ) {
					switch_to_blog( $blog_id );

					self::on_activate();

					restore_current_blog();
				}
			}
		} else {
			self::on_activate();
		}

		do_action( 'geodir_event_plugin_activated', $network_wide );
	}

	/**
	 * Fired during plugin activation.
	 *
	 * @since    2.1.1.1
	 */
	public static function on_activate() {
		// Enable event post type.
		self::enable_post_type();

		do_action( 'geodir_event_plugin_on_activate' );
	}

		/**
	 * Enable event post type of plugin activate.
	 *
	 * @since    2.1.1.1
	 */
	public static function enable_post_type() {
		global $wpdb;

		$geodir_settings = get_option( 'geodir_settings' );
		if ( empty( $geodir_settings ) ) {
			return;
		}

		$post_type = 'gd_event';
		$plugin_prefix = $wpdb->prefix . 'geodir_';
		$post_types = ! empty( $geodir_settings['post_types'] ) ? $geodir_settings['post_types'] : array();
		$taxonomies = ! empty( $geodir_settings['taxonomies'] ) ? $geodir_settings['taxonomies'] : array();
		$post_types_disabled = ! empty( $geodir_settings['post_types_disabled'] ) ? $geodir_settings['post_types_disabled'] : array();
		$taxonomies_disabled = ! empty( $geodir_settings['taxonomies_disabled'] ) ? $geodir_settings['taxonomies_disabled'] : array();

		$save_settings = $geodir_settings;

		if ( ! empty( $post_types_disabled ) && ! empty( $post_types_disabled[ $post_type ] ) ) {
			$post_types[ $post_type ] = $post_types_disabled[ $post_type ];

			unset( $save_settings['post_types_disabled'][ $post_type ] );
		}

		if ( ! empty( $taxonomies_disabled ) && ! empty( $taxonomies_disabled[ $post_type . 'category' ] ) ) {
			$taxonomies[ $post_type . 'category' ] = $taxonomies_disabled[ $post_type . 'category' ];

			unset( $save_settings['taxonomies_disabled'][ $post_type . 'category' ] );
		}

		if ( ! empty( $taxonomies_disabled ) && ! empty( $taxonomies_disabled[ $post_type . '_tags' ] ) ) {
			$taxonomies[ $post_type . '_tags' ] = $taxonomies_disabled[ $post_type . '_tags' ];

			unset( $save_settings['taxonomies_disabled'][ $post_type . '_tags' ] );
		}

		$save_settings['post_types'] = $post_types;
		$save_settings['taxonomies'] = $taxonomies;

		// Update settings.
		update_option( 'geodir_settings', $save_settings );
	}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since    2.1.1.1
	 *
	 * @param bool   $network_wide Whether to enable the plugin for all sites in the network
	 *                             or just the current site. Multisite only. Default false.
	 */
	public static function deactivate( $network_wide = false ) {
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites(
				array(
					'fields' => 'ids'
				)
			);

			if ( ! empty( $site_ids ) ) {
				foreach ( $site_ids  as $blog_id ) {
					switch_to_blog( $blog_id );

					self::on_deactivate();

					restore_current_blog();
				}
			}
		} else {
			self::on_deactivate();
		}

		do_action( 'geodir_event_plugin_deactivated', $network_wide );
	}

	/**
	 * Fired during plugin deactivation.
	 *
	 * @since    2.1.1.1
	 */
	public static function on_deactivate() {
		// Disable event post type.
		self::disable_post_type();

		do_action( 'geodir_event_plugin_on_deactivate' );
	}

	/**
	 * Disable event post type on plugin deactivation.
	 *
	 * @since    2.1.1.1
	 
	 * @global object $wpdb WordPress Database object.
	 */
	public static function disable_post_type() {
		global $wpdb;

		$geodir_settings = get_option( 'geodir_settings' );
		if ( empty( $geodir_settings ) ) {
			return;
		}

		$post_type = 'gd_event';
		$plugin_prefix = $wpdb->prefix . 'geodir_';
		$post_types = ! empty( $geodir_settings['post_types'] ) ? $geodir_settings['post_types'] : array();
		$taxonomies = ! empty( $geodir_settings['taxonomies'] ) ? $geodir_settings['taxonomies'] : array();
		$post_types_disabled = ! empty( $geodir_settings['post_types_disabled'] ) ? $geodir_settings['post_types_disabled'] : array();
		$taxonomies_disabled = ! empty( $geodir_settings['taxonomies_disabled'] ) ? $geodir_settings['taxonomies_disabled'] : array();

		$save_settings = $geodir_settings;

		if ( ! empty( $post_types ) && isset( $post_types[ $post_type ] ) ) {
			$post_types_disabled[ $post_type ] = $post_types[ $post_type ];

			unset( $save_settings['post_types'][ $post_type ] );
		}

		if ( ! empty( $taxonomies ) && isset( $taxonomies[ $post_type . 'category' ] ) ) {
			$taxonomies_disabled[ $post_type . 'category' ] = $taxonomies[ $post_type . 'category' ];

			unset( $save_settings['taxonomies'][ $post_type . 'category' ] );
		}

		if ( ! empty( $taxonomies ) && isset( $taxonomies[ $post_type . '_tags' ] ) ) {
			$taxonomies_disabled[ $post_type . '_tags' ] = $taxonomies[ $post_type . '_tags' ];

			unset( $save_settings['taxonomies'][ $post_type . '_tags' ] );
		}

		$save_settings['post_types_disabled'] = $post_types_disabled;
		$save_settings['taxonomies_disabled'] = $taxonomies_disabled;

		// Update settings.
		update_option( 'geodir_settings', $save_settings );
	}
}
