<?php
/**
 * Adds events to active plugin list.
 *
 * @since 1.0.0
 * @package GeoDirectory_Events
 *
 * @param string $plugin Plugin basename.
 */

function geodir_event_admin_params() {
	$params = array(
    );

    return apply_filters( 'geodir_event_admin_params', $params );
}

/**
 * Deactivate gdevent
 */
function geodir_event_inactive_posttype() {
	global $wpdb, $plugin_prefix;
	
	update_option( "gdevents_installed", 0 );
	
	$posttype = 'gd_event';
	
	$geodir_taxonomies = geodir_get_option('geodir_taxonomies');
	
	if (array_key_exists($posttype.'category', $geodir_taxonomies))
	{
		unset($geodir_taxonomies[$posttype.'category']);
		geodir_update_option( 'geodir_taxonomies', $geodir_taxonomies );
	}
	
	if (array_key_exists($posttype.'_tags', $geodir_taxonomies))
	{
		unset($geodir_taxonomies[$posttype.'_tags']);
		geodir_update_option( 'geodir_taxonomies', $geodir_taxonomies );
	}
	
	
	$geodir_post_types = geodir_get_option( 'geodir_post_types' );
	
	if (array_key_exists($posttype, $geodir_post_types))
	{
		unset($geodir_post_types[$posttype]);
		geodir_update_option( 'geodir_post_types', $geodir_post_types );
	}
	 
	//UPDATE SHOW POST TYPES NAVIGATION OPTIONS 
	
	$get_posttype_settings_options = array('geodir_add_posttype_in_listing_nav','geodir_allow_posttype_frontend','geodir_add_listing_link_add_listing_nav','geodir_add_listing_link_user_dashboard','geodir_listing_link_user_dashboard');
	
	foreach($get_posttype_settings_options as $get_posttype_settings_options_obj)
	{
		$geodir_post_types_listing = geodir_get_option( $get_posttype_settings_options_obj );
		
		if (in_array($posttype, $geodir_post_types_listing))
		{
			$geodir_update_post_type_nav = array_diff($geodir_post_types_listing, array($posttype));
			geodir_update_option( $get_posttype_settings_options_obj, $geodir_update_post_type_nav );
		}
	}
}
// @todo
function geodir_event_deactivation() {
	geodir_event_inactive_posttype();
	
	delete_option( 'geodir_event_recurring_feature');
	delete_option( 'gdevents_installed');
}

/**
 * Replace schema types for even categories.
 *
 * @since 1.4.5
 * @param $schemas
 * @return array
 */
function geodir_event_filter_schemas( $schemas ) {
	if ( isset( $_REQUEST['taxonomy'] ) && $_REQUEST['taxonomy'] == 'gd_eventcategory' ) {
		$schemas = geodir_event_get_schema_types();
	}
	return $schemas;
}

function geodir_event_custom_sort_options( $fields, $post_type ) {
	if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
		$fields['event_dates'] = array(
			'post_type'      => $post_type,
			'data_type'      => '',
			'field_type'     => 'datetime',
			'frontend_title' => __( 'Event date', 'geodirevents' ),
			'htmlvar_name'   => 'event_dates',
			'field_icon'     => 'fas fa-calendar-alt',
			'description'    => __( 'Sort by event date', 'geodirevents' )
		);
	}

	return $fields;
}

/**
 * Add the plugin to uninstall settings.
 *
 * @since 2.0.0
 *
 * @return array $settings the settings array.
 * @return array The modified settings.
 */
function geodir_event_uninstall_settings( $settings ) {
    array_pop( $settings );

	$settings[] = array(
		'name'     => __( 'Events', 'geodirevents' ),
		'desc'     => __( 'Check this box if you would like to completely remove all of its data when Events is deleted.', 'geodirevents' ),
		'id'       => 'uninstall_geodir_event_manager',
		'type'     => 'checkbox',
	);
	$settings[] = array( 
		'type' => 'sectionend',
		'id' => 'uninstall_options'
	);

    return $settings;
}