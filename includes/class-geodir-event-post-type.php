<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies.
 *
 * @class     GeoDir_Post_types
 * @since     2.0.0
 * @package   GeoDirectory
 * @category  Class
 * @author    AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Event_Post_Type Class.
 */
class GeoDir_Event_Post_Type {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 6 );
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 6 );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );

		// Add cpt setting events support option.
		add_filter( 'geodir_get_settings_cpt', array( __CLASS__, 'filter_cpt_settings' ), 11, 3 );

		// Sanitize post type data.
		add_filter( 'geodir_save_post_type', array( __CLASS__, 'sanitize_post_type' ), 11, 3 );

		// Post type saved.
		add_action( 'geodir_post_type_saved', array( __CLASS__, 'post_type_saved' ), 11, 3 );

		// Post type events supports enabled.
		add_action( 'geodir_event_pt_events_supports_enabled', array( __CLASS__, 'pt_events_supports_enabled' ), 11, 1 );

		// Post type events supports disabled.
		add_action( 'geodir_event_pt_events_supports_disabled', array( __CLASS__, 'pt_events_supports_disabled' ), 11, 1 );

		add_filter( 'geodir_post_type_supports', array( __CLASS__, 'post_type_supports' ), 10, 3 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_type() {
		if ( ! is_blog_installed() || post_type_exists( 'gd_event' ) ) {
			return;
		}

		do_action( 'geodirectory_register_post_type' );

		/**
		 * Get available custom posttypes and taxonomies and register them.
		 */
		_x( 'events', 'URL slug', 'geodirectory' );

		$post_types = self::get_post_type_defaults();

		// Register each post type if array of data is returned
		if ( is_array( $post_types ) ) {
			foreach ( $post_types as $post_type => $args ) {
				if ( post_type_exists( $post_type ) ) {
					continue;
				}

				if ( ! empty( $args['rewrite']['slug'] ) ) {
					$args['rewrite']['slug'] = _x( $args['rewrite']['slug'], 'URL slug', 'geodirectory' );
				}
				$args = stripslashes_deep( $args );

				if ( ! empty( $args['labels'] ) ) {
					foreach ( $args['labels'] as $key => $val ) {
						$args['labels'][ $key ] = __( $val, 'geodirectory' ); // allow translation
					}
				}

				// force support post revisions
				$args['supports'][] = 'revisions';

				/**
				 * Filter post type args.
				 *
				 * @since 1.0.0
				 * @param string $args Post type args.
				 * @param string $post_type The post type.
				 */
				$args = apply_filters( 'geodir_post_type_args', $args, $post_type );

				register_post_type( $post_type, $args );
			}
		}

		do_action( 'geodirectory_after_register_post_type' );
	}

	/**
	 * Register core taxonomies.
	 */
	public static function register_taxonomy() {
		if ( ! is_blog_installed() || taxonomy_exists( 'gd_eventcategory' ) ) {
			return;
		}

		do_action( 'geodirectory_register_taxonomy' );

		$taxonomies = self::get_taxonomy_defaults();

		// If custom taxonomies are present, register them
		if ( is_array( $taxonomies ) ) {
			// Sort taxonomies
			ksort( $taxonomies );

			// Register taxonomies
			foreach ( $taxonomies as $taxonomy => $args ) {
				if ( taxonomy_exists( $taxonomy ) ) {
					continue;
				}

				// Allow taxonomy names to be translated
				if ( ! empty( $args['args']['labels'] ) ) {
					foreach ( $args['args']['labels'] as $key => $tax_label ) {
						$args['args']['labels'][$key] = __( $tax_label, 'geodirectory' );
					}
				}

				register_taxonomy( $taxonomy, $args['object_type'], $args['args'] );

				if ( taxonomy_exists( $taxonomy ) ) {
					register_taxonomy_for_object_type( $taxonomy, $args['object_type'] );
				}
			}
		}

		do_action( 'geodirectory_after_register_taxonomy' );
	}

	/**
	 * Get the post type defaults.
	 */
	private static function get_post_type_defaults() {
		$post_type = 'gd_event';

		$post_types = geodir_get_option( 'post_types', array() );

		if ( empty( $post_types[ $post_type ] ) ) {
			$listing_slug = 'events';

			$labels = array(
				'name'               => __( 'Events', 'geodirevents' ),
				'singular_name'      => __( 'Event', 'geodirevents' ),
				'add_new'            => __( 'Add New', 'geodirevents' ),
				'add_new_item'       => __( 'Add New Event', 'geodirevents' ),
				'edit_item'          => __( 'Edit Event', 'geodirevents' ),
				'new_item'           => __( 'New Event', 'geodirevents' ),
				'view_item'          => __( 'View Event', 'geodirevents' ),
				'search_items'       => __( 'Search Events', 'geodirevents' ),
				'not_found'          => __( 'No Event Found', 'geodirevents' ),
				'not_found_in_trash' => __( 'No Event Found In Trash', 'geodirevents' )
			);

			$args = array(
				'labels'          => $labels,
				'can_export'      => true,
				'capability_type' => 'post',
				'description'     => 'Event post type.',
				'has_archive'     => $listing_slug,
				'hierarchical'    => false,  // Hierarchical causes memory issues - WP loads all records!
				'map_meta_cap'    => true,
				'menu_icon'       => 'dashicons-calendar-alt',
				'listing_order'   => self::get_listing_order($post_types),
				'public'          => true,
				'query_var'       => true,
				'rewrite'         => array(
					'slug'         => $listing_slug,
					'with_front'   => false,
					'hierarchical' => true,
					'feeds'        => true
				),
				'supports'        => array(
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'comments',
					'revisions',
				),
				'taxonomies'      => array( $post_type . 'category', $post_type . '_tags' ),
				'supports_events' => true
			);

			// Update custom post types
			$post_types[ $post_type ] = $args;

			geodir_update_option( 'post_types', $post_types );

			if ( ! GeoDir_Event_Admin_Install::is_v2_upgrade() ) {
				// flush rewrite rules
				flush_rewrite_rules();
				do_action( 'geodir_flush_rewrite_rules' );
				wp_schedule_single_event( time(), 'geodir_flush_rewrite_rules' );

				// run the create tables function to add our new columns.
				add_action('init',array('GeoDir_Admin_Install','create_tables'));

				// Run and insert default fields after table created.
				add_action( 'init', array( 'GeoDir_Event_Admin_Install', 'insert_default_fields' ), 10.1 );

				// insert default tabs
				GeoDir_Admin_Install::insert_default_tabs( $post_type );
			}
		}

		return $post_types;
	}

	public static function get_listing_order($post_types){
		$listing_order = 1;
		foreach($post_types as $post_type){
			if(isset($post_type['listing_order']) && $post_type['listing_order'] > $listing_order){
				$listing_order = $post_type['listing_order'];
			}
		}

		$listing_order++;

		return $listing_order;
	}

	/**
	 * Get the taxonomy defaults.
	 */
	private static function get_taxonomy_defaults() {
		$post_type = 'gd_event';

		$taxonomies = geodir_get_option( 'taxonomies', array() );
		$post_types = geodir_get_option( 'post_types', array() );

		if ( empty( $taxonomies[ $post_type . '_tags' ] ) || empty( $taxonomies[ $post_type . 'category' ] ) ) {
			$listing_slug = ! empty( $post_types[ $post_type ]['rewrite']['slug']) ? $post_types[ $post_type ]['rewrite']['slug'] : 'events';
			$singular_name = ! empty( $post_types[ $post_type ]['labels']['singular_name']) ? $post_types[ $post_type ]['labels']['singular_name'] : 'Event';

			// Event tags
			$tags_taxonomy = array();
			$tags_taxonomy['object_type'] = $post_type;
			$tags_taxonomy['listing_slug'] = $listing_slug . '/tags';
			$tags_taxonomy['args'] = array(
				'public' => true,
				'hierarchical' => false,
				'rewrite' => array(
					'slug' => $listing_slug . '/tags',
					'with_front' => false,
					'hierarchical' => true
				),
				'query_var' => true,
				'labels' => array(
					'name' => sprintf( __( '%s Tags', 'geodirectory' ), $singular_name ),
					'singular_name' => sprintf( __( '%s Tag', 'geodirectory' ), $singular_name ),
					'search_items' => sprintf( __( 'Search %s Tags', 'geodirectory' ), $singular_name ),
					'popular_items' => sprintf( __( 'Popular %s Tags', 'geodirectory' ), $singular_name ),
					'all_items' => sprintf( __( 'All %s Tags', 'geodirectory' ), $singular_name ),
					'edit_item' => sprintf( __( 'Edit %s Tag', 'geodirectory' ), $singular_name ),
					'update_item' => sprintf( __( 'Update %s Tag', 'geodirectory' ), $singular_name ),
					'add_new_item' => sprintf( __( 'Add New %s Tag', 'geodirectory' ), $singular_name ),
					'new_item_name' => sprintf( __( 'New %s Tag Name', 'geodirectory' ), $singular_name ),
					'add_or_remove_items' => sprintf( __( 'Add or remove %s tags', 'geodirectory' ), $singular_name ),
					'choose_from_most_used' => sprintf( __( 'Choose from the most used %s tags', 'geodirectory' ), $singular_name ),
					'separate_items_with_commas' => sprintf( __( 'Separate %s tags with commas', 'geodirectory' ), $singular_name ),
				),
			);

			// Event category
			$cat_taxonomy = array();
			$cat_taxonomy['object_type'] = $post_type;
			$cat_taxonomy['listing_slug'] = $listing_slug;
			$cat_taxonomy['args'] = array(
				'public' => true,
				'hierarchical' => true,
				'rewrite' => array(
					'slug' => $listing_slug,
					'with_front' => false,
					'hierarchical' => true
				),
				'query_var' => true,
				'labels' => array(
					'name' => sprintf( __( '%s Categories', 'geodirectory' ), $singular_name ),
					'singular_name' => sprintf( __( '%s Category', 'geodirectory' ), $singular_name ),
					'search_items' => sprintf( __( 'Search %s Categories', 'geodirectory' ), $singular_name ),
					'popular_items' => sprintf( __( 'Popular %s Categories', 'geodirectory' ), $singular_name ),
					'all_items' => sprintf( __( 'All %s Categories', 'geodirectory' ), $singular_name ),
					'edit_item' => sprintf( __( 'Edit %s Category', 'geodirectory' ), $singular_name ),
					'update_item' => sprintf( __( 'Update %s Category', 'geodirectory' ), $singular_name ),
					'add_new_item' => sprintf( __( 'Add New %s Category', 'geodirectory' ), $singular_name ),
					'new_item_name' => sprintf( __( 'New %s Category', 'geodirectory' ), $singular_name ),
					'add_or_remove_items' => sprintf( __( 'Add or remove %s categories', 'geodirectory' ), $singular_name ),
				),
			);

			$taxonomies[ $post_type . '_tags' ] = $tags_taxonomy;
			$taxonomies[ $post_type . 'category'] = $cat_taxonomy;

			geodir_update_option( 'taxonomies', $taxonomies );
		}

		// loop the taxonomies
		if ( ! empty( $taxonomies ) ) {
			$tag_slug = geodir_get_option( 'permalink_tag_base', 'tags' );
			$cat_slug = geodir_get_option( 'permalink_category_base', 'category' );
			
			foreach( $taxonomies as $key => $taxonomy ) {
				if ( $taxonomy == $post_type . 'category' && $taxonomy != $post_type . '_tags' ) {
					continue;
				}

				// add capability to assign terms to any user, if not added then subscribers listings wont have terms
				$taxonomies[$key]['args']['capabilities']['assign_terms'] = 'read';

				// adjust rewrite rules _tags
				$listing_slug = ! empty( $post_types[ $taxonomy['object_type'] ]['rewrite']['slug'] ) ? $post_types[ $taxonomy['object_type'] ]['rewrite']['slug'] : 'places';
				if ( stripos( strrev( $key ), "sgat_" ) === 0 ) { // its a tag
					$taxonomies[$key]['args']['rewrite']['slug'] = $tag_slug ? $listing_slug . '/' . $tag_slug : $listing_slug;
				} else { // its a category
					$taxonomies[$key]['args']['rewrite']['slug'] = $cat_slug ? $listing_slug . '/' . $cat_slug : $listing_slug;
				}

				// Dynamically create the labels from the CPT labels
				$singular_name = ! empty( $post_types[ $taxonomy['object_type'] ]['labels']['singular_name'] ) ? $post_types[ $taxonomy['object_type'] ]['labels']['singular_name'] : 'Place';

				if ( stripos( strrev( $key ), "sgat_" ) === 0 ) { // its a tag
					$taxonomies[$key]['args']['labels'] = array(
						'name' => sprintf( __( '%s Tags', 'geodirectory' ), $singular_name ),
						'singular_name' => sprintf( __( '%s Tag', 'geodirectory' ), $singular_name ),
						'search_items' => sprintf( __( 'Search %s Tags', 'geodirectory' ), $singular_name ),
						'popular_items' => sprintf( __( 'Popular %s Tags', 'geodirectory' ), $singular_name ),
						'all_items' => sprintf( __( 'All %s Tags', 'geodirectory' ), $singular_name ),
						'edit_item' => sprintf( __( 'Edit %s Tag', 'geodirectory' ), $singular_name ),
						'update_item' => sprintf( __( 'Update %s Tag', 'geodirectory' ), $singular_name ),
						'add_new_item' => sprintf( __( 'Add New %s Tag', 'geodirectory' ), $singular_name ),
						'new_item_name' => sprintf( __( 'New %s Tag Name', 'geodirectory' ), $singular_name ),
						'add_or_remove_items' => sprintf( __( 'Add or remove %s tags', 'geodirectory' ), $singular_name ),
						'choose_from_most_used' => sprintf( __( 'Choose from the most used %s tags', 'geodirectory' ), $singular_name ),
						'separate_items_with_commas' => sprintf( __( 'Separate %s tags with commas', 'geodirectory' ), $singular_name ),
					);
				} else { // its a category
					$taxonomies[$key]['args']['labels'] = array(
						'name' => sprintf( __( '%s Categories', 'geodirectory' ), $singular_name ),
						'singular_name' => sprintf( __( '%s Category', 'geodirectory' ), $singular_name ),
						'search_items' => sprintf( __( 'Search %s Categories', 'geodirectory' ), $singular_name ),
						'popular_items' => sprintf( __( 'Popular %s Categories', 'geodirectory' ), $singular_name ),
						'all_items' => sprintf( __( 'All %s Categories', 'geodirectory' ), $singular_name ),
						'edit_item' => sprintf( __( 'Edit %s Category', 'geodirectory' ), $singular_name ),
						'update_item' => sprintf( __( 'Update %s Category', 'geodirectory' ), $singular_name ),
						'add_new_item' => sprintf( __( 'Add New %s Category', 'geodirectory' ), $singular_name ),
						'new_item_name' => sprintf( __( 'New %s Category', 'geodirectory' ), $singular_name ),
						'add_or_remove_items' => sprintf( __( 'Add or remove %s categories', 'geodirectory' ), $singular_name ),
					);
				}
			}
		}

		// add rewrite rules
		return $taxonomies;
	}

	/**
	 * Added product for Jetpack related posts.
	 *
	 * @param  array $post_types
	 * @return array
	 */
	public static function rest_api_allowed_post_types( $post_types ) {
		$post_types[] = 'gd_event';

		return $post_types;
	}

	public static function filter_cpt_settings( $settings, $current_section = '', $post_type_values = array() ) {
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( ! empty( $settings ) ) {
			// Events supports setting
			if ( $post_type != 'gd_event' && defined( 'GEODIR_CP_VERSION' ) ) {
				$new_settings = array();
				foreach ( $settings as $key => $setting ) {
					if ( ! empty( $setting['id'] ) && $setting['id'] == 'cpt_settings' && $setting['type'] == 'sectionend' ) {
						$new_settings[] =  array(
							'name' => __( 'Is Event Post Type?', 'geodirevents' ),
							'desc' => __( 'Tick to treat this post type as an event post type. <span style="color:red;">(WARNING: enabling post type as event post type will move all existing posts to draft.)</span>', 'geodirevents' ),
							'id'   => 'supports_events',
							'type' => 'checkbox',
							'std'  => '0',
							'advanced' => true,
							'value'	   => ( ! empty( $post_type_values['supports_events'] ) ? '1' : '0' )
						);
						$new_settings[] =  array(
							'name' => '',
							'desc' => '',
							'id'   => 'prev_supports_events',
							'type' => 'hidden',
							'value'	   => ( ! empty( $post_type_values['supports_events'] ) ? 'y' : 'n' )
						);
					}
					$new_settings[] = $setting;
				}
				$settings = $new_settings;
			}

			// Past events settings
			if ( ! empty( $post_type_values['post_type'] ) && GeoDir_Post_types::supports( $post_type_values['post_type'], 'events' ) ) {
				$new_settings = array();
				foreach ( $settings as $key => $setting ) {
					$new_settings[] = $setting;

					if ( ! empty( $setting['id'] ) && $setting['id'] == 'cpt_settings_page' && $setting['type'] == 'sectionend' ) {
						$new_settings[] = array(
							'title' => __( 'Manage Past Events', 'geodirevents' ),
							'type' => 'title',
							'id' => 'cpt_settings_past_events',
							'desc' => __( 'Allows to handle past events on the site.', 'geodirevents' ),
							'desc_tip' => false,
							'advanced' => false,
						);
						$new_settings[] =  array(
							'type' => 'checkbox',
							'id' => 'past_event',
							'name' => __( 'Manage Past Events', 'geodirevents' ),
							'desc' => __( 'Tick to auto unpublish or remove event after x days from the end date.', 'geodirevents' ),
							'std' => '0',
							'value' => ( ! empty( $post_type_values['past_event'] ) ? '1' : '0' ),
							'desc_tip' => false,
							'advanced' => false,
						);
						$new_settings[] =  array(
							'type' => 'number',
							'id' => 'past_event_days',
							'name' => __( 'Unpublish/Remove After Days', 'geodirevents' ),
							'desc' => __( 'Set days when you would like to unpublish or remove event after the end date. Examples: 0 or blank to unpublish or remove event on next day of the end date. 7 to keep event live for 7 days after the end date.', 'geodirevents' ),
							'placeholder' => __( 'On Next Day', 'geodirevents' ),
							'std' => '',
							'value' => ( ! empty( $post_type_values['past_event_days'] ) ? absint( $post_type_values['past_event_days'] ) : '' ),
							'custom_attributes' => array(
								'min' => '0',
								'step' => '1',
							),
							'desc_tip' => true,
							'advanced' => false,
						);
						$new_settings[] = array(
							'type' => 'select',
							'id' => 'past_event_status',
							'name' => __( 'Past Event Status', 'geodirevents' ),
							'desc' => __( 'Set status you would like to set to the event after x days from end date. Delete status will remove event permanently.', 'geodirevents' ),
							'placeholder' => __( 'Select Status', 'geodirevents' ),
							'options' => self::past_event_statuses(),
							'class' => 'geodir-select',
							'std' => 'pending',
							'value' => ( ! empty( $post_type_values['past_event_status'] ) ? $post_type_values['past_event_status'] : 'pending' ),
							'desc_tip' => true,
							'advanced' => false,
						);
						$new_settings[] = array( 
							'type' => 'sectionend', 
							'id' => 'cpt_settings_past_events' 
						);
					}
				}
				$settings = $new_settings;
			}
		}

		return $settings;
	}

	public static function sanitize_post_type( $data, $post_type, $request ) {
		// Save supports events setting
		if ( $post_type != 'gd_event' && defined( 'GEODIR_CP_VERSION' ) ) {
			$data[ $post_type ]['supports_events'] = ! empty( $request['supports_events'] ) ? true : false;
		}

		if ( isset( $request['past_event_status'] ) ) {
			$data[ $post_type ]['past_event'] = ! empty( $request['past_event'] ) ? 1 : 0;
			$data[ $post_type ]['past_event_days'] = ! empty( $request['past_event_days'] ) ? absint( $request['past_event_days'] ) : 0;
			$data[ $post_type ]['past_event_status'] = ! empty( $request['past_event_status'] ) ? sanitize_text_field( $request['past_event_status'] ) : 'pending';
		} else {
			if ( isset( $data[ $post_type ]['past_event'] ) ) {
				unset( $data[ $post_type ]['past_event'] );
			}
			if ( isset( $data[ $post_type ]['past_event_days'] ) ) {
				unset( $data[ $post_type ]['past_event_days'] );
			}
			if ( isset( $data[ $post_type ]['past_event_status'] ) ) {
				unset( $data[ $post_type ]['past_event_status'] );
			}
		}

		return $data;
	}

	public static function post_type_saved( $post_type, $args, $new = false ) {
		if ( $post_type != 'gd_event' && defined( 'GEODIR_CP_VERSION' ) ) {
			$current = ! empty( $args['supports_events'] ) ? true : false;
			$previous = ! empty( $_POST['prev_supports_events'] ) && $_POST['prev_supports_events'] == 'y' ? true : false;
			if ( $new ) {
				$previous = false;
			}
			if ( $current != $previous ) {
				if ( $current && ! $previous ) { // Events support enabled.
					do_action( 'geodir_event_pt_events_supports_enabled', $post_type );
				} else if ( ! $current && $previous ) { // Events support disabled.
					do_action( 'geodir_event_pt_events_supports_disabled', $post_type );
				}

				do_action( 'geodir_event_pt_events_supports_changed', $post_type, $current, $previous );
			}
		}

		if ( $new && GeoDir_Post_types::supports( $post_type, 'events' ) ) {
			// Insert default sort fields.
			GeoDir_Event_Admin_Install::insert_default_sort_fields( $post_type );
		}
	}

	/**
	 * Check a post type's support for a given feature.
	 *
	 * @param bool $value       True if supports else False.
	 * @param string $post_type The post type being checked.
	 * @param string $feature   The feature being checked.
	 * @return bool Whether the post type supports the given feature.
	 */
	public static function post_type_supports( $value, $post_type, $feature ) {
		// Check a post type supports events
		if ( $feature == 'events' ) {
			if ( $post_type == 'gd_event' ) {
				return true;
			}

			if ( defined( 'GEODIR_CP_VERSION' ) ) {
				$post_type_object = geodir_post_type_object( $post_type );
				if ( ! empty( $post_type_object ) && ! empty( $post_type_object->supports_events ) ) {
					$value = true;
				} else {
					$value = false;
				}
			}
		}

		return $value;
	}

	/**
	 * Check a taxonomy's support for a given feature.
	 *
	 * @param bool $value       True if supports else False.
	 * @param string $taxonomy  The taxonomy being checked.
	 * @param string $post_type The post type being checked.
	 * @param string $feature   The feature being checked.
	 * @return bool Whether the taxonomy supports the given feature.
	 */
	public static function taxonomy_supports( $value, $taxonomy, $post_type, $feature ) {
		// Check a post type supports events
		if ( $feature == 'events' && defined( 'GEODIR_CP_VERSION' ) ) {
			$value = GeoDir_Post_types::supports( $post_type, $feature, $value );
		}

		return $value;
	}

	public static function pt_events_supports_enabled( $post_type ) {
		global $wpdb;

		$table = geodir_db_cpt_table( $post_type );

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE post_type = %s AND post_status NOT IN( 'draft', 'auto-draft', 'inherit' )", array( 'draft', $post_type ) ) );
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET post_status = %s WHERE post_status NOT IN( 'draft', 'auto-draft', 'inherit' )", array( 'draft' ) ) );

		$fields = GeoDir_Event_Fields::event_custom_fields( $post_type, 0 );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $key => $field ) {
				geodir_custom_field_save( $field );
			}

			self::update_fields_sort_order( $post_type );
		}
	}

	public static function pt_events_supports_disabled( $post_type ) {
		global $wpdb;

		$fields = GeoDir_Event_Fields::event_custom_fields( $post_type, 0 );

		if ( ! empty( $fields ) ) {
			$cfs = new GeoDir_Settings_Cpt_Cf();

			foreach ( $fields as $key => $field ) {
				if ( $field_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . GEODIR_CUSTOM_FIELDS_TABLE . " WHERE post_type = %s AND htmlvar_name = %s LIMIT 1", array( $post_type, $field['htmlvar_name'] ) ) ) ) {
					$cfs->delete_custom_field( $field_id );
				}
			}
		}

		// Delete event schedules.
		$wpdb->query( $wpdb->prepare( "DELETE schedules FROM " . GEODIR_EVENT_SCHEDULES_TABLE . " schedules LEFT JOIN {$wpdb->posts} posts ON posts.ID = schedules.event_id WHERE posts.post_type = %s", array( $post_type ) ) );
	}

	public static function update_fields_sort_order( $post_type ) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE post_type = %s ORDER BY sort_order ASC, id ASC", array( $post_type ) ) );

		if ( ! empty( $results ) ) {
			$sort_order = 0;
			foreach ( $results as $key => $row ) {
				$sort_order++;

				$wpdb->update( GEODIR_CUSTOM_FIELDS_TABLE, array( 'sort_order' => $sort_order ), array( 'id' => $row->id ) );
			}
		}
	}

	public static function get_event_post_types() {
		global $wpdb;

		$post_types = wp_cache_get( 'geodir_event_post_types', 'event_post_types' );

		if ( $post_types !== false ) {
			return $post_types;
		}

		$gd_post_types = geodir_get_posttypes();

		$post_types = array();
		foreach ( $gd_post_types as $post_type ) {
			if ( GeoDir_Post_types::supports( $post_type, 'events' ) ) {
				$post_types[] = $post_type;
			}
		}

		wp_cache_set( 'geodir_event_post_types', $post_types, 'event_post_types' );

		return $post_types;
	}

	public static function past_event_statuses() {
		$statuses = geodir_get_post_statuses();

		$statuses['trash'] = _x( 'Trash', 'Listing status', 'geodirevents' );
		$statuses['delete'] = _x( 'Delete Permanently', 'Listing status', 'geodirevents' );

		if ( isset( $statuses['publish'] ) ) {
			unset( $statuses['publish'] );
		}

		return apply_filters( 'geodir_event_past_event_statuses', $statuses );
	}
}
GeoDir_Event_Post_Type::init();