<?php

class Ev_Pizza {

	protected static $_instance = null;
	public $cart                = null;
	public function __construct() {
		add_filter( 'product_type_options', array( $this, 'woo_type_options' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_ev_pizza', array( $this, 'settings_page' ) );
		add_action( 'woocommerce_update_options_ev_pizza', array( $this, 'update_woo_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_product_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_order_scripts' ) );
		add_action( 'admin_head', array( $this, 'for_correct_react' ) );
		// product admin page
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'woo_set_pizza_tabs' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'woo_add_product_pizza_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'woo_save_data' ), 10, 2 );
		// product front page
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'output_pizza_components' ) );

		add_filter( 'product_type_selector', array( $this, 'add_product_type' ) );
		add_filter( 'woocommerce_product_class', array( $this, 'set_product_class' ), 10, 2 );
		// add_action( 'woocommerce_init', array( $this, 'maybe_crate_product' ) );
		add_action( 'wp_footer', array( $this, 'add_templates' ) );
	}

	/**
	 * Add "PBW-Builder" product type.
	 *
	 * @param array $types Product Types.
	 * @since 1.1
	 */
	public function add_product_type( $types ) {
		$types['pbw_product'] = esc_html__( 'PBW-Builder', 'pizza-builder-for-woocommerce' );
		return $types;
	}

	public function set_product_class( $classname, $product_type ) {
		if ( 'pbw_product' === $product_type ) {
			return 'Ev_Pizza_Product_Builder';
		}
		return $classname;
	}

	/**
	 *
	 * @deprecated
	 */
	public function maybe_crate_product() {

		$pizza_s_data = ev_pizza_get_settings();

		if ( isset( $pizza_s_data['shortcode_disable'] ) && $pizza_s_data['shortcode_disable'] ) {
			return;
		}

		$pbw_builder_product = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array( 'pbw_product' ),
					),
				),
			)
		);

		if ( count( $pbw_builder_product ) > 0 ) {
			return;
		}

		$new_pbw_builder = new Ev_Pizza_Product_Builder();
		$new_pbw_builder->set_name( 'PBW-Builder' );
		$new_pbw_builder->set_regular_price( 0 );
		$new_pbw_builder->set_description( 'Pizza Builder for WooCooomerce: Product must be for working Shortcode Builder' );
		$new_pbw_builder->set_catalog_visibility( 'hidden' );
		$new_pbw_builder->set_status( 'private' );

		$new_pbw_builder->save();

	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function woo_type_options( $options ) {
		$options['ev_pizza'] = array(
			'id'            => '_ev_pizza',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label'         => esc_html__( 'Pizza', 'pizza-builder-for-woocommerce' ),
			'default'       => 'no',
		);
		return $options;
	}


	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['ev_pizza'] = esc_html__( 'Pizza', 'pizza-builder-for-woocommerce' );

		return $settings_tabs;
	}
	public function settings_page() {
		wp_nonce_field( 'ev_pizza_woo_settings', '_pizzanonce' );
		?>
		<div id="admin-pizza-app"></div>
		<?php
	}
	public function update_woo_settings() {
		if ( empty( $_POST['_pizzanonce'] ) || ! wp_verify_nonce( $_POST['_pizzanonce'], 'ev_pizza_woo_settings' ) ) {
			return;
		}

		update_option( 'pizza_components_data', sanitize_text_field( $_POST['pizza_components_data'] ) );
		update_option( 'pizza_settings_data', sanitize_text_field( $_POST['pizza_settings_data'] ) );
		if ( isset( $_POST['ev_pizza_shortcodes'] ) ) {
			$pizza_shortcodes = json_decode( wp_unslash( $_POST['ev_pizza_shortcodes'] ), true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				foreach ( $pizza_shortcodes as  $shortcode ) {

					update_option( 'pizza_shortcodes_data_' . $shortcode['id'], wc_clean( $shortcode ) );
				}
			}
			update_option( 'pizza_shortcodes_data', sanitize_text_field( $_POST['ev_pizza_shortcodes'] ) );
		}

	}

	public function admin_scripts() {
		if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'ev_pizza' ) {

			$pizza_data       = json_decode( wp_unslash( get_option( 'pizza_components_data' ) ), true );
			$pizza_components = ! empty( $pizza_data ) ? $pizza_data : false;

			$pizza_settings = ev_pizza_get_settings();

			$pizza_shortcodes_data = json_decode( wp_unslash( get_option( 'pizza_shortcodes_data' ) ), true );
			$pizza_shortcodes      = ! empty( $pizza_shortcodes_data ) ? $pizza_shortcodes_data : false;

			$pbw_builder_products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => array( 'pbw_product' ),
						),
					),
				)
			);

			$pbw_products      = array();
			$smooth_attributes = ev_pizza_get_product_attributes();

			foreach ( $pbw_builder_products as $pbw_builder_product ) {
				$pbw_products[] = array(
					'value' => $pbw_builder_product->ID,
					'title' => $pbw_builder_product->post_title,
				);
			}

			wp_enqueue_media();

			// roboto fonts for material ui.
			wp_enqueue_style( 'pizza-fonts', plugins_url( 'assets/css/fonts.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );

			wp_enqueue_style( 'pizza-style-code', plugins_url( 'assets/css/adminPizza.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );

			$script_asset_path = EV_PIZZA_PATH . 'assets/dist/adminPizza.asset.php';
			$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => filemtime( $script_asset_path ),
			);

			wp_enqueue_script( 'pizza-admin-react', plugins_url( 'assets/dist/adminPizza.js', EV_PIZZA_DIR ), $script_asset['dependencies'], $script_asset['version'], true );
			wp_localize_script(
				'pizza-admin-react',
				'EV_PIZZA_DATA',
				array(
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'url'               => plugins_url( '/assets/', EV_PIZZA_DIR ),
					'pizza_components'  => $pizza_components,
					'pizza_settings'    => $pizza_settings,
					'pizza_shortcodes'  => $pizza_shortcodes,
					'pbw_products'      => $pbw_products,
					'smooth_attributes' => $smooth_attributes,

					'wc_symbol'         => get_woocommerce_currency_symbol(),
					'nonce'             => wp_create_nonce( 'ev-pizza-nonce' ),

				)
			);
		}
		if ( get_post_type() === 'product' ) {
			global $post;
			$product_ev_components_data = json_decode( wp_unslash( get_post_meta( $post->ID, 'product_ev_components_data', true ) ), true );
			$product_ev_components_data = ! empty( $product_ev_components_data ) ? $product_ev_components_data : false;
			$product_ev_components      = json_decode( wp_unslash( get_post_meta( $post->ID, 'product_ev_components', true ) ), true );
			$product_ev_components      = ! empty( $product_ev_components ) ? $product_ev_components : false;
			$product_pizza_data         = json_decode( wp_unslash( get_post_meta( $post->ID, 'product_ev_pizza_full', true ) ), true );
			$product_ev_pizza_full      = ! empty( $product_pizza_data ) ? $product_pizza_data : false;
			$pizza_data                 = json_decode( wp_unslash( get_option( 'pizza_components_data' ) ), true );
			$pizza_components           = ! empty( $pizza_data ) ? $pizza_data : false;

			$exclude_products = array();
			$product          = wc_get_product( $post->ID );
			if ( $product ) {
				$exclude_products = array( $post->ID );
				if ( $product->is_type( 'variable' ) ) {
					$exclude_products = $product->get_children();
					array_push( $exclude_products, $post->ID );
				}
			}

			$wc_products   = wc_get_products(
				array(
					'limit'   => 10,
					'type'    => array( 'simple', 'variation' ),
					'exclude' => $exclude_products,
				)
			);
			$produts_pizza = array();

			foreach ( $wc_products as $product ) {
				$produts_pizza[] = array(
					'label' => $product->get_name(),
					'value' => $product->get_id(),
				);
			}

			wp_enqueue_media();

			// roboto fonts for material ui.
			wp_enqueue_style( 'pizza-fonts', plugins_url( 'assets/css/fonts.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );

			$script_asset_path = EV_PIZZA_PATH . 'assets/dist/adminProductPizza.asset.php';
			$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => filemtime( $script_asset_path ),
			);

			wp_enqueue_script( 'pizza-product-react', plugins_url( 'assets/dist/adminProductPizza.js', EV_PIZZA_DIR ), $script_asset['dependencies'], $script_asset['version'], true );
			wp_localize_script(
				'pizza-product-react',
				'EV_IL_DATA',
				array(
					'ajax_url'                   => admin_url( 'admin-ajax.php' ),
					'url'                        => plugins_url( 'assets/', EV_PIZZA_DIR ),

					'product_ev_components_data' => $product_ev_components_data,
					'product_ev_pizza_full'      => $product_ev_pizza_full,
					'product_ev_components'      => $product_ev_components,
					'pizza_components'           => $pizza_components,
					'products'                   => $produts_pizza,

					'wc_symbol'                  => get_woocommerce_currency_symbol(),
					'nonce'                      => wp_create_nonce( 'ev-pizza-nonce' ),
				)
			);
		}
	}

	public function enqueue_product_scripts() {

		// wp_register_style( 'pizza-slick', plugins_url( 'assets/js/slick/slick.css', EV_PIZZA_DIR ), array(), '1.8.1', 'all' );
		// wp_register_script( 'pizza-slick', plugins_url( 'assets/js/slick/slick.min.js', EV_PIZZA_DIR ), array( 'jquery' ), '1.8.1', true );

		wp_register_style( 'pizza-swiper', plugins_url( 'assets/css/swiper.min.css', EV_PIZZA_DIR ), array(), '4.1', 'all' );
		wp_register_script( 'pizza-swiper', plugins_url( 'assets/js/swiper.min.js', EV_PIZZA_DIR ), array( 'jquery' ), '4.1', true );

		wp_register_script( 'pizza-popper', plugins_url( 'assets/js/tippy/popper.min.js', EV_PIZZA_DIR ), array(), '2.11.0', true );
		wp_register_script( 'pizza-tipps', plugins_url( 'assets/js/tippy/tippy.min.js', EV_PIZZA_DIR ), array(), '6.3.7', true );
		wp_register_script( 'pizza-slimscroller', plugins_url( 'assets/js/slimscroll/jquery.slimscroll.min.js', EV_PIZZA_DIR ), array( 'jquery' ), '1.3.8', true );

		wp_register_style( 'pizza-fancybox', plugins_url( 'assets/js/fancyBox/fancybox.min.css', EV_PIZZA_DIR ), array(), '4.0.7' );
		wp_register_script( 'pizza-fancybox', plugins_url( 'assets/js/fancyBox/fancybox.min.js', EV_PIZZA_DIR ), array( 'jquery' ), '4.0.7', true );

		wp_register_style( 'pizza-front', plugins_url( 'assets/dist/ev-pizza.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );
		wp_register_script( 'pizza-hooks', plugins_url( 'assets/js/pizza-hooks.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'wp-hooks' ), EV_PIZZA_VERSION, true );
		wp_register_script( 'pizza-front', plugins_url( 'assets/js/pizza.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'wp-hooks', 'pizza-hooks' ), EV_PIZZA_VERSION, true );

		// shortcode builder.
		wp_register_style( 'pizza-builder', plugins_url( 'assets/dist/builder.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );
		wp_register_script( 'pizza-builder', plugins_url( 'assets/js/builder.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'wp-hooks', 'pizza-hooks' ), EV_PIZZA_VERSION, true );

		global $post;

		$is_pizza_product = is_product() ? ev_is_pizza_product( $post->ID ) : false;

		if ( $is_pizza_product && ev_pizza_tipps_enabled() ) {
			wp_enqueue_script( 'pizza-popper' );
			wp_enqueue_script( 'pizza-tipps' );

		}
		if ( $is_pizza_product ) {
			wp_enqueue_style( 'pizza-swiper' );
			wp_enqueue_script( 'pizza-swiper' );
			wp_enqueue_style( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-slimscroller' );

			wp_enqueue_style( 'pizza-front' );
			wp_enqueue_script( 'pizza-front' );

		}

		$settings = ev_pizza_get_settings();

		if ( ev_pizza_quick_view_enabled() && is_woocommerce() ) {

			if ( ev_pizza_tipps_enabled() ) {
				wp_enqueue_script( 'pizza-popper' );
				wp_enqueue_script( 'pizza-tipps' );

			}

			wp_enqueue_style( 'pizza-swiper' );
			wp_enqueue_script( 'pizza-swiper' );
			wp_enqueue_style( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-slimscroller' );
			wp_enqueue_script( 'wc-add-to-cart-variation' );

			wp_enqueue_style( 'pizza-front' );
			wp_enqueue_script( 'pizza-front' );

		}

		if ( $settings['meta_popup'] && ( is_cart() || is_checkout() ) ) {
			wp_enqueue_style( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-fancybox' );

		}

		if ( is_cart() || is_checkout() ) {
			wp_enqueue_style( 'pizza-front' );
		}

		wp_enqueue_script( 'pizza-main', plugins_url( 'assets/js/main.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'wp-hooks', 'pizza-hooks' ), EV_PIZZA_VERSION, true );

		wp_localize_script(
			'pizza-front',
			'EV_PIZZA_FRONT',
			array(
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'wc_symbol'           => get_woocommerce_currency_symbol(),
				'price_position'      => get_option( 'woocommerce_currency_pos' ),
				'decimals'            => wc_get_price_decimals(),
				'decimal_separator'   => wc_get_price_decimal_separator(),
				'thousand_separator'  => wc_get_price_thousand_separator(),
				'layer_default_text'  => apply_filters( 'ev_pizza_template_empty_layer_text', __( 'Choose %s flour pizza', 'pizza-builder-for-woocommerce' ) ),
				'layer_default_image' => ev_pizza_get_image_placeholder( 'empty_layer' ),
				'side_default_text'   => apply_filters( 'ev_pizza_empty_side_text', __( 'Choose cheese', 'pizza-builder-for-woocommerce' ) ),
				'side_default_image'  => ev_pizza_get_image_placeholder( 'empty_side' ),
				'tippy_enabled'       => ev_pizza_tipps_enabled(),
				'redirect_cart'       => ev_pizza_redirect_cart(),
				'nonce'               => wp_create_nonce( 'ev-pizza-fnonce' ),
			)
		);
	}

	public function enqueue_order_scripts() {
		global $current_screen;

		if ( $current_screen->id === 'shop_order' || $current_screen->id === 'woocommerce_page_wc-orders' ) {
			wp_enqueue_style( 'pizza-fancybox', plugins_url( 'assets/js/fancyBox/fancybox.min.css', EV_PIZZA_DIR, array(), '4.0.7' ) );
			wp_enqueue_style( 'pizza-admin', plugins_url( 'assets/css/admin.css', EV_PIZZA_DIR ), array(), EV_PIZZA_VERSION, 'all' );
			wp_enqueue_script( 'pizza-fancybox', plugins_url( 'assets/js/fancyBox/fancybox.min.js', EV_PIZZA_DIR ), array( 'jquery' ), '4.0.7', true );
			wp_enqueue_script( 'pizza-hooks', plugins_url( 'assets/js/pizza-hooks.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'wp-hooks' ), EV_PIZZA_VERSION, true );

			wp_enqueue_script( 'pizza-main', plugins_url( 'assets/js/main.js', EV_PIZZA_DIR ), array( 'jquery', 'wp-util', 'pizza-hooks' ), EV_PIZZA_VERSION, true );
		}
	}

	public function for_correct_react() {
		?>
		<style>
			.MuiFormControl-root .MuiFormLabel-root {
				float: none;
				width: auto;
				margin: 0;
			}

			.MuiFormControl-root input[type=color],
			.MuiFormControl-root input[type=date],

			.MuiFormControl-root input[type=number],
			.MuiFormControl-root input[type=text],
			.MuiFormControl-root input[type=tel],
			.MuiFormControl-root select,
			.MuiFormControl-root textarea {
				background-color: transparent;
				border: 0;
				width: 100%;
				padding: 16.5px 14px;
				height: 55px;
				box-sizing: border-box;
			}

			.MuiFormControl-root input[type=checkbox]:focus,
			.MuiFormControl-root input[type=color]:focus,

			.MuiFormControl-root input[type=number]:focus,
			.MuiFormControl-root input[type=password]:focus,
			.MuiFormControl-root input[type=radio]:focus,
			.MuiFormControl-root input[type=text]:focus,
			.MuiFormControl-root input[type=tel]:focus,
			.MuiFormControl-root select:focus,
			.MuiFormControl-root textarea:focus {
				border-color: transparent;
				box-shadow: none;
				outline: 0;
				border-radius: 0;
			}

			.MuiFormControl-root .MuiSwitch-input {
				height: 100%;
			}
		
			#ev_pizza_product_data label {
				float: none;
				width: auto;
				margin-left: -11px;
				margin-right: 16px;
			}

			.ev_pizza_label {
				font-size: 22px;
			}
			#ev_pizza_product_data.woocommerce_options_panel label, #ev_pizza_product_data.woocommerce_options_panel legend {
				float: unset;
				width: auto;
				padding: 0;
				margin: 0;
			}
			
			#pizza-tabpanel-2 .MuiAccordionSummary-root, #pizza-tabpanel-1 .MuiAccordionSummary-root {
				background-color: aliceblue;
			}

			#admin-pizza-app + p.submit {
				display: none !important;
			}

			#ev_pizza_product_data.woocommerce_options_panel .MuiFormHelperText-root {
				padding: 0;
				margin: 0;
			}
		</style>
		<?php
	}

	public function woo_set_pizza_tabs( $tabs ) {
		$tabs['ev_pizza'] = array(
			'label'    => esc_html__( 'Pizza data', 'pizza-builder-for-woocommerce' ),
			'target'   => 'ev_pizza_product_data',
			'class'    => 'show_if_ev_pizza',
			'priority' => 75,

		);
		return $tabs;
	}

	public function woo_add_product_pizza_fields() {
		?>
		<div id="ev_pizza_product_data" class="panel woocommerce_options_panel hidden wc-metaboxes-wrapper">
			<div id="ilfood_data"></div>
		</div>
		<script>
			if (jQuery('#_ev_pizza').is(':checked')) {
				jQuery('.show_if_ev_pizza').show();
			} else {
				jQuery('.show_if_ev_pizza').hide();
			}
			jQuery('#_ev_pizza').on('change', function() {
				if (jQuery(this).is(':checked')) {
					jQuery('.show_if_ev_pizza').show();
				} else {
					jQuery('.show_if_ev_pizza').hide();
				}
			})
		</script>
		<?php
	}

	public function woo_save_data( $post_id, $post ) {
		update_post_meta( $post_id, '_ev_pizza', isset( $_POST['_ev_pizza'] ) ? 'yes' : 'no' );

		if ( isset( $_POST['_ev_pizza'] ) ) {

			update_post_meta( $post_id, 'product_ev_pizza_full', sanitize_text_field( $_POST['product_ev_pizza_full'] ) );
			update_post_meta( $post_id, 'ev_pizza_price_inc', isset( $_POST['ev_pizza_price_inc'] ) ? 1 : 0 );
		}
	}
	/**
	 * Output components
	 */
	public function output_pizza_components( $post_id = false ) {
		 global $post;

		 $post_id = $post_id ? $post_id : $post->ID;
		 $product = wc_get_product( $post_id );
		if ( ! ev_is_pizza_product( $post_id ) ) {
			return;
		}
		$food_components_data = json_decode( wp_unslash( get_post_meta( $post_id, 'product_ev_pizza_full', true ) ), true );
		$food_components_full = ! empty( $food_components_data ) ? $food_components_data : false;
		if ( $food_components_full ) {
			$data = $this->replace_components( $food_components_full );
			wc_get_template(
				'pizza/components.php',
				array(
					'data'    => apply_filters( 'ev_pizza_components_data', $data ),
					'product' => $product,
				),
				'',
				EV_PIZZA_PATH . 'templates/'
			);
		}
	}

	/**
	 * Replace with origin components data for extra components.
	 */
	public function replace_components( $data ) {
		$pizza_components = ev_pizza_get_components();
		$pizza_groups     = ev_pizza_get_groups();
		$new_data         = array();
		foreach ( $data as $group_key => $group ) {

			// consists
			if ( $group_key === 'consists_of' ) {
				$new_data['consists_of'] = array(
					'enabled'  => $group['enabled'],
					'consists' => $group['consists'],
					'to_add'   => array(),

				);
				foreach ( $group['to_add'] as $key => $component ) {
					$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );
					if ( $origin_component ) {
						$origin_component['price']                 = str_replace( ',', '.', $origin_component['price'] );
						$origin_component['price']                 = $origin_component['price'] === '' ? 0 : $origin_component['price'];
						$new_data['consists_of']['to_add'][ $key ] = $origin_component;
					}
				}
			}

			// extra
			if ( $group_key === 'extra' ) {
				$new_data['extra'] = array(
					'enabled'        => $group['enabled'],
					'tabs'           => $group['tabs'],
					'tab_components' => array(),

				);
				foreach ( $group['components'] as $key => $component ) {
					$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );
					if ( $origin_component ) {
						$origin_component['price']         = str_replace( ',', '.', $origin_component['price'] );
						$origin_component['price']         = $origin_component['price'] === '' ? 0 : $origin_component['price'];
						$new_data['extra']['components'][] = $origin_component;
					}
				}

				if ( ! empty( $group['tab_components'] ) ) {
					$index = 0;
					foreach ( $group['tab_components'] as $key => $tab_group ) {
						$origin_group = ev_pizza_find_group_by_id( $tab_group['id'], $pizza_groups );

						if ( ! $origin_group ) {
							continue;
						}
						$new_data['extra']['tab_components'][ $index ] = array(
							'id'         => $origin_group['id'],
							'groupName'  => $origin_group['groupName'],
							'groupImage' => $origin_group['groupImage'],
							'components' => array(),
						);
						foreach ( $tab_group['components'] as $tab_key => $component ) {
							$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );
							if ( $origin_component ) {
								$origin_component['price']                                     = str_replace( ',', '.', $origin_component['price'] );
								$origin_component['price']                                     = $origin_component['price'] === '' ? 0 : $origin_component['price'];
								$new_data['extra']['tab_components'][ $index ]['components'][] = $origin_component;
							}
						}
						$index++;
					}
				}
			}

			// layers
			if ( $group_key === 'layers' ) {
				$new_data['layers'] = array(
					'enabled'    => $group['enabled'],
					'components' => $group['components'],

				);

			}

			// bortik
			if ( $group_key === 'bortik' ) {
				$new_data['bortik'] = array(
					'enabled'    => $group['enabled'],
					'components' => array(),

				);
				foreach ( $group['components'] as $key => $component ) {
					$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );

					if ( $origin_component ) {
						$origin_component['price']          = str_replace( ',', '.', $component['price'] );
						$new_data['bortik']['components'][] = $origin_component;
					}
				}
			}
		}
		$new_data['ev_inc'] = wc_string_to_bool( $data['ev_inc'] );
		$new_data['rules']  = isset( $data['rules'] ) ? $data['rules'] : array();
		$new_data['dodo']   = isset( $data['dodo'] ) ? wc_string_to_bool( $data['dodo'] ) : false;
		return $new_data;
	}

	public function add_templates() {
		wc_get_template(
			'pizza/templates.php',
			array(),
			'',
			EV_PIZZA_PATH . 'templates/'
		);
	}

}
