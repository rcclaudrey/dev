jQuery(document).ready(function () {
    jQuery('#minicart .cart-table').click(function (eventObject) {
        jQuery('#topCartContent').css({visibility: 'visible'});
    });

    popupcart = {
        qty: '.cart-item .cart-count',
        header: '#minicart',
        addToCart: function (ths, source) {
            var url = '';
            var data = '';
            if (source == 'product') {
                url = ths;
                data = jQuery("#product_addtocart_form").serialize();
            } else {
                url = jQuery(ths).attr('href');
            }

            try {
                jQuery.ajax({
                    url: url,
                    dataType: 'json',
                    type: 'post',
                    data: data,
                    beforeSend: function () {
                        popupcart.show();
                    },
                    success: function (resp) {
                        if (resp.error > 0) {
                            jQuery("#cart-overlay-content").html("<div class=\"erro-message-mainwrap\">"
								+	"<div class=\"close-floating-cart-wrap\"><a href=\"#\" onclick=\"popupcart.hide(event);\" "
								+	"class=\"close-floating-cart\">x</a></div><div class=\"erro-message-wrap\">"
								+ resp.error_message + "</div></div>");
                        } else {
                            jQuery("#cart-overlay-content").html(resp.content);
                            jQuery(popupcart.qty).html(resp.qty);
                            jQuery(popupcart.header).html(resp.header);

                            jQuery('#minicart .cart-table').click(function (event) {
                                jQuery('#topCartContent').css({visibility: 'visible'});
                            });

                            jQuery('#topCartContent').mouseleave(function (event) {
                                jQuery('#topCartContent').css({visibility: 'hidden'});
                            });
                        }
						jQuery(document).trigger('cartChanged', ['top_cart']);
                    }
                });
            } catch (e) {}
        },
        show: function () {
            if (jQuery("#cart-overlay").length < 1) {
                jQuery("body").append("<div id=\"cart-overlay\" />")
                jQuery("body").append("<div id=\"cart-overlay-content\" />")
            }

            jQuery("#cart-overlay").css({
                height: jQuery(window).height() + 'px'
            });

            jQuery("#cart-overlay-content").css({
                right: ((jQuery(window).width() - jQuery("#cart-overlay-content").width()) / 2) + 'px'
            });

            jQuery("#cart-overlay-content").animate({
                top: '+=' + ((jQuery(window).height() - jQuery("#cart-overlay-content").height()) * 0.3)
            }, 200, function () {

            });

            jQuery("#cart-overlay").click(function (event) {
                popupcart.hide(event);
            });
        },
        hide: function (event) {
            event.preventDefault();
            jQuery("#cart-overlay-content").fadeOut('fast', function () {
                jQuery(this).remove();
            });
            jQuery("#cart-overlay").fadeOut('fast', function () {
                jQuery(this).remove();
            });
        },
        resize: function () {
            jQuery("#cart-overlay").css({
                height: jQuery(window).height() + 'px'
            });

            jQuery("#cart-overlay-content").css({
                right: ((jQuery(window).width() - jQuery("#cart-overlay-content").width()) / 2) + 'px',
                top: '0'
            });

            jQuery("#cart-overlay-content").animate({
                top: '+=' + ((jQuery(window).height() - jQuery("#cart-overlay-content").height()) * 0.3)
            }, 200, function () {

            });
        },
		refreshCartItems: function(event, sender) {
			if (sender == 'top_cart') return;
			jQuery.ajax({
					url: location.protocol + '//' + location.host + '/ajaxcheckout/cart/topCart',
					dataType: 'html',
					async: true,
					cache: false,
					success: function (resp) {
						jQuery("#minicart").html(resp);
						jQuery('#minicart .btn-remove').click(cartheaderItems.removeItem.bind(cartheaderItems));
					}
				});
		}
    };

	jQuery('#minicart .btn-remove').click(cartheaderItems.removeItem.bind(cartheaderItems));

    jQuery(window).resize(function () {
        popupcart.resize();
    });

	jQuery(document).on('cartChanged', popupcart.refreshCartItems);

});