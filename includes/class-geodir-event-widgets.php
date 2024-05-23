<?php
/**
 * Add widget settings.
 *
 * @author      AyeCode Ltd
 * @package     GeoDir_Event_Manager/Widgets
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Event_Widgets Class.
 */
class GeoDir_Event_Widgets {

	public static function init() {
		add_filter( 'wp_super_duper_arguments', array( __CLASS__, 'super_duper_arguments'), 9, 2 );
		add_filter( 'geodir_widget_listings_query_args', array( __CLASS__, 'widget_listings_query_args' ), 10, 2 );
	}

	public static function super_duper_arguments( $arguments, $options ) {
		$widget_class = ! empty( $options ) && ! empty( $options['class_name'] ) ? $options['class_name'] : '';

		$match_classes = array(
			'GeoDir_Widget_Listings' => 'category',
			'GeoDir_Widget_Best_Of' => 'post_type',
			'GeoDir_CP_Widget_Post_Linked' => 'post_type',
		);

		if ( ! empty( $widget_class ) && isset( $match_classes[ $widget_class ] ) ) {
			$new_arguments = array();
			foreach ( $arguments as $key => $argument ) {
				$new_arguments[ $key ] = $argument;

				if ( $key == $match_classes[ $widget_class ] ) {
					$event_post_types = GeoDir_Event_Post_Type::get_event_post_types();
					$conditions = array();
					if ( ! empty( $event_post_types ) ) {
						foreach ( $event_post_types as $pt ) {
							$conditions[] = '[%post_type%]=="' . $pt . '"';
						}
					}
					$condition = ! empty( $conditions ) ? implode( ' || ', $conditions ) : '';
					if ( count( $conditions ) > 1 ) {
						$condition = "( " . $condition . ") ";
					}

					$new_arguments['event_type'] = array(
						'type' => 'select',
						'title' => __( 'Show events:', 'geodirevents' ),
						'desc' => __( 'Select events to show.', 'geodirevents' ),
						'options' => array_merge( array( '' => __( 'Default filter', 'geodirevents' ) ), geodir_event_filter_options() ),
						'default' => '',
						'desc_tip' => true,
						'advanced' => true,
						'element_require' => $condition,
						'group' => __( 'Filters', 'geodirectory' )
					);

					$new_arguments['single_event'] = array(
						'type' => 'checkbox',
						'title' => __( 'Show single listing for recurring event?', 'geodirevents' ),
						'value' => '1',
						'default' => '0',
						'desc_tip' => true,
						'advanced' => true,
						'element_require' => $condition,
						'group' => __( 'Filters', 'geodirectory' )
					);
				}
			}
			$arguments = $new_arguments;
		}
		return $arguments;
	}

	public static function widget_listings_query_args( $query_args, $params = array() ) {
		if ( ! empty( $params['post_type'] ) && GeoDir_Post_types::supports( $params['post_type'], 'events' ) ) {
			$merge_args = array( 'event_type', 'single_event' );
			
			foreach ( $merge_args as $key => $arg ) {
				if ( isset( $params[ $arg ] ) && ! isset( $query_args[ $arg ] ) ) {
					$query_args[ $arg ] = $params[ $arg ];
				}
			}
		}

		return $query_args;
	}
}
