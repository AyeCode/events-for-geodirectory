<?php 

/**
 * Geo Directory Event Database Install *
 * 
 * Plugin install database tables
 *
 * @author 		Vikas Sharma
 * @category 	Admin
 * @package 	GeoDirectory Events
 *
 */


function geodir_event_tables_install() {
	
	global $wpdb;

	$wpdb->hide_errors();

    $collate = '';
    if($wpdb->has_cap( 'collation' )) {
        if(!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if(!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
    }

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    /*
     * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
     * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
     * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
     */
    $max_index_length = 191;

    // Check schedule_id exists and make it auto increment if it does not so we can use dbDelta in the future
    if(@$wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix."geodir_event_schedule'")>0){geodir_add_column_if_not_exist($wpdb->prefix."geodir_event_schedule", "schedule_id", "int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL");}


	$event_detail = "CREATE TABLE ".GEODIR_EVENT_DETAIL_TABLE." (
					post_id int(11) NOT NULL,
					post_title text NULL DEFAULT NULL,
					post_status varchar(20) NULL DEFAULT NULL,
					default_category INT NULL DEFAULT NULL,
					post_tags text NULL DEFAULT NULL,
					geodir_link_business varchar(10) NULL DEFAULT NULL,
					post_location_id int(11) NOT NULL,
					marker_json text NULL DEFAULT NULL,
					claimed ENUM( '1', '0' ) NULL DEFAULT '0',
					businesses ENUM( '1', '0' ) NULL DEFAULT '0',
					is_featured ENUM( '1', '0' ) NULL DEFAULT '0',
					featured_image VARCHAR( 254 ) NULL DEFAULT NULL,
					paid_amount DOUBLE NOT NULL DEFAULT '0',
					package_id INT(11) NOT NULL DEFAULT '1',
					alive_days INT(11) NOT NULL DEFAULT '0',
					paymentmethod varchar(30) NULL DEFAULT NULL,
					expire_date VARCHAR( 25 ) NULL DEFAULT NULL,
					is_recurring TINYINT( 1 ) NOT NULL DEFAULT '0',
					recurring_dates TEXT NOT NULL,
					event_reg_desc text NULL DEFAULT NULL,
					event_reg_fees varchar(200) NULL DEFAULT NULL,
					submit_time varchar(15) NULL DEFAULT NULL,
					submit_ip varchar(254) NULL DEFAULT NULL,
					overall_rating float(11) DEFAULT NULL,
					rating_count INT(11) DEFAULT '0',
					rsvp_count INT(11) DEFAULT '0',
					post_locations VARCHAR( 254 ) NULL DEFAULT NULL,
					post_latitude varchar(20) NULL,
					post_longitude varchar(20) NULL,
					post_dummy ENUM( '1', '0' ) NULL DEFAULT '0',
					PRIMARY KEY  (post_id),
						KEY post_locations (post_locations($max_index_length)),
						KEY is_featured (is_featured)
					) $collate ";


    $event_detail = apply_filters('geodir_before_event_detail_table_create', $event_detail);
    dbDelta($event_detail);
	
	do_action('geodir_after_custom_detail_table_create', 'gd_event', GEODIR_EVENT_DETAIL_TABLE);

    $event_schedule = "CREATE TABLE ".GEODIR_EVENT_SCHEDULES_TABLE." (
        schedule_id int(11) AUTO_INCREMENT NOT NULL,
		event_id int(11) NOT NULL,
		event_date datetime NOT NULL,
		event_enddate DATE NOT NULL,
		event_starttime time,
		event_endtime time,
		all_day TINYINT( 1 ) NOT NULL DEFAULT '0',
		recurring TINYINT( 1 ) NOT NULL DEFAULT '0',
		PRIMARY KEY  (schedule_id)
		) $collate ";

    $event_schedule = apply_filters('geodir_before_event_schedule_table_create', $event_schedule);
    dbDelta($event_schedule);
	
	geodir_update_option( 'geodir_event_recurring_feature', '1' );

}	
		

function geodir_event_create_default_fields(){
	
	$package_info = array() ;
	$package_info = geodir_post_package_info($package_info , '', 'gd_event');
	$package_id = $package_info->pid;

	$fields = geodir_default_custom_fields('gd_event',$package_id);

				
	foreach($fields as $field_index => $field )
	{ 
		geodir_custom_field_save( $field ); 
	}							
}