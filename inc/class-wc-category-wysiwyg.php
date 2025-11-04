<?php
/**
 * WC_Category_WYSIWYG class.
 *
 * Main plugin class that handles WooCommerce product category WYSIWYG editor.
 * Replaces the default simple textarea description field with WordPress editor.
 *
 * @package WC_Category_WYSIWYG
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 *
 * Handles initialization and management of WYSIWYG editor for WooCommerce category descriptions.
 * Provides RTL support and proper nonce verification for security.
 */
class WC_Category_WYSIWYG {

    /**
     * Instance of the class.
     *
     * @var WC_Category_WYSIWYG
     * @since 2.0.0
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * Returns the singleton instance of the class, creating it if necessary.
     * Ensures only one instance of the plugin runs at a time.
     *
     * @return WC_Category_WYSIWYG
     * @since 2.0.0
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * Sets up hooks for admin and frontend functionality.
     *
     * @since 2.0.0
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     *
     * Sets up all necessary WordPress hooks for admin and frontend.
     * Hooks are only added if on admin side or when needed on frontend.
     *
     * @return void
     * @since 2.0.0
     */
    private function init_hooks() {
        if (is_admin()) {
            // Admin-side hooks for category add/edit pages.
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
            add_action('product_cat_add_form_fields', [$this, 'render_description_field_add']);
            add_action('product_cat_edit_form_fields', [$this, 'render_description_field_edit'], 10, 2);
            add_action('created_product_cat', [$this, 'save_category_description'], 10, 2);
            add_action('edited_product_cat', [$this, 'save_category_description'], 10, 2);
        }

        // Frontend styles for category descriptions.
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_styles']);
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * Loads editor styles when on the product category add/edit page.
     * Ensures WordPress editor is properly loaded with all required dependencies.
     *
     * @param string $hook The admin page hook.
     * @return void
     * @since 2.0.0
     */
    public function enqueue_admin_scripts($hook) {
        global $current_screen;

        if (!$current_screen || $current_screen->id !== 'product_cat') {
            return;
        }

        // Enqueue the WordPress editor and media libraries.
        wp_enqueue_editor();
        wp_enqueue_media();

        // Add custom styles for editor container.
        wp_add_inline_style('wp-editor', '
            .wc-category-description-wrapper {
                margin: 15px 0;
                padding: 15px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .wc-category-description-wrapper label {
                display: block;
                margin-bottom: 10px;
                font-weight: 600;
                color: #333;
            }
            .wp-editor-container {
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }
        ');
    }

    /**
     * Render description field on category add page.
     *
     * Replaces the default simple textarea with WordPress editor.
     * Adds nonce field for security verification.
     *
     * @return void
     * @since 2.0.0
     */
    public function render_description_field_add() {
        $this->render_editor_field('');
    }

    /**
     * Render description field on category edit page.
     *
     * Loads existing category description into editor.
     * Gets the description content from term meta or term description.
     *
     * @param object $term The category term object.
     * @param string $taxonomy The taxonomy name.
     * @return void
     * @since 2.0.0
     */
    public function render_description_field_edit($term, $taxonomy) {
        // Get existing description.
        $description = isset($term->description) ? $term->description : '';
        $this->render_editor_field($description);
    }

    /**
     * Render the WYSIWYG editor field.
     *
     * Core method that renders the WordPress editor for category description.
     * Includes nonce field for security and proper editor configuration.
     *
     * @param string $content The existing content to populate the editor with.
     * @return void
     * @since 2.0.0
     */
    private function render_editor_field($content = '') {
        // Nonce field for security.
        wp_nonce_field('wc_category_wysiwyg_save', 'wc_category_wysiwyg_nonce');
        ?>
        <div class="wc-category-description-wrapper">
            <label for="description"><?php esc_html_e('Description', 'wc-category-wysiwyg'); ?></label>
            <?php
            wp_editor(
                $content,
                'description',
                array(
                    'media_buttons' => true,
                    'textarea_name' => 'description',
                    'textarea_rows' => 10,
                    'teeny' => false,
                    'wpautop' => true,
                    'tinymce' => array(
                        'toolbar1' => 'formatselect bold italic underline strikethrough | bullist numlist | blockquote | alignleft aligncenter alignright alignjustify | link unlink | wp_more wp_page',
                        'toolbar2' => 'removeformat charmap | outdent indent | undo redo | wp_help',
                        'toolbar3' => '',
                        'wpautop' => true,
                        'apply_source_formatting' => true,
                        'block_formats' => 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Preformatted=pre',
                        'height' => 300,
                    ),
                    'quicktags' => true,
                )
            );
            ?>
        </div>
        <?php
    }

    /**
     * Save category description.
     *
     * Validates nonce and saves the description from the editor.
     * Ensures security by verifying the nonce before processing.
     * Escapes content appropriately to prevent XSS attacks.
     *
     * @param int $term_id The category term ID.
     * @param int $tt_id The term taxonomy ID.
     * @return void
     * @since 2.0.0
     */
    public function save_category_description($term_id, $tt_id) {
        // Verify nonce.
        if (!isset($_POST['wc_category_wysiwyg_nonce']) || 
            !wp_verify_nonce($_POST['wc_category_wysiwyg_nonce'], 'wc_category_wysiwyg_save')) {
            return;
        }

        // Get description from POST.
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';

        // Update the term description.
        wp_update_term($term_id, 'product_cat', array(
            'description' => $description,
        ));
    }

    /**
     * Enqueue frontend styles.
     *
     * Adds custom styles for category descriptions on the frontend.
     * Ensures proper formatting of WYSIWYG content in category pages.
     * Supports RTL layouts properly.
     *
     * @return void
     * @since 2.0.0
     */
    public function enqueue_frontend_styles() {
        if (!is_product_category()) {
            return;
        }

        wp_add_inline_style('woocommerce-general', $this->get_frontend_styles());
    }

    /**
     * Get frontend styles.
     *
     * Returns CSS styles for proper rendering of category descriptions.
     * Ensures proper spacing and formatting for WYSIWYG content.
     * Handles RTL layout gracefully.
     *
     * @return string CSS styles.
     * @since 2.0.0
     */
    private function get_frontend_styles() {
        return '
            .woocommerce-products-header .term-description {
                margin-bottom: 2em;
                line-height: 1.6;
                word-break: break-word;
            }
            .woocommerce-products-header .term-description p {
                margin-bottom: 1em;
            }
            .woocommerce-products-header .term-description p:last-child {
                margin-bottom: 0;
            }
            .woocommerce-products-header .term-description blockquote {
                border-' . (is_rtl() ? 'right' : 'left') . ': 4px solid #ddd;
                padding-' . (is_rtl() ? 'right' : 'left') . ': 15px;
                margin: 1em 0;
                padding-top: 0;
                padding-bottom: 0;
            }
            .woocommerce-products-header .term-description img {
                max-width: 100%;
                height: auto;
            }
            .woocommerce-products-header .term-description a {
                color: inherit;
                text-decoration: underline;
            }
            .woocommerce-products-header .term-description a:hover {
                text-decoration: none;
            }
        ';
    }
}
