<?php
/**
 * Quick Status Module
 * 
 * Adds toggle buttons to enable/disable services directly from the list
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusQuickStatusModule')):

    /**
     * Quick Status Module Class
     */
    class OsPlusQuickStatusModule extends OsPlusModuleBase
    {

        /**
         * Module name
         */
        protected static $module_name = 'quick-status';

        /**
         * Initialize the module
         */
        public static function init()
        {
            // Enqueue admin assets
            add_action('latepoint_plus_enqueue_admin_assets', array(__CLASS__, 'enqueue_admin_assets'));
        }

        /**
         * Enqueue admin scripts and styles
         */
        public static function enqueue_admin_assets()
        {
            // Enqueue stylesheet
            self::enqueue_style('quick-status-admin', 'style.css');

            // Enqueue JavaScript
            self::enqueue_script('quick-status-admin', 'toggle.js', array('jquery'));

            // Localize script with AJAX data
            wp_localize_script('latepoint-plus-quick-status-admin', 'LPServiceToggle', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('lp_toggle_service_nonce')
            ));
        }
    }

endif;
