<?php

/**
 * Template for sides fancybox
 */
defined( 'ABSPATH' ) || exit;


?>

<div class="pizza-fancybox-sides">
	<div class="pizza-layers-selected__footermobile">
		<div class="ev_pizza_total">
			<span class="layers-total"><?php esc_html_e( 'Total:', 'pizza-builder-for-woocommerce' ); ?></span>
			<span class="layers-total-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>
		<button class="ev-pizza-button choose-side-button"><?php echo apply_filters( 'ev_pizza_add_side_button', esc_html__( 'Choose', 'pizza-builder-for-woocommerce' ) ); ?></button>
	</div>
	<div class="pizza-layers-block">
		<?php foreach ( $food_components_full['bortik']['components'] as $c ) : ?>
			<div class="pizza-layer-item" data-side-id="<?php echo esc_attr( $c['id'] ); ?>">

				<img src="<?php echo esc_url( wp_get_attachment_image_url( $c['image_ID'], 'medium' ) ); ?>" alt="">

				<span class="ev-pizza-title"><?php echo esc_html( $c['name'] ); ?></span>

				<?php if ( ! empty( $c['weight'] ) ) : ?>
					<p><span class="ev-pizza-weight"><?php echo esc_html( $c['weight'] ) . '/'; ?></span><span class="ev-pizza-price"><?php echo wc_price( $c['price'] ); ?> </span> </p>
				<?php else : ?>
					<span class="ev-pizza-price"><?php echo wc_price( $c['price'] ); ?></span>
				<?php endif; ?>

			</div>
		<?php endforeach; ?>
	</div>
	<div class="pizza-layers-selected">
		<div class="pizza-layers-selected__header">
			<span><?php echo apply_filters( 'ev_pizza_add_side_title', wp_kses_post( '<span class="pizza-highlight">' . __( 'Choose', 'pizza-builder-for-woocommerce' ) . '</span><span>' . __( ' side', 'pizza-builder-for-woocommerce' ) . '</span>' ) ); ?></span>
		</div>
		<div class="pizza-layers-selected__block">


			<div class="pizza-layers-selected__item pizza-sides-selected__item">

				<div class="pizza-layers-left">
					<img src="<?php echo esc_url( ev_pizza_get_image_placeholder( 'empty_side' ) ); ?>" alt="">
				</div>
				<div class="pizza-layers-right">
					<span class="pizza-text-placeholder"><?php echo apply_filters( 'ev_pizza_empty_side_text', esc_html__( 'Choose cheese', 'pizza-builder-for-woocommerce' ) ); ?></span>

				</div>
			</div>

		</div>
		<div class="pizza-layers-selected__footer">
			<div class="ev_pizza_total">
				<span class="layers-total"><?php esc_html_e( 'Total:', 'pizza-builder-for-woocommerce' ); ?></span>
				<span class="layers-total-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<button class="ev-pizza-button choose-side-button"><?php echo apply_filters( 'ev_pizza_add_side_button', esc_html__( 'Choose', 'pizza-builder-for-woocommerce' ) ); ?></button>
		</div>
	</div>
</div>
