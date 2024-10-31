(function($){
	const evPizzaHooks = window.evPizzaHooks;

	let symbolCurrency = EV_FRONT_BUILDER.wc_symbol,
    pricePosition = EV_FRONT_BUILDER.price_position,
    wcDecimals = EV_FRONT_BUILDER.decimals || 2,
    decimalSep = EV_FRONT_BUILDER.decimal_separator ? EV_FRONT_BUILDER.decimal_separator : ".",
    thousandSep = EV_FRONT_BUILDER.thousand_separator;

  function ev_wc_price(price) {
    function addThousandSep(n) {
      const rx = /(\d+)(\d{3})/;
      return String(n).replace(/^\d+/, function (w) {
        while (rx.test(w)) {
          w = w.replace(rx, "$1" + thousandSep + "$2");
        }
        return w;
      });
    }
    let priceString = price.toFixed(wcDecimals);
    priceString = priceString.replace(".", decimalSep);
    if (thousandSep) {
      priceString = addThousandSep(priceString);
    }
    switch (pricePosition) {
      case "left":
        priceString = `${symbolCurrency}${priceString}`;
        break;
      case "right":
        priceString = `${priceString}${symbolCurrency}`;
        break;
      case "left_space":
        priceString = `${symbolCurrency} ${priceString}`;
        break;
      case "right_space":
        priceString = `${priceString} ${symbolCurrency}`;
        break;
    }

    return priceString;
  }

	//steps
	class EV_Pizza_Builder {
		constructor(stepId) {
		  this.id = stepId;

		  this.data = window[`pbw_builder_${this.id}`];

		  if(typeof this.data === 'undefined') {
			return;
		  }

		  this.type = this.data.type;
		  this.is_modal = 'modal' === this.type || 'steps-slider' === this.type;

		  this.button = $(`.pbw-builder-button[data-steps=${this.id}]`);
	
		  this.stepsModal = $(`.pbw-builder-wrapper[data-steps=${this.id}]`);
		  this.form = $(".pbw-builder-form", this.stepsModal);
		  this.steps = $(".pbw-builder-step-block", this.stepsModal);
		  this.inputComponents = $("input[name=pbw_components]", this.stepsModal);
		  this.choosenContainer = $(".pbw-builder-step__choosen", this.stepsModal);
		  this.priceContainer = $(".pbw-total", this.choosenContainer);
		  this.buyButton = $(".pbw-place-order", this.stepsModal);
		  this.successContainer = $(".pbw-success-container", this.stepsModal);
		  this.selectedComponents = [];
		  this.fancybox = null;
	
		  this.price = 0;
		  this.prevButton = $(".pbw-prev", this.stepsModal);
		  this.nextButton = $(".pbw-next", this.stepsModal);
		  this.step = 1;
		  this.shownSteps = [];
		  this.required = this.data.data.components.map((c) => ({
			step: c.step,
			required: c.required == 1 ? true : false,
		  }));

		  this.hooks();
		  this.init();

		  //events
		  this.button.on("click", this.openModal.bind(this));
		  this.buyButton.on("click", this.submit.bind(this));
		  this.stepsModal.on("click", ".pbw-builder-steps .pbw-builder-step__component", this.chooseComponent.bind(this));

		  this.prevButton.on("click", (e) => {
			e.preventDefault();
			this.step--;
			if (this.step < 1) {
			  this.step = 1;
			}
			this.move();
			this.validate();
		  });
	
		  this.nextButton.on("click", (e) => {
			e.preventDefault();
			
			if (!this.validate()) {
			  return;
			}
	
			this.step++;
			if (this.step > this.steps.length) {
			  this.step = this.steps.length;
			}
			this.move();
			this.validate();
		  });

		  this.validate();

		  evPizzaHooks.builderHooks.doAction("ev-pizza-builder-init", this);
		}

		hooks() {
			//this.builderHooks = wp.hooks.createHooks();
		}

		init() {
	
			if (EV_FRONT_BUILDER.tippy_enabled && !this.is_modal) {
				tippy("[data-tippy-content]", {
					appendTo: this.stepsModal.get(0),
					zIndex: 99999,
				});
			}

			this.responsible();
		}
	
		move() {
		  this.steps.each(function () {
			$(this).removeClass("active");
		  });
		  $(this.steps[this.step - 1]).addClass("active");
		  this.choosenContainer.eq(0).show();
	
		  
		}

		validate() {
		  return this.refreshUI();
		}

		openModal() {
			this.fancybox = Fancybox.show([{
			src: `#pbw-builder-wrapper-${this.id}`,
			type: "inline",
			}],
			{ touch: false,
			buttons: ["close"],
			btnTpl: {
			  smallBtn:
				'<button type="button" data-fancybox-close class="fancybox-button fancybox-close-small" title="{{CLOSE}}">' +
				'<svg xmlns="http://www.w3.org/2000/svg" version="1" viewBox="0 0 24 24"><path d="M13 12l5-5-1-1-5 5-5-5-1 1 5 5-5 5 1 1 5-5 5 5 1-1z" fill="#fff"/></svg>' +
				"</button>",
			},
			on: {
				done: (instance) => {
				//console.log('aftershow', instance);
				if (EV_FRONT_BUILDER.tippy_enabled) {
				  tippy("[data-tippy-content]", {
					appendTo: instance.$container,
					zIndex: 99999,
				  });
				}
	
				this.responsible(); /// ????
			  },
			},
			});
		}
		chooseComponent(e) {
		  const selectedComponent = $(e.target).closest(".pbw-builder-step__component");
		  const componentId = selectedComponent.data("component");
	
		  if (this.selectedComponents.includes(componentId)) {
			this.selectedComponents = this.selectedComponents.filter((el) => el !== componentId);
			selectedComponent.removeClass("active");
		  } else {
			this.selectedComponents.push(componentId);
			selectedComponent.addClass("active");
		  }
		  this.inputComponents.val(JSON.stringify(this.selectedComponents));
		  this.choosenContainer.eq(0).show();
		  this.calculate();
		  this.refreshUI();
		  this.addTemplate();
		}
	
		calculate() {
		  // console.log(this.data.data.components);
		  let summ = 0;
		  let includesArray = [];
		  this.data.data.components.forEach((step) => {
			step.components.forEach((c) => {
			  if (this.selectedComponents.includes(c.id)) {
				if (!includesArray.includes(c.id)) {
				  summ += parseFloat(c.price);
				  includesArray.push(c.id);
				}
			  }
			});
		  });
		  this.price = summ;
		  this.priceContainer.html(ev_wc_price(parseFloat(this.price)));
		}
		refreshUI() {
		  const dataCurrentStep = this.data.data.components[this.step - 1];
	
		  let isValid = true;

		  if (dataCurrentStep && dataCurrentStep.required) {
			if (dataCurrentStep.components.length > 0) {
			  const choosenOne = dataCurrentStep.components.some((c) => this.selectedComponents.includes(c.id));
	
			  if (choosenOne) {
				this.nextButton.attr("disabled", false);
	
				isValid = true;
			  } else {
				this.nextButton.attr("disabled", true);
				isValid = false;
			  }
			}
		  }

		  if (this.step === this.steps.length) {
			this.nextButton.attr("disabled", true);
		  }

		  if (this.step === this.steps.length) {
			if (isValid) {
			  this.buyButton.attr("disabled", false);
			} else {
			  this.buyButton.attr("disabled", true);
			}
		  } else {
			this.buyButton.attr("disabled", true);
		  }
	
		  return isValid;
		}
		addTemplate() {
		  const templateChoosen = wp.template("pizza-builder-choosen");
		  const dataCurrentStep = this.data.data.components[this.step - 1];
		  // const choosenContainer = $(this.steps[this.step-1]).find('.pbw-builder-step__choosen .pbw-builder-step__components');
		  // choosenContainer.html('');
		  if (dataCurrentStep.components.length > 0) {
			dataCurrentStep.components.forEach((c) => {
			  const choosenComponent = this.choosenContainer.find(`[data-choosen=${c.id}]`);
			  // console.log(choosenComponent);
			  if (this.selectedComponents.includes(c.id) && choosenComponent.length < 1) {
				const templateData = {
				  id: c.id,
				  name: c.name,
				  image: this.stepsModal.find(`.pbw-builder-step__component[data-component=${c.id}]`).find("img").attr("src"),
				  price: ev_wc_price(parseFloat(c.price)),
				};
				this.choosenContainer.find(".pbw-builder-step__components").append(templateChoosen(templateData));
			  } else if (!this.selectedComponents.includes(c.id) && choosenComponent.length > 0) {
				choosenComponent.remove();
			  }
			});
		  }
		}
		submit(e) {
		  e.preventDefault();
		  $(this.buyButton).addClass("loading");
		  $(this.buyButton).attr("disabled", true);
		  $.ajax({
			url: EV_FRONT_BUILDER.ajax_url,
			type: "POST",
			data: this.form.serialize() + '&nonce=' + EV_FRONT_BUILDER.nonce,
		  })
			.then((res) => {

				if ( res.fragments ) {
					const $supports_html5_storage = 'sessionStorage' in window && window.sessionStorage !== null;
					if ( typeof window.wc_cart_fragments_params !== 'undefined' ) {
						const cart_hash_key = wc_cart_fragments_params.cart_hash_key;
						$.each( res.fragments, function ( key, value ) {
							$( key ).replaceWith( value );
						} );

						if ( $supports_html5_storage ) {
							sessionStorage.setItem(
								wc_cart_fragments_params.fragment_name,
								JSON.stringify( res.fragments )
							);

							localStorage.setItem( cart_hash_key, res.cart_hash );
							sessionStorage.setItem( cart_hash_key, res.cart_hash );

							if ( res.cart_hash ) {
								sessionStorage.setItem( 'wc_cart_created', new Date().getTime() );
							}
						}
					}

					$( document.body ).trigger( 'wc_fragments_refreshed' );
					$( document.body ).trigger( 'ev_pizza_builder_added', [ res ] );
				
				}

				const callback = evPizzaHooks.builderHooks.applyFilters(
					'ev-pizza-after-builder-request',
					() => {
						if (res.cart_hash) {

							this.successContainer.html(
								'<svg width="67" height="67" viewBox="0 0 67 67" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="33.5" cy="33.5" r="33.5" fill="#71DB1A"/><path d="M45.4639 20.3394L30.0023 46.8724L20.3389 35.0799" stroke="white" stroke-width="3" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg><p>Done!</p>'
							);
			
							this.successContainer.addClass("active");
							setTimeout(() => {
								this.successContainer.removeClass("active");
			
								if(this.fancybox !== null) {
								this.fancybox.close();
								}
			
							}, 2500);
			
							
							}
					},
					res,
					this
				);
				callback();
				


				if (EV_FRONT_BUILDER.redirect_cart) {
				window.location.href = EV_FRONT_BUILDER.redirect_cart;
				}
				else {
					$(this.buyButton).removeClass("loading");
					$(this.buyButton).attr("disabled", false);
					
				}

			})
			.fail((err) => console.log(err));
		}
	
		responsible() {

		const startsSwiper = evPizzaHooks.builderHooks.applyFilters('ev-pizza-swiper-builder-starts', 768, this);
		const defaultSwiperargs = {
			direction: "horizontal",
			loop: false,
			grabCursor: false,
			cssMode: true,
			//centeredSlides: true,
			breakpoints: {
			// when window width is >= 320px
			320: {
				slidesPerView: 2.4,
				spaceBetween: 10,
			},
			
			// when window width is >= 576px
			576: {
				slidesPerView: 3.4,
				initialSlide: 0,
				spaceBetween: 10,
				centeredSlides: false,
			},

			[startsSwiper]: {
				slidesPerView: 5,
				initialSlide: 0,
				spaceBetween: 10,
				
			},
			},
			pagination: {
				el: '.swiper-pagination',
				type: 'bullets',
				clickable: true
			},
		};
		const swiperArgs = evPizzaHooks.builderHooks.applyFilters('ev-pizza-builder-swiper-args', defaultSwiperargs, this);

		  if (window.matchMedia("(max-width: "+startsSwiper+"px)").matches) {

			this.steps.each((i,item) => {
				const builderSwiper = $(".pbw-builder-step__components", $(item));
				builderSwiper.addClass('swiper');
				builderSwiper.find('.pbw-builder-step__component').addClass('swiper-slide').wrapAll('<div class="swiper-wrapper" />');

				if(typeof defaultSwiperargs.pagination !== 'undefined') {
					builderSwiper.append($('<div class="swiper-pagination" />'))
	
				}

				const swiperSteps = new Swiper(builderSwiper.get(0), swiperArgs);
			});
			// if (this.shownSteps.includes(this.step)) {
			//   return;
			// }

		

			//this.shownSteps.push(this.step);
			
		  }
		}
	  }
	  $(".pbw-builder-wrapper").each(function () {
		new EV_Pizza_Builder($(this).data("steps"));
	  });

})(jQuery)