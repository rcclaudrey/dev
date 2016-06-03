var OEMGridEdit = new Class.create();

OEMGridEdit.prototype = {
	grid: null,
	tableId: null,
	table: null,
	config: {
		saveUrl: '',
		templates: {
			container: '<div class="oemGrid-inplace-edit">%EDIT%<div class="oemGrid-inplace-edit-btn-container">'
				+	'<button id="oemGrid_save" class="save oemgrid-inplace-edit-save">Save</button>'
				+	'<button id="oemGrid_cancel" class="back oemgrid-inplace-edit-cancel">Cancel</button>'
				+ '</div></div>',
			edit: {
				input: '<input value="%VALUE%" onfocus="this.select()" />',
				select: '<select value="">%OPTIONS%</select>'
			}
		}
	},
	initialize: function(gridObject, config) {
		Object.extend(this.config, config);
		this.config.saveUrl = this.config.saveUrl.replace('http:', location.protocol);
		gridObject.initCallback = this._initCallback.bind(this);
		this.grid = gridObject;
		this.tableId = this.grid.getContainerId() + '_table';
		this.table = $(this.tableId);
		this._initCallback();
	},
	editStart: function(event) {
		event.stop();
		var cell = event.target;
		if ('TD' !== cell.tagName.toUpperCase()) {
			cell = cell.up('td');
		}
		if (cell.hasOwnProperty('oldValue')) return;
		if (!this.table.down('colgroup col', cell.cellIndex).hasClassName('editable')) return;
		cell.oldValue = cell.innerHTML; // this is used to mark edited items
		var editorHtml = this.config.templates.container;
		var filterCell = this.table.down('.filter th', cell.cellIndex);
		var filterElement;
		var editType = 'input';
		if (filterElement = filterCell.down('input')) {
			var value = cell.innerHTML.replace('&nbsp;', '').trim();
			cell.innerHTML = editorHtml
				.replace('%EDIT%', this.config.templates.edit.input)
				.replace('%VALUE%', value);
		} else if (filterElement = filterCell.down('select')) {
			var textValue = cell.innerHTML.replace('&nbsp;', '').trim();
			var selectValue = null;
			for (var i = 0; i < filterElement.options.length; i++) {
				if (textValue == filterElement.options[i].text) {
					selectValue = filterElement.options[i].value;
					break;
				}
			}
			if (null !== selectValue) {
				cell.innerHTML = editorHtml.replace('%EDIT%', this.config.templates.edit.select);
				var select = cell.down('select');
				select.innerHTML = filterElement.innerHTML;
				select.value = selectValue;
				editType = 'select';
			} else console.log('cannot find option value');
		} else { // alert('Cannot detect field type');
			return; // just do nothing
		}
		if (filterElement) {
			if (!cell.id) {
				cell.id = cell.up('tr').attributes['rowId'].value + '-' + filterElement.name;
				cell.editType = editType;
				cell.fieldName = filterElement.name;
			}
			$$('#oemGrid_save').invoke('observe', 'click', this.editSave.bind(this));
			$$('#oemGrid_cancel').invoke('observe', 'click', this.editCancel.bind(this));
			$$('#' + cell.id + ' ' + editType).invoke('observe', 'keypress', this.editKeyPress.bind(this));
			$$('#' + cell.id + ' ' + editType).first().focus();
		}
	},
	editSave: function(event) {
		event.stop();
		var cell = event.target.up('td');
		var value;
		switch (cell.editType) {
			case 'input':
				value = cell.down('input').value;
				if (!this.validate(value, this.table.down('colgroup col', cell.cellIndex).classList)) {
//					alert('Value format is wrong!'); // the message will be shown at validate()
					return;
				}
				break;

			case 'select':
				value = cell.down('select').value;
				break;
		}
		var params = {
			rowId: cell.up('tr').attributes['rowId'].value,
			editType: cell.editType,
			colName: cell.fieldName,
			value: value
		};
		cell.addClassName('oemGrid-saving');
		new Ajax.Request(this.config.saveUrl, {
			loaderArea: cell.id,
			parameters: params,
			evalScripts: true,
			onFailure: this._editSaveFailure.bind(this),
			onSuccess: this._editSaveSuccess.bind(this)
		});
	},
	_editSaveSuccess: function(response) {
		if (response.responseText.isJSON()) {
			var res = response.responseText.evalJSON();
			if (res.error) {
				alert(res.message);
			}
			if(res.ajaxExpired && res.ajaxRedirect) {
				setLocation(res.ajaxRedirect);
			}
			var cell = $(res.id);
			cell.removeClassName('oemGrid-saving');
			delete cell.oldValue;
			cell.innerHTML = res.html;
		}
	},
	_editSaveFailure: function(response) {
		var cell = $(response.request.parameters.rowId + '-' + response.request.parameters.fieldName);
		cell.removeClassName('oemGrid-saving');
		cell.innerHTML = cell.oldValue;
		alert('Some error occurred. Please contact site administrator.\nError: '
			+ response.status + ' ' + response.statusText);
	},
	editCancel: function(event) {
		event.stop();
		var cell = event.target.up('td');
		cell.innerHTML = cell.oldValue;
		delete cell.oldValue;
	},
	editKeyPress: function(event) {
		var key = event.which || event.keyCode;
		switch (key) {
			case 13:
				this.editSave(event);
				break;

			case 27:
				this.editCancel(event);
				break;
		}
	},
	_initCallback: function(gridObject) {
		$$('#' + this.tableId + ' tbody td').invoke('observe', 'click', this.editStart.bind(this));
	},
	validate: function(value, classList) {
		var res = function(value, classList) {
			for(var i=0; i < classList.length; i++) {
				switch(classList[i]) {
					case 'required-entry':
						if (isNaN(numValue) && (numValue == 0) || !value.trim()) {
							return 'The value must not be empty!';
						}
						break;

					case 'validate-number':
						var numValue = parseNumber(value);
						if (isNaN(numValue)) {
							return 'The value must be a number!';
						}
						break;

					case 'validate-number-nz':
						var numValue = parseNumber(value);
						if (isNaN(numValue) || numValue == 0) {
							return 'The value must not a number and not zero!';
						}
						break;

					case 'validate-number-gz':
						var numValue = parseNumber(value);
						if (isNaN(numValue) || numValue <= 0) {
							return 'The value must be a number greater than zero!';
						}
						break;

					case 'validate-number-gez':
						var numValue = parseNumber(value);
						if (isNaN(numValue) || numValue < 0) {
							return 'The value must be a number greater than or equal to zero!';
						}
						break;

					case 'validate-int':
						var numValue = parseNumber(value);
						if (isNaN(numValue) || numValue != Math.floor(numValue)) {
							return 'The value must be an integer number!';
						}
						break;
				}
			}
			return true;
		}(value, classList);
		if (true !== res) {
			alert(res);
		    return false;
		} else {
			return true;
		}
	}
};
