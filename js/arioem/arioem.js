/*
 * ARI OEM Parts Selector Script
 */
var arioem = {
	config: {
		gateURL: '',
		partAssemblyURL: '',
		appKey: '',
		brand: {code: false, name: false},
		vehicle: {code: false, name: false},
		year: {code: false, name: false},
		model: {code: false, name: false},
		part: {code: false, name: false},
		limits: {
			year: {maxColCount: 5, minColItems: 5},
			model: {maxColCount: 3, minColItems: 10}
		},
		errorMessage: 'Server error, please try again later. If the problem persists, call site support.',
		cssPath: '',
		placeholders: {
			vehicle: 'Vehicle',
			year: 'Year',
			model: 'Model'
		}
	},
	init: function(config) {
		jQuery.extend(true, this.config, config);
		this.config.gateURL = this.config.gateURL.replace('http:', location.protocol);
	},
	vehicleName2ImageName: function(name) {
		return name.toLowerCase().replace(/[^\w\d\-_]/g, '');
	},
	vehicleClick: function(event) {
		this.config.vehicle.code = event.target.attributes['ari_vehicle_hash'].value;
		this.config.vehicle.name = event.target.children[0].innerHTML;

		jQuery('#arioem_statusbar .arioem-statusbar-vehicle').text(this.config.vehicle.name);
		jQuery('#arioem_statusbar .arioem-statusbar-vehicle').css({
			'background-image': 'url("' + this.config.cssPath + '/vehicles/small/' + this.vehicleName2ImageName(this.config.vehicle.name) + '.png")'
		});
		jQuery('#arioem_vehicle').removeClass('active');
		jQuery('#arioem_vehicle').addClass('completed');

		jQuery.ajax({
			url: this.config.gateURL,
			data: {
				action: 'year',
				brand: this.config.brand.code,
				hash: this.config.vehicle.code
			},
			dataType: 'json',
			cache: false,
			success: this.showYears.bind(this)
		});

		if(window.dataLayer) {
			dataLayer.push({
				OEMFunnelStep: 'year',
				OEMBrand: this.config.brand.name,
				OEMVehicle: this.config.vehicle.name,
				OEMYear: 'none selected',
				OEMModel: 'none selected'
			});
		};
	},
	showYears: function(response, textStatus) {
		if(response.error) {
			alert(this.config.errorMessage);
			console.log(response.error_message);
			return;
		}
		var data = response.res;
		var columnCount, columnSize;

		if(data.length >= this.config.limits.year.maxColCount * this.config.limits.year.minColItems) {
			columnSize = Math.ceil(data.length / this.config.limits.year.maxColCount);
		} else {
			columnSize = this.config.limits.year.minColItems;
		}

		var column = 1;
		var row = 0;
		var html = '';
		var items = '';
		var theEnd;

		for(var index=0; index<=data.length; index++) {
			theEnd = (index == data.length);
			if(index >= column * columnSize || theEnd) {
				html += '<div class="' + ((column % 2) ? 'odd ' : '') + 'arioem-step-content-col">' + items + '</div>';
				column++;
				items = '';
			}
			if(theEnd) break;
			items += '<span ari_year_hash="' + data[index][0] + '">' + data[index][1] + '</span>';
		}

		jQuery('#arioem_year').addClass('active');
		jQuery('#arioem_year .arioem-step-content')[0].innerHTML = html;
		jQuery('#arioem_year .arioem-step-content div span').click(this.yearClick.bind(this));
	},
	yearClick: function(event) {
		this.config.year.code = event.target.attributes['ari_year_hash'].value;
		this.config.year.name = event.target.innerHTML;

		jQuery('#arioem_statusbar .arioem-statusbar-year').text(this.config.year.name);
		jQuery('#arioem_year').removeClass('active');
		jQuery('#arioem_year').addClass('completed');

		jQuery.ajax({
			url: this.config.gateURL,
			data: {
				action: 'model',
				brand: this.config.brand.code,
				hash: this.config.year.code
			},
			dataType: 'json',
			cache: false,
			success: this.showModels.bind(this)
		});

		if(window.dataLayer) {
			dataLayer.push({
				OEMFunnelStep: 'model',
				OEMBrand: this.config.brand.name,
				OEMVehicle: this.config.vehicle.name,
				OEMYear: this.config.year.name,
				OEMModel: 'none selected'
			});
		};
	},
	showModels: function(response, textStatus) {
		if(response.error) {
			alert(this.config.errorMessage);
			console.log(response.error_message);
			return;
		}

		var data = response.res;
		var columnCount, columnSize;

		if(data.length >= this.config.limits.model.maxColCount * this.config.limits.model.minColItems) {
			columnSize = Math.ceil(data.length / this.config.limits.model.maxColCount);
		} else {
			columnSize = this.config.limits.model.minColItems;
		}

		var column = 1;
		var html = '';
		var items = '';
		var theEnd;

		for(var index=0; index<=data.length; index++) {
			theEnd = (index == data.length);
			if(index >= column * columnSize || theEnd) {
				column++;
				html += '<div class="' + ((column % 2) ? 'odd ' : '') + 'arioem-step-content-col">' + items + '</div>';
				items = '';
			}
			if(theEnd) break;
			items += '<span ari_model_hash="' + data[index][0] + '">' + data[index][1] + '</span>';
		}

		jQuery('#arioem_model').addClass('active');
		jQuery('#arioem_model .arioem-step-content')[0].innerHTML = html;
		jQuery('#arioem_model .arioem-step-content-col span').click(this.modelClick.bind(this));
	},
	modelClick: function(event) {
		this.config.model.code = event.target.attributes['ari_model_hash'].value;
		this.config.model.name = event.target.textContent;

		jQuery('#arioem_statusbar .arioem-statusbar-model span span').text(this.config.model.name);
		jQuery('#arioem_model').removeClass('active');
		jQuery('#arioem_model').addClass('completed');

		jQuery.ajax({
			url: this.config.gateURL,
			data: {
				action: 'part',
				brand: this.config.brand.code,
				hash: this.config.model.code
			},
			dataType: 'json',
			cache: false,
			success: this.showParts.bind(this)
		});

		// adding this to be picked up on a next page by Google stuff
		document.cookie += '; oemvehicle=' + encodeURIComponent(this.config.vehicle.name) + '; oemyear=' + encodeURIComponent(this.config.year.name);
	},
	showParts: function(response, textStatus) {
		if(response.error) {
			alert(this.config.errorMessage);
			console.log(response.error_message);
			return;
		}
		var year = this.config.year.name.trim();
		if(!isNaN(year)) {
			year = year + ':';
		} else {
			year = '';
		}
		document.location = this.config.partAssemblyURL + '#' + response.res[0][2];
	},
	onChangeLinkClick: function(event) {
		this.config.vehicle = {code: false, name: false};
		this.config.year = {code: false, name: false};
		this.config.model = {code: false, name: false};
		this.config.part = {code: false, name: false};

		jQuery('.arioem-step').removeClass('active');
		jQuery('.arioem-step').removeClass('completed');

		jQuery('#arioem_statusbar .arioem-statusbar-vehicle').html(this.config.placeholders.vehicle);
		jQuery('#arioem_statusbar .arioem-statusbar-vehicle').css({'background-image': ''});
		jQuery('#arioem_statusbar .arioem-statusbar-year').html(this.config.placeholders.year);
		jQuery('#arioem_statusbar .arioem-statusbar-model span span').html(this.config.placeholders.model);

		jQuery('#arioem_vehicle').addClass('active');
	}
};
