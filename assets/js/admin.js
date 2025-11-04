/**
 * WC Category WYSIWYG Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize editor functionality
    function initCategoryEditor() {
        // Ensure WordPress editor is properly initialized
        if (typeof tinyMCE !== 'undefined') {
            // Add custom initialization if needed
            tinyMCE.init({
                selector: '#description',
                setup: function(editor) {
                    editor.on('change', function() {
                        // Trigger change event for form validation
                        $(editor.targetEl).trigger('change');
                    });
                }
            });
        }
    }
    
    // Initialize on page load
    initCategoryEditor();
    
    // Handle category form submission
    $('form#addtag, form#edittag').on('submit', function(e) {
        // Update textarea content before submission
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('description')) {
            var content = tinyMCE.get('description').getContent();
            $('#description').val(content);
        }
    });
    
    // Auto-save functionality (optional)
    var autoSaveTimer;
    $('#description').on('input change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save logic can be implemented here
            console.log('Auto-save triggered');
        }, 30000); // 30 seconds
    });
    
    // Media button enhancement
    $('.wp-media-buttons').on('click', 'button', function(e) {
        // Custom media button handling if needed
        console.log('Media button clicked');
    });
});