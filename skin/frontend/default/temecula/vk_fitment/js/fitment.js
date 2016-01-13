var RideSelector = new Class.create({
	config: {
		baseURL: '',
		searchByRideURL: '',
		activity: null,
		product: null,
		fitment: {
			id: null,
			name: '',
			make: {
				id: null,
				name: null
			},
			year: null,
			model: {
				id: null,
				name: null
			}
		},
		emptyText: {
			makeSelect: 'Select make',
			yearSelect: 'Select year',
			modelSelect: 'Select model',
			rideName: 'None',
			noResultMessage: 'No options available for this configuration'
		},
		saved: null,
		fitmentList: [],
		cachedMakes: [],
		loaderTargetSelector: '',
		errorMessage: 'Some error occurred, please contact site admin'
	},
	onSaveRide: null,
	onResetRide: null,
	onSearchByRide: null,
	init: function(configValues){
		jQuery.extend(true, this.config, configValues);
		this.restoreRide();
		this.config.baseURL = this.config.baseURL.replace('http:', location.protocol);
		this.config.searchByRideURL = this.config.searchByRideURL.replace('http:', location.protocol);
		jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
		document.getElementById('fitment-make').value = '';
		document.getElementById('fitment-year').value = '';
		document.getElementById('fitment-model').value = '';
		document.getElementById('fitment-make').onchange = this.changedMake.bind(this);
		document.getElementById('fitment-year').onchange = this.changedYear.bind(this);
		document.getElementById('fitment-model').onchange = this.changedModel.bind(this);
		jQuery('.fitment-selector-change').click(this.changeRide.bind(this));
		jQuery('.fitment-selector-reset').click(this.resetRide.bind(this));
		jQuery('.fitment-selector-search').click(this.searchByRide.bind(this));
		jQuery('#fitment-ride-name').text(
				(this.config.fitment && this.config.fitment.id)
					?	this.config.fitment.name
					:	this.config.emptyText.rideName
			);
		if(!this.config.fitment.id) {
			if(jQuery('.fitment-selector-search').length) {	jQuery('.fitment-selector-search')[0].disable();	}
		}
		jQuery('.fitment-save').click(this.saveRide.bind(this));
		jQuery('.fitment-cancel').click(this.restoreRide.bind(this));
	},
	request: function(subject){
		this.showLoader('.fitment-selector');
		jQuery.ajax({
			url: this.config.baseURL + 'Request',
			data: {
				subject: subject,
				activity: this.config.activity,
				product: this.config.product,
				make: this.config.fitment.make.id,
				year: this.config.fitment.year
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._populateSelect.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_populateSelect: function(response){
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		if(!response.data.length) {
			alert(this.config.emptyText.noResultMessage);
		}
		switch(response.subject) {
			case 'makes':
				this.config.cachedMakes[this.config.activity] = response.data;
				this.resetMake();
				var html = '';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
				}
				document.getElementById('fitment-make').innerHTML += html;
				break;

			case 'years':
				this.resetYear();
				var html = '';
				response.data.reverse(); // ordering years descending; feel how old your flea-pit is! )))
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i] + '">' + response.data[i] + '</option>';
				}
				document.getElementById('fitment-year').innerHTML += html;
				break;

			case 'models':
				this.resetModel();
				this.config.fitmentList = {};
				var html = '';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
					this.config.fitmentList[response.data[i]['Id']] = response.data[i]['FitmentId'];
				}
				document.getElementById('fitment-model').innerHTML += html;
		}
	},
	setActivity: function(activityId) {
		this.config.activity = activityId;
		this.populateMake();
	},
	populateMake: function() {
		this.resetMake();
		if(this.config.activity in this.config.cachedMakes) {
			this._populateSelect({errorMessage: '', subject: 'makes', data: this.config.cachedMakes[this.config.activity]});
		} else {
			this.request('makes');
		}
	},
	showLoader: function(targetSelector) {
		if(loader) {
			this.config.loaderTargetSelector = targetSelector;
			loader.show(targetSelector);
		}
	},
	hideLoader: function() {
		if(loader) {
			loader.hide(this.config.loaderTargetSelector);
		}
	},
	resetMake: function(){
		this.config.fitment.make = {id: null, name: ''};
		document.getElementById('fitment-make').value = '';
		this.resetYear();
	},
	resetYear: function(){
		this.config.fitment.year = null;
		document.getElementById('fitment-year').innerHTML = '<option value="">' + this.config.emptyText.yearSelect + '</option>';
		document.getElementById('fitment-year').value = '';
		this.resetModel();
	},
	resetModel: function(){
		this.config.fitment.model = {id: null, name: ''};
		document.getElementById('fitment-model').innerHTML = '<option value="">' + this.config.emptyText.modelSelect + '</option>';
		document.getElementById('fitment-model').value = '';
		jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
	},
	changedMake: function(event){
		this.config.fitment.make.id = event.target.value;
		this.config.fitment.make.name = event.target.value
			?	event.target.options[event.target.selectedIndex].text
			:	'';
		this.resetYear();
		if(event.target.value) {
			this.request('years');
		}
	},
	changedYear: function(event){
		this.config.fitment.year = event.target.value;
		this.resetModel();
		if(event.target.value) {
			this.request('models');
		}
	},
	changedModel: function(event){
		this.config.fitment.model.id = event.target.value;
		this.config.fitment.model.name = event.target.value
			?	event.target.options[event.target.selectedIndex].text
			:	'';
		if(event.target.value) {
			this.config.fitment.id = this.config.fitmentList[event.target.value];
			this.config.fitment.name = this.config.fitment.make.name
					+ ' - ' + this.config.fitment.year
					+ ' - ' + this.config.fitment.model.name;
			jQuery('.fitment-selectors .fitment-save').prop('disabled', false);
			jQuery('.fitment-selector-search').prop('disabled', false);
		} else {
			this.config.fitment.id = null;
			jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
			jQuery('.fitment-selector-search').prop('disabled', true);
		}
	},
	changeRide: function(event){
		this._preserveRide();
		this.showSelectors();
	},
	resetRide: function() {
		this.showLoader('.fitment-selector');
		jQuery.ajax({
			url: this.config.baseURL + 'Reset',
			data: {},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._resetRideSuccess.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_resetRideSuccess: function(response){
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
//		alert(response.message);

		this.config.fitment.id = null;
		this.config.fitment.name = '';
		jQuery('#fitment-ride-name').text(this.config.emptyText.rideName);
//		jQuery('.fitment-selector-search').addClass('fitment-hidden');
		if(jQuery('.fitment-selector-search').length) {	jQuery('.fitment-selector-search')[0].disable();	}
		this.resetMake();

		if(this.onResetRide instanceof Function) {
			this.onResetRide();
		}
	},
	_preserveRide: function() {
		this.config.saved = this.config.fitment;
	},
	saveRide: function(){
		this.showLoader('.fitment-selector');
		this._preserveRide();
// TODO: update product to ride compatibility label, setting "yes" there
		jQuery.ajax({
			url: this.config.baseURL + 'Save',
			data: {
				fitment: this.config.fitment.id
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._saveRideSuccess.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_saveRideSuccess: function(response){
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
//		alert(response.message);

		jQuery('#fitment-ride-name').text(this.config.fitment.name);
//		jQuery('.fitment-selector-search').removeClass('fitment-hidden');
		if(jQuery('.fitment-selector-search').length) {	jQuery('.fitment-selector-search')[0].enable();	}
		this.showSelectors(false);

		if(this.onSaveRide instanceof Function) {
			this.onSaveRide();
		}
	},
	restoreRide: function(){
		if(this.config.saved) {
			jQuery.extend(true, this.config.fitment, this.config.saved);
		} else {
			this.resetMake();
		}
		jQuery('#fitment-ride-name').text(
				(this.config.fitment && this.config.fitment.id)
					?	this.config.fitment.name
					:	this.config.emptyText.rideName
			);
		this.showSelectors(false);
	},
	showSelectors: function(value) {
		value = (typeof(value) !== 'undefined') ? value : true;
		if(value) {
			jQuery('.fitment-selector').addClass('selection');

			if(	!document.getElementById('fitment-make').options.length
			||	(	1 == document.getElementById('fitment-make').options.length
				&&	!document.getElementById('fitment-make').options[0].value	)
			) {
				this.request('makes');
			}
		} else {
			jQuery('.fitment-selector').removeClass('selection');
		}
	},
	searchByRide: function() {
		var runDefault = true;
		if(this.onSearchByRide instanceof Function) {
			runDefault = this.onSearchByRide();
		}
		if(runDefault) {
			document.location = this.config.searchByRideURL + '#' + Object.toQueryString({
				activity: this.config.activity,
				fitment: this.config.fitment.id,
				vehicle: this.config.fitment.name
			});
		}
	}
});



var Fitment = new Class.create({
	config: {
		baseURL: '',
		rideSelector: {},
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
		viewMode: 'grid',
		errorMessage: 'Some error occurred, please contact site admin',
		lastRequestTimeMark: 0,
		maintenanceFlag: false,
		filterValuesShrinkerText: {
			more: '+ Show more',
			less: '- Show less'
		}
	},
	rideSelector: null,
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

		for(var blockName in response.blocks) {
			jQuery('.fitment-' + blockName + '-container').html(response.blocks[blockName]);
		}

		this.rideSelector = new RideSelector();
		this.rideSelector.init(response.rideSelectorConfig);
		this.rideSelector.onSaveRide = this.onSaveRide.bind(this);
		this.rideSelector.onResetRide = this.onSaveRide.bind(this); // not this.onResetRide.bind(this) 'cause they do the same stuff
		this.config.maintenanceFlag = false;
	},
	_getRequestTimeMark: function() {
		var d = new Date();
		return d.getTime();
	},
	request: function(caller) {
		jQuery.ajax({
			url: this.config.baseURL,
			data: {
				caller: caller,
				params: this.config.params,
				options: this.config.options,
				viewMode: this.config.viewMode,
				timeMark: this._getRequestTimeMark()
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._populateBlocks.bind(this)
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

		this.config.maintenanceFlag = false;
	},
	onSaveRide: function() {
		this.config.options.fitmentId = this.rideSelector.config.fitment.id;
		this.request('selector');
	},
	_toggleFilterAttributeValue: function(caller, optionName, value) {
		if(!this.config.options.hasOwnProperty(optionName)) {
			this.config.options[optionName] = [value];
		} else {
			var i = this.config.options[optionName].indexOf(value);
			if(i >= 0) {
				this.config.options[optionName].splice(i, 1);
			} else {
				this.config.options[optionName].push(value);
			}
		}
		this.request(caller);
	},
	onActivityChanged: function(sender) {
		if(this.config.maintenanceFlag) return false;
		this.config.params.activity = sender.value;
		this.config.options.fitmentId = 'no'; //null;
		this.config.options.skip = 0;
		this.request('activity');
	},
	onFilterOptionChecked: function(sender) {
		if(this.config.maintenanceFlag) return false;

		sender.up('li').toggleClassName('fitment-filter-value-checked');
		this._toggleFilterAttributeValue('filter', sender.name, sender.value);
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
	viewProduct: function(productId) {
		document.location = this.config.viewProductURL
				+ 'activity/' + this.config.params.activity
				+ '/product/' + productId
				+ (this.config.options.fitmentId ? '/fitment/' + this.config.options.fitmentId : '');
	},
	addToCart: function(productId) {
		loader.show('.fitment-items');
		jQuery.ajax({
			url: this.config.addToCartURL,
			data: {
				activity: this.config.params.activity,
				product: productId,
				fitment: this.config.options.fitmentId
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._addToCartSuccess.bind(this),
			complete: function(response) {loader.hide('.fitment-items');}
		});
	},
	_addToCartSuccess: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		if(response.redirectURL) {
			document.location = response.redirectURL;
			alert(this.config.redirectMessage);
			return;
		}
		alert(response.successMessage);
		if(jQuery('.top-cart') && window.hasOwnProperty('cart')) {
			if(response.topCart) {
				cart.updateTop(response.topCart, response.recentItemsCount)
			} else if (response.recentItemsCount) {
				cart.updateTop(false, response.recentItemsCount);
			}
			cart.show();
			jQuery("html, body").animate({ scrollTop: 0 }, "slow");
		}
	}
});



var TyreSelector = new Class.create({
	config: {
		requestURL: '',
		goSearchURL: '',
		activity: null,
		activityIndex: 0,
		tyreFilter: {
			size: {
				code: '',
				id: null
			},
			brand: null,
			price: {
				from: null,
				to: null
			}
		},
		emptyText: {
			size: 'Select size',
			brand: 'Select brand',
			price: 'Select price',
			noResultMessage: 'No tire options available for this configuration'
		},
		tiresCategoryId: 0,
//		extraFilter: {},
		errorMessage: 'Some error occurred, please contact site admin',
		priceRanges: {'0-0': {from: 0, to: 0}},
		cachedSizes: [],
		loaderTargetSelector: ''
	},
	init: function(configValues){
		jQuery.extend(true, this.config, configValues);
		this.config.requestURL = this.config.requestURL.replace('http:', location.protocol);
		document.getElementById('tireshop-criteria-size').value = '';
		document.getElementById('tireshop-criteria-brand').value = '';
		document.getElementById('tireshop-criteria-price').value = '';
		document.getElementById('tireshop-criteria-size').onchange = this.changedSize.bind(this);
		document.getElementById('tireshop-criteria-brand').onchange = this.changedBrand.bind(this);
		document.getElementById('tireshop-criteria-price').onchange = this.changedPrice.bind(this);
		document.getElementById('tireshop-criteria-goSearch').onclick = this.searchTyre.bind(this);
//		document.getElementById('tireshop-criteria-size').onfocus = this.populateSize.bind(this);
		this.populateSize();
	},
	request: function(subject){
		this.showLoader('.tireshop-criteria');
		var data = {
			subject: subject,
			activityIndex: this.config.activityIndex,
			brandId: this.config.tyreFilter.brand,
			minPrice: this.config.tyreFilter.price.from,
			maxPrice: this.config.tyreFilter.price.to
		};
		data[this.config.tyreFilter.size.code] = this.config.tyreFilter.size.id;
//		jQuery.extend(true, data, this.config.extraFilter);
		jQuery.ajax({
			url: this.config.requestURL,
			data: data,
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._populateSelect.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_populateSelect: function(response){
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		if(!response.data.length) {
			if(response.subject == 'size' && !(response.params.activityIndex in this.config.cachedSizes)) {
				alert(this.config.emptyText.noResultMessage);
			}
		}
		switch(response.subject) {
			case 'size':
				this.config.cachedSizes[response.params.activityIndex] = response.data;
				this.resetValue('size');
				break;

			case 'brand':
				this.resetValue('brand');
				break;

			case 'price':
				this.resetValue('price');
				this.config.priceRanges = [];
				for(var i=0; i<response.data.length; i++) {
					this.config.priceRanges[response.data[i]['Value']] = {
						from: response.data[i]['From'],
						to: response.data[i]['To']
					};
				}
				break;
		}
		var html = '<option value="">' + this.config.emptyText[response.subject] + '</option>';
		for(var i=0; i<response.data.length; i++) {
			html += '<option value="' + response.data[i]['Value'] + '">' + response.data[i]['Name'] + '</option>';
		}
		document.getElementById('tireshop-criteria-' + response.subject).innerHTML = html;
		document.getElementById('tireshop-criteria-' + response.subject).parentNode.addClassName('tireshop-active');
	},
	setActivity: function(activityIndex) {
		this.config.activityIndex = activityIndex;
		this.config.activity = this.config.activities[activityIndex].id;
//		this.config.extraFilter = this.config.activities[activityIndex].extraFilter;
		this.populateSize();
	},
	populateSize: function() {
		this.resetValue('size');
		if(this.config.activityIndex in this.config.cachedSizes) {
			this._populateSelect({errorMessage: '', subject: 'size', data: this.config.cachedSizes[this.config.activityIndex], params: {activityIndex: this.config.activityIndex}});
		} else {
			this.request('size');
		}
	},
	showLoader: function(targetSelector) {
		if(loader) {
			this.config.loaderTargetSelector = targetSelector;
			loader.show(targetSelector);
		}
	},
	hideLoader: function() {
		if(loader) {
			loader.hide(this.config.loaderTargetSelector);
		}
	},
	resetValue: function(subject){
		switch(subject) {
			case 'size':
				this.config.tyreFilter.size.id = null;
				document.getElementById('tireshop-criteria-size').value = '';
				document.getElementById('tireshop-criteria-size').innerHTML = '<option value="">' + this.config.emptyText.size + '</option>';
				this.resetValue('brand');
				this.resetValue('price');
				break;

			case 'brand':
				this.config.tyreFilter.brand = null;
				document.getElementById('tireshop-criteria-brand').innerHTML = '<option value="">' + this.config.emptyText.brand + '</option>';
				document.getElementById('tireshop-criteria-brand').value = '';
				document.getElementById('tireshop-criteria-brand').parentNode.removeClassName('tireshop-active');
				this.resetValue('price');
				break;

			case 'price':
//				this.config.tyreFilter.price = {from: null, to: null};
//				this.config.priceRanges = [];
//				document.getElementById('tireshop-criteria-price').innerHTML = '<option value="">' + this.config.emptyText.price + '</option>';
//				document.getElementById('tireshop-criteria-price').value = '';
				document.getElementById('tireshop-criteria-price').parentNode.removeClassName('tireshop-active');
				document.getElementById('tireshop-criteria-goSearch').disable();
				break;
		}
	},
	changedSize: function(event){
		this.config.tyreFilter.size.id = event.target.value;
		this.resetValue('brand');
		if(event.target.value) {
			this.request('brand');
		}
	},
	changedBrand: function(event){
		this.config.tyreFilter.brand = event.target.value;
		this.resetValue('price');
		if(event.target.value) {
//			this.request('price');
			this.changedPrice({target: {value: document.getElementById('tireshop-criteria-price').value}});
			document.getElementById('tireshop-criteria-price').parentNode.addClassName('tireshop-active');
		}
	},
	changedPrice: function(event){
		this.config.tyreFilter.price = this.config.priceRanges[event.target.value];
		if(event.target.value) {
			document.getElementById('tireshop-criteria-goSearch').enable();
		} else {
			document.getElementById('tireshop-criteria-goSearch').disable();
		}
	},
	searchTyre: function() {
		document.location = this.config.goSearchURL + '#activity=' + this.config.activity
				+ '&category=' + this.config.tiresCategoryId
				+ '&' + this.config.tyreFilter.size.code + '=' + this.config.tyreFilter.size.id
				+ '&brand=' + this.config.tyreFilter.brand
				+ '&fitment=no';
	}
});



var MachineSelector = new Class.create({
	config: {
		baseURL: '',
		searchURL: '',
		activity: null,
		fitment: {
			make: {
				id: null,
				name: null
			},
			year: null,
			model: {
				id: null,
				name: null
			},
			id: null,
			name: ''
		},
		emptyText: {
			makeSelect: 'Select make',
			yearSelect: 'Select year',
			modelSelect: 'Select model',
			rideName: 'None',
			noResultMessage: 'No machine options available for this configuration'
		},
		tiresCategoryId: 0,
		extraFilter: {},
		fitmentList: [],
		cachedMakes: [],
		loaderTargetSelector: '',
		errorMessage: 'Some error occurred, please contact site admin'
	},
	onSearchByRide: null,
	init: function(configValues){
		jQuery.extend(true, this.config, configValues);
		this.config.baseURL = this.config.baseURL.replace('http:', location.protocol);
		this.config.searchURL = this.config.searchURL.replace('http:', location.protocol);
		document.getElementById('fitment-make').value = '';
		document.getElementById('fitment-year').value = '';
		document.getElementById('fitment-model').value = '';
		document.getElementById('fitment-make').onchange = this.changedMake.bind(this);
		document.getElementById('fitment-year').onchange = this.changedYear.bind(this);
		document.getElementById('fitment-model').onchange = this.changedModel.bind(this);
		document.getElementById('tireshop-fitment-goSearch').onclick = this.searchByRide.bind(this);
		document.getElementById('fitment-make').onfocus = this.populateMakes.bind(this);
		this.populateMake();
	},
	request: function(subject){
		this.showLoader('.tireshop-fitment');
		var data = {
			subject: subject,
			activity: this.config.activity,
			make: this.config.fitment.make.id,
			year: this.config.fitment.year
		};
		if(this.config.extraFilter.hasOwnProperty(this.config.activity)) {
			jQuery.extend(true, data, this.config.extraFilter[this.config.activity]);
		};
		jQuery.ajax({
			url: this.config.baseURL + 'Request',
			data: data,
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._populateSelect.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_populateSelect: function(response){
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		if(!response.data.length) {
			if(response.subject == 'makes' && !(this.config.activity in this.config.cachedMakes)) {
				alert(this.config.emptyText.noResultMessage);
			}
		}
		switch(response.subject) {
			case 'makes':
				this.config.cachedMakes[this.config.activity] = response.data;
				this.resetMake();
				var html = '';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
				}
				document.getElementById('fitment-make').innerHTML += html;
				document.getElementById('fitment-make').parentNode.addClassName('tireshop-active');
				break;

			case 'years':
				this.resetYear();
				var html = '';
				response.data.reverse(); // ordering years descending; feel how old your flea-pit is! )))
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i] + '">' + response.data[i] + '</option>';
				}
				document.getElementById('fitment-year').innerHTML += html;
				document.getElementById('fitment-year').parentNode.addClassName('tireshop-active');
				break;

			case 'models':
				this.resetModel();
				this.config.fitmentList = {};
				var html = '';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
					this.config.fitmentList[response.data[i]['Id']] = response.data[i]['FitmentId'];
				}
				document.getElementById('fitment-model').innerHTML += html;
				document.getElementById('fitment-model').parentNode.addClassName('tireshop-active');
		}
	},
	setActivity: function(activityId) {
		this.config.activity = activityId;
		this.populateMake();
	},
	populateMake: function() {
		this.resetMake();
		if(this.config.activity in this.config.cachedMakes) {
			this._populateSelect({errorMessage: '', subject: 'makes', data: this.config.cachedMakes[this.config.activity]});
		} else {
			this.request('makes');
		}
	},
	showLoader: function(targetSelector) {
		if(loader) {
			this.config.loaderTargetSelector = targetSelector;
			loader.show(targetSelector);
		}
	},
	hideLoader: function() {
		if(loader) {
			loader.hide(this.config.loaderTargetSelector);
		}
	},
	resetMake: function(){
		this.config.fitment.make.id = null;
		this.config.fitment.make.name = '';
		document.getElementById('fitment-make').value = '';
		document.getElementById('fitment-year').parentNode.removeClassName('tireshop-active');
		this.resetYear();
	},
	resetYear: function(){
		this.config.fitment.year = null;
		document.getElementById('fitment-year').innerHTML = '<option value="">' + this.config.emptyText.yearSelect + '</option>';
		document.getElementById('fitment-year').value = '';
		document.getElementById('fitment-model').parentNode.removeClassName('tireshop-active');
		this.resetModel();
	},
	resetModel: function(){
		this.config.fitment.model.id = null;
		this.config.fitment.model.name = '';
		document.getElementById('fitment-model').innerHTML = '<option value="">' + this.config.emptyText.modelSelect + '</option>';
		document.getElementById('fitment-model').value = '';
		document.getElementById('tireshop-fitment-goSearch').disable();
	},
	// on change
	changedMake: function(event){
		this.config.fitment.make.id = event.target.value;
		this.config.fitment.make.name = event.target.value
			?	event.target.options[event.target.selectedIndex].text
			:	'';
		this.resetYear();
		if(event.target.value) {
			this.request('years');
		}
	},
	changedYear: function(event){
		this.config.fitment.year = event.target.value;
		this.resetModel();
		if(event.target.value) {
			this.request('models');
		}
	},
	changedModel: function(event){
		this.config.fitment.model.id = event.target.value;
		if(event.target.value) {
			this.config.fitment.id = this.config.fitmentList[event.target.value];
			this.config.fitment.model.name = event.target.options[event.target.selectedIndex].text;
			this.config.fitment.name = this.config.fitment.make.name
					+ ' - ' + this.config.fitment.year
					+ ' - ' + this.config.fitment.model.name;
			document.getElementById('tireshop-fitment-goSearch').enable();
	} else {
			this.config.fitment.id = null;
			this.config.fitment.model.name = '';
			this.config.fitment.name = '';
			document.getElementById('tireshop-fitment-goSearch').disable();
		}
	},
	populateMakes: function() {
		if(	!document.getElementById('fitment-make').options.length
		||	(	1 == document.getElementById('fitment-make').options.length
			&&	!document.getElementById('fitment-make').options[0].value	)
		) {
			this.request('makes');
		}
	},
	searchByRide: function() {
		var runDefault = true;
		if(this.onSearchByRide instanceof Function) {
			runDefault = this.onSearchByRide();
		}
		if(runDefault) {
//			document.location = this.config.searchByRideURL + '#activity=' + this.config.activity +'/fitment=' + this.config.fitment.id + '/vehicle=' + encodeURIComponent(this.config.fitment.name);
			document.location = this.config.searchByRideURL + '#' + Object.toQueryString({
				activity: this.config.activity,
				fitment: this.config.fitment.id,
				vehicle: this.config.fitment.name,
				category: this.config.tiresCategoryId
			});
		}
	}
});