var ProductViewPopup = new Class.create({
	config: {
		requestURL: '',
//		requestRatingURL: '',
		overlaySelector: '.fitment-popup-overlay',
		productContainerSelector: '.fitment-popup-container',
		productWrapper: '.fitment-popup-wrapper',
		ratingSelector: '.fitment-popup-rating-container',
		fitmentSelector: '.fitment-popup-fitment-container',
		emptyText: {
			noResultMessage: 'No tire options available for this configuration'
		},
		errorMessage: 'Some error occurred, please contact site admin',
		cachedProducts: {},
		lastRequestTimeMark: 0,
		showRating: false // we don't need product rating to be displayed currently
	},
	_overlay: null,
	_productContainer: null,
	_productWrapper: null,
	init: function(configValues) {
		jQuery.extend(true, this.config, configValues);
		this.config.requestURL = this.config.requestURL.replace('http:', location.protocol);

		this._overlay = jQuery(this.config.overlaySelector);
		this._productContainer = jQuery(this.config.productContainerSelector);
		this._productWrapper = jQuery(this.config.productWrapper);
		jQuery('.fitment-popup-close').click(this.hide.bind(this));
		this._overlay.click(this.hide.bind(this));
		jQuery(window).resize(this.reposition.bind(this));

		this.hide();
	},
	show: function(ariProductId, options) {
		this._overlay.show();
		if(this.config.cachedProducts.hasOwnProperty(options.cacheIndex)) {
			this._productContainer.show();
			this._productWrapper.html(this.config.cachedProducts[options.cacheIndex]);
			this.reposition();
			return;
		}
		this._productWrapper.html('&nbsp;');
		this._productContainer.show();
		this.reposition();
		window.loader.show(this.config.productWrapper);
		this._request(ariProductId, options);
	},
	hide: function() {
		this._overlay.hide();
		this._productContainer.hide();
		this._hideLoader();
	},
	_hideLoader: function() {
		window.loader.hide(this.config.productWrapper);
	},
	_getRequestTimeMark: function() {
		var d = new Date();
		return d.getTime();
	},
	_request: function(ariProductId, options) {
		jQuery.ajax({
			url: this.config.requestURL,
			data: {
				product: ariProductId,
				activity: fitment.rideSelector.config.activity,
				fitment: fitment.rideSelector.config.fitment.id,
				vehicle: fitment.rideSelector.config.fitment.name,
				options: options,
				timeMark: this._getRequestTimeMark()
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._onRequestSuccess.bind(this),
			error: this._onRequestError.bind(this),
			complete: this._hideLoader.bind(this)
		});
	},
	_onRequestSuccess: function(response) {
		if(response.params.timeMark < this.config.lastRequestTimeMark) return;
		this.config.lastRequestTimeMark = response.params.timeMark;

		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		this.config.cachedProducts[response.params.options.cacheIndex] = response.html;

		this._hideLoader();
		this._productWrapper.html(response.html);
		this.reposition();
		this._requestRating(response.params);
	},
	_onRequestError: function(jqXHR, textStatus, errorThrown) {
		jQuery(this.config.productContainerSelector).html('An error occurred: ' + textStatus + ' ' + errorThrown);
	},
	_requestRating: function(params) {
		if(!this.config.showRating) return;
		jQuery.ajax({
			url: this.config.requestRatingURL,
			data: {
				product: params.product,
				cacheIndex: params.options.cacheIndex,
				timeMark: this.config.lastRequestTimeMark // sending with the timemark from the main block
			},
			success: function(response) {
				if(response.params.timeMark < this.config.lastRequestTimeMark) return;
				jQuery(this.config.ratingSelector).html(response.html);
				this.reposition();
				// re-caching the block with its full content
				this.config.cachedProducts[response.params.cacheIndex] = this._productWrapper.html();
			}.bind(this),
			dataType: 'json',
			type: 'POST',
			cache: false
		});
	},
	_resizeBlock: function(element) {
		element.css({height: 'auto', width: 'auto'});
		var docWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		var left = (docWidth - element.outerWidth())/2;
		if(left < 50) {
			left = 50;
			element.css({width: (docWidth - 100) + 'px'});
		}
		var docHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		var top = (docHeight - element.outerHeight())/2;
		if(top < 50) {
			top = 50;
			element.css({height: (docHeight - 100) + 'px'});
		}
		var isIOS = navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false;
		if (isIOS) {
			element.css({'position': 'absolute'});
			left += window.pageXOffset ? window.pageXOffset : 0;
			top += window.pageYOffset ? window.pageYOffset : 0;
		}
		element.css({left: left + 'px', top: top + 'px'});
	},
	reposition: function() {
		this._resizeBlock(this._productContainer);
	},
	onListChanged: function() {
		this.config.cachedProducts = {};
	}
});
