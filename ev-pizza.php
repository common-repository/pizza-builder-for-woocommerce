<?php
/**
 * Plugin Name: Pizza Builder for WooCommerce
 * Author: wsjrcatarri
 * Description: Create components for WooCommerce product.
 * Version: 2.5
 * Requires at least: 5.5
 * Requires PHP: 7.4
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pizza-builder-for-woocommerce
 * WC requires at least: 5.0.0
 * WC tested up to:      8.8.2
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'EV_PIZZA_PATH' ) ) {
	define( 'EV_PIZZA_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'EV_PIZZA_DIR' ) ) {
	define( 'EV_PIZZA_DIR', __FILE__ );
}

if ( ! defined( 'EV_PIZZA_VERSION' ) ) {
	define( 'EV_PIZZA_VERSION', '2.0' );
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	exit;
}

class Ev_Pizza_Install {

	protected static $instance = null;


	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->include();
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta_links' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	public function include() {
		require_once EV_PIZZA_PATH . 'includes/pizza-functions.php';
		require_once EV_PIZZA_PATH . 'includes/class-pizza-product-builder.php';
		require_once EV_PIZZA_PATH . 'includes/pizza.php';
		require_once EV_PIZZA_PATH . 'includes/pizza-product.php';

		require_once EV_PIZZA_PATH . 'includes/pizza-shortcode.php';
		require_once EV_PIZZA_PATH . 'includes/pizza-display.php';
		require_once EV_PIZZA_PATH . 'includes/pizza-ajax.php';

		require_once EV_PIZZA_PATH . 'includes/pizza-cart.php';
		require_once EV_PIZZA_PATH . 'includes/pizza-checkout.php';

		$pizza_class = Ev_Pizza::instance();

		new Ev_Pizza_Shortcode();
		new Ev_Pizza_Display();
		new Ev_Pizza_Ajax();
		$pizza_class->cart = new Ev_Pizza_Cart();
		new Ev_Pizza_Checkout();

	}

	/**
	 * Load the textdomain based on WP language.
	 */
	public function load_textdomain() {

		load_plugin_textdomain( 'pizza-builder-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

	/**
	 * Add donate link.
	 */
	public function plugin_meta_links( $links, $file ) {
		if ( strpos( $file, 'ev-pizza.php' ) !== false ) {

			$links[] = '<a target="_blank" href="https://www.paypal.com/donate/?hosted_button_id=K22Z8AKBAJMVS">' . __( 'Donate to author', 'pizza-builder-for-woocommerce' ) . '</a>';

		}

		return $links;
	}

	/**
	 * Add Settings.
	 */
	public function plugin_action_links( $links ) {
		$settings_links = array(
			'settings'      => '<a href="' . esc_url( $this->get_settings_link() ) . '" aria-label="' . esc_attr__( 'View Pizza settings', 'pizza-builder-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'pizza-builder-for-woocommerce' ) . '</a>',
			'documentation' => '<a href="https://pizza.evelynwaugh.com.ua/first-steps/" target="_blank" aria-label="' . esc_attr__( 'View Documentation', 'pizza-builder-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'pizza-builder-for-woocommerce' ) . '</a>',
		);
		return array_merge( $settings_links, $links );
	}

	public function get_settings_link() {

		$params = array(
			'page' => 'wc-settings',
			'tab'  => 'ev_pizza',
		);

		return add_query_arg( $params, admin_url( 'admin.php' ) );
	}
}

function ev_pizza_initialize() {
	$errors = array();

	// php verison check.
	if ( ! function_exists( 'phpversion' ) || version_compare( phpversion(), '7.4', '<' ) ) {
		$errors[] = 'php_error';
	}

	// Wocommerce version check.
	if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, '5.0.0', '<' ) ) {
		$errors[] = 'wc_error';
	}

	if ( count( $errors ) > 0 ) {
		add_action(
			'admin_notices',
			function () use ( $errors ) {
				if ( in_array( 'php_error', $errors ) ) {

					?>
				<div class="notice notice-error">
					<p>
						<?php _e( 'Pizza Builder for WooCommerce requires at least 7.4 php version', 'pizza-builder-for-woocommerce' ); ?>
					</p>
				</div>
					<?php
				}
				if ( in_array( 'wc_error', $errors ) ) {

					?>
				<div class="notice notice-error">
					<p>
						<?php _e( 'WooCommerce must be active and have at least 5.0.0 version', 'pizza-builder-for-woocommerce' ); ?>
					</p>
				</div>
					<?php

				}
			}
		);
		return false;
	}

	Ev_Pizza_Install::instance();
}

add_action( 'plugins_loaded', 'ev_pizza_initialize', 5 );

function ev_pizza() {
	return Ev_Pizza::instance();
}

/**
 * HPOS compatible.
 */
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
