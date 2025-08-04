<?php
/**
 * Fired during plugin deactivation
 *
 * @package Gift Message for Woo
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class GMWoo_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * Flush rewrite rules.
	 */
	public static function deactivate() {
		// Clear the permalinks to remove our post type's rules from the database.
		flush_rewrite_rules();
	}
}