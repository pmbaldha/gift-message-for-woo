<?php
/**
 * Uninstall Gift Message for Woo.
 *
 * Deletes all plugin data when the plugin is deleted.
 *
 * @link              https://prashantwp.com/
 * @since             1.0.0
 * @package           Gift_Message_For_Woo
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options.
delete_option( 'gmwoo_enable_gift_message' );
delete_option( 'gmwoo_gift_message_mode' );
delete_option( 'gmwoo_specific_products' );
delete_option( 'gmwoo_specific_categories' );
delete_option( 'gmwoo_character_limit' );
delete_option( 'gmwoo_field_label' );
delete_option( 'gmwoo_field_placeholder' );

// Delete gift message meta from all orders (optional - commented out for safety).
// Uncomment the following lines if you want to remove all gift message data on uninstall.

// phpcs:ignore Squiz.PHP.CommentedOutCode.Found

/*
Global $wpdb;
$wpdb->query(
	"DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta
	WHERE meta_key = 'gmwoo_gift_message'"
);
*/
