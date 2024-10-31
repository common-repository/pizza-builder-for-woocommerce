
const evPizzaHooks = window.evPizzaHooks || {};
evPizzaHooks.pizzaHooks = wp.hooks.createHooks();
evPizzaHooks.builderHooks = wp.hooks.createHooks();
evPizzaHooks.mainHooks = wp.hooks.createHooks();

window.evPizzaHooks = evPizzaHooks;