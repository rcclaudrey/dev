cartheaderItems = {
	removeItem: function (event, ths) {
		event.preventDefault();

		if (!ths) {
			ths = event.target;
		}

		if (confirm('Are you sure you would like to remove this item from the shopping cart?')) {
			jQuery.ajax({
				url: jQuery(ths).attr("href"),
				dataType: 'html',
				async: true,
				cache: false,
				beforeSend: function () {
					jQuery(ths).closest('li.item').remove();

					var newTotal = 0;
					var newPriceTotal = 0;

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
					jQuery(".subtotal .price").html('$' + newPriceTotal.toFixed(2));
				},
				success: function (response) {
					jQuery('#minicart').html(response);
					jQuery('#minicart .btn-remove').click(cartheaderItems.removeItem.bind(cartheaderItems));

					jQuery(document).trigger('cartChanged', ['top_cart']);
				}
			});
		}

		return false;
	}
};