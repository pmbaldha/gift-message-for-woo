<?php
/**
 * Helper functions for Gift Message for Woo plugin
 *
 * This file contains utility functions that can be used throughout
 * the plugin and by third-party developers.
 *
 * @link              https://prashantwp.com/
 * @since             1.0.0
 * @package           Gift_Message_For_Woo
 * @subpackage        Gift_Message_For_Woo/includes
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility function to get gift message from order item.
 *
 * @since 1.0.0
 * @param WC_Order_Item_Product $item The order item object.
 * @return string|null The gift message if set, otherwise null.
 */
function gmwoo_get_gift_message_from_order_item( $item ) {
	return $item->get_meta( 'gmwoo_gift_message' );
}

/**
 * Helper function for other plugins to check if item has gift message.
 *
 * @since 1.0.0
 * @param WC_Order_Item_Product $item The order item object.
 * @return bool True if a gift message exists, false otherwise.
 */
function gmwoo_has_gift_message( $item ) {
	$gift_message = gmwoo_get_gift_message_from_order_item( $item );
	return ! empty( $gift_message );
}
