
cartheaderItems = {
    removeItem: function (event, ths) {
        event.preventDefault();

        var newTotal = 0;
        var newPriceTotal = 0;

        jQuery.ajax({
            url: jQuery(ths).attr("href"),
            dataType: 'html',
            cache: false,
            beforeSend: function () {
                if (!confirm('Are you sure you would like to remove this item from the shopping cart?')) {
                    return false;
                }

                jQuery(ths).parent().parent().remove();

                jQuery(".cart-item-qty").each(function (index) {
                    newTotal += parseInt(jQuery(this).html());
                });

                jQuery(".product-details .price").each(function (index) {
                    newPriceTotal += parseFloat((jQuery(this).html()).match(/[\d\.\d]+/i));
                });

                if (newPriceTotal == 0) {
                    jQuery("#topCartContent .inner-wrapper").html('<p class="cart-empty">You have no items in your shopping cart yet.</p>');
                }
                
                jQuery(".cart-count").html(newTotal);
                jQuery(".subtotal .price").html('$'+newPriceTotal.toFixed(2));

            },
            success: function (response) {
//                jQuery('#minicart').html(response);
            }
        });
    }
};