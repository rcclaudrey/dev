var TyreSelector = new Class.create({
	config: {
		requestURL: '',
		goSearchURL: '',
		activity: null,
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
			activity: this.config.activity,
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
			if(response.subject == 'size' && !(response.params.activity in this.config.cachedSizes)) {
				alert(this.config.emptyText.noResultMessage);
			}
		}
		switch(response.subject) {
			case 'size':
				this.config.cachedSizes[response.params.activity] = response.data;
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
	setActivity: function(activity) {
		this.config.activity = activity;
		this.populateSize();
	},
	populateSize: function() {
		this.resetValue('size');
		if(this.config.activity in this.config.cachedSizes) {
			this._populateSelect({errorMessage: '', subject: 'size', data: this.config.cachedSizes[this.config.activity], params: {activity: this.config.activity}});
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
				+ '&' + this.config.tyreFilter.size.code + '=' + this.config.tyreFilter.size.id
				+ '&brand=' + this.config.tyreFilter.brand
				+ '&pageMode=tireBySize';
	}
});
