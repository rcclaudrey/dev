var Loader = new Class.create({
	config: {
		elementId: 'loader',
		customCSS: {},
	},
	_shaders: [],
	init: function(configValues) {
		jQuery.extend(this.config, configValues);
	},
	_shaderExists: function(selector) {
		for(var i=0; i<this._shaders.length; i++) {
			if(this._shaders[i].selector == selector) {
				return i;
			}
		}
		return false;
	},
	_getFreeShader: function() {
		for(var i=0; i<this._shaders.length; i++) {
			if(!this._shaders[i].active) {
				return i;
			}
		}
		return this._createShader();
	},
	_createShader: function() {
		var newIndex = this._shaders.length;
		var newId = this.config.elementId +'-' + newIndex;
		jQuery('body').append('<div id="'+ newId + '" style="display:none">&nbsp;</div>');
		var loader = jQuery('#' + newId);
		loader.css({
			position: 'absolute',
			opacity: 0.5,
			'z-index': 100000,
			background: 'url() 50% 50% no-repeat scroll #111',
			// preventing appearing
			width: 0,
			height: 0,
			// repeating in style
			display: 'none'
		});
		loader.css(this.config.customCSS);
		this._shaders[newIndex] = {
			selector: '',
			loader: loader,
			active: false
		};
		return newIndex;
	},
	show: function(targetSelector) {
		var shaderIndex = this._shaderExists(targetSelector);
		if(false === shaderIndex) {
			shaderIndex = this._getFreeShader();
		}
		var target = jQuery(targetSelector);
		if(!target.length) return;
		this._shaders[shaderIndex].selector = targetSelector;
		this._shaders[shaderIndex].active = true;
		this._shaders[shaderIndex].loader.css({
			width: target.outerWidth(),
			height: target.outerHeight(),
			left: target.offset().left,
			top: target.offset().top,
			display: 'block'
		});
	},
	hide: function(targetSelector) {
		if(!arguments.length) {
			return this.hideAll();
		}
		var i = this._shaderExists(targetSelector);
		if(false !== i) {
			this._shaders[i].loader.css({display: 'none'});
			this._shaders[i].active = false;
			this._shaders[i].selector = '';
		}
	},
	hideAll: function() {
		for(var i=0; i<this._shaders.length; i++) {
			if(this._shaders[i].active) {
				this._shaders[i].loader.css({display: 'none'});
				this._shaders[i].active = false;
				this._shaders[i].selector = '';
			}
		}
	}
});


jQuery(document).ready(function() {
	loader = new Loader();
	loader.init({
		elementId: 'loader-ticker',
		customCSS: {
			'background-image': 'url("//www.tmsparts.com/skin/frontend/default/temecula/vk_fitment/images/spinner-o.gif")'
		}
	});
});
