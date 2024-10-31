(function ($) {
  "use strict";

  const isMobile = window.matchMedia("(max-width: 576px)").matches;
  const isTablet = window.matchMedia("(max-width: 768px)").matches;

  const evPizzaHooks = window.evPizzaHooks;

  let symbolCurrency = EV_PIZZA_FRONT.wc_symbol,
    pricePosition = EV_PIZZA_FRONT.price_position,
    wcDecimals = EV_PIZZA_FRONT.decimals || 2,
    decimalSep = EV_PIZZA_FRONT.decimal_separator ? EV_PIZZA_FRONT.decimal_separator : ".",
    thousandSep = EV_PIZZA_FRONT.thousand_separator;

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

  function ev_pizza_parsenumber(number) {
    if (parseInt(number, 10) < 1 || isNaN(parseInt(number, 10))) {
      return 0;
    }
    return parseInt(number, 10);
  }

  const evParseNumber = (str, defaultValue = 0) => {
    const num = parseFloat(str);
    if (isNaN(num) || !isFinite(num)) {
      return defaultValue;
    }
    return num;
  };

  function ev_wc_price_sale(price, regular_price) {
    function addThousandSep(n) {
      const rx = /(\d+)(\d{3})/;
      return String(n).replace(/^\d+/, function (w) {
        while (rx.test(w)) {
          w = w.replace(rx, "$1" + thousandSep + "$2");
        }
        return w;
      });
    }
    let priceStringRegular = regular_price.toFixed(wcDecimals);
    let priceString = price.toFixed(wcDecimals);
    priceString = priceString.replace(".", decimalSep);
    if (thousandSep) {
      priceString = addThousandSep(priceString);
    }

    switch (pricePosition) {
      case "left":
        return `<del>${symbolCurrency}${priceStringRegular}</del><ins>${symbolCurrency}${priceString}</ins>`;
      case "right":
        return `<del>${priceStringRegular}${symbolCurrency}</del><ins>${priceString}${symbolCurrency}</ins>`;
      case "left_space":
        return `<del>${symbolCurrency} ${priceStringRegular}</del><ins>${symbolCurrency} ${priceString}</ins>`;
      case "right_space":
        return `<del>${priceStringRegular} ${symbolCurrency}</del><ins>${priceString} ${symbolCurrency}</ins>`;
    }
  }

  class EvPizzaFront {
    constructor(el) {
      this.pizzaContainer = $(el).find(".pizza_components_wrapper");
      //console.log('inited',  this.pizzaContainer);
      if (!this.pizzaContainer.length) {
        return;
      }

      this.pizzaComponentsWrapper = $(el).find(".pizza-components-wrapper");
      this.pizzaComponentTabsWrapper = $(el).find(".pizza-component-tabs-wrapper");

      this.form = $(el);

      if (!this.form.length) {
        return;
      }

      this.wcVariationFormInstance = null;
      this.formHasSmoothSwatches = this.form.find(".pizza-smooth-radio").length;
      this.dodoEnabled = this.form.find(".pizza-dodo-style").length;

      this.productType = $(el).hasClass("variations_form") ? "variable" : "simple";
      this.isQuickView = $(el).closest(".pizza-quick-view").length;
      this.quickViewContainer = $(el).closest(".pizza-quick-view");
      this.componentTabsEnabled = $(".pizza-component-tabs-wrapper", this.pizzaContainer).length;

      this.dataComponents = JSON.parse(this.pizzaContainer.attr("data-pizza"));

      this.layersEnabled = this.dataComponents.layers.enabled;
      this.sidesEnabled = this.dataComponents.bortik.enabled;
      this.pizzaSavedRules = this.dataComponents.rules;
      this.pizzaSavedRulesCustom = this.dataComponents.rules.filter((rule) => rule.action === "custom");

      //   evPizzaHooks.pizzaHooks.addFilter("pizza-rules-custom", "ev_pizza", function (rules) {
      //     return [...rules, { id: "rule", value: "ruuule" }];
      //   });

      this.inputLayer = $("[name=pizza-layer-data]", this.pizzaContainer);
      this.inputSides = $("[name=pizza-sides-data]", this.pizzaContainer);
      this.initialPrice = this.pizzaContainer.attr("data-price");
      this.addToCartButton = this.form.find(".single_add_to_cart_button");
      this.selectedIdLayers = [
        {
          id: this.pizzaContainer.attr("data-product-id"),
          position: 1,
        },
      ];
      this.selectedIdSides = [];

      //for variations
      this.validated = false;

      //
      this.hooks();
      this.init();

      //events
      $(".component-buttons", this.pizzaContainer).on("click", ".qty_button_plus, .qty_button_minus", this.handleQuantity.bind(this));
      //ripple
      $(".component-buttons", this.pizzaContainer).on("mousedown", ".qty_button_plus, .qty_button_minus", this.rippleButtons);

      //base components
      $("[data-id=remove-component] .pizza-components-item", this.pizzaContainer).on("click", this.calculateComponentsRemove.bind(this));

      //base components (dodo)
      $("[data-id=remove-component] .pizza-base-item", this.pizzaContainer).on("click", this.calculateComponentsRemoveDodo.bind(this));

      this.boundhandleNavTabs = this.handleNavTabs.bind(this);
      this.boundopenSides = this.openSides.bind(this);
      this.boundhandleSides = this.handleSides.bind(this);
      this.boundremoveSide = this.removeSide.bind(this);
      this.boundremoveLayer = this.removeLayer.bind(this);
      this.boundhandleLayers = this.handleLayers.bind(this);
      this.boundOpenLayers = this.openLayers.bind(this);
      this.boundCloseFancy = this.closeFancy.bind(this);
      //navigation
      $(document.body).on("click", ".pizza-components-nav a", this.boundhandleNavTabs);
      //sides
      $(document.body).on("click", "#pizza-sides-button", this.boundopenSides);
      $(document.body).on("click", ".pizza-fancybox-sides .pizza-layer-item", this.boundhandleSides);
      $(document.body).on("click", ".ev-remove-side", this.boundremoveSide);
      //layers
      $(document.body).on("click", ".ev-remove-layer", this.boundremoveLayer);
      $(document.body).on("click", ".pizza-fancybox-layers .pizza-layer-item", this.boundhandleLayers);
      $(document.body).on("click", "#pizza-layer-button", this.boundOpenLayers);
      $(document.body).on("click", ".choose-layer-button, .choose-side-button", this.boundCloseFancy);

      //swatches
      $(document.body).on("click", ".ev-smooth-item", this.handleSmoothSwatchClick.bind(this));

      if (this.productType === "variable" && this.formHasSmoothSwatches) {
        this.form.on("wc_variation_form", this.initSwatches.bind(this));
        this.form.on("found_variation", this.foundVariation.bind(this));
        this.form.on("show_variation", this.showVariation.bind(this));
        this.form.on("woocommerce_variation_has_changed", this.onChangeVariation.bind(this));
        this.form.on("click", ".reset_variations", this.resetVariation.bind(this));
      }

      if (this.componentTabsEnabled) {
        $(".pizza-tab-link", this.pizzaContainer).on("click", function (e) {
          e.preventDefault();
          let tabId = $(this).attr("data-tab-id");
          $(".component-item-tab", this.pizzaContainer).each(function () {
            $(this).removeClass("fade-in");
          });
          $(".pizza-tab-link", this.pizzaContainer).each(function () {
            $(this).removeClass("active");
          });
          $(this).addClass("active");
          $(`#${tabId}`, this.pizzaContainer).addClass("fade-in");
        });
      }

      //tippy
      if (EV_PIZZA_FRONT.tippy_enabled && $("form.cart").length) {
        tippy("[data-tippy-content]");
      }

      this.pluginTabs();
      //   $(window).on('resize', () => {
      // 	this.pluginTabs('resize');
      //   })
      evPizzaHooks.pizzaHooks.doAction("ev-pizza-init", this);
    }

    hooks() {
      //this.pizzaHooks = wp.hooks.createHooks();
    }

    init() {
      $("body").addClass("ev-pizza-component");

      //if is variable
      if (this.productType === "variable") {
        this.variationPrice = 0;
        this.variationRegularPrice = 0;

        this.form.on("show_variation", (event, variation) => {
          this.selectedIdLayers[0].id = variation.variation_id;
          this.inputLayer.val(JSON.stringify(this.selectedIdLayers));
          if (variation.display_price !== variation.display_regular_price) {
            this.variationPrice = parseFloat(variation.display_price);
            this.variationRegularPrice = parseFloat(variation.display_regular_price);
            $(".pizza-variable-price", this.pizzaContainer).html(
              ev_wc_price_sale(parseFloat(variation.display_price), parseFloat(variation.display_regular_price)),
            );
          } else {
            this.variationRegularPrice = 0;
            this.variationPrice = parseFloat(variation.display_price);
            $(".pizza-variable-price", this.pizzaContainer).html(ev_wc_price(this.variationPrice));
          }
          if (this.addToCartButton.is(".wc-variation-selection-needed")) {
            this.validated = false;
          } else {
            this.validated = true;
          }
          this.calculate();
        });

        this.form.on("hide_variation", () => {
          //console.log("hide");
          this.variationPrice = 0;
          this.variationRegularPrice = 0;
          setTimeout(() => {
            if (this.addToCartButton.is(".wc-variation-selection-needed")) {
              this.validated = false;
            }
          }, 100);

          this.calculate();
        });
        this.form.on("woocommerce_variation_has_changed", () => {
          //console.log("updated");
          if (this.addToCartButton.is(".wc-variation-selection-needed")) {
            this.validated = false;
          } else {
            this.validated = true;
          }
          this.calculate();
        });
      }
    }

    handleQuantity(e) {
      //return if not validated
      if (this.productType === "variable" && !this.validated) return;

      const currentElement = $(e.currentTarget);

      const $qty = $(e.currentTarget).closest(".pizza-quantity").find(".component-qty"),
        max = parseFloat($qty.attr("max")),
        min = parseFloat($qty.attr("min")),
        step = $qty.attr("step");

      let currentVal = parseFloat($qty.val());

      // Format values
      if (!currentVal || currentVal === "" || currentVal === "NaN") currentVal = 0;
      if (max === "" || max === "NaN") max = "";
      if (min === "" || min === "NaN") min = 0;
      if (step === "any" || step === "" || step === undefined || parseFloat(step) === "NaN") step = 1;

      // Change the value
      if (currentElement.is(".qty_button_plus")) {
        if (max && currentVal >= max) {
          $qty.val(max);
        } else {
          $qty.val(currentVal + parseFloat(step));
        }
        if (currentVal + parseFloat(step) >= 1) {
          $qty.addClass("is-active");
          currentElement.siblings(".qty_button_minus").css("display", "block");
        }
      } else {
        if (min && currentVal <= min) {
          $qty.val(min);
        } else if (currentVal > 0) {
          $qty.val(currentVal - parseFloat(step));
        }
        if (currentVal - parseFloat(step) < 1) {
          $qty.removeClass("is-active");
          currentElement.hide();
        }
      }
      $qty.trigger("change");

      //calculate
      this.calculate();
    }

    handleNavTabs(e) {
      e.preventDefault();
      const currentItem = $(e.currentTarget);

      $(".pizza-components-tab", this.pizzaContainer).each((i, item) => {
        $(item).removeClass("fade-in");

        $(item)
          .parents(".slimScrollDiv")

          .height(0);
      });

      $(".pizza-components-nav a", this.pizzaContainer).each((i, item) => {
        $(item).removeClass("active");
      });

      currentItem.addClass("active");

      const targetedSelector = e.target.hash.replace("#", "");
      const targetedElement = $(`[data-id="${targetedSelector}"]`, this.pizzaContainer);
      targetedElement.addClass("fade-in");
      if (targetedElement.height() > 450) {
        if (!targetedElement.parents(".slimScrollDiv").length) {
          if (window.matchMedia("(min-width: 990px)").matches) {
            targetedElement.css("padding-right", "25px");
            targetedElement.slimScroll({
              height: 450,
              railVisible: true,
              alwaysVisible: true,
              size: "6px",
              color: "#FF0329",
              railColor: "#EAEAEA",
              railOpacity: 1,
              wheelStep: 5,
            });
          }
        }
      }
      targetedElement.parents(".slimScrollDiv").height(450);
    }

    calculate() {
      if (this.productType === "variable" && this.variationPrice === 0) {
        return;
      }
      if (this.productType === "variable" && !this.validated) {
        return;
      }

      let summ = parseFloat(this.initialPrice);
      if (this.productType === "variable") {
        summ = this.variationPrice;
      }

      if (this.dataComponents.ev_inc && $(".pizza-components-block", this.pizzaContainer).length) {
        let inputConsists = $("input[name=ev-pizza-consists]", this.pizzaContainer);
        let inputValue = JSON.parse(inputConsists.val());
        let priceConsistsExcl = 0;
        this.dataComponents.consists_of.consists.map((layer) => {
          inputValue.map((c) => {
            let key = Object.keys(c)[0];

            if (key === layer.id && !c[key]) {
              let layerPrice = parseFloat(layer.price);
              layerPrice = isNaN(layerPrice) || !isFinite(layerPrice) ? 0 : layerPrice;
              priceConsistsExcl += layerPrice;
            }
          });
        });
        summ = summ - priceConsistsExcl;
      }

      $("[data-id=add-component] .pizza-components-item", this.pizzaContainer).each((i, item) => {
        let val = $(item).find(".component-qty").val();
        let componentId = $(item).find(".component-buttons").attr("data-food-item");
        let componentObject = this.dataComponents.consists_of.to_add.find((component) => component.id === componentId);
        // console.log(componentObject);
        if (componentObject !== undefined) {
          let semiPrice = parseFloat(componentObject.price);
          semiPrice = isNaN(semiPrice) || !isFinite(semiPrice) ? 0 : semiPrice;
          // summ += semiPrice * parseInt(val);
          const modPrice = this.runRules(componentObject, { qty: val }, "pizza");
          summ += modPrice;
        }
      });

      if (this.pizzaComponentsWrapper.length) {
        $(".components-item-wrapper .component-item", this.pizzaContainer).each((i, item) => {
          let val = $(item).find(".component-qty").val();
          let componentId = $(item).find(".component-buttons").attr("data-food-item");
          let componentObject = this.dataComponents.extra.components.find((component) => component.id === componentId);
          // console.log(componentObject);
          if (componentObject !== undefined) {
            let semiPrice = parseFloat(componentObject.price);
            semiPrice = isNaN(semiPrice) || !isFinite(semiPrice) ? 0 : semiPrice;
            const modPrice = this.runRules(componentObject, { qty: val }, "extra");
            summ += modPrice;
          }
        });
      }

      if (this.pizzaComponentTabsWrapper.length) {
        this.pizzaComponentTabsWrapper.find(".component-item").each((i, item) => {
          let val = $(item).find(".component-qty").val();

          let componentId = $(item).find(".component-buttons").attr("data-food-item");
          let componentObject = this.dataComponents.extra.components.find((component) => component.id === componentId);

          // console.log(componentObject);
          if (componentObject !== undefined) {
            let semiPrice = parseFloat(componentObject.price);
            semiPrice = isNaN(semiPrice) || !isFinite(semiPrice) ? 0 : semiPrice;
            const modPrice = this.runRules(componentObject, { qty: val }, "tabs");
            summ += modPrice;
          }
        });
      }

      if (this.layersEnabled) {
        let layersData = this.selectedIdLayers.filter((el, i) => i !== 0);

        layersData.forEach((el) => {
          let priceLayer = parseFloat($(`[data-layer=${el.id}]`).attr("data-layer-price"));
          priceLayer = isNaN(priceLayer) || !isFinite(priceLayer) ? 0 : priceLayer;
          summ += priceLayer;
        });
      }
      if (this.sidesEnabled) {
        if (this.selectedIdSides.length > 0) {
          const findSide = this.dataComponents.bortik.components.find((el) => el.id === this.selectedIdSides[0].id);

          if (findSide) {
            let sidePrice = parseFloat(findSide.price);
            sidePrice = isNaN(sidePrice) || !isFinite(sidePrice) ? 0 : sidePrice;
            summ += sidePrice;
          }
        }
      }
      this.refreshPriceHtml(summ);
    }

    refreshPriceHtml(summ) {
      let priceContainer = $(".product.first").find(".price").first();
      if (this.productType === "variable") {
        priceContainer = $("form.variations_form").find(".woocommerce-variation-price .price");
      }

      const isBlockTheme = this.pizzaContainer.parents(".wp-block-column");
      if (this.productType !== "variable" && isBlockTheme.length) {
        priceContainer = isBlockTheme.find(".wp-block-woocommerce-product-price .amount");
      }

      //quick view
      if (this.isQuickView && this.productType !== "variable") {
        priceContainer = this.quickViewContainer.find(".price").first();
      }

      let priceLayerContainer = $(document.body).find(".layers-total-price");

      if (this.productType === "variable" && this.variationRegularPrice > 0) {
        priceContainer.html(
          ev_wc_price_sale(
            summ,

            summ + (this.variationRegularPrice - this.variationPrice),
          ),
        );
      } else {
        priceContainer.html(ev_wc_price(summ));
      }
      if (this.layersEnabled || this.sidesEnabled) {
        priceLayerContainer.html(ev_wc_price(summ));
      }
    }

    calculateComponentsRemove(e) {
      if (this.productType === "variable" && !this.validated) return;

      const currentItem = $(e.currentTarget);

      if (!currentItem.find(".ev-remove-component").length) return;

      let componentId = currentItem.attr("data-component-id");
      let inputConsists = $("input[name=ev-pizza-consists]");
      let inputValue = JSON.parse(inputConsists.val());
      let modiFiedData = inputValue.map((c) => {
        let key = Object.keys(c)[0];
        return c.hasOwnProperty(componentId) ? { [key]: !c[componentId] } : c;
      });
      inputConsists.val(JSON.stringify(modiFiedData));
      this.refreshClasses(modiFiedData);

      this.calculate();
    }

    /**
     *
     * @param {MouseEvent} e
     * @returns
     */
    calculateComponentsRemoveDodo(e) {
      e.preventDefault();
      //console.log(this.validated);
      if (this.productType === "variable" && !this.validated) return;

      const currentItem = $(e.currentTarget);

      let componentId = currentItem.attr("data-component-id");
      let inputConsists = $("input[name=ev-pizza-consists]");
      let inputValue = JSON.parse(inputConsists.val());
      let modiFiedData = inputValue.map((c) => {
        let key = Object.keys(c)[0];
        return c.hasOwnProperty(componentId) ? { [key]: !c[componentId] } : c;
      });
      inputConsists.val(JSON.stringify(modiFiedData));
      this.refreshClasses(modiFiedData);

      this.calculate();
    }

    templateEvLayers() {
      this.inputLayer.val(JSON.stringify(this.selectedIdLayers));
    }

    templateEvSides() {
      this.inputSides.val(JSON.stringify(this.selectedIdSides));
    }

    handleLayers(e) {
      const currentItem = $(e.currentTarget);
      let product_id = currentItem.attr("data-layer");
      let image = currentItem.find("img").attr("src");
      let title = currentItem.find(".ev-pizza-title").text();
      let price = currentItem.find(".ev-pizza-price").html();
      let findElement = this.selectedIdLayers.findIndex((el) => el.id === product_id);
      if (findElement !== -1) {
        return;
      }
      if (this.selectedIdLayers.length >= 3) return;
      let positionIndexes = this.selectedIdLayers.map((l) => l.position);

      let templateIndexes = [1, 2, 3, 4, 5, 6, 7].filter((i) => !positionIndexes.includes(i));

      this.selectedIdLayers = [...this.selectedIdLayers, { id: product_id, position: Math.min(...templateIndexes) }];
      let indexElement = this.selectedIdLayers.findIndex((el) => el.id === product_id);

      this.inputLayer.val(JSON.stringify(this.selectedIdLayers));

      const templateSelected = wp.template("pizza-layer-selected");
      const pizzaSelectedData = {
        name: title,
        image: image,
        product_id: product_id,
        price: price,
      };
      $(".pizza-fancybox-layers .pizza-layers-selected__item")
        .eq(Math.min(...templateIndexes) - 1)
        .replaceWith(templateSelected(pizzaSelectedData));

      this.calculate();
    }

    openLayers(e) {
      e.preventDefault();
      const currentElement = $(e.currentTarget);
      const product_id = currentElement.data("product-id");
      const startsSwiper = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-swiper-layer-starts", 768, this);

      Fancybox.show([{ src: `#ev-pizza-layers-fancybox-${product_id}`, type: "inline" }], {
        touch: false,
        autoSize: false,

        on: {
          done: (instance) => {
            // console.log(instance);
            let layerFancy = $(document.body).find(`#ev-pizza-layers-fancybox-${product_id}`);
            if (window.matchMedia("(min-width: " + startsSwiper + "px)").matches) {
              if (layerFancy.height() > window.innerHeight - 100) {
                layerFancy.css("border-width", "0");
                $(".pizza-layers-block", layerFancy).slimScroll({
                  height: window.innerHeight - 100,
                  railVisible: true,
                  alwaysVisible: true,
                  size: "6px",
                  color: "#FF0329",
                  railColor: "#EAEAEA",
                  railOpacity: 1,
                  wheelStep: 5,
                });
              }
            } else {
              const layerSwiper = $(".pizza-layers-block", layerFancy);
              layerSwiper.addClass("swiper");
              layerSwiper.find(".pizza-layer-item").addClass("swiper-slide").wrapAll('<div class="swiper-wrapper" />');

              const defaultSwiperargs = {
                direction: "horizontal",
                loop: false,
                grabCursor: false,
                cssMode: true,
                //centeredSlides: true,
                breakpoints: {
                  // when window width is >= 320px
                  320: {
                    slidesPerView: 2,
                    spaceBetween: 10,
                  },

                  // when window width is >= 576px
                  576: {
                    slidesPerView: 3,
                    initialSlide: 0,
                    spaceBetween: 10,
                    centeredSlides: false,
                  },

                  [startsSwiper]: {
                    slidesPerView: 4,
                    initialSlide: 0,
                    spaceBetween: 10,
                  },
                },
                pagination: {
                  el: ".swiper-pagination",
                  type: "bullets",
                  clickable: true,
                },
              };
              const swiperArgs = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-layer-swiper-args", defaultSwiperargs, this);

              if (typeof defaultSwiperargs.pagination !== "undefined") {
                layerSwiper.append($('<div class="swiper-pagination" />'));
              }
              const swiperLayer = new Swiper(layerSwiper.get(0), swiperArgs);
            }

            $(document.body).trigger("ev-pizza-layers-show", instance);
          },
          close: (instance) => {
            //instance.destroy();
          },
        },
      });

      this.templateEvLayers();
    }

    removeLayer(e) {
      e.preventDefault();

      const currentItem = $(e.currentTarget);
      const product_id = currentItem.closest(".pizza-layers-selected__item").attr("data-product-id");

      if (!product_id) {
        return;
      }

      let index = $(".pizza-fancybox-layers .pizza-layers-selected__item").index($(`[data-product-id=${product_id}]`));

      const templateDefault = wp.template("pizza-layer-default");
      const pizzaDefaultData = {
        name: EV_PIZZA_FRONT.layer_default_text.replace("%s", index + 1),
        image: EV_PIZZA_FRONT.layer_default_image,
        product_id: "",
      };

      currentItem.closest(".pizza-layers-selected__item").replaceWith(templateDefault(pizzaDefaultData));

      this.selectedIdLayers = this.selectedIdLayers.filter((el) => el.id !== product_id);
      this.inputLayer.val(JSON.stringify(this.selectedIdLayers));
      this.calculate();
    }

    handleSides(e) {
      const currentItem = $(e.currentTarget);
      let side_id = currentItem.attr("data-side-id");
      let image = currentItem.find("img").attr("src");
      let title = currentItem.find(".ev-pizza-title").text();
      let price = currentItem.find(".ev-pizza-price").html();

      this.selectedIdSides = [{ id: side_id }];

      this.inputSides.val(JSON.stringify(this.selectedIdSides));

      const templateSelected = wp.template("pizza-side-selected");
      const pizzaSelectedData = {
        name: title,
        image: image,
        price: price,
      };
      $(".pizza-fancybox-sides .pizza-sides-selected__item").replaceWith(templateSelected(pizzaSelectedData));
      this.calculate();
    }

    openSides(e) {
      e.preventDefault();

      const currentElement = $(e.currentTarget);
      const product_id = currentElement.data("product-id");
      const startsSwiper = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-swiper-sides-starts", 768, this);

      if (this.productType === "variable" && !this.validated) return;
      Fancybox.show([{ src: `#ev-pizza-bortik-fancybox-${product_id}`, type: "inline" }], {
        touch: false,
        on: {
          done: (instance) => {
            // console.log(instance);
            let sideFancy = $(document.body).find(`#ev-pizza-bortik-fancybox-${product_id}`);
            if (window.matchMedia("(min-width: " + startsSwiper + "px)").matches) {
              if (sideFancy.height() > window.innerHeight - 100) {
                sideFancy.css("border-width", "0");
                $(".pizza-layers-block", sideFancy).slimScroll({
                  height: window.innerHeight - 100,
                  railVisible: true,
                  alwaysVisible: true,
                  size: "6px",
                  color: "#FF0329",
                  railColor: "#EAEAEA",
                  railOpacity: 1,
                  wheelStep: 5,
                });
              }
            } else {
              const sidesSwiper = $(".pizza-layers-block", sideFancy);
              sidesSwiper.addClass("swiper");
              sidesSwiper.find(".pizza-layer-item").addClass("swiper-slide").wrapAll('<div class="swiper-wrapper" />');

              const defaultSwiperargs = {
                direction: "horizontal",
                loop: false,
                grabCursor: false,
                cssMode: true,
                //centeredSlides: true,
                breakpoints: {
                  // when window width is >= 320px
                  320: {
                    slidesPerView: 2,
                    spaceBetween: 10,
                  },

                  // when window width is >= 576px
                  576: {
                    slidesPerView: 3,
                    initialSlide: 0,
                    spaceBetween: 10,
                    centeredSlides: false,
                  },

                  [startsSwiper]: {
                    slidesPerView: 4,
                    initialSlide: 0,
                    spaceBetween: 10,
                  },
                },
                pagination: {
                  el: ".swiper-pagination",
                  type: "bullets",
                  clickable: true,
                },
              };
              const swiperArgs = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-sides-swiper-args", defaultSwiperargs, this);

              if (typeof defaultSwiperargs.pagination !== "undefined") {
                sidesSwiper.append($('<div class="swiper-pagination" />'));
              }
              const swiperLayer = new Swiper(sidesSwiper.get(0), swiperArgs);
            }
            $(document.body).trigger("ev-pizza-sides-show", instance);
          },
        },
      });

      this.templateEvSides();
    }

    removeSide(e) {
      e.preventDefault();

      const currentItem = $(e.currentTarget);

      const templateDefault = wp.template("pizza-side-default");
      const pizzaDefaultData = {
        name: EV_PIZZA_FRONT.side_default_text,
        image: EV_PIZZA_FRONT.side_default_image,
        product_id: "",
      };

      currentItem.closest(".pizza-layers-selected__item").replaceWith(templateDefault(pizzaDefaultData));

      this.selectedIdSides = [];
      this.inputSides.val(JSON.stringify(this.selectedIdSides));
      this.calculate();
    }

    runRules(componentObject, args, type = "extra") {
      const qty = args.qty ? args.qty : 0;
      const price = componentObject.price;
      const totalComponentPrice = evParseNumber(price) * qty; //float
      let returnPrice = totalComponentPrice;
      const pizzaCustomRules = evPizzaHooks.pizzaHooks.applyFilters("pizza-rules-custom", [], this);

      pizzaCustomRules.forEach((hook) => {
        if (hook.id === componentObject.id && typeof hook.callback === "function") {
          hook.callback(componentObject, args);
        }
      });
      let pizzaWrapper;
      if (type === "extra") {
        pizzaWrapper = this.pizzaComponentsWrapper;
      } else if (type === "tabs") {
        pizzaWrapper = this.pizzaComponentTabsWrapper;
      } else if (type === "pizza") {
        pizzaWrapper = $("[data-id=add-component]", this.pizzaContainer);
      }

      this.dataComponents.rules.forEach((rule) => {
        if (rule.name === componentObject.id) {
          switch (rule.name_action) {
            case "quantity":
              returnPrice = this.runNumericRule(componentObject, rule, qty, "qty");
              break;
            case "total_price":
              returnPrice = this.runNumericRule(componentObject, rule, qty, "total");
              break;
            case "weight":
              returnPrice = this.runNumericRule(componentObject, rule, qty, "weight");
              break;
          }
        } else if (rule.name === "selected_extra") {
          let componentItem = pizzaWrapper.find(".component-item");
        }
      });
      return returnPrice;
    }

    runNumericRule(componentObject, rule, qty, actionValue) {
      const price = componentObject.price;
      let totalComponentPrice = evParseNumber(price) * qty;
      const pizzaCustomRules = evPizzaHooks.pizzaHooks.applyFilters("pizza-rules-custom", [], this);
      // console.log("meet2", rule, qty, componentObject);
      let valueToMatch;
      if (actionValue === "qty") {
        valueToMatch = qty;
      } else if (actionValue === "total") {
        valueToMatch = totalComponentPrice;
      } else if (actionValue === "weight") {
        valueToMatch = qty * evParseNumber(componentObject.weight);
      }
      if (this.runMeetComparator(rule, valueToMatch)) {
        //   console.log("meet", rule, valueToMatch);
        switch (rule.action) {
          case "custom":
            const foundCallback = pizzaCustomRules.find((hook) => hook.id === rule.value);
            if (foundCallback) {
              return foundCallback.callback(componentObject, rule, valueToMatch);
            }
            return totalComponentPrice;
          case "discount":
            if (rule.value.toString().includes("%")) {
              totalComponentPrice -= (totalComponentPrice / 100) * evParseNumber(rule.value);
            } else {
              totalComponentPrice -= evParseNumber(rule.value);
            }
            return totalComponentPrice;

          case "fee":
            if (rule.value.toString().includes("%")) {
              totalComponentPrice += (totalComponentPrice / 100) * evParseNumber(rule.value);
            } else {
              totalComponentPrice += evParseNumber(rule.value);
            }
            return totalComponentPrice;
          case "hide":
            this.runHideAction(componentObject, rule.value, true);
            return totalComponentPrice;
        }
      } else {
        //revert hide if needed
        this.runHideAction(componentObject, rule.value, false);
        $(".pizza_components_wrapper").trigger("pizza-rules-not-matched", [componentObject, rule, qty, valueToMatch]);
      }
      return totalComponentPrice;
    }
    runMeetComparator(rule, value) {
      switch (rule.comparator) {
        case ">":
          return evParseNumber(value) > evParseNumber(rule.name_value);
        case "<":
          return evParseNumber(value) < evParseNumber(rule.name_value);
        case "=":
          return evParseNumber(value) === evParseNumber(rule.name_value);
        case "!=":
          return evParseNumber(value) !== evParseNumber(rule.name_value);
        case ">=":
          return evParseNumber(value) >= evParseNumber(rule.name_value);
        case "<=":
          return evParseNumber(value) <= evParseNumber(rule.name_value);
        default:
          return false;
      }
    }

    runHideAction(componentObject, value, matched) {
      if (Array.isArray(value)) {
        value.forEach((element) => {
          const qtyItem = $(".pizza_components_wrapper").find(`[data-food-item="${element.id}"]`);
          if (qtyItem.length) {
            const componentToHide = qtyItem.closest(".component-item");
            if (matched) {
              componentToHide.hide();
            } else {
              componentToHide.show();
            }
          }
        });
      }
    }

    refreshClasses(data) {
      if (this.dodoEnabled) {
        const templateBackIcon = wp.template("pizza-back-icon");
        const templateCloseIcon = wp.template("pizza-remove-icon");
        //console.log(data);
        data.forEach((c) => {
          let key = Object.keys(c)[0];
          const componentItem = $(`.pizza-base-item[data-component-id=${key}]`);
          if (!c[key]) {
            componentItem.find(".pizza-component-icon").html(templateBackIcon({}));
            componentItem.addClass("removed");
          } else {
            componentItem.find(".pizza-component-icon").html(templateCloseIcon({}));
            componentItem.removeClass("removed");
          }
        });
      } else {
        $("[data-id=remove-component] .pizza-components-item", this.pizzaContainer).each(function () {
          $(this).removeClass("active");
        });

        data.forEach((c) => {
          let key = Object.keys(c)[0];
          !c[key] && $(`[data-component-id=${key}]`).closest(".pizza-components-item").addClass("active");
        });
      }
    }

    /**
     *
     * @param {MouseEvent} e
     * @returns {void}
     */
    handleSmoothSwatchClick(e) {
      const _thisELement = $(e.currentTarget);
      const selectValue = _thisELement.attr("data-value");
      if (_thisELement.hasClass("disabled")) {
        return;
      }
      const smoothGroup = _thisELement.closest(".pizza-smooth-radio");

      const attributeName = smoothGroup.data("attribute_name");
      const selectEl = this.form.find(`select[data-attribute_name="${attributeName}"]`);
      if (selectEl.length) {
        smoothGroup.addClass("smooth-selected");

        smoothGroup.find(".ev-smooth-item").attr("aria-checked", "false");

        _thisELement.attr("aria-checked", "true");

        selectEl.val(selectValue);

        selectEl.trigger("change");

        this.onVariationChange(this.form);
        this.transformSmooth(smoothGroup, _thisELement);
      }
    }

    /**
     *
     * @param {jQuery.Event} e
     * @returns {void}
     */
    foundVariation(e, variation) {
		
	}

    /**
     * @param {jQuery.Event} e
     * @returns {void}
     */
    showVariation(e, variation) {
      //console.log(variation, this.form);
      this.onVariationChange(this.form);
    }

	/**
	 * @param {jQuery.Event} e
	 */
	onChangeVariation(e) {
		this.onVariationChange(this.form);
	}

    /**
     *
     * @param {MouseEvent} e
     * @returns {void}
     */
    resetVariation(e) {
      //console.log(e);
      if (this.wcVariationFormInstance !== null) {
        this.onVariationChange(this.form);

        const smoothRadios = this.form.find(".pizza-smooth-radio");
        smoothRadios.removeClass("smooth-selected");
		$('.ev-smooth-item', smoothRadios).removeClass('disabled selected');
      }
    }

    /**
     *
     * @param {jQuery.Event} e
     * @param {WooCommerce.VariationForm} wcForm
     * @returns {void}
     */
    initSwatches(e, wcForm) {
      //console.log(e, wcForm);
      const attributes = wcForm.getChosenAttributes();
      const currentAttributes = attributes.data;
      this.wcVariationFormInstance = wcForm;
      //currentAttributes[ currentAttributeName ] = '';

      const matchingVariations = wcForm.findMatchingVariations(wcForm.variationData, currentAttributes);
      //const variation = matchingVariations.shift();
      //console.log(matchingVariations);
      this.onVariationChange($(e.currentTarget));
    }

    findVariationValue(variations, currentAttributeName, liValue) {
      const variationValues = [];
      // Loop through variations.
      for (const varIndex in variations) {
        if (typeof variations[varIndex] !== "undefined") {
          const variationAttributes = variations[varIndex].attributes;
          // console.log(variationAttributes);
          for (const attrName in variationAttributes) {
            if (variationAttributes.hasOwnProperty(attrName)) {
              const attrVal = variationAttributes[attrName];

              if (attrName === currentAttributeName) {
                if (attrVal) {
                }
                variationValues.push(attrVal);
              }
            }
          }
        }
      }
      return variationValues;
    }

    onVariationChange(form) {
      if (!this.wcVariationFormInstance) {
        return;
      }
      const attributes = this.wcVariationFormInstance.getChosenAttributes();
      const currentAttributes = attributes.data;
      const smoothGroups = $(form).find(".pizza-smooth-radio");

      if (smoothGroups.length) {
        smoothGroups.each((index, group) => {
          //default choosen.
          const selectedItem = $(group).find(".ev-smooth-item.selected");
          const smoothItems = $(group).find(".ev-smooth-item");

          if (selectedItem.length) {
            this.transformSmooth($(group), selectedItem);
          }

          const currentAttributeName = $(group).data("attribute_name");

          // The attribute of this select field should not be taken into account when calculating its matching variations:
          // The constraints of this attribute are shaped by the values of the other attributes.
          const checkAttributes = $.extend(true, {}, currentAttributes);

          checkAttributes[currentAttributeName] = "";

          const variations = this.wcVariationFormInstance.findMatchingVariations(
            this.wcVariationFormInstance.variationData,
            checkAttributes,
          );

          const availableValues = this.findVariationValue(variations, currentAttributeName);
          //console.log(availableValues);
          const selectEl = this.form.find(`select[data-attribute_name="${currentAttributeName}"]`);
          const newSelecButtontOptions = selectEl.find("select option");
          const newSelectButtonVal = selectEl.val();

          $(group).removeClass("smooth-disabled");

          smoothItems.each((i, item) => {
            const liValue = $(item).attr("data-value");
            const ariaSelected = newSelectButtonVal === liValue ? "true" : "false";
            $(item).removeClass("disabled");
            $(item).removeClass("selected");

            if (liValue !== "" && availableValues.indexOf(liValue) === -1) {
              $(item).addClass("disabled");
            }

            if (newSelectButtonVal === liValue) {
              $(item).addClass("selected");
            }

            $(item).attr("aria-checked", ariaSelected);
          });
        });
      }
    }

    transformSmooth(smoothGroup, item) {
      const smoothItems = smoothGroup.find(".ev-smooth-item");
      const smoothOverlay = smoothGroup.find(".ev-smooth-overlay");
      const itemIndex = item.parent().children(".ev-smooth-item").index(item);
      //console.log(itemIndex);
      let translatePercent = itemIndex === 0 ? "0%" : itemIndex + "00%";
      smoothOverlay.css("transform", "translateX(" + translatePercent + ")");
    }

    rippleButtons(e) {
      var $self = $(this);
      if ($self.is(".btn-disabled")) {
        return;
      }

      if ($self.closest(".qty_button_plus, .qty_button_minus")) {
        e.stopPropagation();
      }
      var initPos = $self.css("position"),
        offs = $self.offset(),
        x = e.pageX - offs.left,
        y = e.pageY - offs.top,
        dia = Math.min(this.offsetHeight, this.offsetWidth, 100),
        $ripple = $("<div/>", {
          class: "ripple",
          appendTo: $self,
        });
      if (!initPos || initPos === "static") {
        $self.css({ position: "relative" });
      }
      $("<div/>", {
        class: "rippleWave",
        css: {
          background: $self.data("ripple"),
          width: dia,
          height: dia,
          left: x - dia / 2,
          top: y - dia / 2,
        },
        appendTo: $ripple,
        one: {
          animationend: function () {
            $ripple.remove();
          },
        },
      });
    }

    pluginTabs(resize = false) {
      const startsSwiper = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-swiper-media-starts", 990, this);

      $(".pizza-components-tab", this.pizzaContainer).each((i, item) => {
        if (window.matchMedia("(min-width: " + startsSwiper + "px)").matches) {
          if (resize === "resize" && this.swiperTab instanceof Swiper) {
            this.swiperTab.destroy();
          }

          if ($(item).height() > 450) {
            $(item).css("padding-right", "25px");
            $(item).slimScroll({
              height: 450,
              railVisible: true,
              alwaysVisible: true,
              size: "6px",
              color: "#FF0329",
              railColor: "#EAEAEA",
              railOpacity: 1,
              wheelStep: 5,
            });
          }
        } else {
          if (resize === "resize") {
            $(item).slimScroll({ destroy: true });
          }

          $(item).addClass("swiper");
          $(item).find(".pizza-components-item").addClass("swiper-slide").wrapAll('<div class="swiper-wrapper" />');

          const defaultSwiperargs = {
            direction: "horizontal",
            loop: false,
            grabCursor: false,
            cssMode: true,
            //centeredSlides: true,
            slidesPerView: 1,
            breakpoints: {
              // when window width is >= 320px
              320: {
                slidesPerView: 2.4,
                spaceBetween: 10,
              },

              // when window width is >= 576px
              576: {
                slidesPerView: 3.2,
                initialSlide: 0,
                spaceBetween: 10,
                centeredSlides: false,
              },

              [startsSwiper]: {
                slidesPerView: 3.2,
                initialSlide: 0,
                spaceBetween: 10,
              },
            },
            pagination: {
              el: ".swiper-pagination",
              type: "bullets",
              clickable: true,
            },
          };
          const swiperArgs = evPizzaHooks.pizzaHooks.applyFilters("ev-pizza-components-swiper-args", defaultSwiperargs, this);

          if (typeof defaultSwiperargs.pagination !== "undefined") {
            $(item).append($('<div class="swiper-pagination" />'));
          }
          this.swiperTab = new Swiper($(item).get(0), swiperArgs);
        }
      });
    }

    destroy() {
      //navigation
      $(document.body).off("click", ".pizza-components-nav a", this.boundhandleNavTabs);
      //sides
      $(document.body).off("click", "#pizza-sides-button", this.boundopenSides);
      $(document.body).off("click", ".pizza-fancybox-sides .pizza-layer-item", this.boundhandleSides);
      $(document.body).off("click", ".ev-remove-side", this.boundremoveSide);
      //layers
      $(document.body).off("click", ".ev-remove-layer", this.boundremoveLayer);
      $(document.body).off("click", ".pizza-fancybox-layers .pizza-layer-item", this.boundhandleLayers);
      $(document.body).off("click", "#pizza-layer-button", this.boundOpenLayers);
      $(document.body).off("click", ".choose-layer-button, .choose-side-button", this.boundCloseFancy);
    }

    closeFancy(e) {
      e.preventDefault();
      Fancybox.getInstance().close();
    }
  }

  if ($("form.variations_form").length > 0) {
    new EvPizzaFront("form.variations_form");
  } else if ($("form.variations_form").length === 0 && $("form.cart").length > 0) {
    new EvPizzaFront("form.cart");
  }

  window.EvPizzaFront = EvPizzaFront;
})(jQuery);
