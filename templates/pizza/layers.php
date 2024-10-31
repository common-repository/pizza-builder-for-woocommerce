<?php

/**
 * Template for layers fancybox
 */
defined( 'ABSPATH' ) || exit;


?>

<div class="pizza-fancybox-layers">
	<div class="pizza-layers-selected__footermobile">
		<div class="ev_pizza_total">
			<span class="layers-total"><?php esc_html_e( 'Total:', 'pizza-builder-for-woocommerce' ); ?></span>
			<span class="layers-total-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>
		<button class="ev-pizza-button choose-layer-button"><?php echo apply_filters( 'ev_pizza_add_layer_button', esc_html( __( 'Choose', 'pizza-builder-for-woocommerce' ) ) ); ?></button>
	</div>
	<div class="pizza-layers-block">
		
		<?php foreach ( $food_components_full['layers']['components'] as $product_id ) : ?>
			<?php
			$inner_product = wc_get_product( $product_id );

			?>
			<div class="pizza-layer-item" data-layer="<?php echo esc_attr( $product_id ); ?>" data-layer-price="<?php echo esc_attr( Ev_Pizza_Product::get_product( $inner_product )->get_price() ); ?>">

				<?php echo $inner_product->get_image(); ?>

				<span class="ev-pizza-title"><?php echo wp_kses_post( $inner_product->get_name() ); ?></span>
				<span class="ev-pizza-price"><?php echo wp_kses_post( $inner_product->get_price_html() ); ?> </span>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="pizza-layers-selected">
		<div class="pizza-layers-selected__header">
			<span><?php echo apply_filters( 'ev_pizza_add_layer_title', wp_kses_post( '<span class="pizza-highlight">' . __( 'Add', 'pizza-builder-for-woocommerce' ) . '</span><span>' . __( ' layer', 'pizza-builder-for-woocommerce' ) . '</span>' ) ); ?></span>
		</div>
		<div class="pizza-layers-selected__block">
			<div class="pizza-layers-selected__item" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
				<div class="pizza-layers-left">
					<?php echo wp_kses_post( $product->get_image() ); ?>
				</div>
				<div class="pizza-layers-right">
					<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
					<span class="pizza-variable-price"><?php echo $product->get_price_html(); ?> </span>
				</div>
			</div>
			<?php foreach ( range( 2, apply_filters( 'ev_pizza_layers_count', 3 ) ) as $item ) : ?>
				<div class="pizza-layers-selected__item" data-product-id="">
					<a href="#" class="ev-remove-layer">
						<svg width="10" height="9" viewBox="0 0 10 9" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M5.00426 3.44918L7.97954 0H9.90622L5.98465 4.46291L10 9H8.05627L5.00426 5.48901L1.93521 9H0L4.02387 4.46291L0.0937766 0H2.01194L5.00426 3.44918Z" fill="#C3C3C3" />
						</svg>
					</a>
					<div class="pizza-layers-left">

						<img src="<?php echo esc_url( ev_pizza_get_image_placeholder( 'empty_layer' ) ); ?>" alt="">

					</div>
					<div class="pizza-layers-right">
						<span class="pizza-text-placeholder"><?php echo apply_filters( 'ev_pizza_empty_layer_text', sprintf( esc_html__( 'Choose %d flour pizza', 'pizza-builder-for-woocommerce' ), $item ) ); ?></span>

					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="pizza-layers-selected__footer">
			<div class="ev_pizza_total">
				<span class="layers-total"><?php esc_html_e( 'Total:', 'pizza-builder-for-woocommerce' ); ?></span>
				<span class="layers-total-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<button class="ev-pizza-button choose-layer-button"><?php echo apply_filters( 'ev_pizza_add_layer_button', esc_html__( 'Choose', 'pizza-builder-for-woocommerce' ) ); ?></button>
		</div>
	</div>
</div>
