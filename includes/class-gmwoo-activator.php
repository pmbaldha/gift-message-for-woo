<?php
/**
 * Fired during plugin activation
 *
 * @package Gift Message for Woo
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class GMWoo_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Set default options and flush rewrite rules.
	 */
	public static function activate() {
		// Set default options if they don't exist.
		add_option( 'gmwoo_enable_gift_message', 'yes' );
		add_option( 'gmwoo_gift_message_mode', 'all' );
		add_option( 'gmwoo_character_limit', '150' );
		add_option( 'gmwoo_field_label', __( 'Gift Message (Optional)', 'gift-message-for-woo' ) );
		add_option( 'gmwoo_field_placeholder', __( 'Enter your gift message here...', 'gift-message-for-woo' ) );

		// Clear the permalinks after the plugin has been activated.
		flush_rewrite_rules();
	}
}