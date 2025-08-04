<?php
/**
 * Admin Settings Class for Gift Message for Woo
 *
 * @package Gift Message for Woo
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMWoo Admin Settings Class
 */
class GMWoo_Admin_Settings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_gift_message', array( $this, 'settings_tab' ) );
		add_action( 'woocommerce_update_options_gift_message', array( $this, 'update_settings' ) );
		add_action( 'woocommerce_admin_field_gmwoo_product_selector', array( $this, 'product_selector_field' ) );
		add_action( 'woocommerce_admin_field_gmwoo_category_selector', array( $this, 'category_selector_field' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs.
	 * @return array Modified settings tabs array.
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['gift_message'] = __( 'Gift Message', 'gift-message-for-woo' );
		return $settings_tabs;
	}

	/**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 */
	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 */
	public function update_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public function get_settings() {
		$settings = array(
			'section_title' => array(
				'name' => __( 'Gift Message Settings', 'gift-message-for-woo' ),
				'type' => 'title',
				'desc' => __( 'Configure how gift messages are displayed and which products can have gift messages.', 'gift-message-for-woo' ),
				'id'   => 'gmwoo_section_title',
			),
			'enable_gift_message' => array(
				'name'    => __( 'Enable Gift Messages', 'gift-message-for-woo' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Enable gift message functionality on your store', 'gift-message-for-woo' ),
				'id'      => 'gmwoo_enable_gift_message',
				'default' => 'yes',
			),
			'gift_message_mode' => array(
				'name'    => __( 'Gift Message Display Mode', 'gift-message-for-woo' ),
				'type'    => 'select',
				'desc'    => __( 'Choose how to enable gift messages for products', 'gift-message-for-woo' ),
				'id'      => 'gmwoo_gift_message_mode',
				'default' => 'all',
				'options' => array(
					'all'              => __( 'All Products', 'gift-message-for-woo' ),
					'specific_products' => __( 'Specific Products Only', 'gift-message-for-woo' ),
					'specific_categories' => __( 'Specific Categories Only', 'gift-message-for-woo' ),
					'exclude_products' => __( 'All Products Except Specific Products', 'gift-message-for-woo' ),
					'exclude_categories' => __( 'All Products Except Specific Categories', 'gift-message-for-woo' ),
				),
			),
			'specific_products' => array(
				'name' => __( 'Select Products', 'gift-message-for-woo' ),
				'type' => 'gmwoo_product_selector',
				'desc' => __( 'Select products to enable/exclude gift messages', 'gift-message-for-woo' ),
				'id'   => 'gmwoo_specific_products',
			),
			'specific_categories' => array(
				'name' => __( 'Select Categories', 'gift-message-for-woo' ),
				'type' => 'gmwoo_category_selector',
				'desc' => __( 'Select categories to enable/exclude gift messages', 'gift-message-for-woo' ),
				'id'   => 'gmwoo_specific_categories',
			),
			'character_limit' => array(
				'name'              => __( 'Character Limit', 'gift-message-for-woo' ),
				'type'              => 'number',
				'desc'              => __( 'Maximum number of characters allowed in gift message', 'gift-message-for-woo' ),
				'id'                => 'gmwoo_character_limit',
				'default'           => '150',
				'custom_attributes' => array(
					'min'  => 1,
					'max'  => 500,
					'step' => 1,
				),
			),
			'field_label' => array(
				'name'    => __( 'Field Label', 'gift-message-for-woo' ),
				'type'    => 'text',
				'desc'    => __( 'Label text for the gift message field', 'gift-message-for-woo' ),
				'id'      => 'gmwoo_field_label',
				'default' => __( 'Gift Message (Optional)', 'gift-message-for-woo' ),
			),
			'field_placeholder' => array(
				'name'    => __( 'Field Placeholder', 'gift-message-for-woo' ),
				'type'    => 'text',
				'desc'    => __( 'Placeholder text for the gift message field', 'gift-message-for-woo' ),
				'id'      => 'gmwoo_field_placeholder',
				'default' => __( 'Enter your gift message here...', 'gift-message-for-woo' ),
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id'   => 'gmwoo_section_end',
			),
		);
		return apply_filters( 'gmwoo_settings', $settings );
	}

	/**
	 * Custom product selector field
	 *
	 * @param array $value Field value array.
	 */
	public function product_selector_field( $value ) {
		$option_value = get_option( $value['id'], array() );
		$option_value = is_array( $option_value ) ? $option_value : array();
		?>
		<tr valign="top" class="gmwoo-product-selector-row">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['name'] ); ?></label>
			</th>
			<td class="forminp">
				<select name="<?php echo esc_attr( $value['id'] ); ?>[]" 
						id="<?php echo esc_attr( $value['id'] ); ?>" 
						class="gmwoo-product-search wc-product-search" 
						multiple="multiple" 
						style="width: 50%;" 
						data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'gift-message-for-woo' ); ?>"
						data-action="woocommerce_json_search_products">
					<?php
					foreach ( $option_value as $product_id ) {
						$product = wc_get_product( $product_id );
						if ( is_object( $product ) ) {
							echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
						}
					}
					?>
				</select>
				<p class="description"><?php echo esc_html( $value['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Custom category selector field
	 *
	 * @param array $value Field value array.
	 */
	public function category_selector_field( $value ) {
		$option_value = get_option( $value['id'], array() );
		$option_value = is_array( $option_value ) ? $option_value : array();
		$categories   = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'name',
				'hide_empty' => false,
			)
		);
		?>
		<tr valign="top" class="gmwoo-category-selector-row">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['name'] ); ?></label>
			</th>
			<td class="forminp">
				<select name="<?php echo esc_attr( $value['id'] ); ?>[]" 
						id="<?php echo esc_attr( $value['id'] ); ?>" 
						class="gmwoo-category-search wc-enhanced-select" 
						multiple="multiple" 
						style="width: 50%;" 
						data-placeholder="<?php esc_attr_e( 'Select categories&hellip;', 'gift-message-for-woo' ); ?>">
					<?php
					if ( $categories ) {
						foreach ( $categories as $category ) {
							$selected = in_array( $category->term_id, $option_value, true ) ? 'selected="selected"' : '';
							echo '<option value="' . esc_attr( $category->term_id ) . '" ' . $selected . '>' . esc_html( $category->name ) . '</option>';
						}
					}
					?>
				</select>
				<p class="description"><?php echo esc_html( $value['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Enqueue admin scripts for the settings page
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}

		// Only load on our settings tab.
		if ( ! isset( $_GET['tab'] ) || 'gift_message' !== $_GET['tab'] ) {
			return;
		}

		// Enqueue WooCommerce admin scripts for enhanced select.
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_style( 'woocommerce_admin_styles' );

		// Add inline script to handle conditional field display.
		wp_add_inline_script(
			'wc-enhanced-select',
			"
			jQuery(document).ready(function($) {
				function toggleGiftMessageFields() {
					var mode = $('#gmwoo_gift_message_mode').val();
					var productRow = $('.gmwoo-product-selector-row');
					var categoryRow = $('.gmwoo-category-selector-row');
					
					productRow.hide();
					categoryRow.hide();
					
					if (mode === 'specific_products' || mode === 'exclude_products') {
						productRow.show();
					} else if (mode === 'specific_categories' || mode === 'exclude_categories') {
						categoryRow.show();
					}
				}
				
				$('#gmwoo_gift_message_mode').on('change', toggleGiftMessageFields);
				toggleGiftMessageFields();
			});
			"
		);
	}
}

// Initialize the settings class.
new GMWoo_Admin_Settings();