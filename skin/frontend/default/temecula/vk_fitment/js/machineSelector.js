var MachineSelector = new Class.create({
	config: {
		baseURL: '',
		searchURL: '',
		activity: null,
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
				var html = '<option value="">' + this.config.emptyText.makeSelect + '</option>';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
				}
				document.getElementById('fitment-make').innerHTML = html;
				document.getElementById('fitment-make').parentNode.addClassName('tireshop-active');
				break;

			case 'years':
				this.resetYear();
				var html = '<option value="">' + this.config.emptyText.yearSelect + '</option>';
				response.data.reverse(); // ordering years descending; feel how old your flea-pit is! )))
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i] + '">' + response.data[i] + '</option>';
				}
				document.getElementById('fitment-year').innerHTML = html;
				document.getElementById('fitment-year').parentNode.addClassName('tireshop-active');
				break;

			case 'models':
				this.resetModel();
				this.config.fitmentList = {};
				var html = '<option value="">' + this.config.emptyText.modelSelect + '</option>';
				for(var i=0; i<response.data.length; i++) {
					html += '<option value="' + response.data[i]['Id'] + '">' + response.data[i]['Name'] + '</option>';
					this.config.fitmentList[response.data[i]['Id']] = response.data[i]['FitmentId'];
				}
				document.getElementById('fitment-model').innerHTML = html;
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
			document.location = this.config.searchByRideURL + '#' + Object.toQueryString({
				activity: this.config.activity,
				fitment: this.config.fitment.id,
				vehicle: this.config.fitment.name,
				category: this.config.tiresCategoryId,
				pageMode: 'tireByRide'
			});
		}
	}
});