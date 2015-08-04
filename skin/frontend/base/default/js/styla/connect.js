$j('document').on('styla_cart_add_after', function(eventData) {
    /**
     * This script is waiting for an event "styla_cart_add_after" to fire.
     * The event is fired after a user successfully adds a new product to his cart, using the styla product story page.
     *
     * It's used to load updated minicart content and (possibly) open the minicart window, if possible.
     * You can rewrite this to suit your needs better.
     */

    /**
     * Look for the default minicart block, and if it's found - update it's content with the data we got from this event
     */
    var $minicartContentElement = $j("#header-cart");
    if ($minicartContentElement && eventData.hasOwnProperty('html')) {
        $minicartContentElement.html(eventData.html);
    }

    /**
     * The default theme (rwd/default) also has a numeric label next to the minicart - we can update it using the raw
     * cart "metadata".
     */
    var $cartCountElement = $j(".skip-cart .count");
    if ($cartCountElement && eventData.hasOwnProperty('meta')) {
        $cartCountElement.html(eventData.meta.num_items);
    }
});