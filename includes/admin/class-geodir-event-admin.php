<?php
/**
 * GeoDirectory Events Admin
 *
 * @class    GeoDir_Event_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDir_Event_Manager/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Event_Admin class.
 */
class GeoDir_Event_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $pagenow;

		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		add_action( 'geodir_clear_version_numbers', array( $this, 'clear_version_number' ), 12 );
		add_filter( 'geodir_get_settings_pages', array( $this, 'load_settings_page' ), 12, 1 );
		add_filter( 'geodir_seo_options', array( $this, 'seo_options' ), 12, 1 );
		add_filter( 'geodir_cat_schemas', 'geodir_event_filter_schemas', 10, 1 );
		add_filter( 'geodir_add_custom_sort_options', 'geodir_event_custom_sort_options', 10, 2 );
		add_filter( 'geodir_uninstall_options', 'geodir_event_uninstall_settings', 10, 1 );
		add_action( 'geodir_pricing_package_settings', 'geodir_event_pricing_package_settings', 9, 2 );
		add_action( 'geodir_pricing_process_data_for_save', 'geodir_event_pricing_process_data_for_save', 1, 3 );
		add_filter( 'geodir_debug_tools', 'geodir_event_debug_tools', 20, 1 );

		// Duplicate event.
		add_filter( 'post_row_actions', array( $this, 'add_duplicate_action' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'add_duplicate_action' ), 10, 2 );
		add_action( 'admin_post_geodirevents_duplicate', array( $this, 'duplicate_event' ) );

		// Dummy data.
		add_filter( 'geodir_dummy_data_types', array( 'GeoDir_Event_Admin_Dummy_Data', 'dummy_data_types' ), 10, 2 );
		add_action( 'geodir_dummy_data_include_file', array( 'GeoDir_Event_Admin_Dummy_Data', 'include_file' ), 10, 4 );

		// Add the required DB columns.
		add_filter( 'geodir_db_cpt_default_columns', array( __CLASS__, 'add_db_columns' ), 10, 3 );

		// Conditional Fields.
		add_filter( 'geodir_cf_show_conditional_fields_setting', array( $this, 'cf_show_conditional_fields_setting' ), 10, 4 );
		add_filter( 'geodir_conditional_fields_options', array( $this, 'conditional_fields_options' ), 10, 2 );
	}

	/**
	 * Add "Duplicate" action link to post row actions.
	 *
	 * @since 2.3.14
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_Post $post    The post object.
	 * @return array  The filtered actions array.
	 */
	public function add_duplicate_action( $actions, $post ) {
		if ( 'gd_event' === $post->post_type ) {
			$actions['duplicate'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin-post.php?action=geodirevents_duplicate&post=' . $post->ID ), 'geodirevents_duplicate_' . $post->ID ),
				esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'geodirevents' ), $post->post_title ) ),
				__( 'Duplicate', 'geodirevents' )
			);
		}
		return $actions;
	}

	/**
	 * Create a duplicate of the event.
	 *
	 * @since 2.3.14
	 *
	 * @return void
	 */
	public function duplicate_event() {
		global $wpdb, $plugin_prefix;

		// Check user capabilities.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to duplicate this event.', 'geodirevents' ),
				esc_html__( 'Permission Denied', 'geodirevents' ),
				array(
					'response'  => 403,
					'back_link' => true,
				)
			);
		}

		// Verify nonce.
		$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_key( $_GET['_wpnonce'] ) : '';
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		if ( ! wp_verify_nonce( $nonce, 'geodirevents_duplicate_' . $post_id ) ) {
			wp_die(
				esc_html__( 'Security check failed.', 'geodirevents' ),
				esc_html__( 'Security Error', 'geodirevents' ),
				array(
					'response'  => 403,
					'back_link' => true,
				)
			);
		}

		// Get original event.
		$original_event = geodir_get_post_info( $post_id );

		if ( ! $original_event ) {
			wp_die(
				esc_html__( 'Event creation failed, could not find original event.', 'geodirevents' ),
				esc_html__( 'Event Not Found', 'geodirevents' ),
				array(
					'response'  => 404,
					'back_link' => true,
				)
			);
		}

		// Prepare new post data.
		$new_post_data                = array_intersect_key(
			(array) $original_event,
			array_flip(
				array(
					'post_author',
					'post_content',
					'post_title',
					'post_excerpt',
					'post_status',
					'comment_status',
					'ping_status',
					'post_password',
					'post_name',
					'post_content_filtered',
					'post_parent',
					'menu_order',
					'post_type',
					'post_mime_type',
					'comment_count',
				)
			)
		);
		$new_post_data['post_title'] .= ' ' . esc_html__( '(Copy)', 'geodirevents' );

		// Insert new post.
		$new_post_id = wp_insert_post( $new_post_data, true );

		if ( is_wp_error( $new_post_id ) ) {
			wp_die(
				esc_html( $new_post_id->get_error_message() ),
				esc_html__( 'Duplication Error', 'geodirevents' ),
				array(
					'response'  => 500,
					'back_link' => true,
				)
			);
		}

		// Duplicate post meta.
		$table       = geodir_db_cpt_table( $original_event->post_type );
		$post_detail = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE post_id = %d", $original_event->ID ),
			ARRAY_A
		);

		unset( $post_detail['post_id'] );
		$post_detail['post_category']    = $original_event->post_category;
		$post_detail['default_category'] = $original_event->default_category;
		$post_detail['post_tags']        = $original_event->post_tags;

		// Duplicate taxonomies.
		$taxonomies = get_object_taxonomies( $original_event->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $original_event->ID, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $new_post_id, $terms, $taxonomy );
		}

		// Duplicate featured image.
		$post_detail['featured_image'] = $original_event->featured_image;
		geodir_save_post_meta( $new_post_id, 'featured_image', $original_event->featured_image );

		$post_images = geodir_get_images( (int) $original_event->ID, '', true, 0, array( 'post_images' ) );
		if ( ! empty( $post_images ) ) {
			foreach ( $post_images as $key => $image ) {
				GeoDir_Media::insert_attachment(
					$new_post_id,
					'post_images',
					$image->file,
					( $key === 0 ) ? $original_event->post_title : '',
					'',
					$image->menu_order,
					1,
					true
				);
			}
		}

		// Update post details.
		$table  = $plugin_prefix . sanitize_key( $original_event->post_type ) . '_detail';
		$format = array_fill( 0, count( $post_detail ), '%s' );
		$wpdb->update(
			$table,
			$post_detail,
			array( 'post_id' => $new_post_id ),
			$format
		);

		wp_safe_redirect( admin_url( "post.php?action=edit&post={$new_post_id}" ) );
		exit;
	}

	/**
	 * Add the event column to the CPTs tables if events are enabled.
	 *
	 * @param $columns
	 * @param $cpt
	 *
	 * @return mixed
	 */
	public static function add_db_columns( $columns, $cpt, $post_type ) {
		if ( $post_type == 'gd_event' || ! empty( $cpt['supports_events'] ) || GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$columns['recurring']   = "recurring TINYINT(1) DEFAULT '0'";
			$columns['event_dates'] = 'event_dates TEXT NOT NULL';
			$columns['rsvp_count']  = "rsvp_count INT(11) DEFAULT '0'";
		}

		return $columns;
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/class-geodir-event-admin-assets.php';
		include_once GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/class-geodir-event-admin-import-export.php';
	}

	/**
	 * Handle redirects to setup/welcome page after install and updates.
	 *
	 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
	 */
	public function admin_redirects() {
		// Nonced plugin install redirects (whitelisted)
		if ( ! empty( $_GET['geodir-event-install-redirect'] ) ) {
			$plugin_slug = geodir_clean( $_GET['geodir-event-install-redirect'] );

			$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );

			wp_safe_redirect( $url );
			exit;
		}

		// Setup wizard redirect
		if ( get_transient( '_geodir_event_activation_redirect' ) ) {
			delete_transient( '_geodir_event_activation_redirect' );
		}
	}

	/**
	 * Deletes the version number from the DB to run install functions again.
	 *
	 * @since 2.3.13
	 */
	public function clear_version_number() {
		delete_option( 'geodir_event_version' );
	}

	public static function load_settings_page( $settings_pages ) {
		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_text_field( $_REQUEST['post_type'] ) : 'gd_place';

		if ( ! ( ! empty( $_REQUEST['page'] ) && sanitize_text_field( $_REQUEST['page'] ) == $post_type . '-settings' ) ) {
			$settings_pages[] = include GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/settings/class-geodir-event-settings-events.php';
		}

		return $settings_pages;
	}

	/**
	 * Skip conditional for event dates field.
	 *
	 * @since 2.1.1.7
	 *
	 * @param bool   $hide True to hide.
	 * @param string $post_type Current post type.
	 * @param object $field Current field object.
	 * @param array  $data Current field data.
	 * @return bool True to hide, False to show.
	 */
	public function cf_show_conditional_fields_setting( $hide, $post_type, $field, $data ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) && ! empty( $field->htmlvar_name ) && $field->htmlvar_name == 'event_dates' ) {
			$hide = true;
		}

		return $hide;
	}

	/**
	 * Add event fields in conditional fields options.
	 *
	 * @since 2.1.1.7
	 *
	 * @param array  $fields Fields array.
	 * @param string $post_type Current post type.
	 * @return array Filtered field options.
	 */
	public function conditional_fields_options( $fields, $post_type ) {
		if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			$_fields = array();

			foreach ( $fields as $key => $title ) {
				if ( $key == 'event_dates' ) {
					$_fields['start_date'] = __( 'Event start date', 'geodirevents' );
					$_fields['end_date']   = __( 'Event end date', 'geodirevents' );
				} else {
					$_fields[ $key ] = $title;
				}
			}

			$fields = $_fields;
		}

		return $fields;
	}

	public function seo_options( $settings ) {
		// SEOPress
		if ( function_exists( 'seopress_activation' ) ) {
			$new_settings = array();

			foreach ( $settings as $key => $setting ) {
				$new_settings[] = $setting;

				if ( ! empty( $setting['id'] ) && $setting['id'] == 'seopress_disable' ) {
					$new_settings[] = array(
						'id'      => 'seopress_recurring_schedules',
						'type'    => 'checkbox',
						'name'    => __( 'Recurring Schedules in Sitemap', 'geodirectory' ),
						'desc'    => __( 'Show recurring schedules for recurring event in SEOPress XML sitemaps.', 'geodirectory' ),
						'default' => '0',
					);
				}
			}

			$settings = $new_settings;
		}

		return $settings;
	}
}
