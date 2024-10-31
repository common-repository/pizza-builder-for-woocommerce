<?php
class Ev_Pizza_Product {


	private static $instances = array();
	private $data;

	public function __construct( $product ) {
		$this->data = $product;
	}
	public static function get_product( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}
		if ( ! $product ) {
			return;
		}
		// if (!ev_is_pizza_product($product->get_id())) {
		// return;
		// }
		if ( ! array_key_exists( $product->get_id(), self::$instances ) ) {
			self::$instances[ $product->get_id() ] = new self( $product );
		}
		return self::$instances[ $product->get_id() ];
	}
	public function get_price( $pr = '' ) {
		if ( $pr === '' ) {
			$price = $this->data->get_price();
		} else {
			$price = $pr;
		}

		if ( $this->is_price_inc() ) {
			$components = $this->get_consists_components();
			if ( $components ) {

				foreach ( $components as $c ) {
					$price += (float) $c['price'];
				}
			}
		}
		return $price;
	}
	public function get_wc_product() {
		return $this->data;
	}
	public function is_on_sale() {
		return $this->data->is_on_sale();
	}
	public function get_price_suffix() {
		return $this->data->get_price_suffix();
	}
	public function get_regular_price() {
		$price = $this->data->get_regular_price();
		if ( $this->is_price_inc() ) {
			$components = $this->get_consists_components();
			if ( $components ) {

				foreach ( $components as $c ) {
					$price += (float) $c['price'];
				}
			}
		}
		return $price;
	}
	/**
	 * Check whether Consist of block enabled to recalculate product price
	 */
	public function is_price_inc() {
		$product_id = $this->data->get_parent_id() ? $this->data->get_parent_id() : $this->data->get_id();
		return get_post_meta( $product_id, 'ev_pizza_price_inc', true ) && $this->is_consists_enabled();
	}
	public function get_consists_components() {
		 $product_id = $this->data->get_parent_id() ? $this->data->get_parent_id() : $this->data->get_id();

		$food_components_data = json_decode( wp_unslash( get_post_meta( $product_id, 'product_ev_pizza_full', true ) ), true );
		$food_components_full = ! empty( $food_components_data ) ? $food_components_data : false;
		if ( $food_components_full && isset( $food_components_full['consists_of']['consists'] ) ) {
			return $food_components_full['consists_of']['consists'];
		}
		return false;
	}
	public function is_consists_enabled() {
		 $product_id          = $this->data->get_parent_id() ? $this->data->get_parent_id() : $this->data->get_id();
		$food_components_data = json_decode( wp_unslash( get_post_meta( $product_id, 'product_ev_pizza_full', true ) ), true );
		$food_components_full = ! empty( $food_components_data ) ? $food_components_data : false;
		if ( $food_components_full && isset( $food_components_full['consists_of']['consists'] ) ) {
			if ( $food_components_full['consists_of']['enabled'] ) {
				return true;
			}
		}
		return false;
	}
}
