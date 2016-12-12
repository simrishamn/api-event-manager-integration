<?php

namespace EventManagerIntegration;

class App
{
    /*
     * Set to 'dev' or 'min'
     */
    public static $assetSuffix = 'dev';

    public function __construct()
    {
        /* Activation hooks */
        register_activation_hook(plugin_basename(__FILE__), '\EventManagerIntegration\App::addCronJob');
        register_deactivation_hook(plugin_basename(__FILE__), '\EventManagerIntegration\App::removeCronJob');

        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'), 950);
        add_action('wp_enqueue_scripts', array($this, 'enqueueFront'), 950);

        /* Register cron action */
        add_action('import_events_daily', array($this, 'importEventsCron'));

        new PostTypes\Events();
        new Helper\Acf();
        new Widget\DisplayEvents();
        new Admin\Options();
        new Admin\AdminDisplayEvent();

        /* Modularity modules */
        add_action('Modularity', function () {
            new Module\EventModule();
        });

        add_action( 'widgets_init', function(){
            register_widget( 'EventManagerIntegration\Widget\DisplayEvents' );
        });

        // TA BORT
        add_action('admin_menu', array($this, 'createParsePage'));
    }

    /**
     * Enqueue required styles and scripts on front views
     * @return void
     */
    public function enqueueFront()
    {
        // Styles
        wp_register_style('event-integration', EVENTMANAGERINTEGRATION_URL . '/dist/css/event-manager-integration.' . self::$assetSuffix . '.css');
        wp_enqueue_style('event-integration');

        // Scripts
        wp_enqueue_script('vendor-pagination', EVENTMANAGERINTEGRATION_URL . '/source/js/vendor/jquery.blade-pagination.js', 'jquery', false, true);

        wp_register_script('ajax-pagination', EVENTMANAGERINTEGRATION_URL . '/dist/js/event-pagination.' . self::$assetSuffix . '.js', 'jquery', false, true);
        wp_enqueue_script('ajax-pagination');

        wp_localize_script('ajax-pagination', 'ajaxpagination', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));

        global $wp_query;
        wp_localize_script( 'ajax-pagination', 'ajaxpagination', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'query_vars' => json_encode( $wp_query->query )
        ));
    }

    /**
     * Enqueue required styles and scripts on admin ui
     * @return void
     */
    public function enqueueAdmin()
    {
        global $current_screen;
        $type = $current_screen->post_type;
        if ($type != 'event') {
            return;
        }

        // Styles
        wp_register_style('event-integration', EVENTMANAGERINTEGRATION_URL . '/dist/css/event-manager-integration-ui.' . self::$assetSuffix . '.css');
        wp_enqueue_style('event-integration');

        // Scripts
        wp_register_script('event-integration', EVENTMANAGERINTEGRATION_URL . '/dist/js/event-manager-integration.' . self::$assetSuffix . '.js', true);
        wp_localize_script('event-integration', 'eventintegration', array(
            'loading'   => __("Loading", 'event-integration'),
        ));
        wp_enqueue_script('event-integration');
    }

    /**
     * Start cron jobs
     * @return void
     */
    public function importEventsCron()
    {
        if (get_field('event_daily_import', 'option') == true) {
            global $wpdb;
            $db_table = $wpdb->prefix . "integrate_occasions";
            $occasion = $wpdb->get_results("SELECT start_date FROM $db_table ORDER BY start_date ASC LIMIT 1", OBJECT);

            $from_date = count($occasion) == 1 ? date('Y-m-d', strtotime($occasion[0]->start_date)) : date('Y-m-d');
            $days_ahead = ! empty(get_field('days_ahead', 'options')) ? absint(get_field('days_ahead', 'options')) : 30;
            $to_date = date('Y-m-d', strtotime("midnight now + {$days_ahead} days"));

            $api_url = 'http://eventmanager.dev/json/wp/v2/event/time?start=' . $from_date . '&end=' . $to_date;
            $importer = new \EventManagerIntegration\Parser\HbgEventApi($api_url);
            // TA BORT
            file_put_contents(dirname(__FILE__)."/Log/import_events.log", "Event parser last run: ".date("Y-m-d H:i:s"));
        }
    }

    public static function addCronJob()
    {
        wp_schedule_event(time(), 'hourly', 'import_events_daily');
    }

    public static function removeCronJob()
    {
        wp_clear_scheduled_hook('import_events_daily');
    }

    /**
     * Creates necessary database table on plugin activation
     */
    public static function databaseCreation()
    {
        global $wpdb;
        global $event_db_version;
        $table_name = $wpdb->prefix . 'integrate_occasions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
        ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        door_time DATETIME DEFAULT NULL,
        status VARCHAR(50) DEFAULT NULL,
        exception_information VARCHAR(400) DEFAULT NULL,
        content_mode VARCHAR(50) DEFAULT NULL,
        content LONGTEXT DEFAULT NULL,
        PRIMARY KEY  (ID)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option('event_db_version', $event_db_version);
    }

    // TA BORT
    /**
     * Creates a admin page to trigger update data function
     * @return void
     */
    public function createParsePage()
    {
        add_submenu_page(
            null,
            __('Import events', 'event-integration'),
            __('Import events', 'event-integration'),
            'edit_posts',
            'import-events',
            function () {

            global $wpdb;
            $db_table = $wpdb->prefix . "integrate_occasions";
            $occasion = $wpdb->get_results("SELECT start_date FROM $db_table ORDER BY start_date ASC LIMIT 1", OBJECT);

            $from_date = count($occasion) == 1 ? date('Y-m-d', strtotime($occasion[0]->start_date)) : date('Y-m-d');
            $days_ahead = ! empty(get_field('days_ahead', 'options')) ? absint(get_field('days_ahead', 'options')) : 30;
            $to_date = date('Y-m-d', strtotime("midnight now + {$days_ahead} days"));

            $api_url = 'http://eventmanager.dev/json/wp/v2/event/time?start=' . $from_date . '&end=' . $to_date;

            $importer = new \EventManagerIntegration\Parser\HbgEventApi($api_url);
            });

            add_submenu_page(
            null,
            __('Delete all events', 'event-integration'),
            __('Delete all events', 'event-integration'),
            'edit_posts',
            'delete-all-events',
            function () {
                global $wpdb;
                $delete = $wpdb->query("TRUNCATE TABLE `event_postmeta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_posts`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_stream`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_stream_meta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_term_relationships`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_term_taxonomy`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_termmeta`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_terms`");
                $delete = $wpdb->query("TRUNCATE TABLE `event_integrate_occasions`");
            });
    }

}
