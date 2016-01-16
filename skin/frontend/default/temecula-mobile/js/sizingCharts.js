var TableResizer = new Class.create({
	originalContent: [],
	modifiedContent: [],
	tables: [],
	init: function() {
		this.tables = jQuery('#product_tabs_sizing_charts_contents table.sizing_chart');
		for(var i=0; i < this.tables.length; i++) {
			this.originalContent[i] = this.tables[i].innerHTML;
		}
		jQuery(window).resize(this.resize.bind(this));
		this.resize();
	},
	resize: function() {
		var maxWidth = jQuery('.wrapper').outerWidth();

		for(var i=0; i < this.tables.length; i++) {
			if(this.modifiedContent.hasOwnProperty(i)) {
				this.tables[i].innerHTML = this.originalContent[i];
			}

			if(jQuery(this.tables[i]).outerWidth() > maxWidth) {
				if(this.modifiedContent.hasOwnProperty(i)) {
					this.tables[i].innerHTML = this.modifiedContent[i];
				} else {
					for(var row=0; row < this.tables[i].rows.length; row++) {
						for(var col=0; col < this.tables[i].rows[row].cells.length; col++) {
							this.tables[i].rows[row].cells[col].innerHTML =
								this.tables[i].rows[row].cells[col].innerHTML
									.replace('-', '&nbsp;- ')
									.replace(';', '; ')
									.replace(',', ', ');
						}
					}
					this.modifiedContent[i] = this.tables[i].innerHTML;
				}
			}
		}
	}
});


jQuery(document).ready(function () {
	tableResizer = new TableResizer();
	tableResizer.init();
});