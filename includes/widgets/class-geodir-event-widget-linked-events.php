<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Linked Events widget.
 *
 * @since 1.0.0
 */
class GeoDir_Event_Widget_Linked_Events extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    	=> 'geodirevents',
            'block-icon'    	=> 'networking',
            'block-category'	=> 'widgets',
            'block-keywords'	=> "['events','link business','geodir']",
            'class_name'    	=> __CLASS__,
            'base_id'       	=> 'gd_linked_events',
            'name'          	=> __('GD > Linked Events','geodirevents'),
            'widget_ops'    	=> array(
                'classname'   => 'geodir-linked-events',
                'description' => esc_html__( 'Shows the events on detail page linked to the current place listing.','geodirevents' ),
                'geodirectory' => true,
            ),
        );

        parent::__construct( $options );
    }

    /**
     * The Super block output function.
     *
     * @param array $args
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {

		global $gd_post;

		if ( ! ( ! empty( $gd_post ) && $gd_post->post_type == 'gd_place' && geodir_is_page( 'detail' ) ) ) {
			return;
		}

		$args = wp_parse_args(
			(array)$args,
			array(
				'title' => 'asdsad',
				'post_type' => '',
				'category' => array(),
				'related_to' => '',
				'category_title' => '',
				'sort_by' => geodir_get_option( 'geodir_event_linked_sortby', 'latest' ),
				'title_tag' => 'h3',
				'list_order' => '',
				'post_limit' => geodir_get_option( 'geodir_event_linked_count', '5' ),
				'layout' => geodir_get_option( 'geodir_event_linked_listing_view', 'gridview_onehalf' ),
				'listing_width' => '',
				'add_location_filter' => '0',
				'character_count' => geodir_get_option( 'geodir_event_linked_post_excerpt', '20' ),
				'show_featured_only' => '',
				'show_special_only' => '',
				'with_pics_only' => '',
				'with_videos_only' => '',
				'use_viewing_post_type' => '',
				'hide_if_empty' => true,
				'event_type' => geodir_get_option( 'geodir_event_linked_event_type', 'all' ),
				'single_event' => geodir_get_option( 'geodir_event_linked_single_event' )
			)
        );

		$args['post_type'] = 'gd_event';
		$args['link_business'] = $gd_post->ID;

		ob_start();

        $this->output_html( $widget_args, $args );

        return ob_get_clean();
	}

	/**
     * Generates linked events HTML.
     *
     * @global object $post                    The current post object.
     * @global string $gd_layout_class 		   The girdview style of the listings for widget.
     * @global bool $geodir_is_widget_listing  Is this a widget listing?. Default: false.
     * @global object $gd_session              GeoDirectory Session object.
     *
     * @param array|string $args               Display arguments including before_title, after_title, before_widget, and
     *                                         after_widget.
     * @param array|string $instance           The settings for the particular instance of the widget.
     */
    public function output_html( $args = '', $instance = '' ) {
        global $gd_session, $gd_post, $post;

        extract( $args, EXTR_SKIP );

        $title = $instance['title'];
        $post_type = $instance['post_type'];
        $post_number = $instance['post_limit'];
		$character_count = $instance['character_count'];
        $layout = $instance['layout'];
        $add_location_filter = ! empty( $instance['add_location_filter'] ) ? true : false;
        $list_sort = $instance['sort_by'];
		$distance_to_post = $list_sort == 'distance_asc' && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) && geodir_is_page( 'detail' ) ? true : false;
		if ( $list_sort == 'distance_asc' && ! $distance_to_post ) {
			$list_sort = geodir_get_posts_default_sort( $post_type );
		}

        $skip_location = false;
        if ( ! $add_location_filter && $gd_session->get( 'gd_multi_location' ) ) {
            $skip_location = true;
            $gd_session->un_set( 'gd_multi_location' );
        }

		$view_all_params = array();
		if ( ! empty( $instance['event_type'] ) ) {
			$view_all_params['etype'] = $instance['event_type'];
		}
		$view_all_params['sort_by'] = $list_sort;
		$view_all_params['venue'] = $gd_post->ID . '-' . $gd_post->post_title;
		$view_all_url = geodir_getlink( get_post_type_archive_link( $post_type ), $view_all_params );

        if ( $skip_location ) {
            $gd_session->set( 'gd_multi_location', 1 );
        }

        $query_args = array(
            'posts_per_page' 	=> $post_number,
            'is_geodir_loop' 	=> true,
            'gd_location'    	=> $add_location_filter,
            'post_type'      	=> $post_type,
            'order_by'       	=> $list_sort,
			'distance_to_post' 	=> $distance_to_post,
			'link_business' 	=> $instance['link_business']
        );

        if ( $character_count ) {
            $query_args['excerpt_length'] = $character_count;
        }

        if ( ! empty( $instance['show_featured_only'] ) ) {
            $query_args['show_featured_only'] = 1;
        }

        if ( ! empty( $instance['show_special_only'] ) ) {
            $query_args['show_special_only'] = 1;
        }

        if ( ! empty( $instance['with_pics_only'] ) ) {
            $query_args['with_pics_only']      = 0;
            $query_args['featured_image_only'] = 1;
        }

        if ( ! empty( $instance['with_videos_only'] ) ) {
            $query_args['with_videos_only'] = 1;
        }
        $hide_if_empty = ! empty( $instance['hide_if_empty'] ) ? true : false;

        global $gd_layout_class, $geodir_is_widget_listing;

		/*
		 * Filter widget listings query args.
		 */
		$query_args = apply_filters( 'geodir_widget_listings_query_args', $query_args, $instance );

        $widget_listings = geodir_get_widget_listings( $query_args );

        if ( $hide_if_empty && empty( $widget_listings ) ) {
            return;
        }
		
		$view_all_link = '<a href="' . esc_url( $view_all_url ) .'" class="geodir-viewall geodir-viewall-linked clearfix">' . wp_sprintf( __( 'View all events in "%s"', 'geodirevents' ), $gd_post->post_title ) . '</a>';
		$view_all_link = apply_filters( 'geodir_widget_view_all_link', $view_all_link, $instance, $this->base_id );

        ?>
        <div class="geodir_locations geodir_location_listing">
            <?php
			if ( strstr( $layout, 'gridview' ) ) {
                $listing_view_exp   = explode( '_', $layout );
                $gd_layout_class 	= $layout;
                $layout             = $listing_view_exp[0];
            } else {
                $gd_layout_class = '';
            }

			if ( isset( $post ) ) {
				$reset_post = $post;
			}
			if ( isset( $gd_post ) ) {
				$reset_gd_post = $gd_post;
			}
			$geodir_is_widget_listing = true;

			echo $view_all_link;

			geodir_get_template( 'content-widget-listing.php', array( 'widget_listings' => $widget_listings ) );

			$geodir_is_widget_listing = false;

			if ( isset( $reset_post ) ) {
				if ( ! empty( $reset_post ) ) {
					setup_postdata( $reset_post );
				}
				$post = $reset_post;
			}
			if ( isset( $reset_gd_post ) ) {
				$gd_post = $reset_gd_post;
			}
            ?>
        </div>
        <?php
    }

}