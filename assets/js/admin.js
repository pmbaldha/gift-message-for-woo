/**
 * Gift Message for Woo - Admin JavaScript
 */

jQuery(document).ready(function($) {
    console.log('Gift Message Admin JS loaded');
    
    // Function to toggle all gift message settings based on enable checkbox
    function toggleAllGiftMessageSettings() {
        var isEnabled = $('#gmwoo_enable_gift_message').is(':checked');
        
        // Get all rows except the enable checkbox row and section title/end
        var allSettingRows = $('tr').filter(function() {
            var $this = $(this);
            var hasEnableCheckbox = $this.find('#gmwoo_enable_gift_message').length > 0;
            var isSectionTitle = $this.find('h2').length > 0 || $this.hasClass('woocommerce-settings-title');
            var isSectionEnd = $this.find('#gmwoo_section_end').length > 0;
            
            return !hasEnableCheckbox && !isSectionTitle && !isSectionEnd && 
                   ($this.find('[id^="gmwoo_"]').length > 0 || 
                    $this.hasClass('gmwoo-product-selector-row') || 
                    $this.hasClass('gmwoo-category-selector-row'));
        });
        
        if (isEnabled) {
            allSettingRows.fadeIn('slow');
            // After showing all settings, apply the display mode toggle
            setTimeout(function() {
                toggleGiftMessageFields();
            }, 600);
        } else {
            allSettingRows.fadeOut('slow');
        }
    }
    
    // Function to toggle product/category selector visibility with fade effects
    function toggleGiftMessageFields() {
        var mode = $('#gmwoo_gift_message_mode').val();
        var productRow = $('.gmwoo-product-selector-row');
        var categoryRow = $('.gmwoo-category-selector-row');
        
        console.log('Toggle fields called - Mode:', mode);
        console.log('Product row elements found:', productRow.length);
        console.log('Category row elements found:', categoryRow.length);
        
        // Only toggle if gift messages are enabled
        if (!$('#gmwoo_enable_gift_message').is(':checked')) {
            return;
        }
        
        // Fade out all rows first with slow speed
        productRow.fadeOut('slow');
        categoryRow.fadeOut('slow');
        
        // Show appropriate fields based on selected mode with fade in effect
        setTimeout(function() {
            if (mode === 'specific_products' || mode === 'exclude_products') {
                console.log('Showing product selector');
                productRow.fadeIn('slow');
            } else if (mode === 'specific_categories' || mode === 'exclude_categories') {
                console.log('Showing category selector');
                categoryRow.fadeIn('slow');
            }
        }, 600); // Wait for fade out to complete
    }
    
    // Function for initial page load (no animation)
    function initialToggle() {
        var isEnabled = $('#gmwoo_enable_gift_message').is(':checked');
        
        // Get all rows except the enable checkbox row and section title/end
        var allSettingRows = $('tr').filter(function() {
            var $this = $(this);
            var hasEnableCheckbox = $this.find('#gmwoo_enable_gift_message').length > 0;
            var isSectionTitle = $this.find('h2').length > 0 || $this.hasClass('woocommerce-settings-title');
            var isSectionEnd = $this.find('#gmwoo_section_end').length > 0;
            
            return !hasEnableCheckbox && !isSectionTitle && !isSectionEnd && 
                   ($this.find('[id^="gmwoo_"]').length > 0 || 
                    $this.hasClass('gmwoo-product-selector-row') || 
                    $this.hasClass('gmwoo-category-selector-row'));
        });
        
        if (!isEnabled) {
            allSettingRows.hide();
            return;
        }
        
        // Show all settings if enabled
        allSettingRows.show();
        
        var mode = $('#gmwoo_gift_message_mode').val();
        var productRow = $('.gmwoo-product-selector-row');
        var categoryRow = $('.gmwoo-category-selector-row');
        
        // Hide all rows initially without animation
        productRow.hide();
        categoryRow.hide();
        
        // Show appropriate fields based on selected mode without animation
        if (mode === 'specific_products' || mode === 'exclude_products') {
            productRow.show();
        } else if (mode === 'specific_categories' || mode === 'exclude_categories') {
            categoryRow.show();
        }
    }
    
    // Bind change event to the enable checkbox
    $('#gmwoo_enable_gift_message').on('change', function() {
        console.log('Enable gift message changed to:', $(this).is(':checked'));
        toggleAllGiftMessageSettings();
    });
    
    // Bind change event to the display mode dropdown
    $('#gmwoo_gift_message_mode').on('change', function() {
        console.log('Display mode changed to:', $(this).val());
        toggleGiftMessageFields();
    });
    
    // Initial toggle on page load (without animation)
    // Use timeout to ensure WooCommerce has initialized everything
    setTimeout(function() {
        initialToggle();
    }, 100);
});