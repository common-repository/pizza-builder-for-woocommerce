<?php

/**
 * Template for extra components buttons
 */
defined( 'ABSPATH' ) || exit;


?>

<div class="pizza-quantity">

	<button type="button" class="qty_button qty_button_minus"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" ><path d="M6 13a1 1 0 1 1 0-2h12a1 1 0 1 1 0 2H6Z"/></svg></button>

	<input type="number" id="<?php echo esc_attr( $input_id ); ?>" class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>" step="<?php echo esc_attr( $step ); ?>" min="<?php echo esc_attr( $min_value ); ?>" max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>" title="<?php echo esc_attr_x( 'Qty', 'Component quantity input tooltip', 'woocommerce' ); ?>" size="4" placeholder="<?php echo esc_attr( $placeholder ); ?>" inputmode="<?php echo esc_attr( $inputmode ); ?>" />
	<button type="button" class="qty_button qty_button_plus"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" ><path d="M19,11H13V5a1,1,0,0,0-2,0v6H5a1,1,0,0,0,0,2h6v6a1,1,0,0,0,2,0V13h6a1,1,0,0,0,0-2Z"/></svg></button>

</div>
<?php
