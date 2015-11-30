var enableAddAttributeValuesToProductLink = true;

AmConfigurableData = Class.create();
AmConfigurableData.prototype = 
{
    currentIsMain : "",
    optionProducts : null,
    optionDefault : new Array(),
    
    initialize : function(optionProducts)
    {
        this.optionProducts = optionProducts;
    },
    
    hasKey : function(key)
    {
        return ('undefined' != typeof(this.optionProducts[key]));
    },
    
    getData : function(key, param)
    {
        if (this.hasKey(key) && 'undefined' != typeof(this.optionProducts[key][param]))
        {
            return this.optionProducts[key][param];
        }
        return false;
    },
    
    saveDefault : function(param, data)
    {
        this.optionDefault['set'] = true;
        this.optionDefault[param] = data;
    },
    
    getDefault : function(param)
    {
        if ('undefined' != typeof(this.optionDefault[param]))
        {
            return this.optionDefault[param];
        }
        return false;
    }
}

prevNextSetting = [];
// extension Code End
Product.Config.prototype.initialize = function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        if (config.containerId) {
            this.settings   = $$('#' + config.containerId + ' ' + '.super-attribute-select' + '-' + config.productId);
        } else {
            this.settings   = $$('.super-attribute-select' + '-' + config.productId);
        }
     
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;
        
        // Set default values from config
        if (config.defaultValues) {
            this.values = config.defaultValues;
        }
        //hide all labels
         this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
             $('label-' + attributeId).hide();
         }.bind(this))
        
        
        // Overwrite defaults by inputs values if needed
        if (config.inputsInitialized) {
            this.values = {};
            this.settings.each(function(element) {
                if (element.value) {
                    var attributeId = element.id.replace(/[a-z]*/, '');
                    this.values[attributeId] = element.value;
                }
            }.bind(this));
        }
            
        // Put events to check select reloads 
        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            var pos = attributeId.indexOf('-');
            if ('-1' != pos)
                attributeId = attributeId.substring(0, pos);
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))
   //If Ajax Cart     
    if('undefined' != typeof(AmAjaxObj)) {
            var length = this.settings.length;
            for (var i = 0; i < length-1; i++) {
              var element = this.settings[i];
              if(element  && element.config){
                   for (var j = i+1; j < length; j++) {
                       var elementNext = this.settings[j];
                       if(elementNext  && elementNext.config && (elementNext.config['id'] == element.config['id'])){
                            this.settings.splice (i,1);
                            i--;
                            break;    
                       }    
                   }    
              }
            }    
         }  
            
        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){
            var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
            var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
            if (i == 0){
                this.fillSelect(this.settings[i])
            } else {
                this.settings[i].disabled = true;
            }
            $(this.settings[i]).childSettings = childSettings.clone();
            prevNextSetting[this.settings[i].config.id] = [prevSetting, nextSetting];
            var optionId = this.settings[i].id;
            var pos = optionId.indexOf('-');
            if ('-1' != pos){
                optionId = optionId.substring(pos+1, optionId.lenght);
                id = parseInt(optionId);
                prevNextSetting[id] = [];
                prevNextSetting[id][this.settings[i].config.id] = [prevSetting, nextSetting];
            }
            $(this.settings[i]).prevSetting   = prevSetting;
            $(this.settings[i]).nextSetting   = nextSetting;
            childSettings.push(this.settings[i]);
        }
        // Set values to inputs
        this.configureForValues();
        document.observe("dom:loaded", this.configureForValues.bind(this));
}


Product.Config.prototype.amconfCreateOptionImage = function(option, attributeId, key, holderDiv){
    var imgContainer = new Element('div', {
        'class': 'amconf-image-container',
        'id'   : 'amconf-images-container-' + option.id + '-' + this.config.productId
    });
    holderDiv.appendChild(imgContainer);

    var width  = parseInt(this.config.attributes[attributeId].config.cat_small_width)  ? parseInt(this.config.attributes[attributeId].config.cat_small_width): 25;
    var height = parseInt(this.config.attributes[attributeId].config.cat_small_height)  ? parseInt(this.config.attributes[attributeId].config.cat_small_height): 25;
    var useTooltip = this.config.attributes[attributeId].config && this.config.attributes[attributeId].config.cat_use_tooltip != "0" && 'undefined' != typeof(jQuery);

    if (option.color || !option.image) {
        var div = new Element('div', {
            'class': 'amconf-color-container',
            'id'   : 'amconf-image-' + option.id,
        });
        div.setStyle({
            width: width + 'px',
            height: height + 'px'
        });

        if(option.color){
            div.setStyle({background: '#' + option.color});
        }
        else{
            div.setStyle({lineHeight: height + 'px'});
            div.addClassName('amconf-noimage-div');
            div.insert(option.label);
        }
        imgContainer.appendChild(div);
        div.observe('click', this.configureImage.bind(this));

        if(useTooltip){
            amcontentPart = 'background: #' + option.color + '">';
        }
    }
    else {
        var div = new Element('img', {
            'src'   : option.image,
            'class' : "amconf-image",
            'id'    : 'amconf-image-' + option.id + '-' + this.config.productId,
            'alt'   : option.label,
            'title' : option.label,
            'width' : width,
            'height': height
        });

        div.observe('click', this.configureImage.bind(this));
        imgContainer.appendChild(div);

        if(useTooltip){
            amcontentPart = '"><img src="' + option.bigimage + '"/>'
        }
    }

    /*Add tooltip*/
    if(useTooltip){
        var tooltipWidth  = parseInt(this.config.attributes[attributeId].config.cat_big_width) ? parseInt(this.config.attributes[attributeId].config.cat_big_width) : 50;
        var tooltipHeight = parseInt(this.config.attributes[attributeId].config.cat_big_height)? parseInt(this.config.attributes[attributeId].config.cat_big_height): 50;
        switch (this.config.attributes[attributeId].config.cat_use_tooltip) {
            case "1":
                amcontent = '<div class="amtooltip-label">' + option.label + '</div>';
                break;
            case "2":
                amcontent = '<div class="amtooltip-img" style="width: ' + tooltipWidth + 'px; height:' + tooltipHeight + 'px; margin: 0 auto;' + amcontentPart + '</div>';
                break;
            case "3":
                amcontent = '<div class="amtooltip-img" style="width: ' + tooltipWidth + 'px; height:' + tooltipHeight + 'px; margin: 0 auto;' + amcontentPart + '</div>' +
                    '<div class="amtooltip-label">' +
                    option.label +
                    '</div>';
                break;
        }
        try{
            jQuery(div).tooltipster({
                content: jQuery(amcontent),
                theme: 'tooltipster-light',
                animation: 'grow',
                touchDevices: false,
                position: "top"
            });
        }
        catch(exc){
            console.debug(exc);
        }
    }

    /*Add out of stock cross line*/
    if( key.indexOf("," + option.id + ",") > 0 ){
        var keyOpt = key.substr(0, key.length - 1);
    }
    else{
        var keyOpt = key +  option.id;
    }
    if(typeof confData[this.config.productId] != 'undefined' && confData[this.config.productId].getData(keyOpt, 'not_is_in_stock')){
        var hr = new Element('hr', {
            'class'  : 'amconf-hr',
            'size'   : 4,
            'noshade'   : 'noshade',
        });
        div.addClassName('amconf-image-outofstock');

        var angle  = Math.atan(height/width);
        hr.setStyle({
            width     : Math.sqrt(width*width + height*height) + 1 + 'px',
            top       : height/2  + 'px',
            left      : -(Math.sqrt(width*width + height*height) - width)/2  + 'px',
            transform : "rotate(" + Math.floor(180-angle * 180/ Math.PI)+ "deg)"
        });

        imgContainer.appendChild(hr);
        hr.observe('click', this.configureHr.bind(this));
    }
}


Product.Config.prototype.fillSelect = function(element){
    var attributeId = element.id.replace(/[a-z]*/, '');
    var pos = attributeId.indexOf('-');
    if ('-1' != pos)
        attributeId = attributeId.substring(0, pos);
    var options = this.getAttributeOptions(attributeId);
    this.clearSelect(element);
    element.options[0] = new Option(this.config.chooseText, '');

    if('undefined' != typeof(AmTooltipsterObject)) {
        AmTooltipsterObject.load();
    }

    var prevConfig = false;
    if(element.prevSetting){
        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
    }
    if(options) {
        if (this.config.attributes[attributeId].use_image)
        {
            if ($('amconf-images-' + attributeId + '-' + this.config.productId))
            {
                $('amconf-images-' + attributeId + '-' + this.config.productId).remove();
            }
            holder = element.parentNode;
            $('label-' + attributeId + '-' + this.config.productId).show();
            var holderDiv = document.createElement('div');
            holderDiv = $(holderDiv); // fix for IE
            holderDiv.addClassName('amconf-images-container');
            holderDiv.id = 'amconf-images-' + attributeId + '-' + this.config.productId;
            holder.insertBefore(holderDiv, element);
        }
        
        var index = 1, key = '';
        this.settings.each(function(select, ch){
            // will check if we need to reload product information when the first attribute selected
            if (parseInt(select.value))
            {
                key += select.value + ',';   
            }
        });
        for(var i=0;i<options.length;i++){
            var allowedProducts = [];
            if(prevConfig) {
                for(var j=0;j<options[i].products.length;j++){
                    if(prevConfig.config && prevConfig.config.allowedProducts
                       && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                    }
                }
            }
            else {
                allowedProducts = options[i].products.clone();
            }

            if(allowedProducts.size()>0)
            {
                if (this.config.attributes[attributeId].use_image)
                {
                    this.amconfCreateOptionImage(options[i], attributeId, key, holderDiv);
                }
                
                options[i].allowedProducts = allowedProducts;
                element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                element.options[index].config = options[i];
                index++;
            }
        }
        if(index > 1 && this.config.attributes[attributeId].use_image) {
            var amcart  = document.createElement('div');
            amcart = $(amcart); // fix for IE
            amcart.id = 'amconf-amcart-' + this.config.productId;
            holderDiv.appendChild(amcart);
        }
        if(this.config.attributes[attributeId].use_image) {
            var lastContainer = document.createElement('div');
            lastContainer = $(lastContainer); // fix for IE
            lastContainer.setStyle({clear : 'both'});
            holderDiv.appendChild(lastContainer);    
        }
    }
}
Product.Config.prototype.configureHr = function(event){
    var element = Event.element(event);
    element.nextSibling.click();
}

Product.Config.prototype.configureImage = function(event){
    var image = Event.element(event);

    var attributeId = image.parentNode.parentNode.id.replace(/[a-z-]*/, '');
    var optionId = image.id.replace(/[a-z-]*/, '');
    var pos = optionId.indexOf('-');
    if ('-1' != pos)
        optionId = optionId.substring(0, pos);

    /* compatibility with ajax cart*/
    var attribute = $$('#messageBox #attribute' + attributeId);
    if(attribute.length == 0)  attribute = $$('#attribute' + attributeId);
    attribute.each(function(select){
        select.value = optionId;
    });

    this.configureElement(attribute.first());
    this.selectImage(image);
    //jQuery( '#attribute' + attributeId).trigger( "onchange" );
}

Product.Config.prototype.selectImage = function(image)
{
    var attributeId = image.parentNode.parentNode.id.replace(/[a-z-]*/, '');
    $('amconf-images-' + attributeId).childElements().each(function(child){
        child.childElements().each(function(children){
            children.removeClassName('amconf-image-selected');
        });
    });
    image.addClassName('amconf-image-selected');
}

Product.Config.prototype.configureElement = function(element) 
{
    var me = this;
    var optionId = element.value;
    this.reloadOptionLabels(element);

    if(element.value){
        if (element.config.id){
            this.state[element.config.id] = element.value;
        }
        var elId = element.id;
        var pos = elId.indexOf('-');
        if ('-1' != pos){
            elId = elId.substring(pos+1, elId.lenght);
            elId = 	parseInt(elId);
            if(prevNextSetting[elId] && prevNextSetting[elId][element.config.id] && prevNextSetting[elId][element.config.id][1] || element.nextSetting){
                if(prevNextSetting[elId] && prevNextSetting[elId][element.config.id] && prevNextSetting[elId][element.config.id][1]){
                    element.nextSetting = prevNextSetting[elId][element.config.id][1]
                }
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
    }
    else {
        this.resetChildren(element);
    }

    this.reloadProductInfo(element);
}

Product.Config.prototype.reloadProductInfo = function(element){
    if ('undefined' == typeof(confData)) {
        this.reloadPrice();
        return true;
    }

    var attributeId = element.id.replace(/[a-z-]*/, '');
    var pos = attributeId.indexOf('-');
    if ('-1' == pos) return false;

    var parentId = attributeId.substring(pos+1, attributeId.length);
    var attributeId = attributeId.substring(0, pos);
    var optionId = element.value;

    var key = '', stock = 1;
    this.settings.each(function(select){
        if (parseInt(select.value)) {
            key += select.value + ',';
            if('undefined' != typeof(confData) && confData[parentId] && confData[parentId].getData(key.substr(0, key.length - 1), 'not_is_in_stock')) {
                stock = 0;
            }
        }
    });
    key = key.substr(0, key.length - 1);

    try{
        if(stock === 0){
            jQuery(element).closest('div.amconf-block').next("div.actions").hide();
        }
        else{
            jQuery(element).closest('div.amconf-block').next("div.actions").show();
        }
    }
    catch(exc){}
    /*
    * reload product image
    * */
    if('undefined' != typeof(confData[parentId]['optionProducts'][key]) && 'undefined' != typeof(confData[parentId]['optionProducts'][key]['small_image'])){
        var parUrl = confData[parentId]['optionProducts'][key]['parent_image'];
        var possl = parUrl.lastIndexOf('/');
        element.up("li.item").select('.product-image img[src*="' + parUrl.substr(possl, parUrl.length) + '"], .product-image img.amconf-parent-' + parentId).each(function (img) {
            img.src = confData[parentId]['optionProducts'][key]['small_image'];
            img.addClassName('amconf-parent-'+parentId);
        });
    }

    if ('undefined' != typeof(confData[parentId]) && confData[parentId].optionProducts.useSimplePrice == "1") {
        this.reloadSimplePrice(parentId, key);
    }
    else {
        this.reloadPrice();
    }
    //reload links
    if(enableAddAttributeValuesToProductLink && optionId){
       if(typeof confData[parentId] != 'undefined' && typeof confData[parentId].optionProducts.url != 'undefined') {

            var url = confData[parentId].optionProducts.url;
            $$('a[href*="' + url + '"]').each(function (link) {
                var href = link.href;
                if (href.indexOf(attributeId + '=') >= 0) {
                    var replaceText = new RegExp(attributeId + '=' + '\\d+');
                    href = href.replace(replaceText, attributeId + '=' + optionId)
                    link.href = href;
                }
                else {
                    if (href.indexOf('#') >= 0) {
                        link.href = href + '&' + attributeId + '=' + optionId;
                    }
                    else {
                        link.href = href + '#' + attributeId + '=' + optionId;
                    }
                }

            });
        }
    }
}
/*
* start price functionality
*/
Product.Config.prototype.reloadPrice = function(){
        if (this.config.disablePriceReload || optionsPrice[this.config.productId] == undefined) {
            return;
        }
        var price    = 0;
        var oldPrice = 0;
        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config){
                price    += parseFloat(selected.config.price);
                oldPrice += parseFloat(selected.config.oldPrice);
            }
        }

        optionsPrice[this.config.productId].changePrice('config', {'price': price, 'oldPrice': oldPrice});
        optionsPrice[this.config.productId].reload();
        return price;

        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
}

Product.Config.prototype.reloadSimplePrice = function(parentId, key)
{
    if ('undefined' == typeof(confData) || 'undefined' == typeof(confData[parentId]['optionProducts'])
        || 'undefined' == typeof(confData[parentId]['optionProducts'][key])
        || 'undefined' == typeof(confData[parentId]['optionProducts'][key]['price_html']))
    {
        return false;
    }

    var result = false;
    var childConf = confData[parentId]['optionProducts'][key];

    result = childConf['price_html'];

    var elmExpr = '.price-box';// span#product-price-'+parentId+' span.price';
    $$(elmExpr).each(function(container)
    {
        if(container.select('#product-price-'+parentId) != 0 || container.select('#parent-product-price-'+parentId) != 0) {
            var tmp = document.createElement('div');
            tmp = $(tmp); // fix for IE
            tmp.style.display = "none";
            tmp.innerHTML = result;
            container.appendChild(tmp);

            var parent = document.createElement('div');
            parent = $(parent); // fix for IE
            parent.id = 'parent-product-price-'+parentId;
            var tmp1 = tmp.childElements()[0];
            tmp1.appendChild(parent);

            container.innerHTML = tmp1.innerHTML;
        }
    }.bind(this));

    return result; // actually the return value is never used
}
/*
 * end price functionality
 */
Event.observe(window, 'load', function(){
     imageObj = new Image();
     for ( keyVar in confData ) {
         if( parseInt(keyVar) > 0){
             for ( keyImg in confData[keyVar]['optionProducts'] ) {
                 var path = confData[keyVar]['optionProducts'][keyImg]['small_image'];
                 if(path && 'undefined' != typeof(path)) {
                     imageObj.src = path;
                 }
             }
         } 
     }
});

document.observe("dom:loaded", function() {
    amconfAddButtonEvent();
})

amconfAddButtonEvent = function(){
    $$('.amconf-block').each(function(element){
        var id = element.id.replace(/[^\d]/gi, '');
        if(id && confData[id] && confData[id].optionProducts.onclick){
            var onclick = confData[id].optionProducts.onclick;
            var parent = element.up('.item');
            if(onclick && parent){
                var button = parent.select('button.btn-cart').first();
                button = $(button);
                if(button) {
                    button.stopObserving('click');
                    button.removeAttribute("onclick")
                    button.addClassName('amasty-conf-observe');
                    Event.observe(button, 'click', function(){amastyConfButtonClick(this, id, onclick)});
                }


            }
        }
    }.bind(this))
}