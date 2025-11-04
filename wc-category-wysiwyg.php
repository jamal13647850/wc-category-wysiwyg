<?php
/**
 * Plugin Name: WC Category WYSIWYG
 * Description: Adds WYSIWYG editor to WooCommerce product category descriptions
 * Version: 2.0.0
 * Author: Sayyed Jamal Ghasemi
 * Author URI: https://jamalghasemi.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-category-wysiwyg
 * Domain Path: /languages
 * Network: false
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WC_CATEGORY_WYSIWYG_VERSION', '2.0.0');
define('WC_CATEGORY_WYSIWYG_FILE', __FILE__);
define('WC_CATEGORY_WYSIWYG_DIR', plugin_dir_path(__FILE__));
define('WC_CATEGORY_WYSIWYG_URL', plugin_dir_url(__FILE__));

// Load the main plugin class.
require_once WC_CATEGORY_WYSIWYG_DIR . 'inc/class-wc-category-wysiwyg.php';

// Initialize the plugin.
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        WC_Category_WYSIWYG::get_instance();
    }
});
