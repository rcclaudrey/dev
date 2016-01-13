var Fitment = new Class.create({
	config: {
		baseURL: '',
		params: {
			activity: null
		},
		options: {
			includeFacets: true,
			skip: 0,
			take: 10,
			sort: 'Rating',
			fitmentId: null,
			minPrice: null,
			maxPrice: null,
			term: null
		},
		defaultOptionNames: ['includeFacets', 'skip', 'take', 'sort', 'fitmentId', 'minPrice', 'maxPrice', 'term'],
		viewMode: 'grid',
		pageMode: '', // '' | 'tireBySize' | 'tireByRide'
		errorMessage: 'Some error occurred, please contact site admin',
		lastRequestTimeMark: 0,
		maintenanceFlag: false,
		filterValuesShrinkerText: {
			more: '+ Show more',
			less: '- Show less'
		},
		fitmentPageSelector: '.fitment-page'
	},
	rideSelector: null,
	viewPopup: null,
	init: function(initUrl) {
		this.config.maintenanceFlag = true;
		jQuery.ajax({
			url: initUrl.replace('http:', location.protocol),
			data: {
				hash: document.location.hash.substr(1)
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._initRequestSuccess.bind(this)
		});
	},
	_initRequestSuccess: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		jQuery.extend(true, this.config, response.config);
		this.config.baseURL = this.config.baseURL.replace('http:', location.protocol);

		this.rideSelector = new RideSelector();
		this.rideSelector.init(response.rideSelectorConfig);
		this.rideSelector.onSaveRide = this.onSaveRide.bind(this);
		this.rideSelector.onResetRide = this.onSaveRide.bind(this); // not this.onResetRide.bind(this) 'cause they do the same stuff

		this.viewPopup = new ProductViewPopup();
		this.viewPopup.init(response.viewPopupConfig);

		jQuery('.fitment-page h1').text(response.pageHeader);

		for(var blockName in response.blocks) {
			jQuery('.fitment-' + blockName + '-container').html(response.blocks[blockName]);
		}

		// simulating pressing Change button on Search by Fitment block if fitment is empty yet
		if(!response.config.options.fitmentId) {
			this.rideSelector.changeRide();
		}

		this._updateHash();
		this.config.maintenanceFlag = false;
	},
	_getRequestTimeMark: function() {
		var d = new Date();
		return d.getTime();
	},
	request: function(caller) {
		this.showLoader();
		jQuery.ajax({
			url: this.config.baseURL,
			data: {
				caller: caller,
				params: this.config.params,
				options: this.config.options,
				viewMode: this.config.viewMode,
				pageMode: this.config.pageMode,
				timeMark: this._getRequestTimeMark()
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._populateBlocks.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_populateBlocks: function(response) {
		if(this.config.maintenanceFlag) return;
		if(response.params.timeMark < this.config.lastRequestTimeMark) return;
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		this.config.maintenanceFlag = true;

		this.config.lastRequestTimeMark = response.params.timeMark;

		for(var blockName in response.blocks) {
			jQuery('.fitment-' + blockName + '-container').html(response.blocks[blockName]);
			if('search' == blockName) {
				jQuery('#fitment-search-term').keydown(fitment.onFilterSearchKeypress.bind(fitment));
			}
		}
		this.config.params = response.params.params;
		this.config.options = response.params.options;
		this.config.viewMode = response.params.viewMode;
		if(response.update.hasOwnProperty('rideSelector')) {
			jQuery.extend(true, this.rideSelector.config, response.update.rideSelector);
		}
		if(response.update.hasOwnProperty('options')) {
			jQuery.extend(true, this.config.options, response.update.options);
		}


		this._updateHash();

		// simulating pressing Change button on Search by Fitment block if fitment is empty yet
		if(response.params.caller == 'activity') {
			if(!this.config.options.fitmentId) {
				this.rideSelector.changeRide();
			}
		}

		this.viewPopup.onListChanged();

		this.config.maintenanceFlag = false;
	},
	_updateHash: function() {
		var hash = {
			activity: this.config.params.activity,
			viewMode: this.config.viewMode
		};
		if(this.config.pageMode)	{	hash.pageMode = this.config.pageMode;	}
		if(fitment.rideSelector.config.fitment.id)	{	hash.fitment = fitment.rideSelector.config.fitment.id;	}
//		if(fitment.rideSelector.config.fitment.name)	{	hash.vehicle = fitment.rideSelector.config.fitment.name;	}
		var options = {};
		jQuery.extend(true, options, this.config.options);
		var excludedOptions = ['fitmentId', 'includeFacets', 'minPrice', 'maxPrice'];
		for(var optionName in options) {
			if(excludedOptions.indexOf(optionName) >= 0) {
				delete options[optionName];
			}
		}
		if(!options.term) {	delete options['term'];	}
		jQuery.extend(true, hash, options);
		document.location.hash = Object.toQueryString(hash);
	},
	onSaveRide: function() {
		this.config.options.fitmentId = this.rideSelector.config.fitment.id;
		this.request('selector');
	},
	_toggleFilterAttributeValue: function(caller, optionName, value) {
		if(!this.config.options.hasOwnProperty(optionName)) {
			this.config.options[optionName] = [value];
		} else {
			if('object' != typeof(this.config.options[optionName])) {
				this.config.options[optionName] = [this.config.options[optionName], value];
			} else {
				var i = this.config.options[optionName].indexOf(value);
				if(i >= 0) {
					this.config.options[optionName].splice(i, 1);
				} else {
					this.config.options[optionName].push(value);
				}
			}
		}
		this.request(caller);
	},
	onActivityChanged: function(sender) {
		if(this.config.maintenanceFlag) return false;
		this.config.params.activity = sender.value;
		this.rideSelector.config.activity = sender.value;
		this.config.options.fitmentId = null;
		this.config.options.skip = 0;
		this.request('activity');
	},
	onFilterOptionChecked: function(sender) {
		if(this.config.maintenanceFlag) return false;

		sender.up('li').toggleClassName('fitment-filter-value-checked');
		this._toggleFilterAttributeValue('filter', sender.name, sender.value);
	},
	onFilterSelectChanged: function(sender) {
		if(this.config.maintenanceFlag) return false;

		sender.toggleClassName('fitment-filter-select-checked');

		if(sender.value) {
			this.config.options[sender.name] = sender.value;
		} else {
			delete this.config.options[sender.name];
		}

		if(	!this.config.pageMode	// no special page mode
		&&	sender.name.toLowerCase() == 'categoryid'
		) {
			this._resetCategory(true);
		}
		this.request('filter');
	},
	onCategoryFilterReset: function(sender) {
		if(this.config.maintenanceFlag) return false;
		this._resetCategory(false);
		this.request('filter');
	},
	onFilterSearchKeypress: function(event) {
		if(this.config.maintenanceFlag) return false;

		if(event.keyCode == 13) {
			this.config.options.term = document.getElementById('fitment-search-term').value;
			this.request('search');
			return true;
		}
	},
	onViewModeChange: function(sender) {
		if(sender.hasClassName('fitment-toolbar-viewmode-active')) return false;
		jQuery('.fitment-toolbar-viewmode-switch li').removeClass('fitment-toolbar-viewmode-active');
		sender.addClassName('fitment-toolbar-viewmode-active');
		this.config.viewMode = sender.getAttribute('mode');
		this.request('toolbar');
	},
	onSortModeChange: function(sender) {
		if(this.config.maintenanceFlag) return false;

		this.config.options.sort = sender.value;
		this.request('toolbar');
	},
	onPageSizeChange: function(sender) {
		if(this.config.maintenanceFlag) return false;

		this.config.options.take = sender.value;
		this.config.options.skip = 0;
		this.request('pager');
	},
	onPageNumberClick: function(sender) {
		if(this.config.maintenanceFlag) return false;

		this.config.options.skip = sender.getAttribute('value') * this.config.options.take;
		this.request('pager');
	},
	onFilterExpandCollapse: function(sender) {
		if(sender.parentNode.hasClassName('fitment-filter-facet-values-collapsed')) {
			sender.parentNode.removeClassName('fitment-filter-facet-values-collapsed');
			sender.innerHTML = this.config.filterValuesShrinkerText.less;
		} else {
			sender.parentNode.addClassName('fitment-filter-facet-values-collapsed');
			sender.innerHTML = this.config.filterValuesShrinkerText.more;
		}
	},
	viewProduct: function(sender) {
		var productBlock = sender.up('.fitment-list-item-wrapper');
		var ariProductId = productBlock.attributes['ariProductId'].value;
		var options = {
			cacheIndex: productBlock.attributes['cacheIndex'].value,
			fitmentId: this.rideSelector.config.fitment.id,
			vehicle: this.rideSelector.config.fitment.name,
			elements: {
				hasPriceRange: (('undefined' === typeof(productBlock.down('.fitment-list-item-price-from'))) ? 0 : 1),
				price: (productBlock.down('.fitment-list-item-price-value').innerHTML),
				isOnSale: (('undefined' === typeof(productBlock.down('.fitment-item-onsale'))) ? 0 : 1)
			},
		};
		this.viewPopup.show(ariProductId, options);
	},
	showLoader: function() {
		if(loader) {
			loader.show(this.config.fitmentPageSelector);
		}
	},
	hideLoader: function() {
		if(loader) {
			loader.hide(this.config.fitmentPageSelector);
		}
	},
	_resetCategory: function(keepCategorySelect) {
		var optionsToKeep = jQuery.extend([], this.config.defaultOptionNames);
		if(keepCategorySelect) {
			optionsToKeep.push('categoryId');
		}
		for(var optionName in this.config.options) {
			if(optionsToKeep.indexOf(optionName) < 0) {
				delete this.config.options[optionName];
			}
		}
	}
});
