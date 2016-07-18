var Informer = function() {return function($) { return {
	config: {
		overlay: {
			id: 'informerOverlay',
			css: {
				position: 'fixed',
				left: 0,
				top: 0,
				right: 0,
				bottom: 0,
				'background-color': '#aaa',
				opacity: 0.5
			},
			template: '<div id="%ID%"></div>'
		},
		messageBox: {
			id: 'informerMessageBox',
			css: {
				position: 'fixed',
				left: 0,
				top: 0,
				right: 0,
				bottom: 0,
				'z-index': 100,
				'text-align': 'center'
			},
			template: '<div id="%ID%" title="Click to close" onclick="informer.hide()"></div>'
		},
		messageContainer: {
			id: 'informerMessageContainer',
			css: {
				position: 'relative',
				top: '240px',
				display: 'inline-block',
				padding: '40px',
				'max-width': '600px',
				'min-width': '200px',
				border: '1px solid #aaa',
				'font-size': '20px',
				'background-color': '#fff',
				'text-transform': 'uppercase'
			},
			template: '<div id="%ID%"></div>'
		},
		timeoutInterval: 3000
	},
	state: {
		overlay: null,
		messageBox: null,
		messageContainer: null
	},
	init: function(config) {
		$.extend(true, this.config, config);

		var html = this.config.overlay.template
			.replace('%ID%', this.config.overlay.id);
		$('body').append(html);
		this.state.overlay = $('#' + this.config.overlay.id);
		this.state.overlay.css(this.config.overlay.css);

		html = this.config.messageBox.template
			.replace('%ID%', this.config.messageBox.id);
		$('body').append(html);
		this.state.messageBox = $('#' + this.config.messageBox.id);
		this.state.messageBox.css(this.config.messageBox.css);

		html = this.config.messageContainer.template
			.replace('%ID%', this.config.messageContainer.id);
		this.state.messageBox.html(html);
		this.state.messageContainer = $('#' + this.config.messageContainer.id);
		this.state.messageContainer.css(this.config.messageContainer.css);

		this.hide();
	},
	show: function(message) {
		this.state.overlay.show();
		this.state.messageContainer.html(message);
		this.state.messageBox.show();
		this.state.messageContainer.css('top', (this.state.messageBox.innerHeight() - this.state.messageContainer.outerHeight()) / 2);
	},
	appear: function(message, timeout) {
		this.show(message);
		if (typeof(timeout) == 'undefined') {
			timeout = this.config.timeoutInterval;
		}
		setTimeout(this.hide.bind(this), timeout);
	},
	hide: function() {
		this.state.messageBox.hide();
		this.state.overlay.hide();
	}
};}(jQuery);};

jQuery(document).ready(function() {
	informer = new Informer();
	informer.init({});
});