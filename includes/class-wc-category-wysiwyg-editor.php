<?php
/**
 * Editor functionality for WC Category WYSIWYG
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Category_WYSIWYG_Editor {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_footer', [$this, 'remove_default_description_editor']);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $taxonomy;
        
        if (($hook === 'edit-tags.php' || $hook === 'term.php') && $taxonomy === 'product_cat') {
            wp_enqueue_style('wc-category-wysiwyg-admin', WC_CATEGORY_WYSIWYG_PLUGIN_URL . 'assets/css/admin.css', [], WC_CATEGORY_WYSIWYG_VERSION);
            wp_enqueue_script('wc-category-wysiwyg-admin', WC_CATEGORY_WYSIWYG_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WC_CATEGORY_WYSIWYG_VERSION, true);
        }
    }
    
    /**
     * Remove default description editor
     */
    public function remove_default_description_editor() {
        global $taxonomy;
        
        if ($taxonomy === 'product_cat') {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    // Remove default description field if it exists
                    $('#description').closest('.form-field').hide();
                    $('#description').closest('tr').hide();
                });
            </script>
            <?php
        }
    }
}