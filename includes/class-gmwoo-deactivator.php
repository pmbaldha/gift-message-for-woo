<?php
/**
 * Fired during plugin deactivation.
 *
 * This file defines the GMWoo_Deactivator class which contains
 * all code necessary to run during the plugin's deactivation.
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
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Gift_Message_For_Woo
 * @subpackage Gift_Message_For_Woo/includes
 * @author     Prashant Baldha <pmbaldha@gmail.com>
 */
class GMWoo_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * Flush rewrite rules.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
	}
}