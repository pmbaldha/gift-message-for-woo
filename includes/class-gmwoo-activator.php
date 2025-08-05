<?php
/**
 * Fired during plugin activation.
 *
 * This file defines the GMWoo_Activator class which contains
 * all code necessary to run during the plugin's activation.
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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gift_Message_For_Woo
 * @subpackage Gift_Message_For_Woo/includes
 * @author     Prashant Baldha <pmbaldha@gmail.com>
 */
class GMWoo_Activator {

	/**
	 * Run on plugin activation.
	 *
	 * Set default options and flush rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Set default options if they don't exist.
		add_option( 'gmwoo_enable_gift_message', 'yes' );
		add_option( 'gmwoo_gift_message_mode', 'all' );
		add_option( 'gmwoo_character_limit', '150' );
		add_option( 'gmwoo_field_label', __( 'Gift Message (Optional)', 'gift-message-for-woo' ) );
		add_option( 'gmwoo_field_placeholder', __( 'Enter your gift message here...', 'gift-message-for-woo' ) );
	}
}
