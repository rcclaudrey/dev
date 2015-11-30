function onUseImageProductClick(checked_ch){
    $$("[rel=use_image_from_product]").each(function(ch){
        if (ch.id.indexOf('__id__') === -1){ //ignore template checkbox 
            
            if (checked_ch.checked && ch != checked_ch)
                ch.checked = false;

            if (superProduct && typeof(superProduct.attributes) == 'object'){
                for(var indProd in superProduct.attributes){
                    var attributes = superProduct.attributes[indProd];
                    if (typeof(attributes) == 'object'){
                        if (ch.id.indexOf(attributes.html_id) !== -1) {
                            attributes.use_image_from_product = ch.checked ? "1" : "0";
                        }
                    }
                }

                superProduct.updateSaveInput();
            }
        }
    });
}

function checkUseImageProducts(ids){
    
     if (superProduct && typeof(superProduct.attributes) == 'object'){
        for (var indIds in ids){
            var id = ids[indIds];
                if (typeof(id) !== 'function'){
                for (var ind in superProduct.attributes){
                    var attribute = superProduct.attributes[ind];
                    if (attribute.id == id){
                        $('configurable__attribute_' + ind + '_use_image_from_product').checked = true;
                        attribute.use_image_from_product = true;
                    }
                }
            }
        }
        superProduct.updateSaveInput();
    }
}