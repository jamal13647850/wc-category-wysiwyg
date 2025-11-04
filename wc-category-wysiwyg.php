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
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
            add_action('product_cat_add_form', [$this, 'add_wysiwyg_to_description']);
            add_action('product_cat_edit_form', [$this, 'add_wysiwyg_to_description']);
            add_action('product_cat_add_form_fields', [$this, 'add_category_nonce']);
            add_action('product_cat_edit_form_fields', [$this, 'add_category_nonce']);
            add_action('created_product_cat', [$this, 'verify_category_nonce'], 10, 2);
            add_action('edited_product_cat', [$this, 'verify_category_nonce'], 10, 2);
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $current_screen;
        
        if ($current_screen && $current_screen->id === 'product_cat') {
            // Enqueue WordPress editor scripts
            wp_enqueue_editor();
            
            // Add custom script to enhance the description field
            wp_add_inline_script('editor', '
                jQuery(document).ready(function($) {
                    // Wait for editor to be available
                    if (typeof wp.editor === "undefined") {
                        return;
                    }
                    
                    // Hide the default textarea
                    var $textarea = $("textarea#description");
                    $textarea.hide();
                    
                    // Create container for WYSIWYG editor
                    var editorContainer = $("<div id=\"description-wysiwyg-container\" style=\"margin-top: 10px;\"></div>");
                    $textarea.after(editorContainer);
                    
                    // Initialize WordPress editor
                    wp.editor.initialize("description-wysiwyg", {
                        tinymce: {
                            wpautop: true,
                            plugins: "charmap colorpicker compat3s directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview",
                            toolbar1: "formatselect bold italic | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink | wp_more",
                            toolbar2: "strikethrough hr forecolor | pastetext removeformat | charmap | outdent indent | undo redo | wp_help",
                            content_css: "' . includes_url('css/editor.css') . '",
                            height: "300px",
                            setup: function(editor) {
                                editor.on("change", function() {
                                    var content = editor.getContent();
                                    $textarea.val(content);
                                });
                                editor.on("blur", function() {
                                    var content = editor.getContent();
                                    $textarea.val(content);
                                });
                            }
                        },
                        quicktags: true,
                        mediaButtons: true
                    });
                    
                    // Copy content from hidden textarea to editor on load
                    var hiddenContent = $textarea.val();
                    if (hiddenContent) {
                        wp.editor.setContent("description-wysiwyg", hiddenContent);
                    }
                    
                    // Update hidden textarea on form submit
                    $("form#edittag, form#addtag").on("submit", function() {
                        var editorContent = wp.editor.getContent("description-wysiwyg");
                        $textarea.val(editorContent);
                    });
                });
            ');
        }
    }
    
    /**
     * Add WYSIWYG editor to category description field
     */
    public function add_wysiwyg_to_description() {
        // This method is now handled by JavaScript in enqueue_admin_scripts
        // Keeping this hook for potential future enhancements
    }
    
    /**
     * Add nonce verification for category forms
     */
    public function add_category_nonce() {
        wp_nonce_field('wc_category_wysiwyg_save', 'wc_category_wysiwyg_nonce');
    }
    
    /**
     * Verify nonce on category save
     */
    public function verify_category_nonce($term_id, $tt_id) {
        if (!isset($_POST['wc_category_wysiwyg_nonce']) || 
            !wp_verify_nonce($_POST['wc_category_wysiwyg_nonce'], 'wc_category_wysiwyg_save')) {
            wp_die(__('Security check failed', 'wc-category-wysiwyg'));
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (is_product_category()) {
            // Add custom styles for category descriptions if needed
            wp_add_inline_style('woocommerce-general', '
                .woocommerce-products-header .term-description {
                    margin-bottom: 2em;
                    line-height: 1.6;
                }
                .woocommerce-products-header .term-description p {
                    margin-bottom: 1em;
                }
                .woocommerce-products-header .term-description p:last-child {
                    margin-bottom: 0;
                }
            ');
        }
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

// Initialize plugin
new WC_Category_WYSIWYG();