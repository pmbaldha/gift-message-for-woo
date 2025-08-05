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
define( 'GMWOO_PLUGIN_FILE', __FILE__ );

// The code that runs during plugin activation.
function gmwoo_activate() {
	require_once GMWOO_PLUGIN_PATH . 'includes/class-gmwoo-activator.php';
	GMWoo_Activator::activate();
}

// The code that runs during plugin deactivation.
function gmwoo_deactivate() {
	require_once GMWOO_PLUGIN_PATH . 'includes/class-gmwoo-deactivator.php';
	GMWoo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'gmwoo_activate' );
register_deactivation_hook( __FILE__, 'gmwoo_deactivate' );

require_once GMWOO_PLUGIN_PATH . 'includes/helper.php';

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
		
		// Product listing hooks.
		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'display_gift_message_field_on_listing' ), 15 );
		
		// AJAX hooks for product listings.
		add_action( 'wp_ajax_gmwoo_add_to_cart_with_message', array( $this, 'ajax_add_to_cart_with_message' ) );
		add_action( 'wp_ajax_nopriv_gmwoo_add_to_cart_with_message', array( $this, 'ajax_add_to_cart_with_message' ) );
		
		// Session-based approach for gift messages
		add_action( 'wp_ajax_gmwoo_store_gift_message', array( $this, 'ajax_store_gift_message' ) );
		add_action( 'wp_ajax_nopriv_gmwoo_store_gift_message', array( $this, 'ajax_store_gift_message' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_gift_message_from_session' ), 20, 3 );

		// Checkout hooks.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_gift_message_to_order' ), 10, 4 );

		// Display hooks.
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this, 'remove_meta_from_order_display' ), 10, 2 );
		add_filter( 'woocommerce_email_order_items_args', array( $this, 'display_gift_message_in_email' ) );

		// Admin hooks - Support both legacy and HPOS.
		$use_hpos = false;
		
		// Check if HPOS is enabled
		if ( class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) 
			&& method_exists( wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class ), 'custom_orders_table_usage_is_enabled' ) ) {
			$use_hpos = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled();
		}
		
		if ( $use_hpos ) {
			// HPOS is enabled - use new hooks
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_gift_message_column' ) );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'display_gift_message_column_hpos' ), 10, 2 );
		} else {
			// Legacy mode - use old hooks
			add_filter( 'manage_shop_order_posts_columns', array( $this, 'add_gift_message_column' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_gift_message_column' ), 10, 2 );
		}

		// Plugin action links.
		add_filter( 'plugin_action_links_' . plugin_basename( GMWOO_PLUGIN_FILE ), array( $this, 'add_plugin_action_links' ) );

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
		wp_nonce_field( 'gmwoo_add_gift_message', 'gmwoo_gift_message_nonce' );
		echo '</div>';
	}

	/**
	 * Add gift message to cart item data
	 *
	 * @param array $cart_item_data extra cart item data we want to pass into the item.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID.
	 */
	public function add_gift_message_to_cart( $cart_item_data, $product_id = 0, $variation_id = 0 ) {
		// Check if gift message is in POST data (from single product page)
		if ( isset( $_POST['gmwoo_gift_message'] ) && ! empty( $_POST['gmwoo_gift_message'] ) ) {
			// Verify nonce only for single product pages
			if ( isset( $_POST['gmwoo_gift_message_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmwoo_gift_message_nonce'] ) ), 'gmwoo_add_gift_message' ) ) {
				return $cart_item_data;
			}

			// Sanitize and validate input.
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
	 * Display gift message column content (Legacy orders)
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

			$this->render_gift_message_column( $order );
		}
	}

	/**
	 * Display gift message column content (HPOS)
	 *
	 * @param string   $column Column name.
	 * @param WC_Order $order Order object.
	 */
	public function display_gift_message_column_hpos( $column, $order ) {
		if ( 'gmwoo_gift_message' === $column ) {
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			$this->render_gift_message_column( $order );
		}
	}

	/**
	 * Render gift message column content
	 *
	 * @param WC_Order $order Order object.
	 */
	private function render_gift_message_column( $order ) {
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

	/**
	 * Enqueue frontend scripts and styles
	 */
	public function enqueue_scripts() {
		if ( is_product() || is_shop() || is_product_category() || is_product_tag() ) {
			wp_enqueue_script(
				'gift-message-frontend',
				GMWOO_PLUGIN_URL . 'assets/js/frontend.js',
				array( 'jquery', 'wc-add-to-cart' ),
				GMWOO_VERSION,
				true
			);

			wp_enqueue_style(
				'gift-message-frontend',
				GMWOO_PLUGIN_URL . 'assets/css/frontend.css',
				array(),
				GMWOO_VERSION
			);
			
			// Localize script for AJAX
			wp_localize_script(
				'gift-message-frontend',
				'gmwoo_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'woocommerce-add-to-cart' ),
				)
			);
		}
	}

	/**
	 * Enqueues custom admin styles on the WooCommerce orders list page.
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Legacy orders page
		if ( 'edit.php' === $hook && isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] ) {
			wp_enqueue_style(
				'gift-message-admin',
				GMWOO_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				GMWOO_VERSION
			);
		}
		
		// HPOS orders page
		if ( 'woocommerce_page_wc-orders' === $hook ) {
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
			esc_html__( 'Gift Message for Woo', 'gift-message-for-woo' ) .
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

	/**
	 * Add settings link to plugin actions.
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=gift_message' ) . '">' . __( 'Settings', 'gift-message-for-woo' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Display gift message field on product listings (shop/category pages).
	 */
	public function display_gift_message_field_on_listing() {
		global $product;

		if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
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
		$show_field = apply_filters( 'gmwoo_gift_message_show_field_listing', true, $product );

		if ( ! $show_field ) {
			return;
		}

		// Get settings.
		$character_limit = get_option( 'gmwoo_character_limit', '150' );
		$field_label     = get_option( 'gmwoo_field_label', __( 'Gift Message (Optional)', 'gift-message-for-woo' ) );
		$field_placeholder = get_option( 'gmwoo_field_placeholder', __( 'Enter your gift message here...', 'gift-message-for-woo' ) );

		echo '<div class="gmwoo-gift-message-listing-wrapper" data-product-id="' . esc_attr( $product->get_id() ) . '">';
		echo '<div class="gmwoo-gift-message-toggle">';
		echo '<a href="#" class="gmwoo-add-gift-message-link">' . esc_html__( 'Add Gift Message', 'gift-message-for-woo' ) . '</a>';
		echo '</div>';
		echo '<div class="gmwoo-gift-message-fields" style="display: none;">';
		echo '<textarea class="gmwoo-gift-message-textarea" maxlength="' . esc_attr( $character_limit ) . '" placeholder="' . esc_attr( $field_placeholder ) . '"></textarea>';
		echo '<div class="gmwoo-gift-message-counter"><span class="gmwoo-gift-message-count">0</span>/' . esc_html( $character_limit ) . ' ' . esc_html__( 'characters', 'gift-message-for-woo' ) . '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * AJAX handler for adding products to cart with gift message from listings.
	 */
	public function ajax_add_to_cart_with_message() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'woocommerce-add-to-cart' ) ) {
			wp_send_json_error( __( 'Security check failed', 'gift-message-for-woo' ) );
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
		$gift_message = isset( $_POST['gift_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gift_message'] ) ) : '';

		if ( ! $product_id ) {
			wp_send_json_error( __( 'Invalid product', 'gift-message-for-woo' ) );
		}

		// Get the product
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( __( 'Product not found', 'gift-message-for-woo' ) );
		}

		// Validate character limit.
		if ( ! empty( $gift_message ) ) {
			$character_limit = get_option( 'gmwoo_character_limit', '150' );
			if ( strlen( $gift_message ) > $character_limit ) {
				wp_send_json_error( sprintf( __( 'Gift message must be %s characters or less.', 'gift-message-for-woo' ), $character_limit ) );
			}
		}

		// Add to cart with gift message.
		$cart_item_data = array();
		if ( ! empty( $gift_message ) ) {
			$cart_item_data['gmwoo_gift_message'] = $gift_message;
		}

		// Clear any previous notices
		wc_clear_notices();

		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );

		if ( $cart_item_key ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}

			// Return fragments and success response
			$data = array(
				'success' => true,
				'product_id' => $product_id,
			);
			
			// Get refreshed fragments
			ob_start();
			woocommerce_mini_cart();
			$mini_cart = ob_get_clean();
			
			$data['fragments'] = apply_filters( 'woocommerce_add_to_cart_fragments', array(
				'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			) );
			
			$data['cart_hash'] = WC()->cart->get_cart_hash();
			
			wp_send_json( $data );
		} else {
			$error_message = wc_get_notices( 'error' );
			wc_clear_notices();
			
			if ( ! empty( $error_message ) ) {
				wp_send_json_error( wp_strip_all_tags( $error_message[0]['notice'] ) );
			} else {
				wp_send_json_error( __( 'Unable to add product to cart', 'gift-message-for-woo' ) );
			}
		}
	}

	/**
	 * AJAX handler to store gift message in session.
	 */
	public function ajax_store_gift_message() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'woocommerce-add-to-cart' ) ) {
			wp_send_json_error( 'Security check failed' );
		}
		
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$gift_message = isset( $_POST['gift_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gift_message'] ) ) : '';
		
		if ( ! $product_id ) {
			wp_send_json_error( 'Invalid product ID' );
		}
		
		// Start session if not started
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
		
		// Store gift message in session
		$gift_messages = WC()->session->get( 'gmwoo_gift_messages', array() );
		if ( ! empty( $gift_message ) ) {
			$gift_messages[ $product_id ] = $gift_message;
		} else {
			unset( $gift_messages[ $product_id ] );
		}
		WC()->session->set( 'gmwoo_gift_messages', $gift_messages );
		
		wp_send_json_success( array( 'message' => 'Gift message stored' ) );
	}
	
	/**
	 * Add gift message from session when product is added to cart.
	 *
	 * @param array $cart_item_data Cart item data.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID.
	 * @return array
	 */
	public function add_gift_message_from_session( $cart_item_data, $product_id, $variation_id ) {
		// Skip if gift message already exists
		if ( isset( $cart_item_data['gmwoo_gift_message'] ) ) {
			return $cart_item_data;
		}
		
		// Check session for gift message
		if ( WC()->session ) {
			$gift_messages = WC()->session->get( 'gmwoo_gift_messages', array() );
			
			if ( isset( $gift_messages[ $product_id ] ) && ! empty( $gift_messages[ $product_id ] ) ) {
				$cart_item_data['gmwoo_gift_message'] = $gift_messages[ $product_id ];
				
				// Remove from session after use
				unset( $gift_messages[ $product_id ] );
				WC()->session->set( 'gmwoo_gift_messages', $gift_messages );
			}
		}
		
		return $cart_item_data;
	}
}


// Hook into plugins_loaded to ensure WooCommerce is loaded first.
add_action(
	'plugins_loaded',
	function () {
		new GMWoo_Gift_Message();
	}
);

// Declare HPOS compatibility
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
