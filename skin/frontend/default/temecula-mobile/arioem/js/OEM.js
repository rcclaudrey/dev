var OEMCartManager = {
	url: '',
//	partsAdded: [],

	_parseURI: function(paramString) {
		var urlParts = paramString.split('&');
		var result = {};
		var currentPartIndex = 0;
		result[currentPartIndex] = {};

		for(var i=0; i < urlParts.length; i++) {
			var nameValuePair = urlParts[i].split('=', 2);

			switch (nameValuePair[0]) {
				case 'brand':
				case 'ariprice':
				case 'qty':
				case 'sku':
					if('undefined' !== typeof(result[currentPartIndex][nameValuePair[0]])) {
						currentPartIndex++;
						result[currentPartIndex] = {};
					}
					result[currentPartIndex][nameValuePair[0]] = nameValuePair[1];
					if('sku' === nameValuePair[0]) {
						result[currentPartIndex].description = jQuery(jQuery('input.ariMultiCartCheckbox[value="' + nameValuePair[1] + '"]')[0].up('tr')).children('.ariPLDesc')[0].innerHTML.trim();
					}
					break;
				default:
					// do nothing
			}
		}
		return result;
	},

	addToCartSingle: function() {
		var uriParts = this._parseURI(document.getElementById(arguments[0]).name);
		var result = [{
			brand: uriParts[0].brand,
			name: jQuery('#ariHotSpotPartDesc').text().trim(),
			qty: jQuery('input.ariHotSpotToolTipQty')[0].value,
			sku: jQuery('#ariHotSpotToolTipPartNumber span').text().trim(),
			price: jQuery('#ariHotSpotToolTipPrice').text().trim()
		}];
		document.getElementById(arguments[0]).onclick = '';
		this._addToCart(result);
	},

	addToCartMulti: function() {
		var result = [];
		var checkboxes = jQuery('.ariMultiCartCheckbox:checked');

		for(var i=0; i<checkboxes.length; i++) {
			var cbParent = checkboxes[i].up('tr');
			result.push({
				brand: cbParent.down('.ariPLSku').attributes['rel'].value,
				name: cbParent.down('.ariPLDesc').textContent.trim(),
				qty: cbParent.down('.ariPLQty input').value,
				sku: cbParent.down('.ariPLSku').textContent.trim(),
				price: cbParent.down('.ariPLPrice').textContent.trim()
			});
		}
		this._addToCart(result);
	},

	_addToCart: function(orderedParts) {
		jQuery.ajax({
			url : this.url,
			dataType : 'json',
			type : 'POST',
			data : {
				pageURL: location.href,
				parts: orderedParts
			},
			beforeSend: function() {
				this.showOverlay(true);
			}.bind(this),
			success : function(response) {
				if(response.error) {
					jQuery("#cart-overlay-content").html("<div class=\"erro-message-wrap\">" + response.message + "</div>");
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
			}.bind(this),
			complete: function() {
				this.showOverlay(false);
			}.bind(this)
		});
	},

	showOverlay: function(desiredState) {
		var overlay = jQuery('.m-overlay');

		if(!arguments.length) {
			desiredState = ! Boolean(overlay.length);
		}
		if(desiredState) {
			if(!overlay.length) {
				jQuery('body').append('<div class="m-overlay" style="left:0px; top: 0px;width: 100%;height:100%; position: fixed; z-index: 22; background:#f2f2f2; opacity:0.7"></div>');
			}

			if(jQuery('#m-wait').length){
				jQuery('#m-wait').css({display: 'block'});
			} else {
				jQuery('body').append('<div id="m-wait"></div>');
			}
		} else {
			if(overlay.length) {
				jQuery('.m-overlay').remove();
			}
			if(jQuery('#m-wait').length){
				jQuery('#m-wait').css({display: 'none'});
			}
		}
		return this;
	}
};
