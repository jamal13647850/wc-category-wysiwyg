<?php
/**
 * Frontend functionality for WC Category WYSIWYG
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_Category_WYSIWYG_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('woocommerce_taxonomy_archive_description', [$this, 'display_category_description'], 10, 2);
        add_filter('woocommerce_subcategory_count_html', [$this, 'add_description_after_count'], 10, 2);
    }
    
    /**
     * Display category description with proper formatting
     */
    public function display_category_description($description, $term) {
        if (!empty($term->description)) {
            // Apply WordPress content filters for proper display
            $description = wpautop($term->description);
            $description = do_shortcode($description);
            $description = $this->sanitize_output($description);
        }
        
        return $description;
    }
    
    /**
     * Add description after subcategory count
     */
    public function add_description_after_count($count, $category) {
        if (!empty($category->description)) {
            $description = wpautop($category->description);
            $description = do_shortcode($description);
            $description = $this->sanitize_output($description);
            
            return $count . $description;
        }
        
        return $count;
    }
    
    /**
     * Sanitize output for security
     */
    private function sanitize_output($content) {
        // Allow safe HTML tags for content display
        $allowed_tags = wp_kses_allowed_html('post');
        
        return wp_kses($content, $allowed_tags);
    }
}