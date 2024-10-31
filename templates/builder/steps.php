<?php

/**
 * Template for steps builder
 */
defined( 'ABSPATH' ) || exit;

$is_modal  = 'modal' === $type || 'steps-slider' === $type;
$data_type = $is_modal ? 'modal' : $type;
?>

<?php if ( $is_modal ) : ?>
<div class="pbw-builder-button" data-steps="<?php echo esc_attr( $id ); ?>">
	<button><?php echo esc_html( apply_filters( 'ev_pizza_builder_button', $title ) ); ?></button>
</div>
<?php endif; ?>

<div class="pbw-builder-wrapper" id="<?php echo esc_attr( 'pbw-builder-wrapper-' . $id ); ?>" data-cols="<?php echo esc_attr( $columns ); ?>" data-type="<?php echo esc_attr( $data_type ); ?>" data-steps="<?php echo esc_attr( $id ); ?>" style="<?php echo $is_modal ? 'display:none;' : ''; ?>">

	<div class="pbw-builder-title">
		<?php echo wp_kses_post( $title ); ?>
	</div>
	<form class="pbw-builder-form">
		
	<div class="pbw-builder-steps">

		<input type="hidden" name="pbw_components" value="" />
		
			<?php if ( ! wp_is_mobile() ) : ?>
			<button class="pbw-prev">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.16797 10H15.8346" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.5 5L16.5 10L11.5 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<button class="pbw-next">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.16797 10H15.8346" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.5 5L16.5 10L11.5 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<?php endif; ?>
		
		<?php
		if ( ! empty( $data['components'] ) ) :
			foreach ( $data['components'] as $key => $step ) :
				$step_classes = array( 'pbw-builder-step', 'pbw-builder-step-block' );
				if ( 0 === $key ) {
					$step_classes[] = 'active';
				}
				?>
		<div class="<?php echo esc_attr( implode( ' ', $step_classes ) ); ?>" data-step="<?php echo esc_attr( $step['step'] ); ?>">
			<div class="pbw-builder-step__header">
				<?php if ( wp_is_mobile() ) : ?>
			<button class="pbw-prev">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.16797 10H15.8346" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.5 5L16.5 10L11.5 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
	
			<?php endif; ?>
				<span><?php echo esc_html( $step['step'] . '. ' . $step['title'] ); ?></span>
				<?php if ( wp_is_mobile() ) : ?>
					<button class="pbw-next">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.16797 10H15.8346" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M11.5 5L16.5 10L11.5 15" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
				<?php endif; ?>
			</div>
			<div class="pbw-builder-step__components">
				<?php
				if ( ! empty( $step['components'] ) ) :
					foreach ( $step['components'] as $component ) :
						?>
						
					<div class="pbw-builder-step__component" data-component="<?php echo esc_attr( $component['id'] ); ?>">
						<div class="pbw-builder-step__inner">
						<?php if ( ev_pizza_tipps_enabled() && trim( $component['description'] ) !== '' ) : ?>
									<div class="pizza-tippy" data-tippy-content="<?php echo esc_attr( $component['description'] ); ?>">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16ZM10.75 10C10.75 9.30964 11.3096 8.75 12 8.75C12.6904 8.75 13.25 9.30964 13.25 10V10.1213C13.25 10.485 13.1055 10.8338 12.8483 11.091L11.4697 12.4697C11.1768 12.7626 11.1768 13.2374 11.4697 13.5303C11.7626 13.8232 12.2374 13.8232 12.5303 13.5303L13.909 12.1517C14.4475 11.6132 14.75 10.8828 14.75 10.1213V10C14.75 8.48122 13.5188 7.25 12 7.25C10.4812 7.25 9.25 8.48122 9.25 10V10.5C9.25 10.9142 9.58579 11.25 10 11.25C10.4142 11.25 10.75 10.9142 10.75 10.5V10Z" fill="#fff" />
										</svg>
									</div>
								<?php endif; ?>
						<div class="pbw-builder-step__name"><?php echo esc_html( $component['name'] ); ?></div>
						<div class="pbw-builder-step__image">
							<img src="<?php echo esc_url( wp_get_attachment_image_url( $component['image_ID'], 'medium' ) ); ?>" />
							
						</div>
						<div class="pbw-builder-step__price"><?php echo wc_price( $component['price'] ); ?></div>
						</div>
					</div>
						<?php
					endforeach;
				endif;
				?>
			</div>

		</div>
		
				<?php
			endforeach;
		endif;
		?>
	</div>
	<div class="pbw-builder-step__choosen pbw-builder-step active" style="display:<?php echo esc_attr( 0 === $key ? 'none' : 'block' ); ?>">
			<div class="pbw-builder-step__header">
				<span><?php esc_html_e( 'Your product', 'pizza-builder-for-woocommerce' ); ?></span>
				<span><?php esc_html_e( 'Total:', 'pizza-builder-for-woocommerce' ); ?><span class="pbw-total"></span></span>
				
				<input type="hidden" name="action" value="ev_builder_product">
				<input type="hidden" name="pbw-shortcode-id" value="<?php echo esc_attr( $id ); ?>">
				<?php if ( ! wp_is_mobile() ) : ?>
					<button class="pbw-place-order"><?php esc_html_e( 'Buy', 'pizza-builder-for-woocommerce' ); ?></button>
					<?php endif; ?>
			</div>
			<div class="pbw-builder-step__components">
				
			</div>
	</div>
	<?php if ( wp_is_mobile() ) : ?>
					<button class="pbw-place-order"><?php esc_html_e( 'Buy', 'pizza-builder-for-woocommerce' ); ?></button>
					<?php endif; ?>
	</form>
		<div class="pbw-success-container"></div>
</div>
