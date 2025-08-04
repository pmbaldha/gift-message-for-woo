# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

Gift Message for Woo is a WordPress plugin that adds gift message functionality to WooCommerce stores. The plugin allows customers to add personalized gift messages to products during purchase, which are then displayed throughout the order process (cart, checkout, order details, and emails).

## Architecture

### Core Components

1. **Main Plugin File** (`gift-message-for-woo.php`)
   - Entry point that initializes the `GMWoo_Gift_Message` class
   - Handles all WordPress/WooCommerce hooks for gift message functionality
   - Key methods:
     - `display_gift_message_field()`: Shows gift message field on single product pages
     - `display_gift_message_field_on_listing()`: Shows gift message field on shop/category pages
     - `add_gift_message_to_cart()`: Processes gift message when product is added to cart
     - `ajax_add_to_cart_with_message()`: AJAX handler for adding products with messages from listings

2. **Admin Settings** (`includes/class-gmwoo-admin-settings.php`)
   - Adds "Gift Message" tab to WooCommerce settings
   - Manages configuration options:
     - Enable/disable functionality
     - Product/category restrictions
     - Character limits
     - Field labels and placeholders

3. **Frontend JavaScript** (`assets/js/frontend.js`)
   - Handles gift message UI interactions
   - Character counter functionality
   - AJAX add-to-cart override for product listings
   - Key issue: Gift messages from product listings not being added to cart

### Data Flow

1. **Product Page**: Gift message → POST data → `add_gift_message_to_cart()` filter → Cart item data
2. **Product Listing**: Gift message → AJAX request → `ajax_add_to_cart_with_message()` → Cart item data
3. **Cart/Checkout**: Cart item data → Order item meta → Email templates

### Key Hooks Used

- `woocommerce_before_add_to_cart_button`: Display field on product pages
- `woocommerce_after_shop_loop_item`: Display field on listings (priority 15)
- `woocommerce_add_cart_item_data`: Add message to cart data
- `woocommerce_checkout_create_order_line_item`: Save message to order
- `woocommerce_get_item_data`: Display message in cart/checkout

## Common Development Tasks

### Testing Gift Message on Product Listings
1. Navigate to shop page or product category
2. Click "Add Gift Message" link on a product
3. Enter message in textarea
4. Click "Add to Cart"
5. Check cart page for gift message display

### Debugging AJAX Issues
- Check browser console for JavaScript errors
- Verify `gmwoo_ajax` object is defined in console
- Check Network tab for AJAX requests to `admin-ajax.php`
- Enable WordPress debug mode to see PHP error logs

### Modifying Character Limit
The default 150-character limit is set in multiple places:
- PHP: `get_option('gmwoo_character_limit', '150')`
- JS: `parseInt($textarea.attr('maxlength')) || 150`
- HTML: `maxlength` attribute on textareas

## Known Issues

1. **Gift messages not being added from product listings** - The AJAX add-to-cart functionality needs investigation
2. **Variable product support** - Currently only works with simple products
3. **HPOS Compatibility** - Plugin includes HPOS support but may need testing

## Plugin Constants

- `GMWOO_PLUGIN_URL`: Plugin directory URL
- `GMWOO_PLUGIN_PATH`: Plugin directory path
- `GMWOO_VERSION`: Current plugin version
- `GMWOO_PLUGIN_FILE`: Main plugin file path

## Helper Functions

- `gmwoo_get_gift_message_from_order_item($item)`: Get gift message from order item
- `gmwoo_has_gift_message($item)`: Check if order item has gift message

## Filters for Customization

- `gmwoo_gift_message_show_field`: Control field visibility per product
- `gmwoo_gift_message_show_field_listing`: Control field visibility on listings
- `gmwoo_settings`: Modify plugin settings array
- `gmwoo_plugin_loaded`: Hook for custom initialization