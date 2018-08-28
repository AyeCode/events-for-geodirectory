<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       https://wpgeodirectory.com
 * @since      1.0.0
 *
 * @package    GeoDir_Event_Manager
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    GeoDir_Event_Manager
 * @author     GeoDirectory <info@wpgeodirectory.com>
 */
final class GeoDir_Event_Manager {
    
    /**
     * GeoDirectory Event Manager instance.
     *
     * @access private
     * @since  2.0.0
     */
    private static $instance = null;
    
    /**
     * The settings instance variable
     *
     * @access public
     * @since  2.0.0
     * @var    GeoDir_Event_Settings
     */
    public $settings;
    
    /**
     * Main GeoDir_Event_Manager Instance.
     *
     * Ensures only one instance of GeoDirectory Event Manager is loaded or can be loaded.
     *
     * @since 2.0.0
     * @static
     * @see GeoDir()
     * @return GeoDir_Event_Manager - Main instance.
     */
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof GeoDir_Event_Manager ) ) {
            self::$instance = new GeoDir_Event_Manager;
            self::$instance->setup_constants();
            
            add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

            if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
                add_action( 'admin_notices', array( self::$instance, 'php_version_notice' ) );

                return self::$instance;
            }

            self::$instance->includes();
            self::$instance->init_hooks();

            do_action( 'geodir_event_manager_loaded' );
        }
        
        return self::$instance;
    }
    
    /**
     * Setup plugin constants.
     *
     * @access private
     * @since 2.0.0
     * @return void
     */
    private function setup_constants() {
        global $plugin_prefix;

		if ( $this->is_request( 'test' ) ) {
            $plugin_path = dirname( GEODIR_EVENT_PLUGIN_FILE );
        } else {
            $plugin_path = plugin_dir_path( GEODIR_EVENT_PLUGIN_FILE );
        }
        
        $this->define( 'GEODIR_EVENT_PLUGIN_DIR', $plugin_path );
        $this->define( 'GEODIR_EVENT_PLUGIN_URL', untrailingslashit( plugins_url( '/', GEODIR_EVENT_PLUGIN_FILE ) ) );
        $this->define( 'GEODIR_EVENT_PLUGIN_BASENAME', plugin_basename( GEODIR_EVENT_PLUGIN_FILE ) );

        // Database tables
		$this->define( 'GEODIR_EVENT_DETAIL_TABLE', $plugin_prefix . 'gd_event_detail' );
		$this->define( 'GEODIR_EVENT_SCHEDULES_TABLE', $plugin_prefix . 'event_schedule' );
    }
    
    /**
     * Loads the plugin language files
     *
     * @access public
     * @since 2.0.0
     * @return void
     */
    public function load_textdomain() {
        global $wp_version;
        
        $locale = $wp_version >= 4.7 ? get_user_locale() : get_locale();
        
        /**
         * Filter the plugin locale.
         *
         * @since   1.0.0
         * @package GeoDir_Event_Manager
         */
        $locale = apply_filters( 'plugin_locale', $locale, 'geodirevents' );

        load_textdomain( 'geodirevents', WP_LANG_DIR . '/' . 'geodirevents' . '/' . 'geodirevents' . '-' . $locale . '.mo' );
        load_plugin_textdomain( 'geodirevents', FALSE, basename( dirname( GEODIR_EVENT_PLUGIN_FILE ) ) . '/languages/' );
    }
    
    /**
     * Show a warning to sites running PHP < 5.3
     *
     * @static
     * @access private
     * @since 2.0.0
     * @return void
     */
    public static function php_version_notice() {
        echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by GeoDirectory Events. Please contact your host and request that your version be upgraded to 5.3 or later.', 'geodirevents' ) . '</p></div>';
    }
    
    /**
     * Include required files.
     *
     * @access private
     * @since 2.0.0
     * @return void
     */
    private function includes() {
        global $pagenow, $geodir_options, $geodirectory;

        /**
         * Class autoloader.
         */
        include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/class-geodir-event-autoloader.php' );
		
		include_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/class-geodir-event-post-type.php' ); // Registers post type

		GeoDir_Event_AJAX::init();
		GeoDir_Event_Fields::init();
		GeoDir_Event_Schedules::init();
		GeoDir_Event_AYI::init();
		GeoDir_Event_Widgets::init();

		require_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/deprecated-functions.php' );
        require_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/core-functions.php' );
		require_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/template-functions.php' );
		
		GeoDir_Event_API::init();

        if ( $this->is_request( 'admin' ) || $this->is_request( 'test' ) || $this->is_request( 'cli' ) ) {
            new GeoDir_Event_Admin();

	        require_once( GEODIR_EVENT_PLUGIN_DIR . 'includes/admin/admin-functions.php' );

			GeoDir_Event_Admin_Install::init();

			require_once( GEODIR_EVENT_PLUGIN_DIR . 'upgrade.php' );	        
        }

		$this->query = new GeoDir_Event_Query();
    }
    
    /**
     * Hook into actions and filters.
     * @since  2.3
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );

		if ( $this->is_request( 'frontend' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ), 10 );
			add_filter( 'geodir_category_term_link', 'geodir_event_category_term_link', 20, 3 );
		}

		add_action( 'geodir_extra_loop_actions', 'geodir_event_display_event_type_filter', 10, 1 );
		add_filter( 'geodir_seo_variables', 'geodir_event_seo_variables', 10, 2 );
		add_filter( 'geodir_replace_seo_vars', 'geodir_event_replace_seo_vars', 10, 2 );
		add_filter( 'geodir_filter_title_variables_vars', 'geodir_event_filter_title_seo_vars', 10, 4 );
		add_filter( 'geodir_advance_search_filter_titles', 'geodir_event_search_show_current_filters', 10, 1 );
		add_action( 'widgets_init', 'goedir_event_register_widgets' );
		add_filter( 'geodir_details_schema', 'geodir_event_schema', 10, 2 );
		add_filter( 'geodir_bestof_widget_view_all_link', 'geodir_event_bestof_widget_view_all_link', 10, 3 );
		add_filter( 'geodir_location_count_reviews_by_term_sql', 'geodir_event_count_reviews_by_location_term_sql', 10, 7 );
		add_filter( 'the_title', 'geodir_event_title_recurring_event', 100, 2 );
		add_filter( 'the_permalink', 'geodir_event_recurring_event_link', 100 );
    }
    
    /**
     * Init GeoDirectory Event Manager when WordPress Initialises.
     */
    public function init() {
        // Before init action.
        do_action( 'geodir_event_before_init' );

        // Init action.
        do_action( 'geodir_event_init' );
    }
    
    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }
    
    /**
     * Request type.
     *
     * @param  string $type admin, frontend, ajax, cron, test or CLI.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
                break;
            case 'ajax' :
                return wp_doing_ajax();
                break;
            case 'cli' :
                return ( defined( 'WP_CLI' ) && WP_CLI );
                break;
            case 'cron' :
                return wp_doing_cron();
                break;
            case 'frontend' :
                return ( ! is_admin() || wp_doing_ajax() ) && ! wp_doing_cron();
                break;
            case 'test' :
                return defined( 'GD_TESTING_MODE' );
                break;
        }
        
        return null;
    }
	
	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return GEODIR_EVENT_PLUGIN_URL;
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( GEODIR_EVENT_PLUGIN_DIR );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}
	
	/**
	 * Enqueue styles.
	 */
	public function add_styles() {

		// Register admin styles
		// YUI Calendar
		wp_register_style( 'yui-calendar', GEODIR_EVENT_PLUGIN_URL . '/assets/yui/calendar.css', array(), '2.9.0' );
		wp_register_style( 'geodir-event', GEODIR_EVENT_PLUGIN_URL . '/assets/css/style.css', array(), GEODIR_EVENT_VERSION );

		if ( is_page() && geodir_is_page( 'add-listing' ) ) {
			wp_enqueue_style( 'yui-calendar' );
		}
		wp_enqueue_style( 'geodir-event' );
	}

	/**
	 * Enqueue scripts.
	 */
	public function add_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts
		// YUI Calendar
		wp_register_script( 'yui-calendar', GEODIR_EVENT_PLUGIN_URL . '/assets/yui/calendar.min.js', array( 'jquery' ), '2.9.0' );
		wp_register_script( 'geodir-event', GEODIR_EVENT_PLUGIN_URL . '/assets/js/common' . $suffix . '.js', array( 'jquery', 'geodir' ), GEODIR_EVENT_VERSION );
		wp_register_script( 'geodir-event-front', GEODIR_EVENT_PLUGIN_URL . '/assets/js/front' . $suffix . '.js', array( 'jquery', 'geodir', 'geodir-event' ), GEODIR_EVENT_VERSION );
		
		if ( is_page() && geodir_is_page( 'add-listing' ) ) {
			wp_enqueue_script( 'yui-calendar' );
			wp_localize_script( 'yui-calendar', 'cal_trans', geodir_event_yui_calendar_params() );
		}

		wp_enqueue_script( 'geodir-event' );
		wp_enqueue_script( 'geodir-event-front' );
		wp_localize_script( 'geodir-event', 'geodir_event_params', geodir_event_params() );
	}
}
