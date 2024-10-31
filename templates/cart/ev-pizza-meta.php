<?php

/**
 * Template Pizza fancybox on Cart/Checkout pages
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<?php if ( ev_pizza_meta_popup_enabled() ) : ?>
<div class="pizza-composition-block">
	<div class="pizza-composition-toggle" data-product-id='<?php echo esc_attr( $key ); ?>'>
		<span><?php echo apply_filters( 'ev_pizza_composition_text', esc_html( __( 'Components', 'pizza-builder-for-woocommerce' ) ), $product->get_id() ); ?></span>
	</div>

</div>
<div id="ev-pizza-<?php echo esc_attr( $key ); ?>" class="ev-pizza-fancy-ingredients" style="display: none;">
	<?php echo ev_pizza_display_meta( $item_data, true ); ?>
</div>
<?php else : ?>
	<?php echo ev_pizza_display_meta( $item_data, false ); ?>
	<?php endif; ?>
