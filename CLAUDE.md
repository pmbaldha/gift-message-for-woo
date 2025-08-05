# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

Gift Message for Woo is a WordPress plugin that adds gift message functionality to WooCommerce stores. The plugin allows customers to add personalized gift messages to products during purchase, which are then displayed throughout the order process (cart, checkout, order details, and emails).

## Build Commands

```bash
# Create WordPress.org compliant zip file (recommended)
php build.php

# Alternative build commands
./build.sh    # Unix/Linux/Mac
build.bat     # Windows
```

The build process creates a distribution-ready zip in `dist/gift-message-for-woo-{version}.zip`.

## Testing & Quality Assurance

```bash
# Run WordPress Coding Standards check (via GitHub Actions)
# Locally, you would need to install PHPCS and WPCS:
phpcs --standard=WordPress --extensions=php --ignore=vendor/,node_modules/,tests/ .

# The plugin is tested on PHP 7.4, 8.0, 8.1, and 8.2 via GitHub Actions
```

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

## Development Environment

This is a Local by Flywheel WordPress installation on Windows:
- Working directory: `C:\Users\pmbal\Local Sites\gift-message-for-woo`
- Plugin location: `app\public\wp-content\plugins\gift-message-for-woo`
- Local development URL varies based on Local configuration

## Deployment

The plugin uses GitHub Actions for automated deployment to WordPress.org:
- `.github/workflows/test.yml`: Runs tests on push/PR
- `.github/workflows/wordpress-deploy.yml`: Deploys to WordPress.org on tag
- `.github/workflows/wordpress-assets.yml`: Updates WordPress.org assets

## Plugin Constants

- `GMWOO_PLUGIN_URL`: Plugin directory URL
- `GMWOO_PLUGIN_PATH`: Plugin directory path
- `GMWOO_VERSION`: Current plugin version (1.0.0)
- `GMWOO_PLUGIN_FILE`: Main plugin file path

## Database Schema

Gift messages are stored as:
- **Cart**: Session data via WooCommerce cart item data
- **Orders**: Order item meta with key `_gift_message`
- **Settings**: WordPress options table with prefix `gmwoo_`

## Key Classes & Files

- `GMWoo_Gift_Message`: Main plugin class handling all functionality
- `GMWoo_Admin_Settings`: WooCommerce settings integration
- `GMWoo_Activator`: Plugin activation hooks
- `GMWoo_Deactivator`: Plugin deactivation hooks
- `includes/helper.php`: Helper functions for gift messages

## AJAX Endpoints

- Action: `gmwoo_add_to_cart`
- Nonce: `gmwoo_nonce`
- Parameters: `product_id`, `quantity`, `gift_message`

## Known Issues

1. **Gift messages not being added from product listings** - The AJAX add-to-cart functionality needs investigation
2. **Variable product support** - Currently only works with simple products
3. **HPOS Compatibility** - Plugin declares HPOS support but may need testing

## Common Debugging

```bash
# Enable WordPress debug mode in wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

# Check debug log
tail -f app/public/wp-content/debug.log
```

## Filters for Customization

- `gmwoo_gift_message_show_field`: Control field visibility per product
- `gmwoo_gift_message_show_field_listing`: Control field visibility on listings
- `gmwoo_settings`: Modify plugin settings array
- `gmwoo_plugin_loaded`: Hook for custom initialization