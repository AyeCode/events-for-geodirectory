<?php

/**
 * Geo Directory Event Install *
 * 
 * Plugin install script which adds default pages, taxonomies, and database tables
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 *
 */

/**
 * Include core instalation files
 */
 
include_once('gdevents_db_install.php');

/**
 * Plugin activation function.
 *
 * @since 1.0.0
 * @package GeoDirectory_Events
 */
function geodir_events_activation() {
	if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
		if ( is_plugin_active( plugin_basename( GEODIR_EVENT_PLUGIN_FILE ) ) ) {
			deactivate_plugins( plugin_basename( GEODIR_EVENT_PLUGIN_FILE ) );
			
			if ( isset( $_GET[ 'activate' ] ) ) {
				unset( $_GET[ 'activate' ] );
			}
			
			add_action( 'admin_notices', 'geodir_event_PHP_version_notice' );
		}
		return;
	}
	
	if (get_option('geodir_installed')) {
		gdevents_install();
		update_option( "gdevents_installed", 1 );
		add_option('geodir_events_activation_redirect', 1);
	}
}

/**
 * Plugin install function.
 *
 * @since 1.0.0
 * @since 1.4.2 Should not loose previously saved settings when plugin is reactivated - CHANGED.
 * @package GeoDirectory_Events
 */
function gdevents_install() {
	global $gdevents_settings;
	
	geodir_event_tables_install();
	geodir_event_post_type();
	geodir_event_create_default_fields();
	
	$default_options = geodir_event_resave_settings( geodir_event_general_setting_options() );
	geodir_update_options( $default_options, true );
	update_option( "gdevents_db_version", GEODIR_EVENT_VERSION );
}

/**
 * Handle the plugin settings for plugin deactivate to activate.
 *
 * It manages the the settings without loosing previous settings saved when plugin
 * status changed from deactivate to activate.
 *
 * @since 1.4.2
 * @package GeoDirectory_Events
 *
 * @param array $settings The option settings array.
 * @return array The settings array.
 */
function geodir_event_resave_settings($settings = array()) {
    if (!empty($settings) && is_array($settings)) {
        $c = 0;
        
        foreach ($settings as $setting) {
            if (!empty($setting['id']) && false !== ($value = geodir_get_option($setting['id']))) {
                $settings[$c]['std'] = $value;
            }
            $c++;
        }
    }

    return $settings;
}

function geodir_event_post_type() {
	global $wpdb;
	
	$menu_icon  = file_exists(geodir_plugin_path() . '/geodirectory-assets/images/favicon.ico') ? geodir_plugin_url() . '/geodirectory-assets/images/favicon.ico' : geodir_event_plugin_url() . '/gdevents-assets/images/favicon.ico';
	
	/* Event taxonomy */
	
	if ( ! taxonomy_exists('gd_eventcategory') ){

		$gd_placecategory = array();
		$gd_placecategory['object_type']= 'gd_event';
		$gd_placecategory['listing_slug']= 'events';
		$gd_placecategory['args'] = array (
			'public' => true,
			'hierarchical'  => true,
			'rewrite' => array ('slug' =>'events', 'with_front' =>false, 'hierarchical' =>true),
			'query_var' => true,
			'labels' => array (
				'name'          => __( 'Event Categories', 'geodirevents' ),
				'singular_name' => __( 'Event Category', 'geodirevents' ),
				'search_items'  => __( 'Search Event Categories', 'geodirevents' ),
				'popular_items' => __( 'Popular Event Categories', 'geodirevents' ),
				'all_items'     => __( 'All Event Categories', 'geodirevents' ),
				'edit_item'     => __( 'Edit Event Category', 'geodirevents' ),
				'update_item'   => __( 'Update Event Category', 'geodirevents' ),
				'add_new_item'  => __( 'Add New Event Category', 'geodirevents' ),
				'new_item_name' => __( 'New Event Category', 'geodirevents' ),
				'add_or_remove_items' => __( 'Add or remove Event categories', 'geodirevents' ),
			),
		);

		$geodir_taxonomies = geodir_get_option('geodir_taxonomies');
		$geodir_taxonomies['gd_eventcategory'] = $gd_placecategory;
		geodir_update_option( 'geodir_taxonomies', $geodir_taxonomies );

		flush_rewrite_rules();
	}
	
	if ( ! taxonomy_exists('gd_event_tags') ){

		$gd_placetags = array();
		$gd_placetags['object_type']= 'gd_event';
		$gd_placetags['listing_slug']= 'events/tags';
		$gd_placetags['args'] = array (
			'public' => true,
			'hierarchical' => false,
			'rewrite' => array ( 'slug' => 'events/tags', 'with_front' => false, 'hierarchical' => false ),
			'query_var' => true,
			
			'labels' => array (
				'name'          => __( 'Event Tags', 'geodirevents' ),
				'singular_name' => __( 'Event Tag', 'geodirevents' ),
				'search_items'  => __( 'Search Event Tags', 'geodirevents' ),
				'popular_items' => __( 'Popular Event Tags', 'geodirevents' ),
				'all_items'     => __( 'All Event Tags', 'geodirevents' ),
				'edit_item'     => __( 'Edit Event Tag', 'geodirevents' ),
				'update_item'   => __( 'Update Event Tag', 'geodirevents' ),
				'add_new_item'  => __( 'Add New Event Tag', 'geodirevents' ),
				'new_item_name' => __( 'New Event Tag Name', 'geodirevents' ),
				'add_or_remove_items' => __( 'Add or remove Event tags', 'geodirevents' ),
				'choose_from_most_used' => __( 'Choose from the most used Event tags', 'geodirevents' ),
				'separate_items_with_commas' => __( 'Separate Event tags with commas', 'geodirevents' ),
				),
		);

		
		$geodir_taxonomies = geodir_get_option('geodir_taxonomies');
		$geodir_taxonomies['gd_event_tags'] = $gd_placetags;
		geodir_update_option( 'geodir_taxonomies', $geodir_taxonomies );

		flush_rewrite_rules();

	}

	/**
	 * Post Types
	 **/
	if ( ! post_type_exists('gd_event') ) {
		
		$labels = array (
		'name'          => __('Events', 'geodirevents'),
		'singular_name' => __('Event', 'geodirevents'),
		'add_new'       => __('Add New', 'geodirevents'),
		'add_new_item'  => __('Add New Event', 'geodirevents'),
		'edit_item'     => __('Edit Event', 'geodirevents'),
		'new_item'      => __('New Event', 'geodirevents'),
		'view_item'     => __('View Event', 'geodirevents'),
		'search_items'  => __('Search Events', 'geodirevents'),
		'not_found'     => __('No Event Found', 'geodirevents'),
		'not_found_in_trash' => __('No Event Found In Trash', 'geodirevents') );
		
		$place_default = array (
		'labels' => $labels,	
		'can_export' => true,
		'capability_type' => 'post',
		'description' => __('Event post type.', 'geodirevents'),
		'has_archive' => 'events',
		'hierarchical' => false,
		'map_meta_cap' => true,
		'menu_icon' => $menu_icon,
		'public' => true,
		'query_var' => true,
		'rewrite' => array ('slug' => 'events', 'with_front' => false, 'hierarchical' => true, 'feeds' => true),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments' ),
		'taxonomies' => array('gd_eventcategory','gd_event_tags') );
		
		//Update custom post types
		$geodir_post_types = geodir_get_option( 'geodir_post_types' );
		$geodir_post_types['gd_event'] = $place_default;
		geodir_update_option( 'geodir_post_types', $geodir_post_types );

		flush_rewrite_rules();
		
	}
	
	geodir_register_taxonomies();
	geodir_register_post_types();
	
	do_action( 'geodir_create_new_post_type', 'gd_event' );
	
}
