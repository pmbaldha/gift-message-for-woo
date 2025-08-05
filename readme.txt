=== Gift Message for Woo ===
Contributors: pmbaldha
Tags: woocommerce, gift, gift-message, ecommerce, checkout
Requires at least: 6.6
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 7.9
WC tested up to: 10.0
Requires Plugins: woocommerce

Add customizable gift message functionality to WooCommerce products with seamless cart, checkout, and order integration.

== Description ==

Gift Message for Woo adds professional gift message functionality to your WooCommerce store, allowing customers to include personalized messages with their orders. Perfect for gift purchases, special occasions, and personalized shopping experiences.

**Key Features:**

* **Product Page Integration** - Gift message textarea field (150 character limit) appears on single product pages
* **Complete Order Flow** - Gift messages are preserved through cart → checkout → order → admin → email workflow
* **Admin Management** - Dedicated gift message column in WooCommerce orders list with detailed order views
* **Customer Experience** - Real-time character counter with visual feedback and mobile-responsive design
* **Analytics & Export** - Built-in analytics dashboard and export functionality for reporting
* **Developer-Friendly** - Extensible with custom hooks and filters for theme integration

**Perfect for:**

* Gift shops and seasonal retailers
* E-commerce stores with gift options
* Special occasion marketing campaigns
* Customer personalization experiences
* Retail stores with gift services

**Advanced Features:**

The plugin includes comprehensive gift message management with:

* Bulk operations for high-volume stores
* Analytics and reporting dashboard
* Customer gift message history
* Export functionality in multiple formats
* Product-specific gift message settings
* Template system for common messages

**Modern & Responsive:**

Built with modern web standards featuring:

* Mobile-responsive design
* Touch-friendly interface
* Progressive enhancement
* Cross-browser compatibility
* Performance optimized code

**Security First:**

All user inputs are properly sanitized and validated. Output is escaped for XSS prevention. The plugin follows WordPress and WooCommerce security best practices.

**Developer Extensibility:**

```php
// Control field visibility per product
add_filter('gift_message_show_field', function($show, $product) {
    return !has_term('no-gifts', 'product_cat', $product->get_id());
}, 10, 2);

// Custom initialization
add_action('gift_message_plugin_loaded', function() {
    // Your custom code here
});
```

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gift-message-for-woo/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and activated (required dependency)
4. Visit any single product page to see the gift message field
5. Configure display options using the provided hooks and filters

**System Requirements:**

* WordPress 6.1 or higher
* WooCommerce 7.9 or higher
* PHP 7.4 or higher
* Modern browser with JavaScript enabled

== Frequently Asked Questions ==

= Does this work with custom themes? =

Yes! The plugin uses standard WooCommerce hooks and includes CSS classes for easy theme integration and customization.

= Can I customize the gift message field appearance? =

Absolutely. The plugin includes CSS classes for styling and hooks for customizing field behavior, labels, and validation rules.

= Are gift messages included in order emails? =

Yes, gift messages appear in order confirmation emails, admin notifications, and can be displayed in custom email templates.

= Does this support variable products? =

Currently, the plugin supports simple products. Variable product support is planned for a future release.

= Can I set different character limits per product? =

The default limit is 150 characters globally, but you can use the provided hooks to customize limits per product or product category.

= Is this compatible with WooCommerce Blocks? =

Yes, the plugin is designed to work seamlessly with WooCommerce Blocks and modern checkout flows.

= Can I export gift messages for analysis? =

Yes, the plugin includes export functionality supporting multiple formats for reporting and analytics through the admin dashboard.

= Does this work with caching plugins? =

Yes, the plugin is designed to work with popular caching solutions. Gift messages are stored in cart sessions and order meta.

= Can customers edit gift messages after placing an order? =

Currently, gift messages can only be edited during the cart/checkout process. Post-order editing would require admin intervention.

= Is there a character counter for customers? =

Yes, the plugin includes a real-time JavaScript character counter with visual feedback when approaching the 150-character limit.

== Screenshots ==

1. Admin Dashboard settings for Gift Message.
2. Gift message field on single product page with character counter.
3. Gift message field on Products listing.
4. Gift message display in the sidebar cart with item details.
5. Gift message display in the cart page with item details.
6. Checkout page showing gift messages for multiple items.
7. Order received page showing gift messages for multiple items.
8. Admin orders list with gift message column.
9. Gift message display in admin order details.
10. Gift message display in order email sent to store owner (merchant).
11. Gift message display in order email sent to customer.



== Changelog ==

= 1.0.0 =
* Added - Gift message textarea field on single product pages
* Added - Real-time character counter with 150-character limit
* Added - Complete cart, checkout, and order flow integration
* Added - Admin orders list column for quick gift message overview
* Added - Order details page display for full gift messages
* Added - Email integration for order confirmation messages
* Added - Analytics dashboard for gift message insights
* Added - Bulk operations interface for high-volume management
* Added - Export functionality (CSV, XLSX formats)
* Added - Customer gift message history tracking
* Added - Product-specific gift message settings
* Added - Gift message templates for common occasions
* Added - Mobile-responsive design with touch-friendly interface
* Added - Security features with input sanitization and output escaping
* Added - Developer hooks for customization and extensibility
* Added - Session management for cart gift messages
* Added - WordPress and WooCommerce coding standards compliance
* Added - Comprehensive documentation and admin guides

== Upgrade Notice ==

= 1.0.0 =
Initial release with complete gift message functionality, admin dashboard, analytics, and export capabilities. No upgrade steps required for new installations.

== Admin Features ==

**Gift Message Management:**

* Dedicated admin dashboard for gift message overview
* Bulk operations for managing multiple messages
* Export functionality in CSV and XLSX formats
* Analytics and reporting for gift message trends
* Customer history tracking for repeat gift senders

**Integration Points:**

* WooCommerce orders list with gift message indicators
* Order details page with full message display
* Email template integration for notifications
* Product settings for message field customization

**Developer Resources:**

```php
// Control field visibility per product
add_filter('gift_message_show_field', function($show, $product) {
    return !has_term('no-gifts', 'product_cat', $product->get_id());
}, 10, 2);

// Custom initialization
add_action('gift_message_plugin_loaded', function() {
    // Your custom code here
});
```

For complete documentation, visit: https://prashantwp.com/docs/gift-message-for-woo/

== Support ==

For technical support, feature requests, or bug reports:

* Support Forum: https://wordpress.org/support/plugin/gift-message-for-woo/

== Privacy Policy ==

This plugin stores gift messages as part of WooCommerce order data. Gift messages are treated with the same privacy considerations as other order information:

* Gift messages are stored in your WooCommerce database
* Messages are included in order emails and admin displays
* Data retention follows your WooCommerce settings
* No external services are contacted
* No tracking or analytics data is collected by this plugin

== Development ==

This plugin is actively developed on GitHub. Contributions, bug reports, and feature requests are welcome:

* Repository: https://github.com/prashantwp/gift-message-for-woo
* Roadmap: https://github.com/prashantwp/gift-message-for-woo/projects
* Contributing: https://github.com/prashantwp/gift-message-for-woo/blob/main/CONTRIBUTING.md