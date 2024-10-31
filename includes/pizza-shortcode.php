<?php
class Ev_Pizza_Shortcode {
	public function __construct() {
		add_action( 'init', array( $this, 'add_shortcode' ) );
	}

	public function add_shortcode() {
		add_shortcode( 'pbw-builder', array( $this, 'display_shortcode' ) );
	}

	public function display_shortcode( $atts ) {
		$atts        = shortcode_atts(
			array(
				'id'      => 0,
				'steps'   => 1,
				'title'   => '',
				'type'    => 'modal',
				'columns' => 7,
			),
			$atts
		);
		$option_name = 'pizza_shortcodes_data_' . $atts['id'];
		$steps_data  = get_option( $option_name );

		$shortcode_type  = $atts['type'];
		$shortcode_modal = 'modal' === $shortcode_type || 'steps-slider' === $shortcode_type;

		$pbw_builder_product = ev_pizza_shortcode_product();

		if ( ! $steps_data ) {
			return '<div>No data for given shortcode</div>';
		}

		if ( ! $pbw_builder_product ) {
			return '<div>Choose PBW Product</div>';
		}

		$atts['data'] = $steps_data;

		if ( ev_pizza_tipps_enabled() ) {
			wp_enqueue_script( 'pizza-popper' );
			wp_enqueue_script( 'pizza-tipps' );
		}
		if ( $shortcode_modal ) {
			wp_enqueue_style( 'pizza-fancybox' );
			wp_enqueue_script( 'pizza-fancybox' );
		}

		wp_enqueue_style( 'pizza-swiper' );
		wp_enqueue_script( 'pizza-swiper' );

		wp_enqueue_style( 'pizza-builder' );
		wp_enqueue_script( 'pizza-builder' );

		wp_localize_script(
			'pizza-builder',
			'EV_FRONT_BUILDER',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'wc_symbol'          => get_woocommerce_currency_symbol(),
				'price_position'     => get_option( 'woocommerce_currency_pos' ),
				'decimals'           => wc_get_price_decimals(),
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'tippy_enabled'      => ev_pizza_tipps_enabled(),
				'redirect_cart'      => ev_pizza_redirect_cart(),
				'nonce'              => wp_create_nonce( 'ev-builder-nonce' ),
			)
		);
		wp_localize_script(
			'pizza-builder',
			'pbw_builder_' . $atts['id'],
			array(
				'data' => $steps_data,
				'type' => $shortcode_type,
			)
		);
		ob_start();
		wc_get_template( 'builder/steps.php', $atts, '', EV_PIZZA_PATH . 'templates/' );
		?>
		<script type="text/html" id="tmpl-pizza-builder-choosen" >
			<div class="pbw-builder-step__component active" data-choosen="{{{data.id}}}">
				<div class="pbw-builder-step__inner">
					
					<div class="pbw-builder-step__name">{{{data.name}}}</div>
					<div class="pbw-builder-step__image">
						<img src="{{{data.image}}}" />
						
					</div>
					<div class="pbw-builder-step__price">{{{data.price}}}</div>
				</div>
			</div>
		</script>
		<?php
		return ob_get_clean();

	}
}
