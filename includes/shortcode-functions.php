<?php
/**
 * GeoDirectory Events shortcode functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

add_shortcode( 'gd_event_listing', 'geodir_sc_event_listing' );
function geodir_sc_event_listing( $atts ) {
	ob_start();
	$defaults = array(
		'post_type'           => 'gd_event',
		'category'            => '0',
		'post_number'         => 5,
		'layout'              => 'gridview_onehalf',
		'add_location_filter' => 1,
		'listing_width'       => '',
		'list_sort'           => 'latest',
		'list_filter'         => 'all',
		'character_count'     => 20,
		'show_featured_only'  => '0',
        'show_special_only'   => '0',
        'with_pics_only'      => '0',
        'with_videos_only' 	  => '0',
		'title'               => '',
		'before_widget'		  => '',
		'after_widget'		  => '',
		'before_title'		  => '<h3 class="widget-title">',
		'after_title'		  => '</h3>'
	);

	$params = shortcode_atts( $defaults, $atts );

	/**
	 * Begin validating the params
	 */

	// Validate the category/ies chosen
	$params['category'] = gdsc_manage_category_choice( $params['post_type'], $params['category'] );

	// Post_number needs to be a positive integer
	$params['post_number'] = absint( $params['post_number'] );
	if ( 0 == $params['post_number'] ) {
		$params['post_number'] = 1;
	}

	// Validate our layout choice
	// Outside of the norm, I added some more simple terms to match the existing
	// So now I just run the switch to set it properly.
	$params['layout'] = gdsc_validate_layout_choice( $params['layout'] );

	// Validate Listing width, used in the template widget-listing-listview.php
	// The context is in width=$listing_width% - So we need a positive number between 0 & 100
	$params['listing_width'] = gdsc_validate_listing_width( $params['listing_width'] );

	// Validate our sorting choice
	$params['list_sort'] = $params['list_sort'] == 'upcoming' ? $params['list_sort'] : gdsc_validate_sort_choice( $params['list_sort'] );

	// Validate our sorting choice
	$params['list_filter'] = gdsc_validate_list_filter_choice( $params['list_filter'] );

	// Validate character_count
	$params['character_count'] = absint( $params['character_count'] );
	if ( 20 > $params['character_count'] ) {
		$params['character_count'] = 20;
	}
	
	$params['show_featured_only'] = gdsc_to_bool_val($params['show_featured_only']);
    $params['show_special_only'] = gdsc_to_bool_val($params['show_special_only']);
    $params['with_pics_only'] = gdsc_to_bool_val($params['with_pics_only']);
    $params['with_videos_only'] = gdsc_to_bool_val($params['with_videos_only']);

	$params['title'] = sanitize_text_field( $params['title'] );
	if ( empty( $params['title'] ) || $params['title'] == 'All' ) {
		$params['title'] .= ' ' . get_post_type_plural_label( $params['post_type'] );
	}

	geodir_event_postview_output($params, $params);

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'gd_event_calendar', 'geodir_sc_event_calendar' );
function geodir_sc_event_calendar( $atts ) {
	ob_start();
	$defaults = array(
		'title' => '',
		'day'   => '',
		'before_widget'		  => '',
		'after_widget'		  => '',
		'before_title'		  => '<h3 class="widget-title">',
		'after_title'		  => '</h3>',
		'add_location_filter' => 0
	);

	$params = shortcode_atts( $defaults, $atts );

	GeoDir_Event_Calendar::display_calendar($params, $params);

	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'gd_related_events', 'geodir_sc_related_events' );
function geodir_sc_related_events( $atts ) {
	ob_start();
	$defaults = array(
		'post_number'         => 5,
		'layout'              => 'gridview_onehalf',
		'event_type'          => 'all',
		'add_location_filter' => 0,
		'listing_width'       => '',
		'list_sort'           => 'latest',
		'character_count'     => '20',
	);

	$params = shortcode_atts( $defaults, $atts );

	/**
	 * Begin validating params
	 */

	// Validate that post_number is a number and is 1 or higher
	$params['post_number'] = absint( $params['post_number'] );
	if ( 0 === $params['post_number'] ) {
		$params['post_number'] = 1;
	}

	// Validate layout selection
	$params['layout'] = gdsc_validate_layout_choice( $params['layout'] );

	// Validate event type selection
	$params['event_type'] = gdsc_validate_event_type( $params['event_type'] );

	// Validate listing_width
	$params['listing_width'] = gdsc_validate_listing_width( $params['listing_width'] );

	// Validate sorting option
	$params['list_sort'] = $params['list_sort'] == 'upcoming' ? $params['list_sort'] : gdsc_validate_sort_choice( $params['list_sort'] );

	// Validate character_count
	$params['character_count'] = absint( $params['character_count'] );
	if ( 20 > $params['character_count'] ) {
		$params['character_count'] = 20;
	}

	/**
	 * End validating params
	 */

	global $post;
	$post_id   = '';
	$post_type = '';

	if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
		$post      = geodir_get_post_info( $_REQUEST['pid'] );
		$post_type = $post->post_type;
		$post_id   = $_REQUEST['pid'];
	} elseif ( isset( $post->post_type ) && $post->post_type != '' ) {
		$post_type = $post->post_type;
		$post_id   = $post->ID;
	}

	$all_postypes = geodir_get_posttypes();

	if ( ! ( in_array( $post_type, $all_postypes ) ) ) {
		return false;
	}

	if ( $post_type == 'gd_place' && $post_id != '' ) {
		$query_args = array(

			'gd_event_type' 	=> $params['event_type'],
			'event_related_id'  => $post_id,
			'posts_per_page'    => $params['post_number'],
			'is_geodir_loop'    => true,
			'gd_location'       => $params['add_location_filter'],
			'post_type'         => 'gd_event',
			'order_by'          => $params['list_sort'],
			'excerpt_length'    => $params['character_count'],

		);

		geodir_get_post_feature_events( $query_args, $params['layout'] );
		geodir_get_post_past_events( $query_args, $params['layout'] );

	}
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function gdsc_validate_event_type( $event_type ) {
	$options = array(
		'all',
		'feature',
		'past',
		'future',
	);

	if ( ! ( in_array( $event_type, $options ) ) ) {
		$event_type = 'feature';
	}
}