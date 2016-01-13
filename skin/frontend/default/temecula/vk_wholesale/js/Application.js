var DealerApplicationForm = new Class.create({
	config: {
		sendApplicationURL: '',
		addressViewURL: '',
		_loaderTargetSelector: null,
		messages: {
			fieldsValidationNotPopulated: 'Please populate the required field(s)',
			common: 'An error occurred, please contact site administrator'
		}
	},
	init: function(config) {
		jQuery.extend(true, this.config, config);

//		jQuery('#wsap-address-exists').click();
		jQuery('#wsap-address-enter').click();
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
	toggleBlock: function(sender) {
		var groupSelector = jQuery(sender).attr('groupSelector');
		jQuery('.' + groupSelector + '.wsap-toggle').hide();
		jQuery('#' + groupSelector + '-' + jQuery(sender).attr('value') + '-content').show();
	},
	onAddressSelectChanged: function(sender) {
		if(sender.value) {
			this.showLoader('#wsap-address-source-exists-content');
			jQuery.ajax({
				url: this.config.addressViewURL.replace('%address_id%', sender.value),
				dataType: 'json',
				type: 'GET',
				cache: false,
				success: this._onAddressSelectChanged.bind(this),
				complete: this.hideLoader.bind(this)
			});
		} else {
			jQuery('.wsap-address-view-container').html('');
		}
	},
	_onAddressSelectChanged: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		jQuery('.wsap-address-view-container').html(response.html);
	},
	validateOrderFields: function() {
		if(jQuery('#form-validate').is(':visible')) {
			if(!dataForm.validator.validate()) {
				return false;
			}
		}
		var result = true;
		var elements = [];
		var addressTabSelected = jQuery('input[name="address_source"]:checked').val() + '-content';
		elements.push.apply(elements, jQuery('#wsap-address-source-' + addressTabSelected).find('.wsap-required-cond'));
		elements.push.apply(elements, jQuery('.wsap .wsap-required'));

		var focus = true;
		for(var i=0; i<elements.length; i++) {
			if(	(	('checkbox' === String(jQuery(elements[i]).attr('type')).toLowerCase())
				&&	jQuery(elements[i]).is(':checked')
				)
			||	(	jQuery(elements[i]).val()	)
			) {
				jQuery(elements[i]).removeClass('wsqo-required-alert');
			} else {
				jQuery(elements[i]).addClass('wsqo-required-alert');
				if(focus) {
					elements[i].focus();
					focus = false;
					jQuery('html, body').animate({scrollTop: jQuery(elements[i]).offset().top}, 1000);
				}
				result = false;
			}
		}
		if(!result) {
			alert(this.config.messages.fieldsValidationNotPopulated);
		}
		return result;
	},
	sendApplication: function() {
		if(!this.validateOrderFields()) {
			return;
		}

		var elements = jQuery('.wsap').find('input, textarea, select');
		var data = {};
		for(var i=0; i<elements.length; i++) {
			if(		(	'INPUT' === elements[i].tagName.toUpperCase()	)
				&&	(	'radio' === String(jQuery(elements[i]).attr('type')).toLowerCase()	)
				&&	!jQuery(elements[i]).is(':checked')
			) {
				continue;
			}

			if(data.hasOwnProperty(elements[i].name)) {
				if('array' !== typeof(data[elements[i].name])) {
					data[elements[i].name] = [data[elements[i].name]];
				}
				data[elements[i].name].push(elements[i].value);
			} else {
				data[elements[i].name] = elements[i].value;
			}
		}

		this.showLoader('.wsap');
		jQuery.ajax({
			url: this.config.sendApplicationURL,
			data: data,
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._sendApplication.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_sendApplication: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			return;
		}
		jQuery('.wsap').html(response.html);
		jQuery('.wholesale-home').hide();
	}
});