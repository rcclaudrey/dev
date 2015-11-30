amconfAjax = Class.create();
amconfAjax.prototype =
{
    initialize : function(options) {
        this.options = options;
        this.loadContent();
    },

    loadContent : function()
    {
        var postData = 'ids' + '=' + this.options['ids'];
        new Ajax.Request(this.options['url'], {
            method: 'post',
            postBody: postData,
            onCreate: function () {}.bind(this),
            onComplete: function () {}.bind(this),
            onSuccess: function (transport) {
                if (transport.responseText.isJSON()) {
                    var response = transport.responseText.evalJSON();

                    response.each(function(item){
                        var id = item.id;
                        var selector = amconfAjaxObject.getPriceSelector(id);
                        $$(selector).each(function(priceBlock){
                            var price = priceBlock.parentNode;
                            var parent = price? price.parentNode: null;
                            if(parent.hasClassName('price-box')){
                                parent = parent.parentNode;
                            }
                            if(parent) {
								if( 0 == parent.select('.amconf-block').length){
                                    price.insert({after: item.html});
									try{
										item.html.evalScripts();
									}
									catch(exc){
										console.debug(exc);
									}

									var onclick = item.onclick;
									if(onclick && parent){
										var button = parent.select('button.btn-cart').first();
										if(button) {
											button.stopObserving('click');
											button.addClassName('amasty-conf-observe');
											Event.observe(button, 'click', function(){amastyConfButtonClick(this, id, onclick)});
										}
									}
								}
							}
                        })
                    })
                    $$('.amconf-block').each(function(block){
                        Effect.SlideDown(block);
                    });
                    amconfAjaxObject.external();
                }
            }
        });
    },

    getPriceSelector : function(id)
    {
        var selector =  'div.price-box [id=product-price-qq], ' +
            'div.special-price [id=product-price-qq], ' +
            'div.price-box [id=price-excluding-tax-qq], ' +
            'div.price-box [id=price-including-tax-qq], ' +
            'div.price-box [id=parent-product-price-qq], ' +
            'div.price-box [id=old-price-qq]';

        return selector.split('qq').join(id);
    },

    external : function()
    {
        if (typeof setGridItemsEqualHeight != 'undefined') {
            setTimeout('setGridItemsEqualHeight(jQuery)', 300);
            setTimeout('setGridItemsEqualHeight(jQuery)', 1000);
        }

        // venedor/default
        if (typeof products_grid_resize == 'function') {
            products_grid_resize();
        }
    }
}

var optionsPrice = [];
var confData = [];

function amastyConfButtonClick(element, id, onclick){
    if(typeof AmAjaxObj != 'undefined') {
        AmAjaxObj.sendAjax(id, '', "", element);
        return true;
    }

    var amastyBlock = $('amconf-block-' + id);
    if(amastyBlock){
        var valid = true;
        amastyBlock.select('select').each(function(select){
           if (!select.value || parseInt(select.value) <= 0){
                valid = false;
                inputValidation(select);
                return false;
           }
        });

        if(valid){
            var	data = jQuery('#amconf-block-' + id +' :input').serializeArray();
            var  form = document.createElement("form");
            var  node = document.createElement("input");

            form.action  = onclick;

            data.each(function(item) {
                node.name  = item.name;
                node.value = item.value;
                form.appendChild(node.cloneNode());
            });

            form.style.display = "none";
            form.setAttribute("method", "POST");
            document.body.appendChild(form);
            
            form.submit();
            form.remove();
        }

        return false;
    }
}

function inputValidation(input)
{
    if (input){
        input.parentNode.style.border = '1px dashed red';
        input.parentNode.style.margin = '0';
        $$('.dashed-red').each(function(elem){
           elem.removeClassName('dashed-red');
           elem.style.border = '0';
        });
        input.parentNode.addClassName('dashed-red');
         if(amRequaredField)
            $('requared-' + input.id).innerHTML = amRequaredField;
        $$('.required-field').each(function(elem){
           elem.removeClassName('required-field');
           elem.innerHTML = '';
        });
        $('requared-' + input.id).addClassName('required-field');
    }
}