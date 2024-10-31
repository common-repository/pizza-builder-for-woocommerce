<?php
class Ev_Pizza_Product_Builder extends WC_Product {

	/**
	 * Constructor.
	 *
	 * @since 1.1
	 * @param int|WC_Product|object $product Product ID, post object, or product object.
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = 'pbw_product';
		parent::__construct( $product );
	}

	/**
	 * Get product type.
	 *
	 * @since 1.1
	 */
	public function get_type() {
		return 'pbw_product';
	}

	/**
	 * Get catalog visibility.
	 *
	 * @since 1.1
	 */
	public function get_catalog_visibility( $context = 'view' ) {
		return 'hidden';
	}

	public function is_visible() {
		return false;
	}

	public function is_sold_individually() {
		return false;
	}

	public function is_purchasable() {
		return true;
	}

}
