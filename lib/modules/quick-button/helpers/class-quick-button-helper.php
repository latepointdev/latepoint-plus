<?php
/**
 * Quick Button Helper
 * 
 * Adds quick "New Item" buttons to page headers
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusQuickButtonHelper')):

    class OsPlusQuickButtonHelper
    {

        /**
         * Initialize the helper
         */
        public static function init()
        {
            // Add quick button to page headers
            add_action('admin_footer', array(__CLASS__, 'add_quick_button_to_header'));
        }

        /**
         * Check if LatePoint Pro is active
         */
        public static function is_pro_active()
        {
            return class_exists('OsBundlesController') || class_exists('OsCustomFieldsController');
        }

        /**
         * Get the configuration for pages that should have quick buttons
         * Maps route patterns to their button configurations
         */
        public static function get_button_configs()
        {
            $is_pro_active = self::is_pro_active();

            $configs = array();

            // Core features (always available)
            $configs['services__index'] = array(
                'label' => __('New Service', 'latepoint-plus'),
                'route' => 'services__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'service__create'
            );

            $configs['agents__index'] = array(
                'label' => __('New Agent', 'latepoint-plus'),
                'route' => 'agents__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'agent__create'
            );

            $configs['customers__index'] = array(
                'label' => __('New Customer', 'latepoint-plus'),
                'route' => 'customers__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'customer__create'
            );

            $configs['locations__index'] = array(
                'label' => __('New Location', 'latepoint-plus'),
                'route' => 'locations__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'location__create'
            );

            $configs['service_categories__index'] = array(
                'label' => __('New Category', 'latepoint-plus'),
                'route' => 'service_categories__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'service_category__create'
            );

            $configs['service_extras__index'] = array(
                'label' => __('New Extra', 'latepoint-plus'),
                'route' => 'service_extras__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'service_extra__create'
            );

            $configs['processes__index'] = array(
                'label' => __('New Process', 'latepoint-plus'),
                'route' => 'processes__new_form',
                'icon' => 'latepoint-icon-plus',
                'permission' => 'process__create'
            );

            // Pro features (only if Pro is active)
            if ($is_pro_active) {
                $configs['bundles__index'] = array(
                    'label' => __('New Bundle', 'latepoint-plus'),
                    'route' => 'bundles__new_form',
                    'icon' => 'latepoint-icon-plus',
                    'permission' => 'bundle__create'
                );

                $configs['custom_fields__for_booking'] = array(
                    'label' => __('New Custom Field', 'latepoint-plus'),
                    'route' => 'custom_fields__new_form',
                    'icon' => 'latepoint-icon-plus',
                    'permission' => 'custom_field__create',
                    'params' => array('for' => 'booking')
                );

                $configs['custom_fields__for_customer'] = array(
                    'label' => __('New Custom Field', 'latepoint-plus'),
                    'route' => 'custom_fields__new_form',
                    'icon' => 'latepoint-icon-plus',
                    'permission' => 'custom_field__create',
                    'params' => array('for' => 'customer')
                );
            }

            // Allow other plugins to modify button configs
            $configs = apply_filters('latepoint_quick_button_configs', $configs);

            return $configs;
        }

        /**
         * Get current route name from URL parameters
         */
        public static function get_current_route()
        {
            if (!isset($_GET['route_name'])) {
                return '';
            }
            return sanitize_text_field($_GET['route_name']);
        }

        /**
         * Check if we're on a LatePoint admin page
         */
        public static function is_latepoint_page()
        {
            return (is_admin() && isset($_GET['page']) && $_GET['page'] === 'latepoint');
        }

        /**
         * Add quick button to page header via JavaScript
         */
        public static function add_quick_button_to_header()
        {
            // Only run on LatePoint pages
            if (!self::is_latepoint_page()) {
                return;
            }

            $current_route = self::get_current_route();
            $button_configs = self::get_button_configs();

            // Check if current route has a button config
            if (!isset($button_configs[$current_route])) {
                return;
            }

            $config = $button_configs[$current_route];

            // Check permissions if specified
            if (isset($config['permission']) && class_exists('OsRolesHelper')) {
                if (!OsRolesHelper::can_user($config['permission'])) {
                    return;
                }
            }

            // Build the link URL
            if (class_exists('OsRouterHelper')) {
                $params = isset($config['params']) ? $config['params'] : array();
                $link_url = OsRouterHelper::build_link($config['route'], $params);
            } else {
                return;
            }

            // Output the button HTML and JavaScript to inject it
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    // Create the quick button
                    var quickButton = $('<a>', {
                        'href': <?php echo wp_json_encode($link_url); ?>,
                        'class': 'latepoint-btn latepoint-btn-primary latepoint-quick-add-btn',
                        'html': '<i class="latepoint-icon <?php echo esc_attr($config['icon']); ?>"></i><span><?php echo esc_html($config['label']); ?></span>'
                    });

                    // Find the page header and add the button
                    var pageHeader = $('.page-header-w');
                    if (pageHeader.length) {
                        // Check if there's already a button container
                        var buttonContainer = pageHeader.find('.page-header-actions');
                        if (!buttonContainer.length) {
                            // Create button container
                            buttonContainer = $('<div>', {
                                'class': 'page-header-actions'
                            });
                            pageHeader.append(buttonContainer);
                        }

                        // Add button to end of header
                        buttonContainer.append(quickButton);
                    }
                });
            </script>
            <?php
        }
    }

endif;

// Initialize the helper when LatePoint is ready
add_action( 'latepoint_init', array( 'OsPlusQuickButtonHelper', 'init' ) );
