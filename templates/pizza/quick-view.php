<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// global $post;

$product = wc_get_product( $product_id );

// Ensure visibility.
if ( ! $product || ! $product->is_visible() ) {
	return;
}

$classes   = array();
$classes[] = 'pizza-quick-view single-product-content quick-view-horizontal';

wc_set_loop_prop( 'ev_quick_view', 'quick-view' );


/**
 * woocommerce_before_single_product hook.
 *
 * @hooked wc_print_notices - 10
 */
	do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}

$product_summary_class = '';

if ( is_rtl() ) {
	$product_summary_class .= ' text-right';
} else {
	$product_summary_class .= ' text-left';
}


$attachment_ids = $product->get_gallery_image_ids();

$attachment_count = count( $attachment_ids );
$gallery_classes  = apply_filters( 'ev_pizza_galerry_classes', '', $product );


?>

<div id="product-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>

	<div class="pizza-quick-view-summary">
		<div class="pizza-product-images woocommerce-product-gallery">
			<div class="pizza-images-container images">
				<div class="woocommerce-product-gallery__wrapper quick-view-gallery <?php echo esc_attr( $gallery_classes ); ?>">
					<?php
						$attributes = array(
							'title' => esc_attr( get_the_title( get_post_thumbnail_id() ) ),
						);

						if ( has_post_thumbnail() ) {
							echo '<figure class="woocommerce-product-gallery__image">' . get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'woocommerce_single' ), $attributes ) . '</figure>';

							if ( $attachment_count > 0 ) {
								foreach ( $attachment_ids as $attachment_id ) {
									echo '<div class="product-image-wrap"><figure class="woocommerce-product-gallery__image">' . wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'woocommerce_single' ), false, array( 'class' => 'wp-post-image' ) ) . '</figure></div>';
								}
							}
						} else {
							echo '<figure class="woocommerce-product-gallery__image--placeholder">' . apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" />', wc_placeholder_img_src(), __( 'Placeholder', 'pizza-builder-for-woocommerce' ) ), $post->ID ) . '</figure>';
						}
						?>
				</div>
	
			</div>
		</div>
		<div class="summary entry-summary<?php echo esc_attr( $product_summary_class ); ?>">
			<div class="ev-pizza-scroll">
				<div class="summary-inner ev-scroll-content">
					<?php
						/**
						 * woocommerce_single_product_summary hook
						 *
						 * @hooked woocommerce_template_single_title - 5
						 * @hooked woocommerce_template_single_rating - 10
						 * @hooked woocommerce_template_single_price - 10
						 * @hooked woocommerce_template_single_excerpt - 20
						 * @hooked woocommerce_template_loop_add_to_cart - 30
						 * @hooked woocommerce_template_single_meta - 40
						 * @hooked woocommerce_template_single_sharing - 50
						 */
						do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
			</div>
		</div><!-- .summary -->
	</div>


</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
