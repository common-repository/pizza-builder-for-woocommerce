=== Pizza builder for WooCommerce ===
 
Contributors: wsjrcatarri
Tags: pizza, restaurant, food, pizza woocommerce, woocommerce components, pizza components, dodo pizza, product builder, composite product
Requires at least: 5.5
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
  
A WordPress plugin for creating restaurant/pizza components. 
  
== Description ==
  
Pizza builder for [WooCommerce](https://wordpress.org/plugins/woocommerce/) - creative plugin for building not only restaurant/pizza store but any store with custom components . It allows create components like ingredients, which normally not required to be a woocommerce product. You have the flexibility to include component costs in the product price or individually add them to customize the final product price based on selected components.

[Plugin home page](https://pizza.evelynwaugh.com.ua/) | [Docs](https://pizza.evelynwaugh.com.ua/first-steps/) | [Demo #1](https://pizza.evelynwaugh.com.ua/product/burger/) | [Demo #2](https://pizza.evelynwaugh.com.ua/product/pepperoni-pizza/) | [Demo #3](https://pizza.evelynwaugh.com.ua/product/dodo-style-pizza/)

https://www.youtube.com/watch?v=2vdk1YnXEt0

Pizza builder for WooCommerce is user-friendly and developer-friendly. Also, there is a page for developers where not-so-complicated hooks are displayed, primarily for text display.

= What it can =
* **Create components for WooCommerce product** - supports only simple and variable products. Commonly created for pizza, but could be any food/meal product, or any other product.
* **Has 3 different views** - depends on what you choose to display. There are 2 views for additional components(extra components, for example, we have product "Fried moose with marjoram" and we want add to this meal ginger, roasted almonds, 
chopped garlic, seasoned with fresh ham and cabrales with blue mold) with tabs or without, and one view for Consist of block(which is by default for pizza)
* **Build Custom Product** - with Shortcode Builder you can create steps form
* **Nice looking admin** - thanks to [Material Ui](https://mui.com/)
== Installation ==

**Installing via WordPress**

Login to the WordPress Administrator Panel.
Go to Plugins > Add New > Upload.
Click Choose file button and select the zip folder of Pizza builder for WooCommerce plugin and press Install now button.
Click Activate button.

**Installing via FTP**

Login to your hosting space via an FTP software, e.g. FileZilla.
Unzip the downloaded Pizza builder for WooCommerce plugin folder without making any changes to the folder.
Upload the plugin into the following location wp-content>wp-plugins.
Login to the WordPress Administrator Panel.
Activate Pizza builder for WooCommerce by going to Plugins and pressing Activate button.

**Creating a Component**
Go to WooCommerce->Settings, Select Pizza tab
On Pizza Tab select Components tab
Create Group and components for it
Then you will be able to choose components on WooCommerce product page

Go to Products->Add New
Select Pizza checkbox, click on Pizza data tab
Choose Style.
Publish product
== Frequently Asked Questions ==
  
= How do I use this plugin? =
  
Follow Installation guide or watch video tutorial
  
= How to uninstall the plugin? =
  
Simply deactivate and delete the plugin. 
 
== Screenshots ==

1.  Pizza builder for WooCommerce - Create group & components
2.  Pizza builder for WooCommerce - Choose created components
3.  Pizza builder for WooCommerce - Components view #1 on front
4.  Pizza builder for WooCommerce - Fancybox #1
5.  Pizza builder for WooCommerce - Fancybox #2
6.  Pizza builder for WooCommerce - Components view #2 on front
7.  Pizza builder for WooCommerce - Shortcode Builder Admin
8.  Pizza builder for WooCommerce - Product Builder on Front

== Changelog ==
= 1.0 =
* Plugin released. 
= 1.1 =
* Fixed indexes in Layers component.
* Fixed removing components from WooCommerce Pizza Tab.
* Added Shortcode Builder.
= 1.1.1 =
* Fixed calculation on Cart page.
* Added responsive to Builder Product
= 1.1.3 =
* Display order meta in email
* Added enable/disable cart/order meta popup
= 1.1.4 =
* Remove error with flat rate
* Fixed empty price returns NaN
= 1.1.4 =
* Add rules
* Origin components preserves their data
= 2.0 =
* Encrease Performance
* Quick view
= 2.5 =
* Move to Swiper.js
* New Style (Dodo pizza)
* Smooth Attribute Swatches
* Compatible with qty buttons