<?php
/**
 * GeoDirectory Events rename plugin functions.
 *
 * @since 1.0.0
 * @package GeoDirectory_Event_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function geodir_event_rename_plugin( $current_name, $new_name ) {
	global $geodir_event_manager_file;

	// Allow us to easily interact with the filesystem.
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	WP_Filesystem();

	global $wp_filesystem;

	$plugin_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $current_name . DIRECTORY_SEPARATOR . $current_name . '.php';

	// Plugin file exists & readable.
	if ( ! ( $wp_filesystem->exists( $plugin_file ) && $wp_filesystem->is_readable( $plugin_file ) ) ) {
		return false;
	}

	// Current path
	$source_dir_path = dirname( $plugin_file );
	$source_file_path = $plugin_file;

	// Destination path
	$destination_dir_path = str_replace( $current_name, $new_name, $source_dir_path );
	$destination_file_path = str_replace( $current_name, $new_name, $source_file_path );

	if ( $wp_filesystem->exists( $destination_file_path ) ) {
		return false;
	}

	// File exists & already renamed.
	if ( ! $wp_filesystem->exists( $destination_dir_path ) && $wp_filesystem->exists( $source_dir_path ) && $wp_filesystem->is_readable( $source_dir_path ) && $wp_filesystem->move( $source_dir_path, $destination_dir_path, true ) ) {
		// Folder renamed.
		$source_file_path = str_replace( $source_dir_path, $destination_dir_path, $source_file_path );

		if ( ! $wp_filesystem->exists( $destination_file_path ) && $wp_filesystem->exists( $source_file_path ) && $wp_filesystem->is_readable( $source_file_path ) && $wp_filesystem->move( $source_file_path, $destination_file_path, true ) ) {
			// File renamed.
			if ( is_multisite() ) {
				$sites = get_sites();

				if ( ! empty( $sites ) ) {
					foreach ( $sites as $site ) {
						geodir_event_change_plugin_name( $current_name, $new_name, (int) $site->blog_id );
					}
				}
			} else {
				geodir_event_change_plugin_name( $current_name, $new_name );
			}

			wp_cache_flush();

			// Update event plugin file
			$geodir_event_manager_file = str_replace( $current_name, $new_name, $geodir_event_manager_file );

			return true;
		}
	}

	return false;
}

function geodir_event_change_plugin_name( $current_name, $new_name, $blog_id = 0 ) {
	global $wpdb;

	$table = $wpdb->get_blog_prefix( (int) $blog_id ) . 'options';
	$results = $wpdb->get_results( "SELECT option_id, option_name, option_value FROM `{$table}` WHERE option_value LIKE '%" . $current_name . "%'" );

	if ( ! empty( $results ) ) {
		foreach ( $results as $row ) {
			$option_value = geodir_event_recursive_replace( $current_name, $new_name, $row->option_value );

			$wpdb->update( $table, array( 'option_value' => $option_value ), array( 'option_id' => $row->option_id ) );
		}
	}
}

function geodir_event_recursive_replace( $from = '', $to = '', $data = '', $serialised = false, $case_insensitive = false ) {
	try {
		if ( is_string( $data ) && ! is_serialized_string( $data ) && ( $unserialized = geodir_event_unserialize( $data ) ) !== false ) {
			$data = geodir_event_recursive_replace( $from, $to, $unserialized, true, $case_insensitive );
		} elseif ( is_array( $data ) ) {
			$_tmp = array( );

			foreach ( $data as $key => $value ) {
				$key = geodir_event_recursive_replace( $from, $to, $key, false, $case_insensitive );

				$_tmp[ $key ] = geodir_event_recursive_replace( $from, $to, $value, false, $case_insensitive );
			}

			$data = $_tmp;
			unset( $_tmp );
		} elseif ( is_object( $data ) ) {
			if ( '__PHP_Incomplete_Class' !== get_class( $data ) ) {
				$_tmp = $data;
				$props = get_object_vars( $data );
				foreach ( $props as $key => $value ) {
					$_tmp->$key = geodir_event_recursive_replace( $from, $to, $value, false, $case_insensitive );
				}

				$data = $_tmp;
				unset( $_tmp );
			}
		} elseif ( is_serialized_string( $data ) ) {
			$unserialized = geodir_event_unserialize( $data );

			if ( $unserialized !== false ) {
				$data = geodir_event_recursive_replace( $from, $to, $unserialized, true, $case_insensitive );
			}
		} else {
			if ( is_string( $data ) ) {
				if ( $case_insensitive ) {
					$data = str_ireplace( $from, $to, $data );
				} else {
					$data = str_replace( $from, $to, $data );
				}
			}
		}

		if ( $serialised ) {
			return maybe_serialize( $data );
		}
	} catch( Exception $error ) { }

	return $data;
}

function geodir_event_unserialize( $string ) {
	if ( ! is_serialized( $string ) ) {
		return false;
	}

	$string = trim( $string );
	$string = maybe_unserialize( $string );

	return $string;
}