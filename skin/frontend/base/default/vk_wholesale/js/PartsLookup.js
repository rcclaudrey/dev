var PartsLookup = new Class.create({
	config: {
		addToCartURL: '',
		assemblyPageBaseUrl: '',
		_loaderTargetSelector: null,
		messages: {
			common: 'An error occurred, please contact site administrator'
		}
	},
	init: function(config) {
		jQuery.extend(true, this.config, config);
	},
	showLoader: function(targetSelector, multiMode) {
		multiMode = multiMode || false;
		if(window.hasOwnProperty('loader')) {
			if(!multiMode) {
				this.config._loaderTargetSelector = targetSelector;
			}
			window.loader.show(targetSelector);
		}
	},
	hideLoader: function(selector) {
		if(window.hasOwnProperty('loader')) {
			if(!selector || ('string' != typeof selector)) {
				selector = this.config._loaderTargetSelector;
			}
			window.loader.hide(selector);
		}
	},
	addToCart: function(senderId) { //ARI.PartStream.Cart.PostSkuQty(this.id, 'AJAX', 'PartsSearch', '', '')
		var sender = jQuery('#' + senderId);
		var itemData = {
			brand: sender.attr('brand'),
			sku: sender.closest('tr').find('.ari_searchResults_Column_Content_PartNum').text().trim(),
			price: sender.closest('tr').find('.ari_searchResults_Column_Content_Price')[0].innerHTML,
			name: sender.closest('tr').find('.ari_searchResults_Column_Content_Assembly').text().trim(),
			qty: 1
		};
		this.showLoader('#ariPartStream');
		jQuery.ajax({
			url: this.config.addToCartURL,
			data: {
				pageURL: location.href,
				parts: [itemData]
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._addToCart.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_addToCart: function(response) {
		if(response.error) {
			jQuery("#cart-overlay-content").html("<div class=\"erro-message-wrap\">" + response.message + "</div>");
			alert(response.message);
		} else {
			jQuery(window.popupcart.header).html(response.cart_top);
			jQuery(window.popupcart.qty).html(response.qty);
			jQuery('#minicart .cart-table').click(function() {
				jQuery('#topCartContent').css({visibility: 'visible'});
			});
			jQuery('#topCartContent').mouseleave(function() {
				jQuery('#topCartContent').css({visibility: 'hidden'});
			});

			if(response.hasOwnProperty('cart_content')) {
				window.popupcart.show();
				jQuery("#cart-overlay-content").html(response.cart_content);
			} else {	// since we're not displaying the pop-up, it's time to add some animation!
				jQuery('#topCartContent').css({visibility: 'visible'});
				jQuery('html, body').animate({scrollTop: jQuery("#minicart").offset().top}, 1500);
			}

			for(var i=0; i<response.products_added.length; i++) {
				var partNumberSpans = jQuery('#ariPartList span.ariPLSku[name="' + response.products_added[i] + '"]');
				for(var j=0; j<partNumberSpans.length; j++) {
					var tr = partNumberSpans[j].up('tr');
					tr.addClassName('added');
				}
			}
		}
	},
	goAssemblyPage: function(urlInfo) {
		location.href = this.config.assemblyPageBaseUrl + '?' + urlInfo.modeluTag.substr(1) + '#' + urlInfo.slug + urlInfo.sluga + urlInfo.modeluTag + urlInfo.assyutag;
	}
});
