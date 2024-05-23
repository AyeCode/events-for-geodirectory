<?php
/**
 * Deprecated functions
 *
 * Functions that no longer in use after v2.0.0.
 *
 * @author   AyeCode Ltd
 * @package  GeoDir_Event_Manager\Functions
 * @version  2.0.0
 */

/**
 * Update the terms reviews count for upcoming events.
 * @deprecated
 */
function geodir_event_review_count_force_update(){
    _deprecated_function( 'geodir_event_review_count_force_update', '2.0.0' );
}

/**
 * Update the reviews count for upcoming events for current location.
 * @deprecated
 */
function geodir_event_location_update_count_reviews(){
    _deprecated_function( 'geodir_event_location_update_count_reviews', '2.0.0' );
}

/**
 * Filter the GROUP BY clause of the event listings query.
 * @deprecated
 */
function geodir_event_loop_filter( $query ){
    _deprecated_function( 'geodir_event_loop_filter', '2.0.0', 'GeoDir_Event_Query::__construct()' );
}

/**
 * Get widget event posts.
 * @deprecated
 */
function geodir_event_get_widget_events( $query_args, $count_only = false ) {
	_deprecated_function( 'geodir_event_get_widget_events', '2.0.0' );
}

/**
 *
 * @since 1.4.6 Same day events should just show date and from - to time.
 * @deprecated
 */
function geodir_event_show_schedule_date() {
	_deprecated_function( 'geodir_event_show_schedule_date', '2.0.0', 'GeoDir_Event_Schedules::get_schedules_html()' );
}

/**
 * Displays the event dates in the meta info in the map info window.
 * @deprecated
 */
function geodir_event_infowindow_meta_event_dates( $post_id, $post, $preview ) {
	_deprecated_function( 'geodir_event_infowindow_meta_event_dates', '2.0.0' );
}

/**
 * Filter reviews sql query fro upcoming events.
 * @deprecated
 */
function geodir_event_count_reviews_by_term_sql( $sql, $term_id, $taxonomy, $post_type ) {
	_deprecated_function( 'geodir_event_count_reviews_by_term_sql', '2.0.0' );
}

/**
 * Filter the past events count in terms array results.
 * @deprecated
 */
function geodir_event_get_terms( $terms, $taxonomies, $args ) {
	_deprecated_function( 'geodir_event_get_terms', '2.0.0' );
}

/**
 * Check schedule exists or not.
 * @deprecated
 */
function geodir_event_schedule_exist( $date, $event_id ) {
	_deprecated_function( 'geodir_event_schedule_exist', '2.0.0', 'GeoDir_Event_Schedules::has_schedule()' );
}

/**
 * @deprecated
 */
function geodir_event_function_related_post_ids_where( $where ) {
	_deprecated_function( 'geodir_event_function_related_post_ids_where', '2.0.0' );
}

/**
 * @deprecated
 */
function geodir_get_detail_page_related_events( $request ) {
	_deprecated_function( 'geodir_get_detail_page_related_events', '2.0.0' );
}

/**
 * Get the event schedule dates array.
 * @deprecated
 */
function geodir_event_get_schedule_dates($post, $preview = false, $event_type = 'upcoming') {
	_deprecated_function( 'geodir_event_get_schedule_dates', '2.0.0', 'GeoDir_Event_Schedules::get_schedules()' );
}

/**
 * @deprecated
 */
function geodir_event_date_occurrences( $type = 'year', $start_date = '', $end_date = '', $interval = 1, $limit = '', $repeat_end = '', $repeat_days = array(), $repeat_weeks = array() ) {
	_deprecated_function( 'geodir_event_date_occurrences', '2.0.0' );
}

/**
 * @deprecated
 */
function geodir_event_get_my_listings( $post_type = 'all', $search = '', $limit = 5 ) {
	_deprecated_function( 'geodir_event_get_my_listings', '2.0.0' );
}