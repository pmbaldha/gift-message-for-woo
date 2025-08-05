/**
 * Gift Message Frontend JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initGiftMessageField();
        initListingGiftMessages();
    });

    /**
     * Initialize gift message field functionality for single product pages
     */
    function initGiftMessageField() {
        const $giftMessageField = $('#gmwoo_gift_message');
        const $counter = $('#gmwoo-gift-message-count');
        const $counterWrapper = $('.gmwoo-gift-message-counter');

        if ($giftMessageField.length && $counter.length) {
            // Initialize counter
            updateCounter();

            // Bind events
            $giftMessageField.on('input keyup paste', updateCounter);
            $giftMessageField.on('focus', function() {
                $counterWrapper.fadeIn(200);
            });
        }

        /**
         * Update character counter
         */
        function updateCounter() {
            const currentLength = $giftMessageField.val().length;
            const maxLength = parseInt($giftMessageField.attr('maxlength')) || 150;
            const remaining = maxLength - currentLength;

            $counter.text(currentLength);

            // Add warning class if approaching limit
            if (remaining <= 20) {
                $counterWrapper.addClass('warning');
            } else {
                $counterWrapper.removeClass('warning');
            }

            // Prevent input if at limit (backup to maxlength attribute)
            if (currentLength >= maxLength) {
                $giftMessageField.val($giftMessageField.val().substring(0, maxLength));
                $counter.text(maxLength);
            }
        }
    }

    /**
     * Initialize gift message functionality for product listings
     */
    function initListingGiftMessages() {
        // Handle toggle link clicks
        $(document).on('click', '.gmwoo-add-gift-message-link', function(e) {
            e.preventDefault();
            const $wrapper = $(this).closest('.gmwoo-gift-message-listing-wrapper');
            const $fields = $wrapper.find('.gmwoo-gift-message-fields');
            const $link = $(this);
            
            if ($fields.is(':visible')) {
                $fields.slideUp('fast');
                $link.removeClass('active').text('Add Gift Message');
                // Clear the textarea when closing
                $wrapper.find('.gmwoo-gift-message-textarea').val('');
                $wrapper.find('.gmwoo-gift-message-count').text('0');
                $wrapper.find('.gmwoo-gift-message-counter').removeClass('warning');
            } else {
                $fields.slideDown('fast');
                $link.addClass('active').text('Cancel Gift Message');
                $wrapper.find('.gmwoo-gift-message-textarea').focus();
            }
        });
        
        // Handle character counter for listing textareas
        $(document).on('input keyup paste', '.gmwoo-gift-message-textarea', function() {
            const $textarea = $(this);
            const currentLength = $textarea.val().length;
            const maxLength = parseInt($textarea.attr('maxlength')) || 150;
            const $counter = $textarea.siblings('.gmwoo-gift-message-counter').find('.gmwoo-gift-message-count');
            const $counterWrapper = $textarea.siblings('.gmwoo-gift-message-counter');
            
            $counter.text(currentLength);
            
            // Add warning class when approaching limit
            if (currentLength > maxLength * 0.9) {
                $counterWrapper.addClass('warning');
            } else {
                $counterWrapper.removeClass('warning');
            }
            
            // Prevent input if at limit
            if (currentLength >= maxLength) {
                $textarea.val($textarea.val().substring(0, maxLength));
                $counter.text(maxLength);
            }
        });
        
        // Store gift message in session when changed
        $(document).on('change blur', '.gmwoo-gift-message-textarea', function() {
            const $textarea = $(this);
            const $wrapper = $textarea.closest('.gmwoo-gift-message-listing-wrapper');
            const productId = $wrapper.data('product-id');
            const giftMessage = $textarea.val();
            
            if (productId) {
                // Store in session via AJAX
                $.ajax({
                    url: gmwoo_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'gmwoo_store_gift_message',
                        product_id: productId,
                        gift_message: giftMessage,
                        nonce: getAjaxNonce()
                    },
                    success: function(response) {
                        console.log('Gift message stored in session for product ' + productId);
                    }
                });
            }
        });
        
        // Override add to cart for products with gift messages
        $(document).on('click', '.ajax_add_to_cart', function(e) {
            const $button = $(this);
            const $product = $button.closest('.product');
            const $wrapper = $product.find('.gmwoo-gift-message-listing-wrapper');
            
            // Check if this product has a gift message
            if ($wrapper.length) {
                const giftMessage = $wrapper.find('.gmwoo-gift-message-textarea').val();
                
                if (giftMessage && giftMessage.trim() !== '') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    const productId = $button.data('product_id');
                    const quantity = $button.data('quantity') || 1;
                    
                    console.log('Adding to cart with gift message:', {
                        product_id: productId,
                        quantity: quantity,
                        gift_message: giftMessage,
                        ajax_url: gmwoo_ajax.ajax_url
                    });
                    
                    // Add loading state
                    $button.removeClass('added').addClass('loading');
                    
                    // Make AJAX request
                    $.ajax({
                        url: gmwoo_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'gmwoo_add_to_cart_with_message',
                            product_id: productId,
                            quantity: quantity,
                            gift_message: giftMessage,
                            nonce: getAjaxNonce()
                        },
                        success: function(response) {
                            console.log('AJAX Response:', response);
                            
                            if (response && response.fragments) {
                                $button.removeClass('loading').addClass('added');
                                
                                // Update cart fragments
                                $.each(response.fragments, function(key, value) {
                                    $(key).replaceWith(value);
                                });
                                
                                // Update cart count
                                if (response.cart_hash) {
                                    $(document.body).trigger('wc_fragment_refresh');
                                }
                                
                                // Trigger added_to_cart event
                                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                                
                                // Reset gift message field
                                $wrapper.find('.gmwoo-gift-message-textarea').val('');
                                $wrapper.find('.gmwoo-gift-message-count').text('0');
                                $wrapper.find('.gmwoo-gift-message-counter').removeClass('warning');
                                $wrapper.find('.gmwoo-gift-message-fields').slideUp('fast');
                                $wrapper.find('.gmwoo-add-gift-message-link').removeClass('active').text('Add Gift Message');
                            } else {
                                $button.removeClass('loading');
                                if (response && response.data) {
                                    alert(response.data);
                                } else {
                                    alert('Unable to add product to cart. Please try again.');
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            $button.removeClass('loading');
                            console.error('AJAX Error:', error);
                            console.error('Response:', xhr.responseText);
                            alert('An error occurred. Please try again.');
                        }
                    });
                    
                    return false;
                }
            }
        });
    }

    /**
     * Handle AJAX cart updates to preserve gift message
     */
    $(document).on('updated_cart_totals', function() {
        // Re-initialize if cart is updated via AJAX
        initGiftMessageField();
    });
    
    /**
     * Alternative approach: Hook into WooCommerce add to cart events
     */
    $(document.body).on('should_send_ajax_request.adding_to_cart', function(e, $form, data) {
        // Check if this is from a product listing
        const $button = $form.find('.ajax_add_to_cart');
        if ($button.length) {
            const $product = $button.closest('.product');
            const $wrapper = $product.find('.gmwoo-gift-message-listing-wrapper');
            
            if ($wrapper.length) {
                const giftMessage = $wrapper.find('.gmwoo-gift-message-textarea').val();
                if (giftMessage) {
                    // Store message before standard add to cart
                    const productId = $button.data('product_id');
                    $.ajax({
                        url: gmwoo_ajax.ajax_url,
                        type: 'POST',
                        async: false,
                        data: {
                            action: 'gmwoo_store_gift_message',
                            product_id: productId,
                            gift_message: giftMessage,
                            nonce: getAjaxNonce()
                        }
                    });
                }
            }
        }
    });

    /**
     * Get the appropriate nonce for AJAX requests
     */
    function getAjaxNonce() {
        // For public/non-logged in users, use public nonce if available
        if (typeof gmwoo_public_ajax !== 'undefined' && gmwoo_public_ajax.nonce) {
            return gmwoo_public_ajax.nonce;
        }
        
        // Try to get WooCommerce's nonce from the page if it exists
        var wcNonce = null;
        
        // Check for WooCommerce add to cart nonce in various places
        var $nonceInput = $('input[name="woocommerce-add-to-cart-nonce"]');
        if ($nonceInput.length) {
            wcNonce = $nonceInput.val();
        }
        
        // Check for _wpnonce in add to cart forms
        var $wpNonce = $('.add_to_cart_button').first().closest('form').find('input[name="_wpnonce"]');
        if ($wpNonce.length) {
            wcNonce = $wpNonce.val();
        }
        
        // Check in wc_add_to_cart_params if available
        if (!wcNonce && typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.wc_ajax_url) {
            // Extract nonce from URL if present
            var matches = wc_add_to_cart_params.wc_ajax_url.match(/wc-ajax-nonce=([^&]+)/);
            if (matches && matches[1]) {
                wcNonce = matches[1];
            }
        }
        
        // Return WC nonce if found, otherwise use our custom nonce
        return wcNonce || gmwoo_ajax.nonce;
    }
    
    // Override the nonce in AJAX calls
    $(document).ready(function() {
        // Update nonce in gmwoo_ajax object
        gmwoo_ajax.dynamic_nonce = getAjaxNonce();
    });

})(jQuery);