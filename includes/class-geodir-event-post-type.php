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
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 5.1 );
		add_action( 'init', array( __CLASS__, 'register_post_type' ), 5.1 );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
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
				'taxonomies'      => array( $post_type . 'category', $post_type . '_tags' )
			);

			// Update custom post types
			$post_types[ $post_type ] = $args;

			geodir_update_option( 'post_types', $post_types );
		}

		return $post_types;
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

}
GeoDir_Event_Post_Type::init();