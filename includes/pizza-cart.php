<?php
class Ev_Pizza_Cart {

	public function __construct() {
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_data_from_session' ), 10, 3 );

		add_filter( 'woocommerce_get_item_data', array( $this, 'display_meta_cart' ), 10, 2 );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'add_extra_payment' ), 10, 1 );
		add_action( 'woocommerce_after_cart_item_name', array( $this, 'display_meta_cart_hook' ), 10, 2 );
		// add_action( 'woocommerce_before_cart', array( $this, 'debug_cart' ) );
	}

	public function add_cart_item_data( $cart_item_data, $product_id ) {
		if ( ev_is_pizza_product( $product_id ) ) {
			$food_config          = array();
			$food_components_data = json_decode( wp_unslash( get_post_meta( $product_id, 'product_ev_pizza_full', true ) ), true );

			if ( isset( $_POST['evc_quantity'] ) && ! empty( $_POST['evc_quantity'] ) ) {

				$pizza_components = ev_pizza_get_components();

				foreach ( wc_clean( $_POST['evc_quantity'] ) as $component_id => $quantity ) {
					if ( $quantity == 0 ) {
						continue;
					}
					foreach ( $food_components_data['extra']['components'] as $component ) {
						if ( $component['id'] === $component_id ) {
							$origin_component = ev_pizza_find_component_by_id( $component['id'], $pizza_components );
							if ( $origin_component ) {
								$food_config['extra']['components'][] = array(
									'id'          => $origin_component['id'],
									'name'        => $origin_component['name'],
									'price'       => $origin_component['price'],
									'quantity'    => $quantity,
									'description' => $origin_component['description'],
									'weight'      => $origin_component['weight'],
									'image'       => $origin_component['image'],
								);
							}
						}
					}
				}
			}

			if ( isset( $_POST['ev_quantity'] ) && ! empty( $_POST['ev_quantity'] ) ) {

				foreach ( wc_clean( $_POST['ev_quantity'] ) as $component_id => $quantity ) {
					if ( $quantity == 0 ) {
						continue;
					}
					foreach ( $food_components_data['consists_of']['to_add'] as $component ) {
						if ( $component['id'] === $component_id ) {
							$food_config['consists_of']['to_add'][] = array(
								'id'          => $component['id'],
								'name'        => $component['name'],
								'price'       => $component['price'],
								'quantity'    => $quantity,
								'description' => $component['description'],
								'weight'      => $component['weight'],
								'image'       => $component['image'],
							);
						}
					}
				}
			}
			if ( isset( $_POST['ev-pizza-consists'] ) ) {
				$pizza_consists                         = json_decode( wp_unslash( sanitize_text_field( $_POST['ev-pizza-consists'] ) ), true );
				$food_config['consists_of']['consists'] = array();
				if ( ! empty( $pizza_consists ) ) {

					foreach ( $pizza_consists as $component_key => $component_val ) {
						foreach ( $component_val as $component_id => $component_bool ) {
							foreach ( $food_components_data['consists_of']['consists'] as $component ) {
								if ( $component['id'] === $component_id && $component_bool ) {
									$food_config['consists_of']['consists'][] = array(
										'id'          => $component['id'],
										'name'        => $component['name'],
										'price'       => $component['price'],
										'description' => $component['description'],
										'weight'      => $component['weight'],
										'image'       => $component['image'],
									);
								}
							}
						}
					}
				}
			}
			if ( isset( $_POST['pizza-layer-data'] ) ) {
				$pizza_layers = json_decode( wp_unslash( sanitize_text_field( $_POST['pizza-layer-data'] ) ), true );
				if ( ! empty( $pizza_layers ) ) {

					foreach ( $pizza_layers as $product ) {
						$product_pizza = wc_get_product( $product['id'] );
						if ( $product_pizza ) {
							$food_config['layers']['components'][] = array(
								'id'       => $product_pizza->get_id(),
								'name'     => $product_pizza->get_name(),
								'image_id' => $product_pizza->get_image_id(),
								'price'    => Ev_Pizza_Product::get_product( $product_pizza )->get_price(),
								'position' => $product['position'],
							);
						}
					}
				}
			}
			if ( isset( $_POST['pizza-sides-data'] ) ) {
				$pizza_sides = json_decode( wp_unslash( sanitize_text_field( $_POST['pizza-sides-data'] ) ), true );
				if ( ! empty( $pizza_sides ) ) {
					foreach ( $pizza_sides as $pizza_side ) {
						foreach ( $food_components_data['bortik']['components'] as $component ) {
							if ( $component['id'] === $pizza_side['id'] ) {
								$food_config['bortik']['components'][] = array(
									'id'          => $component['id'],
									'name'        => $component['name'],
									'price'       => $component['price'],
									'description' => $component['description'],
									'weight'      => $component['weight'],
									'image'       => $component['image'],
								);
							}
						}
					}
				}
			}
			$cart_item_data['ev_pizza_config'] = $food_config;
		}
		return $cart_item_data;
	}

	public function get_cart_data_from_session( $cart_item, $cart_session_item, $key ) {

		if ( isset( $cart_session_item['ev_pizza_config'] ) ) {
			$cart_item['ev_pizza_config'] = $cart_session_item['ev_pizza_config'];
		}

		if ( isset( $cart_session_item['pbw_components'] ) ) {
			$cart_item['pbw_components'] = $cart_session_item['pbw_components'];
		}

		if ( isset( $cart_session_item['pbw_product_name'] ) ) {
			$cart_item['pbw_product_name'] = $cart_session_item['pbw_product_name'];
		}
		return $cart_item;
	}

	public function display_meta_cart( $item_data, $cart_item ) {
		if ( isset( $cart_item['ev_pizza_config'] ) ) {

			if ( isset( $cart_item['ev_pizza_config']['extra']['components'] ) ) {

				foreach ( $cart_item['ev_pizza_config']['extra']['components'] as $component ) {
					$item_data[] = array(
						'key'   => $component['name'],
						'value' => $component['weight'] !== '' ? '<span>' . $component['weight'] . '/' . '</span>' . wc_price( $component['price'] ) . ' x' . $component['quantity'] : wc_price( $component['price'] ) . ' x' . $component['quantity'],
					);
				}
			}
		}

		if ( isset( $cart_item['pbw_components'] ) ) {
			foreach ( $cart_item['pbw_components'] as $component ) {
				$item_data[] = array(
					'key'   => $component['name'],
					'value' => $component['weight'] !== '' ? '<span>' . $component['weight'] . '/' . '</span>' . wc_price( $component['price'] ) : wc_price( $component['price'] ),
				);
			}
		}

		return $item_data;
	}

	public function display_meta_cart_hook( $cart_item, $cart_item_key ) {
		$item_data  = array();
		$product_id = $cart_item['data']->get_parent_id() ? $cart_item['data']->get_parent_id() : $cart_item['data']->get_id();
		$product    = wc_get_product( $product_id );

		if ( ev_is_pizza_product( $product_id ) ) {

			if ( isset( $cart_item['ev_pizza_config'] ) ) {

				if ( isset( $cart_item['ev_pizza_config']['extra'] ) ) {

					return false;
				}

				if ( isset( $cart_item['ev_pizza_config']['consists_of']['to_add'] ) ) {

					$item_data['consists_of']['to_add_text'] = apply_filters( 'ev_pizza_components_adds_text', esc_html__( 'Extra Components:', 'pizza-builder-for-woocommerce' ), $cart_item['data']->get_id() );
					foreach ( $cart_item['ev_pizza_config']['consists_of']['to_add'] as $component ) {
						$item_data['consists_of']['to_add'][] = array(
							'key'   => $component['name'],
							'value' => $component['weight'] !== '' ? '<span>' . $component['weight'] . '/' . '</span>' . wc_price( $component['price'] ) . '<span class="pizza-quantity-badge">' . ' x' . $component['quantity'] . '</span>' : wc_price( $component['price'] ) . '<span class="pizza-quantity-badge">' . ' x' . $component['quantity'] . '</span>',
						);
					}
				}
				if ( isset( $cart_item['ev_pizza_config']['consists_of']['consists'] ) ) {
					$item_data['consists_of']['consists_text'] = apply_filters( 'ev_pizza_components_basic_text', esc_html__( 'Basic Components:', 'pizza-builder-for-woocommerce' ), $cart_item['data']->get_id() );
					foreach ( $cart_item['ev_pizza_config']['consists_of']['consists'] as $component ) {
						$item_data['consists_of']['consists'][] = array(
							'key'   => $component['name'],
							'value' => $component['weight'] !== '' ? '<span>' . $component['weight'] . '/' . '</span>' . wc_price( $component['price'] ) : wc_price( $component['price'] ),
						);
					}
				}
				if ( isset( $cart_item['ev_pizza_config']['layers']['components'] ) ) {
					$item_data['layers']['layers_text'] = apply_filters( 'ev_pizza_components_layers_text', esc_html__( 'Layers:', 'pizza-builder-for-woocommerce' ), $cart_item['data']->get_id() );
					foreach ( $cart_item['ev_pizza_config']['layers']['components'] as $component ) {

						$item_data['layers']['components'][] = array(
							'key'   => $component['name'],
							'value' => wc_price( $component['price'] ),
						);
					}
				}
				if ( isset( $cart_item['ev_pizza_config']['bortik']['components'] ) ) {
					$item_data['bortik']['bortik_text'] = apply_filters( 'ev_pizza_components_side_text', esc_html__( 'Side:', 'pizza-builder-for-woocommerce' ), $cart_item['data']->get_id() );
					foreach ( $cart_item['ev_pizza_config']['bortik']['components'] as $component ) {

						$item_data['bortik']['components'][] = array(
							'key'   => $component['name'],
							'value' => wc_price( $component['price'] ),
						);
					}
				}
				
				wc_get_template(
					'cart/ev-pizza-meta.php',
					array(
						'product'   => $product,
						'item_data' => $item_data,
						'key'       => $cart_item_key,
					),
					'',
					EV_PIZZA_PATH . 'templates/'
				);
			}
		}
	}

	public function add_extra_payment( $cart_object ) {
		$pizza_components = ev_pizza_get_components();
		foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];

			if ( ev_is_pizza_product( $product_id ) ) {
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
							if ( ! empty( $food_components_data['rules'] ) ) {
								foreach ( $food_components_data['rules'] as $rule ) {
									if ( $rule['name'] === $component['id'] ) {
										switch ( $rule['name_action'] ) {
											case 'quantity':
												$price = $this->run_numeric_rule( $price, $origin_component, $rule, $qty, 'qty' );
												break;
											case 'total_price':
												$price = $this->run_numeric_rule( $price, $origin_component, $rule, $qty, 'total' );
												break;
											case 'weight':
												$price = $this->run_numeric_rule( $price, $origin_component, $rule, $qty, 'weight' );
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
							$pizza_consists    = $food_components_data['consists_of']['consists'];

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

				$cart_item['data']->set_price( $price );
			}

			if ( isset( $cart_item['pbw_components'] ) ) {
				$price = 0;
				foreach ( $cart_item['pbw_components'] as $component ) {

						$price += floatval( $component['price'] );

				}
				$cart_item['data']->set_price( $price );
			}
		}
	}

	public function run_numeric_rule( $price, $component, $rule, $qty, $action_value ) {

		$pizzaCustomRules = apply_filters( 'ev_pizza_rules_custom', array() );

		$valueToMatch = null;
		if ( $action_value === 'qty' ) {
			$valueToMatch = $qty;
		} elseif ( $action_value === 'total' ) {
			$valueToMatch = $price;
		} elseif ( $action_value === 'weight' ) {
			$valueToMatch = $qty * floatval( $component['weight'] );
		}
		if ( $this->run_meet_comparator( $rule, $valueToMatch ) ) {

			switch ( $rule['action'] ) {
				case 'custom':
					foreach ( $pizzaCustomRules as $c_rule ) {
						if ( $c_rule['id'] === $rule['value'] ) {
							if ( is_callable( ( $rule['callback'] ) ) ) {
								$extra_price = call_user_func_array( $rule['callback'], array( $component, $rule, $valueToMatch ) );

								if ( is_numeric( $extra_price ) ) {
									$price += $extra_price;
								}
							}

							break;
						}
					}

					return $price;
				case 'discount':
					if ( strpos( $rule['value'], '%' ) !== false ) {
						$price -= ( $price / 100 ) * floatval( $rule['value'] );
					} else {
						$price -= floatval( $rule['value'] );
					}
					return $price;

				case 'fee':
					if ( strpos( $rule['value'], '%' ) !== false ) {
						$price += ( $price / 100 ) * floatval( $rule['value'] );
					} else {
						$price += $rule['value'];
					}
					return $price;

			}
		}
		return $price;
	}

	public function run_meet_comparator( $rule, $value ) {
		switch ( $rule['comparator'] ) {
			case '>':
				return floatval( $value ) > floatval( $rule['name_value'] );
			case '<':
				return floatval( $value ) < floatval( $rule['name_value'] );
			case '=':
				return floatval( $value ) === floatval( $rule['name_value'] );
			case '!=':
				return floatval( $value ) !== floatval( $rule['name_value'] );
			case '>=':
				return floatval( $value ) >= floatval( $rule['name_value'] );
			case '<=':
				return floatval( $value ) <= floatval( $rule['name_value'] );
			default:
				return false;
		}
	}

	public function debug_cart() {
		// debugg
		$cart = WC()->cart->get_cart();

		echo '<pre>';
		print_r( $cart );
		echo '</pre>';
	}
}
