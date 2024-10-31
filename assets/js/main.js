(function($) {
	"use strict";

	let evPizzaInstance;
	let evPizzaHooks = window.evPizzaHooks;
	
	
	$(document.body).on('click', '.ev-pizza-choose-btn', function(e) {
		e.preventDefault();
		const post_id = $(this).data('product-id');
		// $.ajax({
			
		// })

		const fancybox = Fancybox.show([
			{
			  src: `${EV_PIZZA_FRONT.ajax_url}?action=ev_pizza_show_fancy&post_id=${post_id}`,
			  type: "ajax",
			},
		  ], {id: 'ev_pizza', touch: false, on: {destroy: () => {
			//console.log('fdsfdsf');
		}}});

		  fancybox.on("done", (instance) => {
			//console.log(instance);
			const fancyboxContainer = instance.$container;
			const variationForm = $(instance.$container).find('form.variations_form');
			if(variationForm.length) {
				const newForm = variationForm.wc_variation_form();
				evPizzaInstance = new EvPizzaFront("#fancybox-ev_pizza form.variations_form");
			}
			else {
				evPizzaInstance = new EvPizzaFront("#fancybox-ev_pizza form.cart");
			}
			//$('.pizza-quick-view .single_add_to_cart_button').removeClass('single_add_to_cart_button').off();
			$('.pizza-quick-view form.cart').on('submit', pizzaAddToCart)
			
		  });
		  fancybox.on("destroy", (instance) => {
			//instance.destroy();
			// console.log('closeses');
			// console.log(evPizzaInstance);
			evPizzaInstance.destroy();
			$('.pizza-quick-view form.cart').off('submit', pizzaAddToCart)
		  });

		
	} );

	//yith qv compat
	$(document).on('qv_loader_stop', function() {
		const variationForm = $('#yith-quick-view-content form.variations_form')
		if(variationForm.length) {
			const newForm = variationForm.wc_variation_form();
			evPizzaInstance = new EvPizzaFront("#yith-quick-view-content form.variations_form");
		}
		else {
			evPizzaInstance = new EvPizzaFront("#yith-quick-view-content form.cart");
		}
	});


	  //cart&checkout
	  $(document.body).on("click", ".pizza-composition-toggle", function () {
		Fancybox.show([{ src: `#ev-pizza-${$(this).attr("data-product-id")}`, type: "inline" }] );
			
	  });
	  
	  $(document.body).on("click", ".ev-remove-component", function (e) {
		e.preventDefault();
	  });


	  //ajax add to cart archive.

	  function pizzaAddToCart(e) {
	  
		e.preventDefault();
		const form = $(this);
		const button = form.find('.single_add_to_cart_button');
		//console.log(form.serializeArray());
		$(document.body).trigger( 'ev-pizza-before-adding-to-cart', [ button ] );

		button.attr( 'disabled', true );
		button.addClass( 'loading' );
		let serializedForm = form.serialize();

		const dataPairs = serializedForm.split('&');

		//remove add-to-cart to handle adding to cart
		const filteredPairs = dataPairs.filter(function(pair) {
			return pair.indexOf('add-to-cart') === -1;
		});

		serializedForm = filteredPairs.join('&');

		if(!serializedForm.includes('product_id')) {
			serializedForm += '&product_id=' + button.val();
		}

		$.ajax( {
			url: EV_PIZZA_FRONT.ajax_url,
			type: 'POST',
			data: 'action=ev_pizza_add_product&nonce=' + EV_PIZZA_FRONT.nonce + '&' + serializedForm,
			beforeSend: () => {
				$( document.body ).trigger( 'adding_to_cart' );
			},
		} )
			.then( ( res ) => {
				//console.log( res );
				if ( res.success && res.data.fragments ) {
					const $supports_html5_storage = 'sessionStorage' in window && window.sessionStorage !== null;

					if ( typeof window.wc_cart_fragments_params !== 'undefined' ) {
						const cart_hash_key = wc_cart_fragments_params.cart_hash_key;
						$.each( res.data.fragments, function ( key, value ) {
							$( key ).replaceWith( value );
						} );

						if ( $supports_html5_storage ) {
							sessionStorage.setItem(
								wc_cart_fragments_params.fragment_name,
								JSON.stringify( res.data.fragments )
							);

							localStorage.setItem( cart_hash_key, res.data.cart_hash );
							sessionStorage.setItem( cart_hash_key, res.data.cart_hash );

							if ( res.data.cart_hash ) {
								sessionStorage.setItem( 'wc_cart_created', new Date().getTime() );
							}
						}
					}

					$( document.body ).trigger( 'wc_fragments_refreshed' );
					$( document.body ).trigger( 'ev_pizza_add_success', [ res.data ] );
					button.attr( 'disabled', false );
					button.removeClass( 'loading' );
					Fancybox.getInstance().close();
				}
			} )
			.catch( ( err ) => {
				console.log( err );
				$( document.body ).trigger( 'ev_pizza_add_error' );
				$( document.body ).trigger( 'wc_fragments_ajax_error' );
			} );
	};
	  
})(jQuery)