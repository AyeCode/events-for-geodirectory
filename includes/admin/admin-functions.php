<?php
/**
 * Adds events to active plugin list.
 *
 * @since 1.0.0
 * @package GeoDirectory_Events
 *
 * @param string $plugin Plugin basename.
 */
 // @todo
function geodir_event_plugin_activated( $plugin ) {
	if ( !get_option( 'geodir_installed' ) )  {
		$file = plugin_basename( GEODIR_EVENT_PLUGIN_FILE );
		
		if ( $file == $plugin ) {
			$all_active_plugins = get_option( 'active_plugins', array() );
			
			if ( !empty( $all_active_plugins ) && is_array( $all_active_plugins ) ) {
				foreach ( $all_active_plugins as $key => $plugin ) {
					if ( $plugin == $file ) {
						unset( $all_active_plugins[$key] );
					}
				}
			}
			update_option( 'active_plugins', $all_active_plugins );
		}
		
		wp_die( __( '<span style="color:#FF0000">There was an issue determining where GeoDirectory Plugin is installed and activated. Please install or activate GeoDirectory Plugin.</span>', 'geodirevents' ) );
	}
}

 // @todo
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

function geodir_event_activation_redirect() {

	if (get_option('geodir_events_activation_redirect', false)) {
		
		delete_option('geodir_events_activation_redirect');
		
		wp_redirect(admin_url('admin.php?page=geodirectory&tab=gd_event_fields_settings&subtab=gd_event_general_options')); 
			
	}
	
}


function geodir_event_default_taxonomies(){
	 
	global $wpdb,$dummy_image_path;
		
	$category_array = array('Events');
	
	$last_catid = isset($last_catid) ? $last_catid : '';
	
	$last_term = get_term($last_catid, 'gd_eventcategory');
			
	$uploads = wp_upload_dir(); // Array of key => value pairs
		
	
	for($i=0;$i < count($category_array); $i++)
	{
		$parent_catid = 0;
		if(is_array($category_array[$i]))
		{
			$cat_name_arr = $category_array[$i];
			for($j=0;$j < count($cat_name_arr);$j++)
			{
				$catname = $cat_name_arr[$j];
				
				if(!term_exists( $catname, 'gd_eventcategory' )){
					$last_catid = wp_insert_term( $catname, 'gd_eventcategory', $args = array('parent'=>$parent_catid) );
		
					if($j==0)
					{
						$parent_catid = $last_catid;
					}
					
					
					if(geodir_event_dummy_folder_exists())
						$dummy_image_url = geodir_event_plugin_url() . "/gdevents-admin/dummy/cat_icon";
					else
						$dummy_image_url = 'https://wpgeodirectory.com/dummy_event/cat_icon';

					$dummy_image_url = apply_filters('event_dummy_cat_image_url', $dummy_image_url);

					$catname = str_replace(' ', '_', $catname);
					$uploaded =  (array)fetch_remote_file("$dummy_image_url/".$catname.".png");
					
					if(empty($uploaded['error']))
					{	
						$new_path = $uploaded['file'];
						$new_url = $uploaded['url'];
					}
					
					$wp_filetype = wp_check_filetype(basename($new_path), null );
				    
				    $attachment = array(
					 'guid' => $uploads['baseurl'] . '/' . basename( $new_path ), 
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', basename($new_path)),
					 'post_content' => '',
					 'post_status' => 'inherit'
				    );
				    $attach_id = wp_insert_attachment( $attachment, $new_path );
				  	
					// you must first include the image.php file
				    // for the function wp_generate_attachment_metadata() to work
				    require_once(ABSPATH . 'wp-admin/includes/image.php');
				    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
				    wp_update_attachment_metadata( $attach_id, $attach_data );
					
					if(!geodir_get_tax_meta($last_catid['term_id'], 'ct_cat_icon'))
					{geodir_update_tax_meta($last_catid['term_id'], 'ct_cat_icon', array( 'id' => 'icon', 'src' => $new_url));}
				}
			}
			
		}else
		{
			$catname = $category_array[$i];
			
			if(!term_exists( $catname, 'gd_eventcategory' )){
				$last_catid = wp_insert_term( $catname, 'gd_eventcategory' );
				
				if(geodir_event_dummy_folder_exists())
					$dummy_image_url = geodir_event_plugin_url() . "/gdevents-admin/dummy/cat_icon";
				else
					$dummy_image_url = 'https://wpgeodirectory.com/dummy_event/cat_icon';

				$dummy_image_url = apply_filters('event_dummy_cat_image_url', $dummy_image_url);

				$catname = str_replace(' ', '_', $catname);
				$uploaded = (array) fetch_remote_file("$dummy_image_url/".$catname.".png");
				
				if(empty($uploaded['error']))
				{	
					$new_path = $uploaded['file'];
					$new_url = $uploaded['url'];
				}
				
				$wp_filetype = wp_check_filetype(basename($new_path), null );
				    
				    $attachment = array(
					 'guid' => $uploads['baseurl']  . '/' . basename( $new_path ), 
					 'post_mime_type' => $wp_filetype['type'],
					 'post_title' => preg_replace('/\.[^.]+$/', '', basename($new_path)),
					 'post_content' => '',
					 'post_status' => 'inherit'
				    );
					$attach_id = wp_insert_attachment( $attachment, $new_path );

				  	
					// you must first include the image.php file
				    // for the function wp_generate_attachment_metadata() to work
				    require_once(ABSPATH . 'wp-admin/includes/image.php');
				    $attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
				    wp_update_attachment_metadata( $attach_id, $attach_data );


				if(!geodir_get_tax_meta($last_catid['term_id'], 'ct_cat_icon', false, 'gd_event'))
				{geodir_update_tax_meta($last_catid['term_id'], 'ct_cat_icon', array( 'id' => $attach_id, 'src' => $new_url), 'gd_event');}
			}
		}
		
	}
}

function geodir_event_event_schedule_setting() {
	global $post, $post_id, $post_info;  
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_event_event_schedule_noncename' );
	
	$post_info_recurring_dates = '';
	if ( !empty( $post ) && isset( $post->ID ) ) {
		if ( ! isset( $post->event_dates ) && ! empty( $post_info ) && isset( $post_info->event_dates ) && ( $gd_post = geodir_get_post_info( $post->ID ) ) ) {
			$post = $gd_post;
		}
		$post_info_recurring_dates = maybe_unserialize( geodir_get_post_meta( $post->ID, 'event_dates', true ) );
	}
	
	$recuring_data = maybe_unserialize( $post_info_recurring_dates );
	
	// Check recurring enabled
	$recurring_pkg = geodir_event_recurring_pkg( $post );
	
	// recurring event
	$recuring_data['recurring'] = !empty( $post ) && isset( $post->recurring ) && $post->recurring && $recurring_pkg ? true : false;
		
	do_action( 'geodir_event_add_fields_on_metabox', $recuring_data );
}


function geodir_event_business_setting(){
	
	global $post,$post_id,$post_info;  
	
	wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_event_business_noncename' );
	
	do_action('geodir_event_business_fields_on_metabox');
	
}


/* ------------------------------------------------------------------*/
/* Check if dummy folder exists or not , if not then fetch from live url */
/*--------------------------------------------------------------------*/
function geodir_event_dummy_folder_exists(){

	$path = geodir_event_plugin_path(). '/gdevents-admin/dummy/';
	if(!is_dir($path))
		return false;
	else
		return true;
		
}

function geodir_event_admin_menu_order( $menu_order ) {
	
	// Initialize our custom order array
	$gdevents_menu_order = array();
	$gdevents_menu_order[] = 'edit.php?post_type=gd_event';
	
	// Get index of deals menu
	$gdevents_events = array_search( 'edit.php?post_type=gd_event', $menu_order );
	
	if($gdevents_separator = array_search( 'separator-geodirectory', $menu_order )){
		array_splice( $menu_order, $gdevents_separator + 1, 0, $gdevents_menu_order ); 
		unset( $menu_order[$gdevents_events] );
	}	
	
	// Return order
	return $menu_order;
}


function geodir_event_admin_custom_menu_order() {
	if ( !current_user_can( 'manage_options' ) ) return false;
	return true;
}

function geodir_event_package_add_extra_fields( $priceinfo = array() ) {
	// recurring event
	$recurring_pkg = isset( $priceinfo->recurring_pkg ) && (int)$priceinfo->recurring_pkg == 1 ? 1 : 0;
	?>
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Event Features Only', 'geodirevents');?></th>
		<td class="forminp"><div class="gtd-formfield"> </div></td>
	</tr>
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Link business', 'geodirevents');?></th>
		<td class="forminp"><div class="gtd-formfield">
				<select style="min-width:200px;" name="gd_link_business_pkg" >
					<option value="0" <?php if((isset($priceinfo->link_business_pkg) && $priceinfo->link_business_pkg=='0') || !isset($priceinfo->link_business_pkg)){ echo 'selected="selected"';}?> >
					<?php _e("No", 'geodirevents');?>
					</option>
					<option value="1" <?php if(isset($priceinfo->link_business_pkg) && $priceinfo->link_business_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", 'geodirevents');?>
					</option>
				</select>
			</div></td>
	</tr>
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Registration Description', 'geodirevents');?></th>
		<td class="forminp">
			<div class="gtd-formfield">
				<select name="gd_reg_desc_pkg" >
					<option value="0" <?php if(!isset($priceinfo->reg_desc_pkg) || $priceinfo->reg_desc_pkg=='0'){ echo 'selected="selected"';}?> >
					<?php _e("No", 'geodirevents');?>
					</option>
					<option value="1" <?php if(isset($priceinfo->reg_desc_pkg) && $priceinfo->reg_desc_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", 'geodirevents');?>
					</option>
				</select>
			</div>
		</td>
	</tr>
	<tr valign="top" class="single_select_page">
		<th class="titledesc" scope="row"><?php _e('Registration Fees', 'geodirevents');?></th>
		<td class="forminp">
			<div class="gtd-formfield">
				<select name="gd_reg_fees_pkg" >
					<option value="0" <?php if(!isset($priceinfo->reg_fees_pkg) || $priceinfo->reg_fees_pkg=='0'){ echo 'selected="selected"';}?> >
					<?php _e("No", 'geodirevents');?>
					</option>
					<option value="1" <?php if(isset($priceinfo->reg_fees_pkg) && $priceinfo->reg_fees_pkg=='1'){ echo 'selected="selected"';}?> >
					<?php _e("Yes", 'geodirevents');?>
					</option>
				</select>
			</div>
		</td>
	</tr>
	<tr valign="top" class="single_select_page">
	  <th class="titledesc" scope="row"><?php _e( 'Recurring Events', 'geodirevents' );?></th>
	  <td class="forminp">
		<div class="gtd-formfield">
		  <select name="gd_recurring_pkg">
			<option value="0" <?php selected( $recurring_pkg, 0 );?>><?php _e( "Yes", 'geodirevents' );?></option>
			<option value="1" <?php selected( $recurring_pkg, 1 );?>><?php _e( "No", 'geodirevents' );?></option>
		  </select>
		</div>
	  </td>
	</tr>
	<?php
}

function geodir_event_manager_tabs($tabs){

$geodir_post_types = geodir_get_option( 'geodir_post_types' );

	foreach($geodir_post_types as $geodir_post_type => $geodir_posttype_info){
		
		$originalKey = $geodir_post_type.'_fields_settings';
		
		if($geodir_post_type == 'gd_event'){
		
			if(array_key_exists($originalKey, $tabs)){
				
				if(array_key_exists('subtabs', $tabs[$originalKey])){
					
					$tabs[$originalKey]['request'] = array();
					
					$insertValue = array('subtab' => $geodir_post_type.'_general_options',
													'label' =>__( 'General', 'geodirevents'),
													'form_action' => admin_url('admin-ajax.php?action=geodir_event_manager_ajax')
												);
					
					$new_array = array();	
					$new_array[] = $insertValue;						
					foreach($tabs[$originalKey]['subtabs'] as $key => $val){
						
						$new_array[] = $val;
					
					}
					
					$tabs[$originalKey]['subtabs'] = $new_array;
					
				}
				
			}
			
		}
		
	}
	
	return $tabs;
	
}

// @todo remove
function geodir_event_tab_content($tab){
	
	if($tab == 'gd_event_fields_settings' && isset($_REQUEST['subtab']) && $_REQUEST['subtab']=='gd_event_general_options') { 
	
		geodir_admin_fields( geodir_event_general_setting_options() ); ?>
	
		<p class="submit">
		<input name="gd_event_general_settings" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'geodirevents' ); ?>" />
		<input type="hidden" name="subtab" value="" id="last_tab" />
		</p>
		</div> <?php

		
	}
	
}


function geodir_event_general_setting_options($arr=array())
{

	$arr[] = array( 'name' => __( 'Filter Settings', 'geodirevents' ), 'type' => 'no_tabs', 'desc' => '', 'id' => 'geodir_eventgeneral_options' );
	
	
	$arr[] = array( 'name' => __( 'Listing settings', 'geodirevents' ), 'type' => 'sectionstart', 'id' => 'geodir_event_general_options');
	
	$arr[] = array(  
			'name' => __( 'Default event filter', 'geodirevents' ),
			'desc' 		=> __( 'Set the default filter view of event on listing page', 'geodirevents' ),
			'id' 		=> 'geodir_event_defalt_filter',
			'css' 		=> 'min-width:300px;',
			'std' 		=> 'upcoming',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'all' => __( 'All', 'geodirevents' ),
				'today' => __( 'Today', 'geodirevents' ),
				'upcoming' => __( 'Upcoming', 'geodirevents' ),
				'past' => __( 'Past', 'geodirevents' )
				))
		);
	$arr[] = array(  
				'name' => __( 'Disable Recurring Feature', 'geodirevents' ),
				'desc' => __( 'This allows to disable recurring event feature', 'geodirevents' ),
				'id' => 'geodir_event_disable_recurring',
				'type' => 'checkbox',
				'std' => '0'
			);
	$arr[] = array(  
				'name' => __( 'Hide event past dates', 'geodirevents' ),
				'desc' => __( 'Hide event past dates in the detail page sidebar of recurring events', 'geodirevents' ),
				'id' => 'geodir_event_hide_past_dates',
				'type' => 'checkbox',
				'std' => '0'
			);
	$arr[] = array(  
				'name' => __( 'Event dates in map infowindow', 'geodirevents' ),
				'desc' => __( 'No. of schedule dates to display for event marker info window on the map. Default: 1', 'geodirevents' ),
				'id' => 'geodir_event_infowindow_dates_count',
				'type' => 'text',
				'css' => 'min-width:300px;',
				'std' => '1'
			);
	$arr[] = array(  
			'name' => __( 'Filter event dates in map infowindow', 'geodirevents' ),
			'desc' 		=> __( 'Set the filter to view schedule dates for event marker info window on the map.', 'geodirevents' ),
			'id' 		=> 'geodir_event_infowindow_dates_filter',
			'css' 		=> 'min-width:300px;',
			'std' 		=> 'upcoming',
			'type' 		=> 'select',
			'class'		=> 'chosen_select',
			'options' => array_unique( array( 
				'all' => __( 'All', 'geodirevents' ),
				'today' => __( 'Today', 'geodirevents' ),
				'upcoming' => __( 'Upcoming', 'geodirevents' ),
				'past' => __( 'Past', 'geodirevents' )
			))
		);
    $arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_event_general_options');
    
    $arr[] = array( 'name' => __( 'Date settings', 'geodirevents' ), 'type' => 'sectionstart', 'id' => 'geodir_event_date_options');
    
    $date_formats = array(
        'm/d/Y',
        'd/m/Y',
        'Y/m/d',
        'm-d-Y',
        'd-m-Y',
        'Y-m-d',
        'j F Y',
        'F j, Y',
    );
    /**
     * Filter the event fields date format options.
     *
     * @since 1.3.7
     * @param array $date_formats The PHP date format array.
     */
    $date_formats = apply_filters( 'geodir_event_fields_date_formats', $date_formats );
    
    $date_formats_fields = array();
    foreach ( $date_formats as $format ) {
        $date_formats_fields[$format] = $format . ' ( ' . date_i18n( $format, time() ) . ' )';
    }
    
    $arr[] = array(  
            'name' => __( 'Date format for add event feilds', 'geodirevents' ),
            'desc' => __( 'Set the date format for the date feilds and calendar in add event form.', 'geodirevents' ),
            'id' => 'geodir_event_date_format_feild',
            'css' => 'min-width:300px;',
            'std' => 'F j, Y',
            'type' => 'select',
            'class' => 'chosen_select',
            'options' => array_unique( $date_formats_fields )
        );
    
    $date_formats[] = get_option( 'date_format' );
    $date_formats[] = 'j M Y';
    /**
     * Filter the display event dates date format options.
     *
     * @since 1.3.7
     * @param array $date_formats The PHP date format array.
     */
    $date_formats = apply_filters( 'geodir_event_dates_date_formats', $date_formats );
    
    $date_formats_dates = array();
    foreach ( $date_formats as $format ) {
        $date_formats_dates[$format] = $format . ' ( ' . date_i18n( $format, time() ) . ' )';
    }
    
    $arr[] = array(  
        'name' => __( 'Date format for display event dates', 'geodirevents' ),
        'desc' => __( 'Set the date format to display event dates.', 'geodirevents' ),
        'id' => 'geodir_event_date_format',
        'css' => 'min-width:300px;',
        'std' => get_option( 'date_format' ),
        'type' => 'select',
        'class' => 'chosen_select',
        'options' => array_unique( $date_formats_dates )
    );
    
    $arr[] = array(
            'name' => '',
            'desc' => __( 'OR use custom date form setting for display event dates.', 'geodirevents' ),
            'id' => 'geodir_event_date_use_custom',
            'std' => '',
            'type' => 'checkbox',
            'value' => '1',
        );
        
    $arr[] = array(  
            'name' => __( 'Custom date format', 'geodirevents' ),
            'desc' => __( 'Set the custom date format to display event dates.', 'geodirevents' ),
            'id' => 'geodir_event_date_format_custom',
            'type' => 'text',
            'css' => 'min-width:300px;',
            'std' => ''
        );
	
	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_event_date_options' );




	$arr[] = array( 'name' => __( 'Link Business settings', 'geodirevents' ), 'type' => 'sectionstart', 'id' => 'geodir_event_linking_options');

	$arr[] = array(
		'name' => __( 'Any linking Author', 'geodirevents' ),
		'desc' => __( 'Allow linking to any post not just users own posts?', 'geodirevents' ),
		'id' => 'geodir_event_link_any',
		'type' => 'checkbox',
		'std' => '0'
	);

	$arr[] = array( 'type' => 'sectionend', 'id' => 'geodir_event_linking_options');



	$arr = apply_filters('geodir_ajax_duplicate_general_options' ,$arr );
	
	return $arr;
}


function geodir_display_event_messages(){

	if(isset($_REQUEST['event_success']) && $_REQUEST['event_success'] != '')
	{
			echo '<div id="message" class="updated fade"><p><strong>' . __( $_REQUEST['event_success'], 'geodirevents' ) . '</strong></p></div>';
				
	}
	
}


function geodir_event_delete_unnecessary_fields(){
	global $wpdb;
	
	if(!geodir_get_option('geodir_event_delete_unnecessary_fields')){
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'categories'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `categories`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'Recurring'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `Recurring`");
			
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'event_start'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `event_start`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'event_end'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `event_end`");
			
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'event_start_time'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `event_start_time`");
		
		if($wpdb->get_var("SHOW COLUMNS FROM ".GEODIR_EVENT_DETAIL_TABLE." WHERE field = 'event_end_time'"))
			$wpdb->query("ALTER TABLE `".GEODIR_EVENT_DETAIL_TABLE."` DROP `event_end_time`");
		
		geodir_update_option('geodir_event_delete_unnecessary_fields', '1');
		
	}
}

/* Admin init loader */
 
//add_action('admin_init', 'geodir_event_activation_redirect');

//add_action( 'add_meta_boxes', 'geodir_event_meta_box_add' );

//add_action('menu_order', 'geodir_event_admin_menu_order',12); // @todo

//add_action('custom_menu_order', 'geodir_event_admin_custom_menu_order'); // @todo

//add_action('geodir_event_add_fields_on_metabox', 'geodir_event_show_event_fields_html');

//add_action('geodir_event_business_fields_on_metabox', 'geodir_event_show_business_fields_html');

//add_action('geodir_payment_package_extra_fields','geodir_event_package_add_extra_fields', 2, 1);

//add_action('geodir_before_admin_panel' , 'geodir_display_event_messages'); 

//add_action('geodir_admin_option_form' , 'geodir_event_tab_content', 110); // @todo

//add_filter('geodir_settings_tabs_array','geodir_event_manager_tabs',110); // @todo

//add_action('admin_init', 'geodir_event_delete_unnecessary_fields');

function geodir_event_meta_box_add()
{
	global $post;
  
	add_meta_box( 'geodir_event_schedule', __( 'Event Schedule', 'geodirevents' ), 'geodir_event_event_schedule_setting', 'gd_event','normal', 'high' );
	
	$package_info = array();
	$package_info = geodir_post_package_info($package_info , $post);
	
	if(!isset($package_info->post_type) || $package_info->post_type != 'gd_event')
		return false;
	
	if(isset($package_info->link_business_pkg) && $package_info->link_business_pkg  == '1'){	
		
		add_meta_box('geodir_event_business',__( 'Businesses', 'geodirevents' ),'geodir_event_business_setting','gd_event','side','high');
		
	}
	
}

function geodir_event_insert_dummy_data_loop($post_type,$data_type,$item_index){

	if($post_type=='gd_event' && $data_type=='standard_events'){
		/**
		 * Contains dummy property for sale post content.
		 *
		 * @since 1.6.11
		 * @package GeoDirectory
		 */
		include_once('gdevents_dummy_post.php');
	}

}
//add_action('geodir_insert_dummy_data_loop','geodir_event_insert_dummy_data_loop',10,3);

function geodir_event_date_types_for($data_types,$post_type){
	if($post_type=='gd_event'){
		$data_types = array(
			'standard_events' => array(
			'name'=>__('Events','geodirectory'),
			'count'=> 13
		)
		);
	}
	return $data_types;

}
//add_filter('geodir_dummy_date_types_for','geodir_event_date_types_for',10,2);

//add_action('geodir_sample_csv_download_link', 'geodir_sample_csv_for_events_download_link', 1);

function geodir_sample_csv_for_events_download_link(){
	?>
	<div class="geodir_event_csv_download">
	<a href="<?php echo geodir_event_plugin_url() . '/gdevents-assets/event_listing.csv'?>" ><?php _e("Download sample csv for Events", 'geodirevents')?></a>
	</div>
	<?php
}

/**
 * Place detail page linked events settings
 */
function geodir_event_design_settings( $settings = array() ) {
	$return = array();
	foreach ( $settings as $key => $setting ) {
		$return[] = $setting;
		
		if ( isset( $setting['type'] ) && $setting['type'] == 'sectionend' && $setting['id'] == 'detail_page_related_post_settings' ) {
			$return[] = array(
							'name' => __( 'Linked Events Settings', 'geodirevents' ),
							'type' => 'sectionstart',
							'desc' => '',
							'id' => 'geodir_event_linked_event_settings'
						);
			$return[] = array(
							'name' => __( 'Display events filter:', 'geodirevents' ),
							'desc' => '',
							'id' => 'geodir_event_linked_event_type',
							'css' => 'min-width:300px;',
							'std' => 'all',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'all' => __( 'All Events', 'geodirevents' ),
											'today' => __( 'Today', 'geodirevents' ),
											'upcoming' => __( 'Upcoming', 'geodirevents' ),
											'past' => __( 'Past', 'geodirevents' ),
										) )
						);
			$return[] = array(
							'name' => __( 'Sort by:', 'geodirevents' ),
							'desc' => __( 'Set the linked event listing sort by view', 'geodirevents' ),
							'id' => 'geodir_event_linked_sortby',
							'css' => 'min-width:300px;',
							'std' => 'latest',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'az' => __( 'A-Z', 'geodirevents' ),
											'latest' => __( 'Latest', 'geodirevents' ),
											'featured' => __( 'Featured', 'geodirevents' ),
											'high_review' => __( 'Review', 'geodirevents' ),
											'high_rating' => __( 'Rating', 'geodirevents' ),
											'random' => __( 'Random', 'geodirevents' ),
											'upcoming' => __( 'Upcoming', 'geodirevents' ),
										) )
						);
			$return[] = array(  
							'name' => __( 'Number of events:', 'geodirevents' ),
							'desc' => __( 'Enter number of events to display on linked events listing', 'geodirevents' ),
							'id' => 'geodir_event_linked_count',
							'type' => 'text',
							'css' => 'min-width:300px;',
							'std' => '5'
						);
			$return[] = array(
							'name' => __( 'Layout:', 'geodirevents' ),
							'desc' => __( 'Set the listing view of linked event on place detail page', 'geodirevents' ),
							'id' => 'geodir_event_linked_listing_view',
							'css' => 'min-width:300px;',
							'std' => 'gridview_onehalf',
							'type' => 'select',
							'class' => 'chosen_select',
							'options' => array_unique( array( 
											'gridview_onehalf' => __( 'Grid View (Two Columns)', 'geodirevents' ),
											'gridview_onethird' => __( 'Grid View (Three Columns)', 'geodirevents' ),
											'gridview_onefourth' => __( 'Grid View (Four Columns)', 'geodirevents' ),
											'gridview_onefifth' => __( 'Grid View (Five Columns)', 'geodirevents' ),
											'listview' => __( 'List view', 'geodirevents' ),
										) )
						);
			$return[] = array(  
							'name' => __( 'Event content excerpt:', 'geodirevents' ),
							'desc' => __( 'Enter event content excerpt character count.', 'geodirevents' ),
							'id' => 'geodir_event_linked_post_excerpt',
							'type' => 'text',
							'css' => 'min-width:300px;',
							'std' => '20'
						);
			$return[] = array( 
							'type' => 'sectionend',
							'id' => 'geodir_event_linked_event_settings'
						);
		}
	}
	return $return;
}
// This add a new fields in Geodirectory > Design > Detail > Linked Event Settings 
//add_filter( 'geodir_design_settings', 'geodir_event_design_settings', 1 );

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
	if ( $post_type == 'gd_event' ) {
		$fields['event_dates'] = array(
			'post_type'      => $post_type,
			'data_type'      => '',
			'field_type'     => 'datetime',
			'frontend_title' => __( 'Event date', 'geodirevents' ),
			'htmlvar_name'   => 'event_dates',
			'field_icon'     => 'fa fa-calendar',
			'description'    => __( 'Sort by event date', 'geodirevents' )
		);
	}

	return $fields;
}