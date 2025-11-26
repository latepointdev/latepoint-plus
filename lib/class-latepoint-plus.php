<?php
/**
 * LatePoint+ Main Class
 * 
 * Coordinates all modules and integrates with LatePoint core
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('LatePointPlus')):

    /**
     * Main LatePoint+ Class
     */
    class LatePointPlus
    {

        /**
         * Plugin version
         */
        public $version = '1.0.0';
        public $db_version = '1.0.0';
        public $addon_name = 'latepoint-plus';

        /**
         * Constructor
         */
        public function __construct()
        {
            // Load module base class immediately - must be loaded before modules
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/class-module-base.php';

            // Call includes directly - the latepoint_includes hook has already fired by the time we load
            $this->includes();

            // Register hooks
            $this->init_hooks();
        }

        /**
         * Initialize hooks
         */
        public function init_hooks()
        {
            // Hook into the latepoint initialization action and initialize this addon
            add_action('latepoint_init', array($this, 'latepoint_init'));

            // Register this addon with LatePoint
            add_filter('latepoint_installed_addons', array($this, 'register_addon'));

            // Load admin scripts and styles
            add_action('latepoint_admin_enqueue_scripts', array($this, 'load_admin_scripts_and_styles'));

            // Initialize WordPress
            add_action('init', array($this, 'init'), 0);

            // Handle addon deactivation
            add_action('latepoint_on_addon_deactivate', array($this, 'on_addon_deactivate'), 10, 2);

            // BULK SERVICES: Register hook directly here (like original plugin does)
            // This hook needs to be registered early, not waiting for latepoint_init_hooks
            add_action('latepoint_order_quick_edit_form_content_after', array($this, 'add_bulk_services_to_order_form'));
        }

        /**
         * Initialize when LatePoint is ready
         * This is called by the latepoint_init hook
         */
        public function latepoint_init()
        {
            // Initialize the addon with LatePoint's router system
            // This is CRITICAL for LatePoint to recognize and route to our addon
            LatePoint\Cerber\Router::init_addon();
        }

        /**
         * Add bulk services interface to order form
         * This is called by the latepoint_order_quick_edit_form_content_after hook
         */
        public function add_bulk_services_to_order_form($order)
        {
            // Delegate to the Bulk Services module class if not already loaded
            if (!class_exists('OsPlusBulkServicesModule')) {
                require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/bulk-services/class-bulk-services-module.php';
            }

            // Call the module's method to add the interface
            OsPlusBulkServicesModule::add_bulk_services_to_order_form($order);
        }

        /**
         * Load required files
         */
        public function includes()
        {
            // Load all module helpers
            $this->load_module_helpers();

            // Load all module controllers
            $this->load_module_controllers();

            // Load module classes so they're available when needed
            $this->load_module_classes();
        }

        /**
         * Load module helpers
         */
        private function load_module_helpers()
        {
            // Bulk Services Module
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/bulk-services/helpers/class-bulk-services-helper.php';

            // Quick Status Module
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/quick-status/helpers/class-quick-status-helper.php';

            // Quick Button Module
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/quick-button/helpers/class-quick-button-helper.php';
        }

        /**
         * Load module controllers
         */
        private function load_module_controllers()
        {
            // Bulk Services Module
            if (file_exists(LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/bulk-services/controllers/class-bulk-services-controller.php')) {
                require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/bulk-services/controllers/class-bulk-services-controller.php';
            }
        }

        /**
         * Load module classes
         */
        private function load_module_classes()
        {
            // Bulk Services Module
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/bulk-services/class-bulk-services-module.php';

            // Quick Status Module  
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/quick-status/class-quick-status-module.php';

            // Quick Button Module
            require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/quick-button/class-quick-button-module.php';

            // Initialize modules::init();
            OsPlusQuickStatusModule::init();
            OsPlusQuickButtonModule::init();
        }

        /**
         * Init when WordPress initializes
         */
        public function init()
        {
            // Set up localization
            $this->load_plugin_textdomain();
        }

        /**
         * Load plugin text domain for translations
         */
        public function load_plugin_textdomain()
        {
            load_plugin_textdomain(
                'latepoint-plus',
                false,
                dirname(plugin_basename(LATEPOINT_PLUS_PLUGIN_FILE)) . '/languages'
            );
        }

        /**
         * Register addon with LatePoint
         */
        public function register_addon($installed_addons)
        {
            $installed_addons[] = array(
                'name' => $this->addon_name,
                'db_version' => $this->db_version,
                'version' => $this->version
            );

            return $installed_addons;
        }

        /**
         * Load admin scripts and styles
         */
        public function load_admin_scripts_and_styles()
        {
            // Each module will enqueue its own assets
            do_action('latepoint_plus_enqueue_admin_assets');
        }

        /**
         * Handle addon deactivation cleanup
         */
        public function on_addon_deactivate($addon_name, $addon_version)
        {
            if ($addon_name !== $this->addon_name) {
                return;
            }

            // Clean up router cache if using LatePoint Pro
            if (class_exists('LatePoint\\Cerber\\RouterPro')) {
                LatePoint\Cerber\RouterPro::wipe($addon_name, $addon_version);
            }

            // Remove from routed addons list
            if (class_exists('OsAddonsHelper')) {
                OsAddonsHelper::remove_routed_addon($addon_name);
            }
        }

        /**
         * Get plugin URL for assets
         */
        public static function plugin_url()
        {
            return LATEPOINT_PLUS_PLUGIN_URL;
        }

        /**
         * Get plugin path
         */
        public static function plugin_path()
        {
            return LATEPOINT_PLUS_PLUGIN_PATH;
        }
    }

endif;
