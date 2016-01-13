var QuickOrderForm = new Class.create({
	config: {
		uploadFileURL: '',
		autocompleteURL: '',
		checkPartNumberURL: '',
		sendOrderURL: '',
		minQueryLength: 5,
		rowTemplateId: '#wqop_HASH_',
		_rowTemplate: '',
		_loaderTargetSelector: null,
		_state: '',
		messages: {
			dragFileHere: 'Drag and drop a .csv file here',
			fileIsUploading: 'Uploading the file, please wait...',
			fieldsValidationNotPopulated: 'Please populate the required field(s)',
			fieldsValidationNotFound: 'You can only order parts that exist in our database',
			fieldsValidationPONumberLength: 'PO Number cannot exceed 10 characters',
			common: 'An error occurred, please contact site administrator'
		}
	},
	init: function(config) {
		jQuery.extend(true, this.config, config);

		this.config._rowTemplate = jQuery(this.config.rowTemplateId)[0].outerHTML;
		jQuery(this.config.rowTemplateId).remove();
		this.addRow();
		this.setState('manual');

		jQuery('#wsqo-uploadFile').fileupload({
			url: this.config.uploadFileURL,
			dataType: 'json',
			dropZone: jQuery('#dropzone'),
			done: this._uploadFileResponse.bind(this),
			fail: function(e, data) {
				this.hideLoader('.wsqo-dropzone');
				alert(this.config.messages.common + String.fromCharCode(13) + String.fromCharCode(10) + data.errorThrown);
			}.bind(this),
			always: function() {
				this.hideLoader('.wsqo-dropzone');
			}.bind(this),
			start: function() {
				this.showLoader('.wsqo-dropzone');
			}.bind(this)
		});

		jQuery(document).bind('dragover', function (e) {
			var dropZone = jQuery('#dropzone'),
				timeout = window.dropZoneTimeout;
			if (!timeout) {
				dropZone.addClass('in');
			} else {
				clearTimeout(timeout);
			}
			var found = false,
				node = e.target;
			do {
				if (node === dropZone[0]) {
					found = true;
					break;
				}
				node = node.parentNode;
			} while (node != null);

			if (found) {
				dropZone.addClass('hover');
			} else {
				dropZone.removeClass('hover');
			}
			window.dropZoneTimeout = setTimeout(function () {
				window.dropZoneTimeout = null;
				dropZone.removeClass('in hover');
			}, 100);
		});

		jQuery(document).bind('drop dragover', function (e) {
			e.preventDefault();
		});
	},
	addRow: function() {
		var newRowNumber = jQuery('.wsqo-parts tbody tr').length;
		var newRow = this.config._rowTemplate
				.replace(/_HASH_/g, Math.floor((Math.random() * 1000000) + 1))
				.replace(/_ROW_/g, newRowNumber + 1);
		jQuery('.wsqo-parts tbody').append(newRow);
	},
	deleteRow: function(sender) {
		jQuery(sender).closest('tr').remove();
		this._renumberRows();
	},
	_renumberRows: function() {
		var rows = jQuery('.wsqo-parts tbody tr');
		for(var i=0; i<rows.length; i++) {
			jQuery(rows[i]).find('.wsqo-parts-list-lineNo').text(i+1);
		}
	},
	showUploadInstructions: function(show) {
		if(!arguments.length) {
			show = !jQuery('.wsqo-uploadInstructions').is(':visible');
		}
		if(show) {
			jQuery('.wsqo-uploadInstructions').show();
		} else {
			jQuery('.wsqo-uploadInstructions').hide();
		}
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
	setState: function(state) {
		if(state == this.config._state) return;
		var previousState = this.config._state;
		this.config._state = state;
		switch (state) {
			case 'manual':
				jQuery('#wsqo-inputType-manual').prop('checked', 'checked');
				jQuery('.wsqo-uploadFile-container').hide();
				jQuery('.wsqo-resetUpload').hide();
				jQuery('.wsqo-parts').show();
				this.showUploadInstructions(false);
				break;

			case 'file-upload':
				if(previousState == 'manual') {
					jQuery('.wsqo-uploadFile-container').show();
					jQuery('.wsqo-resetUpload').hide();
					jQuery('.wsqo-parts').hide();
					this.showUploadInstructions(false);
				}
				break;

			case 'file-reupload':
				jQuery('.wsqo-uploadFile-container').show();
				jQuery('.wsqo-resetUpload').hide();
				jQuery('.wsqo-parts').hide();
				this.showUploadInstructions(false);
				jQuery('#wsqo-uploadFile').click();
				break;

			case 'file-uploading':
				jQuery('.wsqo-parts').hide();
				break;

			case 'file-uploaded':
				jQuery('.wsqo-uploadFile-container').hide();
				jQuery('.wsqo-resetUpload').show();
				jQuery('.wsqo-parts').show();
				break;
		}
	},
	_uploadFileResponse: function(e, data) {
		var response = data._response.result;
		if(response.errorMessage) {
			alert(response.errorMessage);
			if(response.redirect) {
				location = response.redirect;
			}
			if(!response.html) return;
	//			jQuery('.wsqo-parts tbody').html('<tr><td colspan="100">' + response.html + '</td></tr>');
		}
		this.setState('file-uploaded');
		jQuery('.wsqo-parts tbody').html(response.html);
	},
	onPartNumberChange: function(sender) {
		var partNumber = sender.value;
		if(this.config.minQueryLength > partNumber.length) return;

		this.checkPartNumber(sender);
		/**
		jQuery.ajax({
			url: this.config.autocompleteURL,
			data: {
				search: partNumber,
				id: jQuery(sender).closest('TR').attr('id')
			},
			dataType: 'json',
			type: 'GET',
			cache: false,
			success: this._onAutoComplete.bind(this)
		});
		/**/
	},
	_onAutoComplete: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			if(response.redirect) {
				location = response.redirect;
			}
			if(!response.html) return;
		}
		jQuery('#' + response.id + ' .wsqo-parts-list-autocomplete-container').html(response.html);
	},
	checkPartNumber: function(sender) {
		var rowId = jQuery(sender).closest('TR').attr('id');
		this.showLoader('#' + rowId + ' .wsqo-parts-list-description', true);
		var partNumber = jQuery('#' + rowId + ' .wsqo-parts-list-partnumber input').val();
		if(this.config.minQueryLength > partNumber.length) return;
		jQuery.ajax({
			url: this.config.checkPartNumberURL,
			data: {
				partNumber: jQuery('#' + rowId + ' .wsqo-parts-list-partnumber input').val(),
				id: rowId
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._checkPartNumber.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_checkPartNumber: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			if(response.redirect) {
				location = response.redirect;
			}
			if(!response.html) return;
		}
		this.hideLoader('#' + response.id + ' .wsqo-parts-list-description');
		jQuery('#' + response.id + ' .wsqo-parts-list-description').html(response.html);
	},
	validateOrderFields: function() {
		var result = true;
		var elements = jQuery('.wsqo-form .wsqo-required');
		var focus = true;
		for(var i=0; i<elements.length; i++) {
			if(jQuery(elements[i]).val()) {
				jQuery(elements[i]).removeClass('wsqo-required-alert');
			} else {
				jQuery(elements[i]).addClass('wsqo-required-alert');
				if(focus) {
					elements[i].focus();
					focus = false;
				}
				result = false;
			}
		}
		if(!result) {
			alert(this.config.messages.fieldsValidationNotPopulated);
		}

		if(result) {
			if(jQuery('#wsqo-poNumber').val().length > 10) {
				jQuery('#wsqo-poNumber').addClass('wsqo-required-alert');
				alert(this.config.messages.fieldsValidationPONumberLength);
				result = false;
			} else {
				jQuery('#wsqo-poNumber').removeClass('wsqo-required-alert');
			}
		}

		return result;
	},
	sendOrder: function() {
		if(!this.validateOrderFields()) {
			return;
		}

		var rows = [];
		var elements = jQuery('.wsqo-parts tbody tr');
		for(var i=0; i<elements.length; i++) {
			rows.push([
				jQuery(elements[i]).find('.wsqo-parts-list-partnumber input').val(),
				jQuery(elements[i]).find('.wsqo-parts-list-qty input').val()]
			);
		}

		this.showLoader('.wsqo-form');
		jQuery.ajax({
			url: this.config.sendOrderURL,
			data: {
				rows: rows,
				poNumber: jQuery('#wsqo-poNumber').val(),
				notes: jQuery('#wsqo-notes').val(),
			},
			dataType: 'json',
			type: 'POST',
			cache: false,
			success: this._sendOrderResponse.bind(this),
			complete: this.hideLoader.bind(this)
		});
	},
	_sendOrderResponse: function(response) {
		if(response.errorMessage) {
			alert(response.errorMessage);
			if(!response.html) return;
		}
		jQuery('.wsqo').html(response.html);
	}
});