/**
 * Gift Message Frontend JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        initGiftMessageField();
    });

    /**
     * Initialize gift message field functionality
     */
    function initGiftMessageField() {
        const $giftMessageField = $('#gmwoo_gift_message');
        const $counter = $('#gmwoo-gift-message-count');
        const $counterWrapper = $('.gmwoo_gift-message-counter');

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
     * Handle AJAX cart updates to preserve gift message
     */
    $(document).on('updated_cart_totals', function() {
        // Re-initialize if cart is updated via AJAX
        initGiftMessageField();
    });

})(jQuery);