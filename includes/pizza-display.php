<?php
class Ev_Pizza_Display {

	public function __construct() {

		add_filter( 'woocommerce_get_price_html', array( $this, 'change_price' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'modify_attr_array' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'modify_price_mini_cart' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'modify_name_mini_cart' ), 10, 2 );

		// archive.
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'archive_link' ), 10, 3 );

		// variations.
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'render_variations' ), 40, 2 );
	}
	/**
	 * Set price for product in form.cart attributes
	 */
	public function modify_attr_array( $data, $product_variable, $variation ) {
		if ( ! ev_is_pizza_product( $product_variable->get_id() ) ) {
			return $data;
		}
		$product_pizza                 = Ev_Pizza_Product::get_product( $product_variable );
		$data['display_price']         = wc_get_price_to_display( $variation, array( 'price' => $product_pizza->get_price( $variation->get_price() ) ) );
		$data['display_regular_price'] = wc_get_price_to_display( $variation, array( 'price' => $product_pizza->get_price( $variation->get_regular_price() ) ) );
		return $data;
	}
	/**
	 * Recalculate price for simple & variable pizza product
	 */
	public function change_price( $price, $product ) {
		$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ( ! ev_is_pizza_product( $product_id ) ) {
			return $price;
		}
		if ( is_admin() ) {

			return $price;
		}
		$product_pizza = Ev_Pizza_Product::get_product( $product );
		if ( $product->is_type( 'simple' ) ) {

			if ( $product_pizza->is_on_sale() ) {

				$price = wc_format_sale_price( wc_get_price_to_display( $product_pizza->get_wc_product(), array( 'price' => $product_pizza->get_regular_price() ) ), wc_get_price_to_display( $product_pizza->get_wc_product(), array( 'price' => $product_pizza->get_price() ) ) ) . $product_pizza->get_price_suffix();
			} else {
				$price = wc_price( wc_get_price_to_display( $product_pizza->get_wc_product(), array( 'price' => $product_pizza->get_price() ) ) ) . $product_pizza->get_price_suffix();
			}
		} elseif ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices();
			if ( empty( $prices['price'] ) ) {
				$price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
			} else {
				$min_price     = $product_pizza->get_price( current( $prices['price'] ) );
				$max_price     = $product_pizza->get_price( end( $prices['price'] ) );
				$min_reg_price = $product_pizza->get_price( current( $prices['regular_price'] ) );
				$max_reg_price = $product_pizza->get_price( end( $prices['regular_price'] ) );

				if ( $min_price !== $max_price ) {
					$price = wc_format_price_range( $min_price, $max_price );
				} elseif ( $product_pizza->is_on_sale() && $min_reg_price === $max_reg_price ) {
					$price = wc_format_sale_price( wc_price( $max_reg_price ), wc_price( $min_price ) );
				} else {
					$price = wc_price( $min_price );
				}

				$price = apply_filters( 'woocommerce_variable_price_html', $price . $product_pizza->get_price_suffix(), $product );
			}
		} elseif ( $product->is_type( 'variation' ) ) {

			$product_variable     = $product->get_parent_id();
			$product_pizza_parent = Ev_Pizza_Product::get_product( $product_variable );

			if ( $product_pizza_parent->is_on_sale() ) {

				$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product_pizza_parent->get_price( $product->get_regular_price() ) ) ), wc_get_price_to_display( $product, array( 'price' => $product_pizza_parent->get_price( $product->get_price() ) ) ) ) . $product_pizza_parent->get_price_suffix();
			} else {
				$price = wc_price( wc_get_price_to_display( $product, array( 'price' => $product_pizza_parent->get_price( $product->get_price() ) ) ) ) . $product_pizza_parent->get_price_suffix();
			}
		}

		return $price;
	}
	/**
	 * Recalculate price in mini cart
	 */
	public function modify_price_mini_cart( $price, $cart_item, $cart_item_key ) {
		$product_id = $cart_item['product_id'];
		if ( ! ev_is_pizza_product( $product_id ) ) {
			return $price;
		}
		$pizza_components     = ev_pizza_get_components();
		$food_components_data = json_decode( wp_unslash( get_post_meta( $product_id, 'product_ev_pizza_full', true ) ), true );
		$product_sid          = $cart_item['variation_id'] ? $cart_item['variation_id'] : $product_id;
		$price                = Ev_Pizza_Product::get_product( $product_sid )->get_price();
		if ( isset( $cart_item['ev_pizza_config'] ) ) {
			if ( isset( $cart_item['ev_pizza_config']['extra']['components'] ) ) {

				$pizza_add = $food_components_data['extra']['components'];
				foreach ( $cart_item['ev_pizza_config']['extra']['components'] as $component ) {
					$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );
					$qty              = intval( $component['quantity'] );
					if ( $origin_component ) {
						$price += floatval( $origin_component['price'] ) * $qty;

					}
					// rules
					$pizza_class = Ev_Pizza::instance();
					if ( ! empty( $food_components_data['rules'] ) ) {
						foreach ( $food_components_data['rules'] as $rule ) {
							if ( $rule['name'] === $component['id'] ) {
								switch ( $rule['name_action'] ) {
									case 'quantity':
										$price = $pizza_class->cart->run_numeric_rule( $price, $origin_component, $rule, $qty, 'qty' );
										break;
									case 'total_price':
										$price = $pizza_class->cart->run_numeric_rule( $price, $origin_component, $rule, $qty, 'total' );
										break;
									case 'weight':
										$price = $pizza_class->cart->run_numeric_rule( $price, $origin_component, $rule, $qty, 'weight' );
										break;
								}
							}
						}
					}
				}
			}
			if ( isset( $cart_item['ev_pizza_config']['consists_of']['consists'] ) ) {
				if ( Ev_Pizza_Product::get_product( $product_id )->is_price_inc() ) {

					$selected_consists = $cart_item['ev_pizza_config']['consists_of']['consists'];

					$pizza_consists = $food_components_data['consists_of']['consists'];

					if ( ! empty( $pizza_consists ) ) {
						foreach ( $pizza_consists as $component ) {

							$found = false;
							if ( ! empty( $selected_consists ) ) {
								foreach ( $selected_consists as $selected_component ) {
									if ( $component['id'] === $selected_component['id'] ) {
										$found = true;
									}
								}
							}
							if ( ! $found ) {
								$price -= floatval( $component['price'] );
							}
						}
					}
				}
			}
			if ( isset( $cart_item['ev_pizza_config']['consists_of']['to_add'] ) ) {

				$pizza_add = $food_components_data['consists_of']['to_add'];
				foreach ( $cart_item['ev_pizza_config']['consists_of']['to_add'] as $component ) {
					foreach ( $pizza_add as $add_component ) {
						if ( $component['id'] === $add_component['id'] ) {
							$price += floatval( $add_component['price'] ) * intval( $component['quantity'] );
						}
					}
				}
			}
			if ( isset( $cart_item['ev_pizza_config']['layers']['components'] ) ) {
				$pizza_layers = $food_components_data['layers']['components'];
				foreach ( $cart_item['ev_pizza_config']['layers']['components'] as $component ) {
					foreach ( $pizza_layers as $layer_product_id ) {

						if ( $component['id'] === $layer_product_id ) {
							if ( $product_sid === $layer_product_id ) {
								continue;
							}
							$price += floatval( $component['price'] );
						}
					}
				}
			}
			if ( isset( $cart_item['ev_pizza_config']['bortik']['components'] ) ) {
				$pizza_sides = $food_components_data['bortik']['components'];
				foreach ( $cart_item['ev_pizza_config']['bortik']['components'] as $component ) {
					foreach ( $pizza_sides as $side_component ) {
						if ( $component['id'] === $side_component['id'] ) {
							$price += floatval( $side_component['price'] );
						}
					}
				}
			}
		}
		return wc_price( $price );
	}

	/**
	 * Change name for PBW-Builder product (from shortcode builder).
	 */
	public function modify_name_mini_cart( $name, $cart_item ) {
		$product = $cart_item['data'];

		if ( $product->is_type( 'pbw_product' ) ) {
			return $cart_item['pbw_product_name'];
		}
		return $name;
	}

	public function archive_link( $link, $product, $args ) {

		if ( ! ev_pizza_quick_view_enabled() ) {
			return $link;
		}

		if ( ! ev_is_pizza_product( $product->get_id() ) ) {
			return $link;
		}
		$button_text = __( 'Choose' );
		$classes     = isset( $args['class'] ) ? $args['class'] : 'button';
		$classes    .= ' ev-pizza-choose-btn';
		return sprintf(
			'<button class="%s" data-product-id="%s">%s</button>',
			esc_attr( $classes ),
			$product->get_id(),
			$button_text
		);
	}

	public function render_variations( $html, $args ) {

		$args = wp_parse_args(
			apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ),
			array(
				'options'          => false,
				'attribute'        => false,
				'product'          => false,
				'selected'         => false,
				'name'             => '',
				'id'               => '',
				'class'            => '',
				'show_option_none' => esc_html__( 'Choose an option', 'woo-variation-swatches' ),
				'is_archive'       => false,
			)
		);

		if ( $args['product'] instanceof WC_Product && ! ev_is_pizza_product( $args['product']->get_id() ) ) {
			return $html;
		}

		$pizza_style = ev_pizza_get_style_type( $args['product']->get_id() );

		// if not dodo, skip.
		if ( $pizza_style !== '3' ) {
			return $html;
		}

		if ( apply_filters( 'ev_pizza_default_single_product_dropdown_html', false, $args, $html, $this ) ) {
			return $html;
		}

		// Get selected value.
		if ( empty( $args['selected'] ) && $args['attribute'] && $args['product'] instanceof WC_Product ) {
			$selected_key = wc_variation_attribute_name( $args['attribute'] );
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] );
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		$pizza_settings    = ev_pizza_get_settings();
		$smooth_attributes = $pizza_settings['smooth_attributes'];

		$options   = $args['options'];
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$name      = $args['name'] ? $args['name'] : wc_variation_attribute_name( $attribute );

		if ( ! in_array( sanitize_title( $attribute ), $smooth_attributes, true ) ) {
			return $html;
		}

		$id               = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class            = $args['class'];
		$show_option_none = (bool) $args['show_option_none'];
		// $show_option_none      = true;
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : esc_html__( 'Choose an option', 'woo-variation-swatches' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			// $attributes = $this->get_cached_variation_attributes( $product );
			$options = $attributes[ $attribute ];
		}

		$attribute_types = array(
			'select' => 'Select',
			'smooth' => 'Smooth',
		);
		$attribute_type  = 'smooth';

		$swatches_data = array();

		if ( ! in_array( $attribute_type, array_keys( $attribute_types ), true ) ) {
			return $html;
		}

		$select_inline_style = '';

		if ( $attribute_type !== 'select' ) {
			$select_inline_style = 'style="display:none"';
			$class              .= ' ev-pizza-raw-select';
		}

		$html  = '<select ' . $select_inline_style . ' id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="' . esc_attr( wc_variation_attribute_name( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( ! empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					array(
						'fields' => 'all',
					)
				);

				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {

						$swatches_data[] = $this->get_swatch_data( $args, $term );

						$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ) . '</option>';
					}
				}
			} else {
				foreach ( $options as $option ) {
					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );

					$swatches_data[] = $this->get_swatch_data( $args, $option );

					$html .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ) . '</option>';
				}
			}
		}

		$html .= '</select>';

		$item        = '';
		$wrapper     = '';
		$wrapper_end = '';

		if ( ! empty( $options ) && ! empty( $swatches_data ) && $product ) {

			$html_attributes = $this->wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options );

			$default_selected = false;
			$wrapper_start    = sprintf( '<div %s><div class="ev-smooth-overlay"></div>', wc_implode_html_attributes( $html_attributes ) );

			foreach ( $swatches_data as $data ) {

				if ( ! $default_selected ) {
					$default_selected = $data['is_selected'];

				}

				$item .= $this->item_start( $data, $attribute_type );
				$item .= $this->smooth_attribute( $data, $attribute_type );
				$item .= $this->item_end();
			}

			$wrapper_end = '</div>';
		}

		$html .= $wrapper_start . $item . $wrapper_end;

		return apply_filters( 'ev_pizza_variation_swatches_html', $html, $args, $swatches_data, $this );
	}

	public function wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options ) {

		$raw_html_attributes = array();
		$css_classes         = array( 'pizza-smooth-radio' );
		$count_options       = count( $options );
		if ( $args['selected'] ) {
			$css_classes[] = 'smooth-selected';
		}
		if ( $count_options ) {
			$css_classes[] = 'smooth-col-' . $count_options;
		}

		$raw_html_attributes['role']                  = 'radiogroup';
		$raw_html_attributes['aria-label']            = wc_attribute_label( $attribute, $product );
		$raw_html_attributes['class']                 = implode( ' ', array_unique( array_values( $css_classes ) ) );
		$raw_html_attributes['data-attribute_name']   = wc_variation_attribute_name( $attribute );
		$raw_html_attributes['data-attribute_values'] = wc_esc_json( wp_json_encode( array_values( $options ) ) );

		return $raw_html_attributes;
	}

	public function item_start( $data, $attribute_type, $variation_data = array() ) {

		$args           = $data['args'];
		$term_or_option = $data['item'];

		$options     = $args['options'];
		$product     = $args['product'];
		$attribute   = $args['attribute'];
		$is_selected = $data['is_selected'];
		$option_name = $data['option_name'];
		$option_slug = $data['option_slug'];
		$slug        = $data['slug'];

		$css_class = implode( ' ', array_unique( array_values( apply_filters( 'ev_pizza_swatch_item_css_class', $this->get_item_css_classes( $data, $attribute_type, $variation_data ), $data, $attribute_type, $variation_data ) ) ) );

		$html_attributes = array(
			'aria-checked' => ( $is_selected ? 'true' : 'false' ),
			'tabindex'     => ( wp_is_mobile() ? '2' : '0' ),
		);

		return sprintf( '<div %1$s class="ev-%2$s-item ev-%2$s-item-%3$s %4$s" title="%5$s" data-title="%5$s" data-value="%6$s" role="radio" tabindex="0">', wc_implode_html_attributes( $html_attributes ), esc_attr( $attribute_type ), esc_attr( $option_slug ), esc_attr( $css_class ), esc_html( $option_name ), esc_attr( $slug ) );
	}

	public function get_item_css_classes( $data, $attribute_type, $variation_data = array() ) {

		$css_classes = array();

		$is_selected = wc_string_to_bool( $data['is_selected'] );

		if ( $is_selected ) {
			$css_classes[] = 'selected';
		}

		return $css_classes;
	}

	public function smooth_attribute( $data, $attribute_type, $variation_data = array() ) {

		$option_name = $data['option_name'];

		$template_format = apply_filters( 'ev_pizza_smooth_attribute_template', '<span class="ev-smooth-label">%s</span>', $data, $attribute_type, $variation_data );

		return sprintf( $template_format, esc_html( $option_name ) );

	}

	public function get_swatch_data( $args, $term_or_option ) {

		$options          = $args['options'];
		$product          = $args['product'];
		$attribute        = $args['attribute'];
		$attributes       = $product->get_variation_attributes();
		$count_attributes = count( array_keys( $attributes ) );

		$is_term = is_object( $term_or_option );

		if ( $is_term ) {

			$term        = $term_or_option;
			$slug        = $term->slug;
			$is_selected = ( sanitize_title( $args['selected'] ) === $term->slug );
			$option_name = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );

		} else {
			$option      = $slug = $term_or_option;
			$is_selected = ( sanitize_title( $args['selected'] ) === $args['selected'] ) ? ( $args['selected'] === sanitize_title( $option ) ) : ( $args['selected'] === $option );
			$option_name = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
		}

		$attribute_name = wc_variation_attribute_name( $attribute );

		$data = array(
			'is_archive'       => isset( $args['is_archive'] ) ? $args['is_archive'] : false,
			'is_selected'      => $is_selected,
			'is_term'          => $is_term,
			'term_id'          => $is_term ? $term->term_id : wc_clean( $option ),
			'slug'             => $slug,
			'total_attributes' => absint( $count_attributes ),
			'option_slug'      => wc_clean( $slug ),
			'item'             => $term_or_option,
			'options'          => $options,
			'option_name'      => $option_name,
			'attribute'        => $attribute,
			'attribute_key'    => sanitize_title( $attribute ),
			'attribute_name'   => wc_variation_attribute_name( $attribute ),
			'attribute_label'  => wc_attribute_label( $attribute, $product ),
			'args'             => $args,
			'product'          => $product,
		);

		return apply_filters( 'ev_pizza_get_swatch_data', $data, $args, $product );
	}

	public function item_end() {

		$html = '';

		$html .= '</div>';

		return $html;
	}
}
