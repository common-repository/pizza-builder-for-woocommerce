<script type="text/html" id="tmpl-pizza-layer-default">
	<div class="pizza-layers-selected__item" data-product-id="">
		<a href="#" class="ev-remove-layer">
			<svg width="10" height="9" viewBox="0 0 10 9" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.00426 3.44918L7.97954 0H9.90622L5.98465 4.46291L10 9H8.05627L5.00426 5.48901L1.93521 9H0L4.02387 4.46291L0.0937766 0H2.01194L5.00426 3.44918Z" fill="#C3C3C3" />
			</svg>
		</a>
		<div class="pizza-layers-left">
			<img src="{{{data.image}}}" alt="">
		</div>
		<div class="pizza-layers-right">
			<span class="pizza-text-placeholder">{{{data.name}}}</span>

		</div>
	</div>
</script>
<script type="text/html" id="tmpl-pizza-layer-selected">
	<div class="pizza-layers-selected__item" data-product-id="{{{data.product_id}}}">
		<a href="#" class="ev-remove-layer">
			<svg width="10" height="9" viewBox="0 0 10 9" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.00426 3.44918L7.97954 0H9.90622L5.98465 4.46291L10 9H8.05627L5.00426 5.48901L1.93521 9H0L4.02387 4.46291L0.0937766 0H2.01194L5.00426 3.44918Z" fill="#C3C3C3" />
			</svg>
		</a>
		<div class="pizza-layers-left">
			<img src="{{{data.image}}}" alt="">
		</div>
		<div class="pizza-layers-right">
			<span>{{{data.name}}}</span>
			<span>{{{data.price}}} </span>
		</div>
	</div>
</script>
<script type="text/html" id="tmpl-pizza-side-default">
	<div class="pizza-layers-selected__item pizza-sides-selected__item">

		<div class="pizza-layers-left">
			<img src="{{{data.image}}}" alt="">
		</div>
		<div class="pizza-layers-right">
			<span class="pizza-text-placeholder">{{{data.name}}}</span>

		</div>
	</div>
</script>
<script type="text/html" id="tmpl-pizza-side-selected">
	<div class="pizza-layers-selected__item pizza-sides-selected__item">
		<a href="#" class="ev-remove-side">
			<svg width="10" height="9" viewBox="0 0 10 9" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M5.00426 3.44918L7.97954 0H9.90622L5.98465 4.46291L10 9H8.05627L5.00426 5.48901L1.93521 9H0L4.02387 4.46291L0.0937766 0H2.01194L5.00426 3.44918Z" fill="#C3C3C3" />
			</svg>
		</a>
		<div class="pizza-layers-left">
			<img src="{{{data.image}}}" alt="">
		</div>
		<div class="pizza-layers-right">
			<span>{{{data.name}}}</span>
			<span>{{{data.price}}}</span>
		</div>

	</div>
</script>

<script type="text/html" id="tmpl-pizza-back-icon">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15.207 10.854"><path d="M11.707 0h-4v1h4c1.378 0 2.5 1.122 2.5 2.5S13.085 6 11.707 6H1.914L5.06 2.854l-.706-.708L0 6.5l4.354 4.354.707-.707L1.914 7h9.793c1.93 0 3.5-1.57 3.5-3.5s-1.57-3.5-3.5-3.5z"></path></svg>
</script>


<script type="text/html" id="tmpl-pizza-remove-icon">
	<svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 24 24" ><path d="M13.4,12l6.3-6.3c0.4-0.4,0.4-1,0-1.4c-0.4-0.4-1-0.4-1.4,0L12,10.6L5.7,4.3c-0.4-0.4-1-0.4-1.4,0c-0.4,0.4-0.4,1,0,1.4
	l6.3,6.3l-6.3,6.3C4.1,18.5,4,18.7,4,19c0,0.6,0.4,1,1,1c0.3,0,0.5-0.1,0.7-0.3l6.3-6.3l6.3,6.3c0.2,0.2,0.4,0.3,0.7,0.3
	s0.5-0.1,0.7-0.3c0.4-0.4,0.4-1,0-1.4L13.4,12z"></path></svg>
</script>
