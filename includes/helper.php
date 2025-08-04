<?php
/**
 * Helper methods
 *
 * @package Gift Message for Woo
 */

/**
 * Utility function to get gift message from order item
 *
 * @param WC_Order_Item|object $item The order item object.
 *
 * @return string|null The gift message if set, otherwise null.
 */
function gmwoo_get_gift_message_from_order_item( $item ) {
	return $item->get_meta( 'gmwoo_gift_message' );
}

/**
 * Helper function for other plugins to check if item has gift message
 *
 * @param WC_Order_Item|object $item The order item object.
 *
 * @return bool True if a gift message exists, false otherwise.
 */
function gmwoo_has_gift_message( $item ) {
	$gift_message = gmwoo_get_gift_message_from_order_item( $item );
	return ! empty( $gift_message );
}
