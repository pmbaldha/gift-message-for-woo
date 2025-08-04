<?php
/**
 * Plugin Name: Gift Message for Woo
 * Description: Adds a gift message field to WooCommerce products and displays it across cart, checkout, orders, and emails.
 * Version: 1.0.0
 * Author: Prashant Baldha
 * Author URI: https://prashantwp.com/
 * Text Domain: gift-message-for-woo
 * Domain Path: /languages/
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * WC requires at least: 7.1
 * WC tested up to: 10.0
 *
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 **/


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'GMWOO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GMWOO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GMWOO_VERSION', '1.0.0' );

require_once GMWOO_PLUGIN_PATH . 'helper.php';

// Include admin settings class.
if ( is_admin() ) {
	require_once GMWOO_PLUGIN_PATH . 'includes/class-gmwoo-admin-settings.php';
}
/**
 * Main Gift Message Plugin Class
 */
class GMWoo_Gift_Message {


	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		// Initialize hooks.
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Frontend hooks.
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'display_gift_message_field' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_gift_message_to_cart' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_gift_message_in_cart' ), 10, 2 );

		// Checkout hooks.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_gift_message_to_order' ), 10, 4 );

		// Display hooks.
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'remove_meta_from_order_display' ), 10, 2 );
		add_filter( 'woocommerce_email_order_items_args', array( $this, 'display_gift_message_in_email' ) );

		// Admin hooks.
		add_filter( 'manage_shop_order_posts_columns', array( $this, 'add_gift_message_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_gift_message_column' ), 10, 2 );

		// Scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Custom hook for extensibility.
		do_action( 'gmwoo_plugin_loaded' );
	}


	/**
	 * Display gift message field on product page.
	 */
	public function display_gift_message_field() {
		global $product;

		if ( ! $product || ! $product->is_type( 'simple' ) ) {
			return;
		}

		// Check if gift messages are enabled.
		if ( 'yes' !== get_option( 'gmwoo_enable_gift_message', 'yes' ) ) {
			return;
		}

		// Check product/category restrictions.
		if ( ! $this->is_gift_message_allowed_for_product( $product ) ) {
			return;
		}

		// Apply filter for field visibility.
		$show_field = apply_filters( 'gmwoo_gift_message_show_field', true, $product );

		if ( ! $show_field ) {
			return;
		}

		// Get settings.
		$character_limit = get_option( 'gmwoo_character_limit', '150' );
		$field_label     = get_option( 'gmwoo_field_label', __( 'Gift Message (Optional)', 'gift-message-for-woo' ) );
		$field_placeholder = get_option( 'gmwoo_field_placeholder', __( 'Enter your gift message here...', 'gift-message-for-woo' ) );

		echo '<div class="gmwoo-gift-message-wrapper">';
		echo '<label for="gmwoo_gift_message">' . esc_html( $field_label ) . '</label>';
		echo '<textarea id="gmwoo_gift_message" name="gmwoo_gift_message" maxlength="' . esc_attr( $character_limit ) . '" placeholder="' . esc_attr( $field_placeholder ) . '"></textarea>';
		echo '<div class="gmwoo-gift-message-counter"><span id="gmwoo-gift-message-count">0</span>/' . esc_html( $character_limit ) . ' ' . esc_html__( 'characters', 'gift-message-for-woo' ) . '</div>';
		echo '</div>';
	}

	/**
	 * Add gift message to cart item data
	 *
	 * @param array $cart_item_data extra cart item data we want to pass into the item.
	 */
	public function add_gift_message_to_cart( $cart_item_data ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['gmwoo_gift_message'] ) && ! empty( $_POST['gmwoo_gift_message'] ) ) {
			// Sanitize and validate input.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$gift_message = sanitize_textarea_field( wp_unslash( $_POST['gmwoo_gift_message'] ) );

			$character_limit = get_option( 'gmwoo_character_limit', '150' );
			if ( strlen( $gift_message ) > $character_limit ) {
				wc_add_notice( sprintf( __( 'Gift message must be %s characters or less.', 'gift-message-for-woo' ), $character_limit ), 'error' );
				return $cart_item_data;
			}

			$cart_item_data['gmwoo_gift_message'] = $gift_message;
		}

		return $cart_item_data;
	}

	/**
	 * Adds the gift message to the cart item data for display in the cart and checkout.
	 *
	 * @param array $item_data Array of item data displayed in the cart and checkout.
	 * @param array $cart_item The cart item array containing product and custom data.
	 *
	 * @return array Modified item data array with gift message included, if present.
	 */
	public function display_gift_message_in_cart( $item_data, $cart_item ) {
		if ( isset( $cart_item['gmwoo_gift_message'] ) && ! empty( $cart_item['gmwoo_gift_message'] ) ) {
			$item_data[] = array(
				'key'     => __( 'Gift Message', 'gift-message-for-woo' ),
				'value'   => esc_html( $cart_item['gmwoo_gift_message'] ),
				'display' => '',
			);
		}

		return $item_data;
	}

	/**
	 * Saves the gift message from the cart item to the order item meta data.
	 *
	 * @param WC_Order_Item_Product $item           Order item object.
	 * @param string                $cart_item_key  Unique key of the cart item.
	 * @param array                 $values         Cart item data.
	 * @param WC_Order              $order          The order object.
	 */
	public function save_gift_message_to_order( $item, $cart_item_key, $values, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( isset( $values['gmwoo_gift_message'] ) && ! empty( $values['gmwoo_gift_message'] ) ) {
			$item->add_meta_data( 'gmwoo_gift_message', sanitize_textarea_field( $values['gmwoo_gift_message'] ) );
		}
	}

	/**
	 * Customizes the display key for the gift message meta in order details.
	 *
	 * @param string       $display_key Display name of the meta key.
	 * @param WC_Meta_Data $meta        Meta data object.
	 *
	 * @return string Modified display key for the gift message meta.
	 */
	public function remove_meta_from_order_display( $display_key, $meta ) {
		// Hide specific meta keys.
		if ( 'gmwoo_gift_message' === $meta->key ) {
			return __( 'Gift message', 'gift-message-for-woo' );
		}
		return $display_key;
	}


	/**
	 * Display gift message in email
	 *
	 * @param array $args Order item args.
	 */
	public function display_gift_message_in_email( $args ) {
		$args['show_purchase_note'] = true;
		return $args;
	}

	/**
	 * Add gift message column to orders admin
	 *
	 * @param array $columns Product columns.
	 */
	public function add_gift_message_column( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			if ( 'order_status' === $key ) {
				$new_columns['gmwoo_gift_message'] = __( 'Gift Message', 'gift-message-for-woo' );
			}
		}

		return $new_columns;
	}

	/**
	 * Display gift message column content
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Product id.
	 */
	public function display_gift_message_column( $column, $post_id ) {
		if ( 'gmwoo_gift_message' === $column ) {
			$order = wc_get_order( $post_id );

			if ( ! $order ) {
				return;
			}

			$has_gift_message = false;

			foreach ( $order->get_items() as $item ) {
				$gift_message = $item->get_meta( 'gmwoo_gift_message' );

				if ( ! empty( $gift_message ) ) {
					$has_gift_message = true;
					$truncated        = strlen( $gift_message ) > 30 ? substr( $gift_message, 0, 30 ) . '...' : $gift_message;
					echo '<div title="' . esc_attr( $gift_message ) . '">' . esc_html( $truncated ) . '</div>';
				}
			}

			if ( ! $has_gift_message ) {
				echo '<span class="na">–</span>';
			}
		}
	}

	/**
	 * Enqueue frontend scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_product() ) {
			wp_enqueue_script(
				'gift-message-frontend',
				GMWOO_PLUGIN_URL . 'assets/js/frontend.js',
				array( 'jquery' ),
				GMWOO_VERSION,
				true
			);

			wp_enqueue_style(
				'gift-message-frontend',
				GMWOO_PLUGIN_URL . 'assets/css/frontend.css',
				array(),
				GMWOO_VERSION
			);
		}
	}

	/**
	 * Enqueues custom admin styles on the WooCommerce orders list page.
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'edit.php' === $hook && 'shop_order' === get_post_type() ) {
			wp_enqueue_style(
				'gift-message-admin',
				GMWOO_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				GMWOO_VERSION
			);
		}
	}

	/**
	 * WooCommerce missing notice
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p><strong>' .
			esc_html__( 'Gift Message for WooCommerce', 'gift-message-for-woo' ) .
			'</strong> ' .
			esc_html__( 'requires WooCommerce to be installed and active.', 'gift-message-for-woo' ) .
			'</p></div>';
	}

	/**
	 * Check if gift message is allowed for a specific product.
	 *
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	private function is_gift_message_allowed_for_product( $product ) {
		$mode = get_option( 'gmwoo_gift_message_mode', 'all' );

		switch ( $mode ) {
			case 'all':
				return true;

			case 'specific_products':
				$allowed_products = get_option( 'gmwoo_specific_products', array() );
				return in_array( $product->get_id(), $allowed_products, true );

			case 'specific_categories':
				$allowed_categories = get_option( 'gmwoo_specific_categories', array() );
				$product_categories = $product->get_category_ids();
				return ! empty( array_intersect( $product_categories, $allowed_categories ) );

			case 'exclude_products':
				$excluded_products = get_option( 'gmwoo_specific_products', array() );
				return ! in_array( $product->get_id(), $excluded_products, true );

			case 'exclude_categories':
				$excluded_categories = get_option( 'gmwoo_specific_categories', array() );
				$product_categories  = $product->get_category_ids();
				return empty( array_intersect( $product_categories, $excluded_categories ) );

			default:
				return true;
		}
	}
}


// Hook into plugins_loaded to ensure WooCommerce is loaded first.
add_action(
	'plugins_loaded',
	function () {
		new GMWoo_Gift_Message();
	}
);
