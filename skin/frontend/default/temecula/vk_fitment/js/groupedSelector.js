var GroupedFitmentSelector = new Class.create({
	config: {
		baseURL: '',
		saveFitmentURL: '',
		activity: null,
		product: null,
		parentProduct: null,
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
		fitmentList: {},
		cachedMakes: [],
		loaderTargetSelector: '',
		errorMessage: 'Some error occurred, please contact site admin'
	},
	loader: null,
	onSaveRide: null,
	onResetRide: null,
	onSearchByRide: null,
	init: function(configValues){
		jQuery.extend(true, this.config, configValues);
		this.restoreRide();
		this.config.baseURL = this.config.baseURL.replace('http:', location.protocol);
		this.config.saveFitmentURL = this.config.saveFitmentURL.replace('http:', location.protocol);
		jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
		jQuery('#fitment-make').value = '';
		jQuery('#fitment-year').value = '';
		jQuery('#fitment-model').value = '';
		jQuery('#fitment-ride-name').text(
				(this.config.fitment && this.config.fitment.id)
					?	this.config.fitment.name
					:	this.config.emptyText.rideName
			);
		if(!this.config.fitment.id) {
			if(jQuery('.fitment-selector-search').length) {	jQuery('.fitment-selector-search')[0].disable();	}
		} else {
			this.saveRide();
		}

		if(window.hasOwnProperty('loader')) {
			this.loader = window.loader;
		}
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
		if(!response.data || !response.data.length) {
			alert(this.config.emptyText.noResultMessage);
		}
		switch(response.subject) {
			case 'makes':
				this.config.cachedMakes[this.config.activity] = response.data;
				this.resetMake();
				var html = '<option value="">' + this.config.emptyText.makeSelect + '</option>';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
				}
				jQuery('#fitment-make').html(html);
				jQuery('#fitment-make').prop('disabled', false);
				break;

			case 'years':
				this.resetYear();
				var html = '<option value="">' + this.config.emptyText.yearSelect + '</option>';
				response.data.reverse(); // ordering years descending; feel how old your flea-pit is! )))
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i] + '">' + response.data[i] + '</option>';
				}
				jQuery('#fitment-year').html(html);
				jQuery('#fitment-year').prop('disabled', false);
				break;

			case 'models':
				this.resetModel();
				this.config.fitmentList = {};
				var html = '<option value="">' + this.config.emptyText.modelSelect + '</option>';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
					this.config.fitmentList[response.data[i]['Id']] = response.data[i]['FitmentId'];
				}
				jQuery('#fitment-model').html(html);
				jQuery('#fitment-model').prop('disabled', false);
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
		if(this.loader) {
			this.config.loaderTargetSelector = targetSelector;
			this.loader.show(targetSelector);
		}
	},
	hideLoader: function() {
		if(this.loader) {
			this.loader.hide(this.config.loaderTargetSelector);
		}
	},
	resetMake: function(){
		this.config.fitment.make = {id: null, name: ''};
		jQuery('#fitment-make').prop('value', '');
		this.resetYear();
	},
	resetYear: function(){
		this.config.fitment.year = null;
		jQuery('#fitment-year').html('<option value="">' + this.config.emptyText.yearSelect + '</option>');
		jQuery('#fitment-year').prop('value', '');
		this.resetModel();
		jQuery('#fitment-year').prop('disabled', true);
	},
	resetModel: function(){
		this.config.fitment.model = {id: null, name: ''};
		jQuery('#fitment-model').html('<option value="">' + this.config.emptyText.modelSelect + '</option>');
		jQuery('#fitment-model').prop('value', '');
		jQuery('#fitment-model').prop('disabled', true);
		jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
	},
	changedMake: function(event) {
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
		} else {
			this.config.fitment.id = null;
			jQuery('.fitment-selectors .fitment-save').prop('disabled', true);
		}
	},
	changeRide: function(){
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
		this.config.fitment.id = null;
		this.config.fitment.name = '';
		jQuery('#fitment-ride-name').text(this.config.emptyText.rideName);
		jQuery('.fitment-selector-reset').addClass('fitment-hidden');
		jQuery('.fitment-notice').removeClass('fitment-compatible-yes');
		if(jQuery('.fitment-selector-search').length) {	jQuery('.fitment-selector-search')[0].disable();	}
		this.resetMake();

		jQuery('.fitment-grouped-partlist-container').html('');

		if(this.onResetRide instanceof Function) {
			this.onResetRide();
		}
	},
	_preserveRide: function() {
		jQuery.extend(true, this.config.saved , this.config.fitment);
	},
	keepRide: function() {
		this._saveRideSuccess({errorMessage: ''});
	},
	saveRide: function() {
		this.showLoader('.fitment-selector');
		this._preserveRide();
		jQuery.ajax({
			url: this.config.saveFitmentURL,
			data: {
				activity: this.config.activity,
				vehicle: this.config.fitment.name,
				fitment: this.config.fitment.id,
				product: this.config.product,
				parent: this.config.parentProduct
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._saveRideSuccess.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_saveRideSuccess: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		jQuery('#fitment-ride-name').text(this.config.fitment.name);
		jQuery('.fitment-selected-ridename-container').removeClass('fitment-hidden');
		jQuery('.fitment-selector-reset').removeClass('fitment-hidden');
		this.showSelectors(false);

		jQuery('.fitment-grouped-partlist-container').html(response.partList);

		if(this.onSaveRide instanceof Function) {
			this.onSaveRide(response);
		}
	},
	restoreRide: function() {
		if(this.config.saved) {
			jQuery.extend(true, this.config.fitment , this.config.saved);
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
	}
});
