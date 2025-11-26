<?php
/**
 * Module Base Class
 * 
 * Base class that all modules can extend for common functionality
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('OsPlusModuleBase')):

    /**
     * Base class for LatePoint+ modules
     */
    abstract class OsPlusModuleBase
    {

        /**
         * Module name/slug
         */
        protected static $module_name = '';

        /**
         * Module version
         */
        protected static $module_version = '1.0.0';

        /**
         * Initialize the module
         * Must be implemented by child classes
         */
        abstract public static function init();

        /**
         * Get module asset URL
         */
        protected static function get_asset_url($path)
        {
            return LATEPOINT_PLUS_PLUGIN_URL . 'assets/' . static::$module_name . '/' . ltrim($path, '/');
        }

        /**
         * Get module path
         */
        protected static function get_module_path($path = '')
        {
            return LATEPOINT_PLUS_PLUGIN_PATH . 'lib/modules/' . static::$module_name . '/' . ltrim($path, '/');
        }

        /**
         * Enqueue module stylesheet
         */
        protected static function enqueue_style($handle, $file, $deps = array())
        {
            wp_enqueue_style(
                'latepoint-plus-' . $handle,
                static::get_asset_url('css/' . $file),
                $deps,
                static::$module_version
            );
        }

        /**
         * Enqueue module script
         */
        protected static function enqueue_script($handle, $file, $deps = array('jquery'))
        {
            wp_enqueue_script(
                'latepoint-plus-' . $handle,
                static::get_asset_url('js/' . $file),
                $deps,
                static::$module_version,
                true
            );
        }
    }

endif;
