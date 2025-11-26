<?php
/**
 * Plugin Name: LatePoint+
 * Plugin URI: https://latepoint.dev
 * Description: Unified addon for LatePoint providing bulk services, quick status toggles, quick action buttons, and more
 * Version: 1.0.0
 * Author: Latepoint Dev
 * Author URI: https://latepoint.dev/
 * Text Domain: latepoint-plus
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define('LATEPOINT_PLUS_VERSION', '1.0.0');
define('LATEPOINT_PLUS_DB_VERSION', '1.0.0');
define('LATEPOINT_PLUS_PLUGIN_FILE', __FILE__);
define('LATEPOINT_PLUS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LATEPOINT_PLUS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main LatePoint+ initialization function
 * Checks for LatePoint core and initializes the plugin
 */
function latepoint_plus_init()
{
    // Check if LatePoint is active
    if (!class_exists('LatePoint')) {
        // Add admin notice if LatePoint is not active
        add_action('admin_notices', 'latepoint_plus_missing_latepoint_notice');
        add_action('network_admin_notices', 'latepoint_plus_missing_latepoint_notice');
        return;
    }

    // Load the main plugin class
    require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/class-latepoint-plus.php';

    // Initialize the plugin
    $GLOBALS['LATEPOINT_PLUS'] = new LatePointPlus();

    // Initialize GitHub updater for automatic updates
    require_once LATEPOINT_PLUS_PLUGIN_PATH . 'lib/class-github-updater.php';
    if (class_exists('LatePointPlusGitHubUpdater')) {
        new LatePointPlusGitHubUpdater(__FILE__);
    }
}

/**
 * Display admin notice when LatePoint is not installed/activated
 */
function latepoint_plus_missing_latepoint_notice()
{
    // Only show to users who can activate plugins
    if (!(current_user_can('activate_plugins') && current_user_can('install_plugins'))) {
        return;
    }

    // Don't show on plugin update screen
    $screen = get_current_screen();
    if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
        return;
    }

    $latepoint_plugin_path = 'latepoint/latepoint.php';

    // Check if LatePoint is installed but not activated
    if (file_exists(WP_PLUGIN_DIR . '/latepoint/latepoint.php')) {
        $action_url = wp_nonce_url(
            'plugins.php?action=activate&plugin=' . $latepoint_plugin_path . '&plugin_status=all&paged=1&s',
            'activate-plugin_' . $latepoint_plugin_path
        );
        $button_label = __('Activate LatePoint', 'latepoint-plus');
    } else {
        $action_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=latepoint'),
            'install-plugin_latepoint'
        );
        $button_label = __('Install LatePoint', 'latepoint-plus');
    }

    $button = '<p><a href="' . esc_url($action_url) . '" class="button-primary">' . esc_html($button_label) . '</a></p>';
    $message = sprintf(
        __('%1$sLatePoint+%2$s requires %1$sLatePoint%2$s core plugin to be installed and activated.', 'latepoint-plus'),
        '<strong>',
        '</strong>'
    );

    printf(
        '<div class="notice notice-error"><p>%s</p>%s</div>',
        wp_kses_post($message),
        wp_kses_post($button)
    );
}

/**
 * Display admin notice when conflicting addons are detected
 */
function latepoint_plus_conflicting_addons_notice()
{
    $conflicting_plugins = array();
    $active_plugins = get_option('active_plugins', array());

    // Check for conflicting individual addons
    $conflicts = array(
        'latepoint-bulk-services/latepoint-bulk-services.php' => 'LatePoint Bulk Services',
        'latepoint-quick-status/latepoint-quick-status.php' => 'LatePoint Quick Status',
        'latepoint-quick-button/latepoint-quick-button.php' => 'LatePoint Quick Button',
    );

    foreach ($conflicts as $plugin_path => $plugin_name) {
        if (in_array($plugin_path, $active_plugins, true)) {
            $conflicting_plugins[] = $plugin_name;
        }
    }

    if (!empty($conflicting_plugins)) {
        $message = sprintf(
            __('%1$sLatePoint+%2$s has detected the following conflicting plugins that should be deactivated: %3$s. These features are now included in LatePoint+.', 'latepoint-plus'),
            '<strong>',
            '</strong>',
            '<strong>' . implode(', ', $conflicting_plugins) . '</strong>'
        );

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            wp_kses_post($message)
        );
    }
}

/**
 * Plugin activation hook
 */
function latepoint_plus_activate()
{
    // Check for LatePoint
    if (!class_exists('LatePoint')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            wp_kses_post(__('<strong>LatePoint+</strong> requires <strong>LatePoint</strong> to be installed and activated.', 'latepoint-plus')),
            esc_html__('Plugin Activation Error', 'latepoint-plus'),
            array('back_link' => true)
        );
    }

    // Trigger LatePoint addon activation hook
    do_action('latepoint_on_addon_activate', 'latepoint-plus', LATEPOINT_PLUS_VERSION);

    // Set activation flag for welcome notice
    set_transient('latepoint_plus_activated', true, 60);
}

/**
 * Plugin deactivation hook
 */
function latepoint_plus_deactivate()
{
    // Trigger LatePoint addon deactivation hook
    do_action('latepoint_on_addon_deactivate', 'latepoint-plus', LATEPOINT_PLUS_VERSION);

    // Clean up if needed
    delete_transient('latepoint_plus_activated');
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'latepoint_plus_activate');
register_deactivation_hook(__FILE__, 'latepoint_plus_deactivate');

// Check for conflicting addons
add_action('admin_notices', 'latepoint_plus_conflicting_addons_notice');

// Initialize the plugin after all plugins are loaded
add_action('plugins_loaded', 'latepoint_plus_init', 20);
