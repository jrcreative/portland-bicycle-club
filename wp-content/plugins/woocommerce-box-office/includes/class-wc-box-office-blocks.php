<?php
/**
 * Implements Box Office blocks related functionality.
 *
 * @package woocommerce-box-office
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && ! version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), '6.7.0', '>' ) ) {
	return;
}

/**
 * Class responsible for dealing with everything related to the adoption of
 * Gutenberg Blocks and WooCommerce.
 */
class WC_Box_Office_Blocks {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter(
			'woocommerce_store_api_product_quantity_editable',
			function ( $quantity, $product ) {
				return ! wc_box_office_is_product_ticket( $product );
			},
			10,
			2
		);

		/**
		 * These two functions enable block based product list components to change
		 * the Add to cart button into a link to the product detail page for
		 * ticket info collection.
		 */
		add_filter(
			'woocommerce_product_has_options',
			function ( $has_options, $product ) {
				if ( wc_box_office_is_product_ticket( $product ) ) {
					return true;
				}
				return $has_options;
			},
			10,
			2
		);

		/**
		 * This is needed so that we can get to the product detail page first from any
		 * WooCommerce related block that displays products using the add to cart button.
		 * See: https://github.com/woocommerce/woocommerce-gutenberg-products-block/issues/5895
		 */
		add_filter(
			'woocommerce_product_supports',
			function ( $supports, $feature, $product ) {
				if ( wc_box_office_is_product_ticket( $product ) && 'ajax_add_to_cart' === $feature ) {
					return false;
				}
				return $supports;
			},
			10,
			3
		);

		add_action(
			'init',
			array( $this, 'register_blocks' )
		);

		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_admin_assets' )
		);
	}

	/**
	 * Registers the block category 'WooCommerce Box Office'.
	 *
	 * @param array $block_categories Array of block categories.
	 *
	 * @return array
	 */
	public function register_block_category( $block_categories ) {
		$block_categories[] = array(
			'slug'  => 'woocommerce-box-office',
			'title' => __( 'WooCommerce Box Office', 'woocommerce-box-office' ),
			'icon'  => 'tickets-alt',
		);

		return $block_categories;
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		register_block_type( WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/user-tickets' );

		register_block_type(
			WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/order-tickets',
			array(
				'render_callback' => function ( $attributes ) {
					$params = array();

					$params['order_id']      = isset( $attributes['orderId'] ) ? (int) $attributes['orderId'] : 0;
					$params['amount']        = $attributes['numberOfTickets'] ?? 'all';
					$params['fields_format'] = $attributes['format'] ?? 'list';
					$params['title']         = $attributes['title'] ?? '';
					$params['description']   = $attributes['description'] ?? '';

					$shortcode = new WC_Box_Office_Ticket_Shortcode();
					return $shortcode->order_ticket_list( $params );
				},
			)
		);

		register_block_type(
			WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/scan-ticket',
			array(
				'editor_style'    => 'woocommerce-box-office-admin',
				'render_callback' => function () {
					$shortcode = new WC_Box_Office_Ticket_Shortcode();
					return $shortcode->ticket_scan_form();
				},
			)
		);

		$scan_ticket_block_data = wp_json_encode(
			array(
				'is_meeting_dependency' => is_plugin_active( 'woocommerce-order-barcodes/woocommerce-order-barcodes.php' ),
			)
		);

		wp_add_inline_script(
			'woocommerce-box-office-scan-ticket-editor-script',
			'const wcboScanTicketBlockData = ' . $scan_ticket_block_data . ';',
		);

		register_block_type(
			WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/private-content',
			array(
				'render_callback' => function ( $attributes ) {
					$params               = array();
					$params['product_id'] = isset( $attributes['productId'] ) ? (int) $attributes['productId'] : 0;

					$shortcode = new WC_Box_Office_Ticket_Shortcode();
					return $shortcode->private_content( $params );
				},
			)
		);

		register_block_type(
			WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/tickets',
			array(
				'render_callback' => function ( $attributes ) {
					$params                = array();
					$params['products']    = isset( $attributes['products'] ) ? (string) $attributes['products'] : '0';
					$params['amount']      = isset( $attributes['numberOfTickets'] ) ? (int) $attributes['numberOfTickets'] : -1;
					$params['order_by']    = isset( $attributes['orderBy'] ) ? (string) $attributes['orderBy'] : 'date';
					$params['order']       = isset( $attributes['order'] ) ? (string) $attributes['order'] : 'DESC';
					$params['avatar_size'] = isset( $attributes['avatarSize'] ) ? (int) $attributes['avatarSize'] : 96;
					$params['columns']     = isset( $attributes['columns'] ) ? (int) $attributes['columns'] : 3;

					$shortcode = new WC_Box_Office_Ticket_Shortcode();
					return $shortcode->display_tickets( $params );
				},
			)
		);

		register_block_type(
			WOOCOMMERCE_BOX_OFFICE_ABSPATH . 'build/admin/blocks/my-ticket',
			array(
				'render_callback' => function ( $attributes ) {
					$params = array();

					if ( isset( $attributes['token'] ) && ! empty( $attributes['token'] ) ) {
						$params['token'] = (string) $attributes['token'];
					} elseif ( ! empty( $_GET['token'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$params['token'] = wc_clean( wp_unslash( $_GET['token'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
					}

					$shortcode = new WC_Box_Office_Ticket_Shortcode();
					return $shortcode->my_ticket( $params );
				},
			)
		);
	}

	/**
	 * Loads the Box-Office specific frontend styles to the editor.
	 */
	public function enqueue_admin_assets() {
		wp_register_style( 'woocommerce-box-office-admin', esc_url( WCBO()->assets_url ) . 'css/frontend.css', array(), WCBO()->_version );
	}
}
