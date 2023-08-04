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

global $wpdb, $plugin_prefix;

$geodir_settings = get_option( 'geodir_settings' );

// Clear schedules
wp_clear_scheduled_hook( 'geodir_event_schedule_handle_past_events' );
 
if ( ( ! empty( $geodir_settings ) && ( ! empty( $geodir_settings['admin_uninstall'] ) || ! empty( $geodir_settings['uninstall_geodir_event_manager'] ) ) ) || ( defined( 'GEODIR_UNINSTALL_GEODIR_EVENT_MANAGER' ) && true === GEODIR_UNINSTALL_GEODIR_EVENT_MANAGER ) ) {
	if ( empty( $plugin_prefix ) ) {
		$plugin_prefix = $wpdb->prefix . 'geodir_';
	}

	$event_detail_table = $plugin_prefix . 'gd_event_detail';
	$event_schedules_table = defined( 'GEODIR_EVENT_SCHEDULES_TABLE' ) ? GEODIR_EVENT_SCHEDULES_TABLE : $plugin_prefix . 'event_schedule';
	$attachments_table = defined( 'GEODIR_ATTACHMENT_TABLE' ) ? GEODIR_ATTACHMENT_TABLE : $plugin_prefix . 'attachments';
	$custom_fields_table = defined( 'GEODIR_CUSTOM_FIELDS_TABLE' ) ? GEODIR_CUSTOM_FIELDS_TABLE : $plugin_prefix . 'custom_fields';
	$custom_sort_fields_table = defined( 'GEODIR_CUSTOM_SORT_FIELDS_TABLE' ) ? GEODIR_CUSTOM_SORT_FIELDS_TABLE : $plugin_prefix . 'custom_sort_fields';
	$reviews_table = defined( 'GEODIR_REVIEW_TABLE' ) ? GEODIR_REVIEW_TABLE : $plugin_prefix . 'post_review';
	$search_fields_table = defined( 'GEODIR_ADVANCE_SEARCH_TABLE' ) ? GEODIR_ADVANCE_SEARCH_TABLE : $plugin_prefix . 'custom_advance_search_fields';
	$tabs_layout_table = defined( 'GEODIR_TABS_LAYOUT_TABLE' ) ? GEODIR_TABS_LAYOUT_TABLE : $plugin_prefix . 'tabs_layout';
	$pricing_packages_table = defined( 'GEODIR_PRICING_PACKAGES_TABLE' ) ? GEODIR_PRICING_PACKAGES_TABLE : $plugin_prefix . 'price';
	$pricing_package_meta_table = defined( 'GEODIR_PRICING_PACKAGE_META_TABLE' ) ? GEODIR_PRICING_PACKAGE_META_TABLE : $plugin_prefix . 'pricemeta';
	$pricing_post_packages_table = defined( 'GEODIR_PRICING_POST_PACKAGES_TABLE' ) ? GEODIR_PRICING_POST_PACKAGES_TABLE : $plugin_prefix . 'post_packages';
	$link_posts_table = defined( 'GEODIR_CP_LINK_POSTS' ) ? GEODIR_CP_LINK_POSTS : $plugin_prefix . 'cp_link_posts';

	// Delete table
	$wpdb->query( "DROP TABLE IF EXISTS `{$event_detail_table}`" );
	$wpdb->query( "DROP TABLE IF EXISTS `{$event_schedules_table}`" );

	if ( ! empty( $geodir_settings ) ) {
		$post_type = 'gd_event';

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$search_fields_table}'" ) ) {
			$search_fields_table = '';
		}
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$pricing_packages_table}'" ) ) {
			$pricing_packages_table = '';
		}
		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$link_posts_table}'" ) ) {
			$link_posts_table = '';
		}

		$save_settings = $geodir_settings;

		$remove_options = array(
			'event_default_filter',
			'event_map_filter',
			'event_disable_recurring',
			'event_hide_past_dates',
			'event_remove_title_date',
			'event_map_popup_count',
			'event_map_popup_dates',
			'event_field_date_format',
			'event_display_date_format',
			'event_use_custom_format',
			'event_custom_date_format',
			'event_max_schedules',
			'event_include_ongoing',
			'seopress_recurring_schedules',
			'uninstall_geodir_event_manager',
		);

		$post_types = ! empty( $geodir_settings['post_types'] ) ? $geodir_settings['post_types'] : array();
		$taxonomies = ! empty( $geodir_settings['taxonomies'] ) ? $geodir_settings['taxonomies'] : array();
		$post_types_disabled = ! empty( $geodir_settings['post_types_disabled'] ) ? $geodir_settings['post_types_disabled'] : array();
		$taxonomies_disabled = ! empty( $geodir_settings['taxonomies_disabled'] ) ? $geodir_settings['taxonomies_disabled'] : array();

		$remove_options[] = $post_type . '_dummy_data_type';

		if ( ! empty( $post_types ) && isset( $post_types[ $post_type ] ) ) {
			unset( $save_settings['post_types'][ $post_type ] );
		}

		if ( ! empty( $taxonomies ) && isset( $taxonomies[ $post_type . 'category' ] ) ) {
			unset( $save_settings['taxonomies'][ $post_type . 'category' ] );
		}

		if ( ! empty( $taxonomies ) && isset( $taxonomies[ $post_type . '_tags' ] ) ) {
			unset( $save_settings['taxonomies'][ $post_type . '_tags' ] );
		}

		// Remove disabled post type
		if ( ! empty( $post_types_disabled ) && isset( $post_types_disabled[ $post_type ] ) ) {
			unset( $save_settings['post_types_disabled'][ $post_type ] );
		}

		if ( ! empty( $taxonomies_disabled ) && isset( $taxonomies_disabled[ $post_type . 'category' ] ) ) {
			unset( $save_settings['taxonomies_disabled'][ $post_type . 'category' ] );
		}

		if ( ! empty( $taxonomies_disabled ) && isset( $taxonomies_disabled[ $post_type . '_tags' ] ) ) {
			unset( $save_settings['taxonomies_disabled'][ $post_type . '_tags' ] );
		}

		// Delete post table
		$wpdb->query( "DROP TABLE IF EXISTS {$plugin_prefix}{$post_type}_detail" );

		// Delete posts
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_type = %s", array( $post_type ) ) );

		// Delete post menu
		$wpdb->query( "DELETE posts FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id WHERE posts.post_type = 'nav_menu_item' AND meta.meta_key = '_menu_item_object' AND meta.meta_value = '{$post_type}'" );
		$wpdb->query( "DELETE posts FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id WHERE posts.post_type= 'nav_menu_item' AND meta.meta_key = '_menu_item_url' AND meta.meta_value LIKE '%listing_type={$post_type}%'" );

		// Delete term taxonomies
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s OR taxonomy = %s", array( $post_type . 'category', $post_type . '_tags' ) ) );

		delete_option( $post_type . 'category_installed' );

		// Delete orphan attachment.
		$wpdb->query( "DELETE post1 FROM {$wpdb->posts} post1 LEFT JOIN {$wpdb->posts} post2 ON post1.post_parent = post2.ID WHERE post1.post_parent > 0 AND post1.post_type = 'attachment' AND post2.ID IS NULL" );

		// Delete orphan post meta
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL" );

		// Delete orphan relationships
		$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL" );

		// Delete orphan terms
		$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL" );

		// Delete orphan term meta
		$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL" );
		
		// Delete orphan comments
		$wpdb->query( "DELETE comments FROM {$wpdb->comments} AS comments LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = comments.comment_post_ID WHERE posts.ID IS NULL" );
		$wpdb->query( "DELETE meta FROM {$wpdb->commentmeta} meta LEFT JOIN {$wpdb->comments} comments ON comments.comment_ID = meta.comment_id WHERE comments.comment_ID IS NULL" );

		// Delete orphan post attachments
		$wpdb->query( "DELETE attachments FROM {$attachments_table} attachments LEFT JOIN {$wpdb->posts} posts ON posts.ID = attachments.post_id WHERE posts.ID IS NULL" );

		// Delete custom fields
		$wpdb->query( "DELETE FROM {$custom_fields_table} WHERE post_type = '{$post_type}' OR htmlvar_name = '{$post_type}'" );

		// Delete custom sort fields
		$wpdb->query( "DELETE FROM {$custom_sort_fields_table} WHERE post_type = '{$post_type}' OR htmlvar_name = '{$post_type}'" );

		// Delete search fields
		if ( $search_fields_table ) {
			$wpdb->query( "DELETE FROM {$search_fields_table} WHERE post_type = '{$post_type}' OR htmlvar_name = '{$post_type}'" );
		}

		// Delete tabs layout
		$wpdb->query( "DELETE FROM {$tabs_layout_table} WHERE post_type = '{$post_type}' OR tab_key = '{$post_type}'" );

		// Delete price packages
		if ( $pricing_packages_table ) {
			$wpdb->query( "DELETE FROM {$pricing_packages_table} WHERE post_type = '{$post_type}'" );

			// Delete orphan price metas
			$wpdb->query( "DELETE pricemeta FROM {$pricing_package_meta_table} pricemeta LEFT JOIN {$pricing_packages_table} price ON price.id = pricemeta.package_id WHERE price.id IS NULL" );

			// Delete orphan price post packages
			$wpdb->query( "DELETE post_packages FROM {$pricing_post_packages_table} post_packages LEFT JOIN {$pricing_packages_table} price ON price.id = post_packages.package_id WHERE price.id IS NULL" );
		}

		// Delete link posts
		if ( $link_posts_table ) {
			$wpdb->query( "DELETE FROM {$link_posts_table} WHERE post_type = '{$post_type}'" );
		}
			
		// Delete orphan post reviews
		$wpdb->query( "DELETE reviews FROM {$reviews_table} reviews LEFT JOIN {$wpdb->posts} posts ON posts.ID = reviews.post_id WHERE posts.ID IS NULL" );

		foreach ( $remove_options as $option ) {
			if ( isset( $save_settings[ $option ] ) ) {
				unset( $save_settings[ $option ] );
			}
		}

		// Update options.
		update_option( 'geodir_settings', $save_settings );
	}

	// Delete options
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN('widget_geodir_event_ayi', 'widget_geodir_event_calendar', 'gd_eventcategory_children');" );

	// Delete core options
	delete_option( 'geodir_event_version' );
	delete_option( 'geodir_event_db_version' );
	delete_option( 'geodirevents_db_version' );
	
	// Clear any cached data that has been removed.
	wp_cache_flush();
}