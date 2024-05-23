<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Event Manager Autoloader.
 *
 * @class       GeoDir_Event_Autoloader
 * @version     2.0.0
 * @package     GeoDir_Event_Manager/Classes
 * @category    Class
 * @author      GeoDirectory
 */
class GeoDir_Event_Autoloader {

    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * The Constructor.
     */
    public function __construct() {
        if ( function_exists( "__autoload" ) ) {
            spl_autoload_register( "__autoload" );
        }

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->include_path = untrailingslashit( plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE ) ) . '/includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @param  string $class
     * @return string
     */
    private function get_file_name_from_class( $class ) {
        return 'class-' . str_replace( '_', '-', $class ) . '.php';
    }

    /**
     * Include a class file.
     *
     * @param  string $path
     * @return bool successful or not
     */
    private function load_file( $path ) {
        if ( $path && is_readable( $path ) ) {
            include_once( $path );
            return true;
        }
        return false;
    }

    /**
     * Auto-load GeoDir classes on demand to reduce memory consumption.
     *
     * @param string $class
     */
    public function autoload( $class ) {

        $class = strtolower( $class );

        if ( 0 !== strpos( $class, 'geodir_event_' ) ) {
            return;
        }

        $file  = $this->get_file_name_from_class( $class );
        $path  = '';

        if ( strpos( $class, 'geodir_event_shortcode_' ) === 0 ) {
            $path = $this->include_path . 'shortcodes/';
        } elseif ( strpos( $class, 'geodir_event_admin' ) === 0 ) {
            $path = $this->include_path . 'admin/';
        }elseif ( strpos( $class, 'geodir_event_widget' ) === 0 ) {
            $path = $this->include_path . 'widgets/';
        }elseif ( strpos( $class, 'geodir_event_settings' ) === 0 ) {
            $path = $this->include_path . 'admin/settings/';
        }

        if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
            $this->load_file( $this->include_path . $file );
        }
    }
}

new GeoDir_Event_Autoloader();
