<?php

/**
 * Template for whole components block
 */
defined( 'ABSPATH' ) || exit;


?>
<?php
$pizza_components = ev_pizza_get_components();

$data_json     = wp_json_encode( $data );
$data_attr     = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data_json ) : _wp_specialchars( $data_json, ENT_QUOTES, 'UTF-8', true );
$product_pizza = Ev_Pizza_Product::get_product( $product );
$product_price = $product_pizza->get_price();
$dodo_style    = ( isset( $data['dodo'] ) && $data['dodo'] );
?>
<div class="pizza_components_wrapper" data-pizza="<?php echo $data_attr; ?>" data-price="<?php echo esc_attr( $product_price ); ?>" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
	
	<?php if ( $dodo_style ) : ?>

		<div class="pizza-components-block pizza-dodo-style">

			<div data-id="remove-component" class="pizza-base-components">
				<?php if ( ! empty( $data['consists_of']['consists'] ) ) : ?>
						<?php
						$pizza_consists_of = array();
						foreach ( $data['consists_of']['consists'] as $c ) {
							$pizza_consists_of[] = array( $c['id'] => true );
						}
						$pizza_consists_of_json = wp_json_encode( $pizza_consists_of );
						$pizza_consists_of_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $pizza_consists_of_json ) : _wp_specialchars( $pizza_consists_of_json, ENT_QUOTES, 'UTF-8', true );

						$consists_index = 0;
						?>
						<?php foreach ( $data['consists_of']['consists'] as $c ) : ?>
							<?php
							if ( ! $c['visible'] ) {
								continue;
							}
							$consists_index++;
							if ( $consists_index !== 1 ) {
								echo ', ';
							}
							?>
					
							<?php if ( ! $c['required'] ) : ?>
								<button class="pizza-base-item" data-component-id="<?php echo esc_attr( $c['id'] ); ?>">
									<span class="pizza-component-name"><?php echo esc_html( $c['name'] ); ?></span>
									<span class="pizza-component-icon">	
										<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" ><path d="M13.4,12l6.3-6.3c0.4-0.4,0.4-1,0-1.4c-0.4-0.4-1-0.4-1.4,0L12,10.6L5.7,4.3c-0.4-0.4-1-0.4-1.4,0c-0.4,0.4-0.4,1,0,1.4
												l6.3,6.3l-6.3,6.3C4.1,18.5,4,18.7,4,19c0,0.6,0.4,1,1,1c0.3,0,0.5-0.1,0.7-0.3l6.3-6.3l6.3,6.3c0.2,0.2,0.4,0.3,0.7,0.3
												s0.5-0.1,0.7-0.3c0.4-0.4,0.4-1,0-1.4L13.4,12z"></path>
										</svg>
									</span>
									
								</button>
								<?php else : ?>
									<span class="pizza-component-name"><?php echo esc_html( $c['name'] ); ?></span>
							<?php endif; ?>
							
							
						<?php endforeach; ?>

					<input type="hidden" name="ev-pizza-consists" value="<?php echo $pizza_consists_of_attr; ?>">
		
					<?php endif; ?>
			</div>

			<span><?php echo esc_html( apply_filters( 'ev_pizza_add_ingredient_text', __( 'Add ingredient', 'pizza-builder-for-woocommerce' ) ) ); ?></span>

			<div class="pizza-components-tabs">
				<div data-id="add-component" class="pizza-components-tab fade-in">

					<?php foreach ( $data['consists_of']['to_add'] as $c ) : ?>
						<div class="pizza-components-item">
							<?php if ( ev_pizza_tipps_enabled() && trim( $c['description'] ) !== '' ) : ?>
								<div class="pizza-tippy" data-tippy-content="<?php echo esc_attr( $c['description'] ); ?>">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16ZM10.75 10C10.75 9.30964 11.3096 8.75 12 8.75C12.6904 8.75 13.25 9.30964 13.25 10V10.1213C13.25 10.485 13.1055 10.8338 12.8483 11.091L11.4697 12.4697C11.1768 12.7626 11.1768 13.2374 11.4697 13.5303C11.7626 13.8232 12.2374 13.8232 12.5303 13.5303L13.909 12.1517C14.4475 11.6132 14.75 10.8828 14.75 10.1213V10C14.75 8.48122 13.5188 7.25 12 7.25C10.4812 7.25 9.25 8.48122 9.25 10V10.5C9.25 10.9142 9.58579 11.25 10 11.25C10.4142 11.25 10.75 10.9142 10.75 10.5V10Z" fill="#22282F" />
									</svg>
								</div>
							<?php endif; ?>
							<div class="component-buttons" data-food-item="<?php echo esc_attr( $c['id'] ); ?>">
								<?php
								ev_pizza_woo_quantity_input(
									array(
										'input_name'  => 'ev_quantity[' . $c['id'] . ']',
										'classes'     => array( 'input-text', 'text', 'component-qty' ),
										'min_value'   => 0,
										'max_value'   => 100,
										'input_value' => 0,
									)
								);

								?>
							</div>
							<span class="pizza-component-name"><?php echo esc_html( $c['name'] ); ?></span>
							<img class="pizza-component-image" src="<?php echo esc_url( wp_get_attachment_image_url( $c['image_ID'], 'medium' ) ); ?>" alt="">
							<?php if ( ! empty( $c['weight'] ) ) : ?>
								<p class="pizza-component-meta"><span class="pizza-component-weight"><?php echo esc_html( $c['weight'] ) . '/'; ?></span><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
							<?php else : ?>
								<p class="pizza-component-meta"><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
							<?php endif; ?>

						</div>
					<?php endforeach; ?>


				</div>
			</div>

		
		</div>
		</div>
	<?php elseif ( $data['consists_of']['enabled'] && ! $dodo_style ) : ?>

		<div class="pizza-components-block">
			<div class="pizza-components-nav">
				<ul>
					<li><a class="active" href="#add-component"><?php echo esc_html( apply_filters( 'ev_pizza_add_ingredient_text', __( 'Add ingredient', 'pizza-builder-for-woocommerce' ) ) ); ?></a></li>
					<li><a href="#remove-component"><?php echo esc_html( apply_filters( 'ev_pizza_remove_ingredient_text', __( 'Remove ingredient', 'pizza-builder-for-woocommerce' ) ) ); ?></a></li>
				</ul>
			</div>
			<div class="pizza-components-tabs">
				<div data-id="add-component" class="pizza-components-tab fade-in">

					<?php foreach ( $data['consists_of']['to_add'] as $c ) : ?>
						<div class="pizza-components-item">
							<?php if ( ev_pizza_tipps_enabled() && trim( $c['description'] ) !== '' ) : ?>
								<div class="pizza-tippy" data-tippy-content="<?php echo esc_attr( $c['description'] ); ?>">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16ZM10.75 10C10.75 9.30964 11.3096 8.75 12 8.75C12.6904 8.75 13.25 9.30964 13.25 10V10.1213C13.25 10.485 13.1055 10.8338 12.8483 11.091L11.4697 12.4697C11.1768 12.7626 11.1768 13.2374 11.4697 13.5303C11.7626 13.8232 12.2374 13.8232 12.5303 13.5303L13.909 12.1517C14.4475 11.6132 14.75 10.8828 14.75 10.1213V10C14.75 8.48122 13.5188 7.25 12 7.25C10.4812 7.25 9.25 8.48122 9.25 10V10.5C9.25 10.9142 9.58579 11.25 10 11.25C10.4142 11.25 10.75 10.9142 10.75 10.5V10Z" fill="#22282F" />
									</svg>
								</div>
							<?php endif; ?>
							<div class="component-buttons" data-food-item="<?php echo esc_attr( $c['id'] ); ?>">
								<?php
								ev_pizza_woo_quantity_input(
									array(
										'input_name'  => 'ev_quantity[' . $c['id'] . ']',
										'classes'     => array( 'input-text', 'text', 'component-qty' ),
										'min_value'   => 0,
										'max_value'   => 100,
										'input_value' => 0,
									)
								);

								?>
							</div>
							<span class="pizza-component-name"><?php echo esc_html( $c['name'] ); ?></span>
							<img class="pizza-component-image" src="<?php echo esc_url( wp_get_attachment_image_url( $c['image_ID'], 'medium' ) ); ?>" alt="">
							<?php if ( ! empty( $c['weight'] ) ) : ?>
								<p class="pizza-component-meta"><span class="pizza-component-weight"><?php echo esc_html( $c['weight'] ) . '/'; ?></span><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
							<?php else : ?>
								<p class="pizza-component-meta"><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
							<?php endif; ?>

						</div>
					<?php endforeach; ?>


				</div>
				<div data-id="remove-component" class="pizza-components-tab">
					<?php if ( ! empty( $data['consists_of']['consists'] ) ) : ?>
						<?php
						$pizza_consists_of = array();
						foreach ( $data['consists_of']['consists'] as $c ) {
							$pizza_consists_of[] = array( $c['id'] => true );
						}
						$pizza_consists_of_json = wp_json_encode( $pizza_consists_of );
						$pizza_consists_of_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $pizza_consists_of_json ) : _wp_specialchars( $pizza_consists_of_json, ENT_QUOTES, 'UTF-8', true );
						?>
						<?php foreach ( $data['consists_of']['consists'] as $c ) : ?>
							<?php
							if ( ! $c['visible'] ) {
								continue;}
							?>
							<div class="pizza-components-item" data-component-id="<?php echo esc_attr( $c['id'] ); ?>">
								<?php if ( ev_pizza_tipps_enabled() && trim( $c['description'] ) !== '' ) : ?>
									<div class="pizza-tippy" data-tippy-content="<?php echo esc_attr( $c['description'] ); ?>">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.75C7.44365 3.75 3.75 7.44365 3.75 12C3.75 16.5563 7.44365 20.25 12 20.25C16.5563 20.25 20.25 16.5563 20.25 12C20.25 7.44365 16.5563 3.75 12 3.75ZM2.25 12C2.25 6.61522 6.61522 2.25 12 2.25C17.3848 2.25 21.75 6.61522 21.75 12C21.75 17.3848 17.3848 21.75 12 21.75C6.61522 21.75 2.25 17.3848 2.25 12ZM13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16ZM10.75 10C10.75 9.30964 11.3096 8.75 12 8.75C12.6904 8.75 13.25 9.30964 13.25 10V10.1213C13.25 10.485 13.1055 10.8338 12.8483 11.091L11.4697 12.4697C11.1768 12.7626 11.1768 13.2374 11.4697 13.5303C11.7626 13.8232 12.2374 13.8232 12.5303 13.5303L13.909 12.1517C14.4475 11.6132 14.75 10.8828 14.75 10.1213V10C14.75 8.48122 13.5188 7.25 12 7.25C10.4812 7.25 9.25 8.48122 9.25 10V10.5C9.25 10.9142 9.58579 11.25 10 11.25C10.4142 11.25 10.75 10.9142 10.75 10.5V10Z" fill="#22282F" />
										</svg>
									</div>
								<?php endif; ?>
								<?php if ( ! $c['required'] ) : ?>
									<a href="#" class="ev-remove-component">
										<svg width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
											<path d="M 4 4 L 16 16 M 16 4 L 4 16" fill="#fff" stroke-width="3" />
										</svg>

									</a>
								<?php endif; ?>
								<span class="pizza-component-name"><?php echo esc_html( $c['name'] ); ?></span>
								<img class="pizza-component-image" src="<?php echo esc_url( wp_get_attachment_image_url( $c['image_ID'], 'medium' ) ); ?>" alt="">
								<?php if ( ! empty( $c['weight'] ) ) : ?>
									<p class="pizza-component-meta"><span class="pizza-component-weight"><?php echo esc_html( $c['weight'] ) . '/'; ?></span><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
								<?php else : ?>
									<p class="pizza-component-meta"><span class="pizza-component-price"><?php echo wc_price( $c['price'] ); ?></span></p>
								<?php endif; ?>

							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

		</div>
		<div class="pizza-components-buttons">
			<?php if ( ! empty( $data['consists_of']['consists'] ) ) : ?>
				<input type="hidden" name="ev-pizza-consists" value="<?php echo $pizza_consists_of_attr; ?>">
			<?php endif; ?>
			<?php if ( $data['layers']['enabled'] ) : ?>
				<input type="hidden" name="pizza-layer-data" value="">
				<button class="ev-pizza-button" id="pizza-layer-button" data-product-id="<?php echo esc_html( get_the_ID() ); ?>"><?php esc_html_e( 'Add layer', 'pizza-builder-for-woocommerce' ); ?></button>
				
					<div class="pizza-fancybox" id="ev-pizza-layers-fancybox-<?php echo esc_attr( get_the_ID() ); ?>" style="display: none;">
						<?php
						wc_get_template(
							'pizza/layers.php',
							array(
								'food_components_full' => $data,
								'product'              => $product,
							),
							'',
							EV_PIZZA_PATH . 'templates/'
						);
						?>
					</div>
				
			<?php endif; ?>

			<?php if ( $data['bortik']['enabled'] ) : ?>
				<input type="hidden" name="pizza-sides-data" value="">
				<button class="ev-pizza-button" id="pizza-sides-button" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>"><?php esc_html_e( 'Choose side', 'pizza-builder-for-woocommerce' ); ?></button>
				
					<div class="pizza-fancybox" id="ev-pizza-bortik-fancybox-<?php echo esc_attr( get_the_ID() ); ?>" style="display: none;">
						<?php
						wc_get_template(
							'pizza/sides.php',
							array(
								'food_components_full' => $data,
								'product'              => $product,
							),
							'',
							EV_PIZZA_PATH . 'templates/'
						);
						?>
					</div>
				

			<?php endif; ?>
		</div>
	<?php elseif ( $data['extra']['enabled'] ) : ?>
		<?php if ( $data['extra']['tabs'] ) : ?>
			<div class="pizza-component-tabs-wrapper">
				<h3><?php echo apply_filters( 'ev_pizza_extras_text', esc_html( 'Ingredients', 'pizza-builder-for-woocommerce' ) ); ?></h3>
				<ul class="pizza-tab-nav">
					<?php foreach ( $data['extra']['tab_components'] as $tab_key => $tab ) : ?>
						<li>
							<a href="" data-tab-id="<?php echo esc_attr( $tab['id'] ); ?>" class="pizza-tab-link<?php echo $tab_key === 0 ? ' active' : ''; ?>" title="<?php echo esc_attr( $tab['groupName'] ); ?>">
								<img src="<?php echo esc_url( $tab['groupImage'] ); ?>" alt="">
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="tab-components-wrapper">
					<?php foreach ( $data['extra']['tab_components'] as $tab_key => $tab ) : ?>
						<div id="<?php echo esc_attr( $tab['id'] ); ?>" class="component-item-tab <?php echo $tab_key === 0 ? 'fade-in' : ''; ?>">
							<?php foreach ( $tab['components'] as $component ) : ?>
								<div class="component-item">
									<div class="component-img" style="background-image:url('<?php echo esc_url( wp_get_attachment_image_url( $component['image_ID'], 'medium' ) ); ?>');background-repeat:no-repeat;">

										<div class="component-buttons" data-food-item="<?php echo esc_attr( $component['id'] ); ?>">
											<?php
											ev_pizza_woo_quantity_input(
												array(
													'input_name'   => 'evc_quantity[' . $component['id'] . ']',
													'classes' => array( 'input-text', 'text', 'component-qty' ),
													'min_value'   => 0,
													'max_value'   => 100,
													'input_value' => 0,
												)
											);


											?>
										</div>
									</div>
									<p class="component-name"><?php echo esc_html( $component['name'] ); ?></p>
									<?php if ( ! empty( $component['weight'] ) ) : ?>
										<p class="component-price"><?php echo esc_html( $component['weight'] ) . '/' . wc_price( $component['price'] ); ?></p>
									<?php else : ?>
										<p class="component-price"><?php echo wc_price( $component['price'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
		<?php else : ?>
			<div class="pizza-components-wrapper">
				<h3><?php echo apply_filters( 'ev_pizza_extras_text', esc_html( 'Ingredients', 'pizza-builder-for-woocommerce' ) ); ?></h3>
				<div class="components-item-wrapper">
					<?php
					foreach ( $data['extra']['components'] as $component ) :

						?>

						<div class="component-item">
							<div class="component-img" style="background-image:url('<?php echo esc_url( wp_get_attachment_image_url( $component['image_ID'], 'medium' ) ); ?>');background-repeat:no-repeat;">

								<div class="component-buttons" data-food-item="<?php echo esc_attr( $component['id'] ); ?>">
									<?php
									ev_pizza_woo_quantity_input(
										array(
											'input_name'  => 'evc_quantity[' . $component['id'] . ']',
											'classes'     => array( 'input-text', 'text', 'component-qty' ),
											'min_value'   => 0,
											'max_value'   => 100,
											'input_value' => 0,
										)
									);
									?>
								</div>
							</div>
							<p class="component-name"><?php echo esc_html( $component['name'] ); ?></p>
							<?php if ( ! empty( $component['weight'] ) ) : ?>
								<p class="component-price"><?php echo esc_html( $component['weight'] ) . '/' . wc_price( $component['price'] ); ?></p>
							<?php else : ?>
								<p class="component-price"><?php echo wc_price( $component['price'] ); ?></p>
							<?php endif; ?>

						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	<?php endif; ?>

</div>
