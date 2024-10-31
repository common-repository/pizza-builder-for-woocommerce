<?php

/**
 * Custom quantity input for different input_names.
 */
function ev_pizza_woo_quantity_input( $args = array(), $product = null, $echo = true ) {
	if ( is_null( $product ) ) {
		$product = $GLOBALS['product'];
	}

	$defaults = array(
		'input_id'     => uniqid( 'quantity_' ),
		'input_name'   => 'quantity',
		'input_value'  => '1',
		'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
		'max_value'    => apply_filters( 'woocommerce_quantity_input_max', -1, $product ),
		'min_value'    => apply_filters( 'woocommerce_quantity_input_min', 0, $product ),
		'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
		'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
		'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
		'product_name' => $product ? $product->get_title() : '',
		'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
	);

	$args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $args, $defaults ), $product );

	// Apply sanity to min/max args - min cannot be lower than 0.
	$args['min_value'] = max( $args['min_value'], 0 );
	$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

	// Max cannot be lower than min if defined.
	if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
		$args['max_value'] = $args['min_value'];
	}

	ob_start();

	wc_get_template( 'global/component-quantity-input.php', $args, '', EV_PIZZA_PATH . 'templates/' );

	if ( $echo ) {

		echo ob_get_clean();
	} else {
		return ob_get_clean();
	}
}

/**
 * Check whether product is Pizza product.
 */
function ev_is_pizza_product( $product_id ) {
	return get_post_meta( $product_id, '_ev_pizza', true ) === 'yes' ? true : false;
}

/**
 * Check if Tipps enabled in Settings page.
 */
function ev_pizza_tipps_enabled() {
	 $pizza_s_data  = ev_pizza_get_settings();
	$pizza_settings = ! empty( $pizza_s_data ) ? $pizza_s_data : false;
	if ( $pizza_settings ) {
		return $pizza_settings['tipps']['enabled'];
	}
	return false;
}

/**
 * Check if Cart/Order meta popup enabled in Settings page.
 *
 * @since 1.1.3
 */
function ev_pizza_meta_popup_enabled() {
	$pizza_s_data   = ev_pizza_get_settings();
	$pizza_settings = ! empty( $pizza_s_data ) ? $pizza_s_data : false;
	if ( ! $pizza_settings ) {
		return true;
	} elseif ( $pizza_settings && isset( $pizza_settings['meta_popup'] ) && $pizza_settings['meta_popup'] ) {
		return true;
	} elseif ( $pizza_settings && ! isset( $pizza_settings['meta_popup'] ) ) {
		return true;
	}
	return false;
}

/**
 * Get style type for product.
 *
 * @since 2.5
 */
function ev_pizza_get_style_type( $product_id ) {
	$food_components_data = json_decode( wp_unslash( get_post_meta( $product_id, 'product_ev_pizza_full', true ) ), true );
	$data                 = ! empty( $food_components_data ) ? $food_components_data : false;
	if ( ! $data ) {
		return '100';
	}

	$dodo_style = ( isset( $data['dodo'] ) && $data['dodo'] );
	if ( $dodo_style ) {
		return '3';
	} elseif ( $data['consists_of']['enabled'] && ! $dodo_style ) {
		return '2';
	} elseif ( $data['extra']['enabled'] ) {
		return '1';
	} else {
		return '100';
	}

}

/**
 * Get image placeholders from Settings page.
 */
function ev_pizza_get_image_placeholder( string $image ) {
	$pizza_s_data   = ev_pizza_get_settings();
	$pizza_settings = ! empty( $pizza_s_data ) ? $pizza_s_data : false;
	if ( $pizza_settings ) {
		if ( $pizza_settings[ $image ] ) {
			if ( isset( $pizza_settings[ $image ]['image_ID'] ) ) {
				return wp_get_attachment_image_url( $pizza_settings[ $image ]['image_ID'], 'medium' );
			}
			return $pizza_settings[ $image ]['image'];
		}
	}
}

/**
 * Get cart url from Settings page.
 */
function ev_pizza_redirect_cart() {
	$pizza_s_data = ev_pizza_get_settings();
	if ( isset( $pizza_s_data['redirect_cart'] ) && $pizza_s_data['redirect_cart'] ) {
		return wc_get_cart_url();
	}

	return false;
}

/**
 * Display list of cart/order meta.
 */
function ev_pizza_display_meta( $item_data, $popup_enabled = true ) {
	ob_start();
	?>
	<ul class="pizza-meta-list">
		<?php if ( isset( $item_data['consists_of']['consists'] ) ) : ?>
			<li>
				<strong><?php echo esc_html( $item_data['consists_of']['consists_text'] ); ?></strong>
				<?php foreach ( $item_data['consists_of']['consists'] as $component ) : ?>
					<p><span><?php echo wp_kses_post( $component['key'] ); ?></span>
					<?php if ( ! $popup_enabled ) : ?>
						<span> - </span>
						<?php endif; ?>
					<span><?php echo wp_kses_post( $component['value'] ); ?></span></p>
				<?php endforeach; ?>
			</li>
		<?php endif; ?>
		<?php if ( isset( $item_data['consists_of']['to_add'] ) ) : ?>
			<li>
				<strong><?php echo esc_html( $item_data['consists_of']['to_add_text'] ); ?></strong>
				<?php foreach ( $item_data['consists_of']['to_add'] as $component ) : ?>
					<p><span><?php echo wp_kses_post( $component['key'] ); ?></span>
					<?php if ( ! $popup_enabled ) : ?>
						<span> - </span>
						<?php endif; ?>
					<span><?php echo wp_kses_post( $component['value'] ); ?></span></p>
				<?php endforeach; ?>
			</li>
		<?php endif; ?>

		<?php if ( isset( $item_data['layers']['components'] ) ) : ?>
			<li>
				<strong><?php echo esc_html( $item_data['layers']['layers_text'] ); ?></strong>
				<?php foreach ( $item_data['layers']['components'] as $component ) : ?>
					<p><span><?php echo wp_kses_post( $component['key'] ); ?></span>
					<?php if ( ! $popup_enabled ) : ?>
						<span> - </span>
						<?php endif; ?>
					<span><?php echo wp_kses_post( $component['value'] ); ?></span></p>
				<?php endforeach; ?>
			</li>
		<?php endif; ?>
		<?php if ( isset( $item_data['bortik']['components'] ) ) : ?>
			<li>
				<strong><?php echo esc_html( $item_data['bortik']['bortik_text'] ); ?></strong>
				<?php foreach ( $item_data['bortik']['components'] as $component ) : ?>
					<p><span><?php echo wp_kses_post( $component['key'] ); ?></span>
					<?php if ( ! $popup_enabled ) : ?>
						<span> - </span>
						<?php endif; ?>
					<span><?php echo wp_kses_post( $component['value'] ); ?></span></p>
				<?php endforeach; ?>
			</li>
		<?php endif; ?>
	</ul>
	<?php
	return ob_get_clean();
}

function ev_pizza_get_components() {
	$pizza_data       = json_decode( wp_unslash( get_option( 'pizza_components_data' ) ), true );
	$pizza_components = ! empty( $pizza_data ) ? $pizza_data : false;
	if ( $pizza_components ) {

		return array_merge( ...wp_list_pluck( $pizza_components, 'components' ) );
	}
	return false;
}
function ev_pizza_get_groups() {
	$pizza_data       = json_decode( wp_unslash( get_option( 'pizza_components_data' ) ), true );
	$pizza_components = ! empty( $pizza_data ) ? $pizza_data : false;
	return $pizza_components;
}

/**
 * Get quick view settings.
 *
 * @since 1.1.6
 */
function ev_pizza_quick_view_enabled() {
	$pizza_s_data = ev_pizza_get_settings();

	return isset( $pizza_s_data['quick_view'] ) ? $pizza_s_data['quick_view'] : true;
}

/**
 * Get shortcode product.
 */

function ev_pizza_shortcode_product() {

	$pizza_s_data = ev_pizza_get_settings();

	return isset( $pizza_s_data['shortcode_product'] ) ? $pizza_s_data['shortcode_product'] : false;

}

function ev_pizza_find_component_by_id( $id, $data ) {
	foreach ( $data as $component ) {
		if ( $component['id'] === $id ) {
			return $component;
		}
	}
	return false;
}

function ev_pizza_find_group_by_id( $id, $data ) {
	foreach ( $data as $group ) {
		if ( $group['id'] === $id ) {
			return $group;
		}
	}
	return false;
}

function ev_pizza_get_settings() {

	$default_args = array(
		'empty_layer'       => array(
			'image' => plugins_url( '/assets/', EV_PIZZA_DIR ) . 'images/placeholder.svg',
		),
		'empty_side'        => array(
			'image' => plugins_url( '/assets/', EV_PIZZA_DIR ) . 'images/placeholder.svg',
		),

		'tipps'             => array(
			'enabled' => false,
		),
		'meta_popup'        => true,

		'redirect_cart'     => false,
		'shortcode_disable' => false,
		'shortcode_product' => '0',
		'quick_view'        => true,
		'smooth_attributes' => array(),
	);

	$settings = wp_cache_get( 'ev_pizza_settings' );
	if ( false === $settings ) {
		$settings = get_option( 'pizza_settings_data' );
		if ( ! $settings ) {
			return $default_args;
		}
		$settings = json_decode( wp_unslash( $settings ), true );
		if ( json_last_error() === JSON_ERROR_NONE ) {

			$settings = wp_parse_args( $settings, $default_args );

			wp_cache_set( 'ev_pizza_settings', $settings );
			return $settings;

		}
		return $default_args;
	}
	return $settings;
}

function ev_pizza_get_attribute_taxonomy_by_name( $attribute_name ) {

	$transient_key = sprintf( 'ev_pizza_cache_attribute_taxonomy__%s', $attribute_name );

	global $wpdb;
	if ( ! taxonomy_exists( $attribute_name ) ) {
		return false;
	}

	if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
		$attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
	} else {
		return false;
	}

	$attribute_taxonomy = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", esc_sql( $attribute_name ) ) );

	return apply_filters( 'ev_pizza_get_wc_attribute_taxonomy', $attribute_taxonomy, $attribute_name );
}

function ev_pizza_get_product_attributes() {

	$terms_data = wp_cache_get( 'wccon_product_attributes_search' );

	$taxonomies = get_taxonomies( array( 'object_type' => array( 'product' ) ), 'objects' );

	if ( $terms_data === false ) {
		$attr_labels = wc_get_attribute_taxonomy_labels();
		$terms_data  = array();

		foreach ( $taxonomies as $key => $taxonomy ) {
			if ( ! preg_match( '/^pa_/', $taxonomy->name ) ) {
				continue;
			}

			$taxonomy_name = $attr_labels[ str_replace( 'pa_', '', $taxonomy->name ) ];
			$terms_data[]  = array(
				'label' => $taxonomy_name,
				'value' => $taxonomy->name,
			);
		}

		wp_cache_set( 'wccon_product_attributes_search', $terms_data );

	}
	return $terms_data;
}
