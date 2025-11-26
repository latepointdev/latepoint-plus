<?php
/**
 * Quick Button Module
 * 
 * Adds quick "New Item" buttons to page headers
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusQuickButtonModule')):

    /**
     * Quick Button Module Class
     */
    class OsPlusQuickButtonModule extends OsPlusModuleBase
    {

        /**
         * Module name
         */
        protected static $module_name = 'quick-button';

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
            self::enqueue_style('quick-button-admin', 'style.css');

            // Enqueue JavaScript
            self::enqueue_script('quick-button-admin', 'quick-button.js', array('jquery'));
        }
    }

endif;
