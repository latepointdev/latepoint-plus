<?php
/**
 * GitHub Updater Class
 * 
 * Enables automatic updates from GitHub releases for the plugin
 * Works with both public and private repositories
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LatePointPlusGitHubUpdater')):

    class LatePointPlusGitHubUpdater
    {
        private $file;
        private $plugin;
        private $basename;
        private $active;
        private $username;
        private $repository;
        private $github_response;

        public function __construct($file)
        {
            $this->file = $file;
            $this->plugin = plugin_basename($file);
            $this->basename = dirname($this->plugin);
            $this->active = is_plugin_active($this->plugin);

            // GitHub repository details
            $this->username = 'latepointdev';
            $this->repository = 'latepoint-plus';

            add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
            add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
            add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        }

        /**
         * Get information from GitHub API
         */
        private function get_repository_info()
        {
            if (is_null($this->github_response)) {
                $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository);

                $args = array(
                    'timeout' => 15,
                    'headers' => array(
                        'Accept' => 'application/vnd.github.v3+json',
                    )
                );

                // Add authentication for private repos if token is defined
                if (defined('LATEPOINT_PLUS_GITHUB_TOKEN')) {
                    $args['headers']['Authorization'] = 'token ' . LATEPOINT_PLUS_GITHUB_TOKEN;
                }

                $response = wp_remote_get($request_uri, $args);

                if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                    return false;
                }

                $this->github_response = json_decode(wp_remote_retrieve_body($response));
            }

            return $this->github_response;
        }

        /**
         * Modify the plugin update transient
         */
        public function modify_transient($transient)
        {
            if (empty($transient->checked)) {
                return $transient;
            }

            $repo_info = $this->get_repository_info();

            if ($repo_info === false) {
                return $transient;
            }

            // Get the latest version from GitHub
            $latest_version = isset($repo_info->tag_name) ? ltrim($repo_info->tag_name, 'v') : false;

            if (!$latest_version) {
                return $transient;
            }

            // Get current plugin version
            $plugin_data = get_plugin_data($this->file);
            $current_version = $plugin_data['Version'];

            // Compare versions
            if (version_compare($current_version, $latest_version, '<')) {
                $plugin = array(
                    'slug' => $this->basename,
                    'new_version' => $latest_version,
                    'url' => $repo_info->html_url,
                    'package' => $this->get_download_url($repo_info),
                    'tested' => '6.4',
                    'requires_php' => '7.4',
                );

                $transient->response[$this->plugin] = (object) $plugin;
            }

            return $transient;
        }

        /**
         * Get the download URL for the latest release
         */
        private function get_download_url($repo_info)
        {
            // Try to find a .zip asset
            if (isset($repo_info->assets) && is_array($repo_info->assets)) {
                foreach ($repo_info->assets as $asset) {
                    if (isset($asset->name) && strpos($asset->name, '.zip') !== false) {
                        return $asset->browser_download_url;
                    }
                }
            }

            // Fallback to zipball URL
            return isset($repo_info->zipball_url) ? $repo_info->zipball_url : false;
        }

        /**
         * Show plugin information popup
         */
        public function plugin_popup($result, $action, $args)
        {
            if ($action !== 'plugin_information') {
                return $result;
            }

            if (!isset($args->slug) || $args->slug !== $this->basename) {
                return $result;
            }

            $repo_info = $this->get_repository_info();

            if ($repo_info === false) {
                return $result;
            }

            $plugin = array(
                'name' => 'LatePoint+',
                'slug' => $this->basename,
                'version' => ltrim($repo_info->tag_name, 'v'),
                'author' => '<a href="https://latepoint.dev">Latepoint Dev</a>',
                'homepage' => $repo_info->html_url,
                'requires' => '5.0',
                'tested' => '6.4',
                'requires_php' => '7.4',
                'downloaded' => 0,
                'last_updated' => $repo_info->published_at,
                'sections' => array(
                    'description' => $repo_info->body ? $this->parse_markdown($repo_info->body) : 'Unified addon for LatePoint providing bulk services, quick status toggles, and quick action buttons.',
                    'changelog' => $repo_info->body ? $this->parse_markdown($repo_info->body) : 'See release notes on GitHub.',
                ),
                'download_link' => $this->get_download_url($repo_info),
            );

            return (object) $plugin;
        }

        /**
         * Simple markdown to HTML converter
         */
        private function parse_markdown($text)
        {
            // Convert headers
            $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
            $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
            $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);

            // Convert lists
            $text = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $text);
            $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);

            // Convert line breaks
            $text = nl2br($text);

            return $text;
        }

        /**
         * Perform additional actions after installation
         */
        public function after_install($response, $hook_extra, $result)
        {
            global $wp_filesystem;

            $install_directory = plugin_dir_path($this->file);
            $wp_filesystem->move($result['destination'], $install_directory);
            $result['destination'] = $install_directory;

            if ($this->active) {
                activate_plugin($this->plugin);
            }

            return $result;
        }
    }

endif;
