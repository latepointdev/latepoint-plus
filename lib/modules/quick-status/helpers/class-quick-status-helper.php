<?php
/**
 * Quick Status Helper
 * 
 * Adds toggle buttons to service listings for quick enable/disable
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusQuickStatusHelper')):

    class OsPlusQuickStatusHelper
    {

        /**
         * Initialize the helper
         */
        public static function init()
        {
            // Add toggle button to service listings
            add_action('latepoint_service_tile_info_rows_after', array(__CLASS__, 'add_toggle_button'), 10, 1);

            // Register AJAX handlers
            add_action('wp_ajax_lp_toggle_service', array(__CLASS__, 'handle_toggle_service'));
        }

        /**
         * Add toggle button to service tile
         */
        public static function add_toggle_button($service)
        {
            $status = $service->is_active() ? 'active' : 'disabled';
            $button_text = $status === 'active' ? __('Disable', 'latepoint-plus') : __('Enable', 'latepoint-plus');

            // Output button with data attributes - JavaScript will handle moving and styling
            echo '<button class="lp-toggle-service latepoint-btn latepoint-btn-block" data-id="' . esc_attr($service->id) . '" data-status="' . esc_attr($status) . '">';
            echo esc_html($button_text);
            echo '</button>';
        }

        /**
         * Handle AJAX request to toggle service status
         */
        public static function handle_toggle_service()
        {
            check_ajax_referer('lp_toggle_service_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => __('Unauthorized', 'latepoint-plus')));
            }

            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

            if (!$service_id) {
                wp_send_json_error(array('message' => __('Invalid service ID', 'latepoint-plus')));
            }

            $service = new OsServiceModel($service_id);

            if (!$service->id) {
                wp_send_json_error(array('message' => __('Service not found', 'latepoint-plus')));
            }

            // Toggle status
            $new_status = $service->is_active() ? LATEPOINT_SERVICE_STATUS_DISABLED : LATEPOINT_SERVICE_STATUS_ACTIVE;
            $service->status = $new_status;

            if ($service->save()) {
                $new_status_text = $new_status === LATEPOINT_SERVICE_STATUS_ACTIVE ? 'active' : 'disabled';
                $button_text = $new_status_text === 'active' ? __('Disable', 'latepoint-plus') : __('Enable', 'latepoint-plus');

                wp_send_json_success(array(
                    'new_status' => $new_status_text,
                    'button_text' => $button_text,
                    'message' => __('Service status updated', 'latepoint-plus')
                ));
            } else {
                wp_send_json_error(array('message' => __('Failed to update service status', 'latepoint-plus')));
            }
        }
    }

endif;

// Initialize the helper when LatePoint is ready
add_action('latepoint_init', array('OsPlusQuickStatusHelper', 'init'));
