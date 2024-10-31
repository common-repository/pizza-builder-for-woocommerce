<?php
class Ev_Pizza_Ajax {
	public function __construct() {

		// admin.
		add_action( 'wp_ajax_ev_pizza_products', array( $this, 'get_products' ) );
		add_action( 'wp_ajax_nopriv_ev_pizza_products', array( $this, 'get_products' ) );

		add_action( 'wp_ajax_ev_pizza_get_layers', array( $this, 'get_layers_data' ) );
		add_action( 'wp_ajax_nopriv_ev_pizza_get_layers', array( $this, 'get_layers_data' ) );

		add_action( 'wp_ajax_ev_pizza_save_shortcodes', array( $this, 'save_shortcodes' ) );
		add_action( 'wp_ajax_ev_pizza_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_ev_pizza_save_components', array( $this, 'save_components' ) );
		add_action( 'wp_ajax_ev_pizza_save_shop', array( $this, 'save_shop' ) );

		// front.
		add_action( 'wp_ajax_ev_builder_product', array( $this, 'ev_builder_product' ) );
		add_action( 'wp_ajax_nopriv_ev_builder_product', array( $this, 'ev_builder_product' ) );

		add_action( 'wp_ajax_ev_pizza_show_fancy', array( $this, 'show_fancy_archive' ) );
		add_action( 'wp_ajax_nopriv_ev_pizza_show_fancy', array( $this, 'show_fancy_archive' ) );

		add_action( 'wp_ajax_ev_pizza_add_product', array( $this, 'add_product' ) );
		add_action( 'wp_ajax_nopriv_ev_pizza_add_product', array( $this, 'add_product' ) );

	}

	public function get_products() {
		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );
		if ( ! isset( $_POST['field'] ) ) {
			wp_send_json_error();
		}
		global $wpdb;
		$user_field = sanitize_text_field( wp_unslash( $_POST['field'] ) );

		$product = '%' . $wpdb->esc_like( $user_field ) . '%';
		$result  = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type IN ('product', 'product_variation') AND post_status = 'publish' AND post_title LIKE %s ", $product ), ARRAY_A );

		wp_send_json_success(
			array(
				'fields' => $result,

			)
		);
	}

	public function get_layers_data() {
		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );
		$product_ids = wc_clean( json_decode( wp_unslash( $_POST['ids'] ) ), true );

		$returns_data = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$returns_data[] = array(
					'label' => $product->get_name(),
					'value' => $product_id,
				);
			}
		}
		wp_send_json_success(
			array(
				'products' => $returns_data,
			)
		);

	}

	public function ev_builder_product() {

		check_ajax_referer( 'ev-builder-nonce', 'nonce' );

		$pizza_s_data = ev_pizza_get_settings();
		if ( isset( $pizza_s_data['shortcode_disable'] ) && $pizza_s_data['shortcode_disable'] ) {
			wp_send_json_error( 'This feature is disabled' );
		}

		$pbw_builder_product = ev_pizza_shortcode_product();

		if ( ! $pbw_builder_product ) {
			wp_send_json_error( 'Can\'t add product' );
		}

		$components_ids = json_decode( wp_unslash( $_POST['pbw_components'] ), true );
		$shortcode_id   = intval( $_POST['pbw-shortcode-id'] );
		$data           = get_option( 'pizza_shortcodes_data_' . $shortcode_id );
		if ( ! $data ) {
			wp_send_json_error( 'No data' );
		}
		$meta_data = array();
		foreach ( $data['components'] as $step ) {

			foreach ( $step['components'] as $component ) {
				if ( in_array( $component['id'], $components_ids ) ) {
					$meta_data['pbw_components'][] = $component;
				}
			}
		}
		$meta_data['pbw_product_name'] = $data['title'];
		$quantity                      = 1;
		$product_id                    = $pbw_builder_product;
		$passed_validation             = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, '', '', $meta_data ) ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}

			WC_AJAX::get_refreshed_fragments();

		} else {
			wp_send_json_error(
				array(
					'message' => 'Not added',
					'valid'   => $passed_validation,
				)
			);
		}
		wp_send_json_success();

	}

	public function show_fancy_archive() {

		$post_id = absint( wp_unslash( $_GET['post_id'] ) );

		if ( ! $post_id ) {
			wp_die();
		}

		ob_start();

		$post_object = get_post( $post_id );
		setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

		// ev_pizza()->output_pizza_components( $post_id );
		wc_get_template(
			'pizza/quick-view.php',
			array(
				'product_id' => $post_id,

			),
			'',
			EV_PIZZA_PATH . 'templates/'
		);

		wp_reset_postdata();
		$html = ob_get_clean();
			echo $html;
		wp_die();
	}

	public function add_product() {

		check_ajax_referer( 'ev-pizza-fnonce', 'nonce' );

		$product_id   = absint( wp_unslash( $_POST['product_id'] ) );
		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : '';
		$variation    = '';
		$quantity     = absint( wp_unslash( $_POST['quantity'] ) );

		$posted_data = wc_clean( $_POST );

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		$cart_item_data = apply_filters( 'ev_pizza_cart_item_data', array(), $product_id );

		if ( $passed_validation ) {
			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
		}

		do_action( 'ev_pizza_after_add_products', $posted_data, $product_id );

		ob_start();

		wc_print_notices();

		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();

		$return_data = array(
			'fragments' => apply_filters(
				'woocommerce_add_to_cart_fragments',
				array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',

				)
			),
			'cart_hash' => WC()->cart->get_cart_hash(),
			'redirect'  => wc_get_cart_url(),
			'cart'      => WC()->cart->get_cart(),
		);

		$respose_args = apply_filters(
			'ev_pizza_buy_products_response_args',
			$return_data
		);

		wp_send_json_success( $respose_args );
	}

	public function save_settings() {
		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );
		$data = wc_clean( json_decode( wp_unslash( $_POST['data'] ), true ) );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			update_option( 'pizza_settings_data', wc_clean( wp_unslash( $_POST['data'] ) ) );

			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public function save_components() {
		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );
		$data = wc_clean( json_decode( wp_unslash( $_POST['data'] ), true ) );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			update_option( 'pizza_components_data', wc_clean( wp_unslash( $_POST['data'] ) ) );

			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public function save_shop() {
		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );
		$data = wc_clean( json_decode( wp_unslash( $_POST['data'] ), true ) );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			update_option( 'pizza_settings_data', wc_clean( wp_unslash( $_POST['data'] ) ) );

			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public function save_shortcodes() {

		check_ajax_referer( 'ev-pizza-nonce', 'nonce' );

		$pizza_shortcodes = wc_clean( json_decode( wp_unslash( $_POST['data'] ), true ) );

		if ( json_last_error() === JSON_ERROR_NONE ) {

			foreach ( $pizza_shortcodes as  $shortcode ) {

				update_option( 'pizza_shortcodes_data_' . $shortcode['id'], wc_clean( $shortcode ) );
			}

			update_option( 'pizza_shortcodes_data', wc_clean( wp_unslash( $_POST['data'] ) ) );

			wp_send_json_success();

		}

		wp_send_json_error();
	}
}
