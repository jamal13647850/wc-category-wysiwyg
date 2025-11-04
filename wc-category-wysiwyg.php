<?php
/**
 * Plugin Name: WC Category WYSIWYG
 * Description: Adds WYSIWYG editor to WooCommerce product category descriptions
 * Version: 1.0.0
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

define('WC_CATEGORY_WYSIWYG_VERSION', '1.0.0');
define('WC_CATEGORY_WYSIWYG_PLUGIN_DIR', __DIR__);
define('WC_CATEGORY_WYSIWYG_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WC_Category_WYSIWYG {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'init']);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }
        
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once WC_CATEGORY_WYSIWYG_PLUGIN_DIR . '/includes/class-wc-category-wysiwyg-editor.php';
        require_once WC_CATEGORY_WYSIWYG_PLUGIN_DIR . '/includes/class-wc-category-wysiwyg-frontend.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            add_action('product_cat_add_form_fields', [$this, 'add_category_editor_field']);
            add_action('product_cat_edit_form_fields', [$this, 'edit_category_editor_field']);
            add_action('created_product_cat', [$this, 'save_category_editor_field']);
            add_action('edited_product_cat', [$this, 'save_category_editor_field']);
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }
    
    /**
     * Add editor field to category add form
     */
    public function add_category_editor_field() {
        ?>
        <div class="form-field term-description-wrap">
            <label for="description"><?php _e('Description', 'wc-category-wysiwyg'); ?></label>
            <?php 
            wp_editor('', 'description', [
                'textarea_name' => 'description',
                'textarea_rows' => 10,
                'media_buttons' => true,
                'teeny' => false,
                'wpautop' => true
            ]);
            ?>
            <p><?php _e('The description is not prominent by default; however, some themes may show it.', 'wc-category-wysiwyg'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add editor field to category edit form
     */
    public function edit_category_editor_field($term) {
        ?>
        <tr class="form-field term-description-wrap">
            <th scope="row" valign="top"><label for="description"><?php _e('Description', 'wc-category-wysiwyg'); ?></label></th>
            <td>
                <?php 
                wp_editor(html_entity_decode($term->description), 'description', [
                    'textarea_name' => 'description',
                    'textarea_rows' => 10,
                    'media_buttons' => true,
                    'teeny' => false,
                    'wpautop' => true
                ]);
                ?>
                <p class="description"><?php _e('The description is not prominent by default; however, some themes may show it.', 'wc-category-wysiwyg'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save category editor field
     */
    public function save_category_editor_field($term_id) {
        if (isset($_POST['description'])) {
            $description = wp_kses_post($_POST['description']);
            wp_update_term($term_id, 'product_cat', ['description' => $description]);
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Enqueue styles if needed for frontend display
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('WC Category WYSIWYG requires WooCommerce to be installed and active.', 'wc-category-wysiwyg'); ?></p>
        </div>
        <?php
    }
}

// Initialize the plugin
new WC_Category_WYSIWYG();