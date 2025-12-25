<?php

/**
 * Plugin Name: Gallery Filter
 * Plugin URI: https://nexgenlk.com/gallery-filter
 * Description: Allows users to tag images, create customizable galleries, and filter images by categories or tags.
 * Version: 2.1.2
 * Author: NexGen Devs
 * Author URI: https://nexgenlk.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gallery-filter
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('GALLERY_FILTER_VERSION', '1.0.1');
define('GALLERY_FILTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GALLERY_FILTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GALLERY_FILTER_ASSETS_URL', GALLERY_FILTER_PLUGIN_URL . 'assets/');

// Include required classes
require_once GALLERY_FILTER_PLUGIN_DIR . 'includes/class-gallery-filter.php';
require_once GALLERY_FILTER_PLUGIN_DIR . 'includes/class-gallery-filter-admin.php';
require_once GALLERY_FILTER_PLUGIN_DIR . 'includes/class-gallery-filter-ajax.php';
require_once GALLERY_FILTER_PLUGIN_DIR . 'includes/class-gallery-filter-shortcode.php';

// Initialize plugin
function gallery_filter_init()
{
    new Gallery_Filter();
    new Gallery_Filter_Admin();
    new Gallery_Filter_AJAX();
    new Gallery_Filter_Shortcode();
}
add_action('plugins_loaded', 'gallery_filter_init');

// Activation hook
register_activation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
