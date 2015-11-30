var POSPermissions = {
    
    permissions : {},
    
    save : function() {
        new Ajax.Request(save_url, {
            method: 'post',
            parameters: {permissions : Object.toJSON(this.permissions)},
            onFailure: function() {
                alert('An error occurred while saving the data.');
            },
            onSuccess: function(response) {
                var data = response.responseText.evalJSON();
                if (typeof data != 'object')
                    alert('An error occurred while saving the data.');
            }
        });
    },
    
    reinit : function() {
        document.location.href = document.location.href;
    },
    
    update_permissions : function() {
        this.permissions = {};
        // update permissions from selected checkboxes
        $$('div.grid')[1].select('input[type=checkbox]').each(function(cbx) {
            if (cbx.checked && !cbx.disabled) {
                var user_id = cbx.id.split('_')[0];
                var store_id = cbx.id.split('_')[1];
                if (Object.isUndefined(this.permissions[user_id])) this.permissions[user_id] = new Array();
                this.permissions[user_id].push(store_id);
            }
        }.bind(this));
        this.update_debug("<b>Updated value</b>");
        this.update_debug(Object.toJSON(this.permissions));
    },
    
    update_debug : function(msg) {
        if ($('debug')) 
            $('debug').update(msg+"<br/>"+$('debug').innerHTML);
    }

};



document.observe('dom:loaded',function() {
    
    // update checkboxes with permissions stored in db
    if (permissions != '*')
        for (user_id in permissions) {
			if($(user_id+'_all')){
				var all = false;
				permissions[user_id].each(function(store) {
					if (store=="all") {
						all = true;
					} else {
						$(user_id+'_'+store).checked = true;
					}
				});
				if (all) {
					$(user_id+'_all').checked = true;
					var tr = $(user_id+'_all').up('tr');
					var children = tr.select('input.store_cbx');

					children.each(function(cbx) {
						cbx.checked = true;
						cbx.disabled = true;
					});
				}
			}
		}
    
    
    
    POSPermissions.update_permissions();
    
    
    $$('div.grid')[1].select('input[type=checkbox]').each(function(cbx) {
        
        
        // checkboxes observers
        cbx.observe('click',function(evt) {
            var id = evt.element().id;
            var checked = evt.element().checked;
            if (id.indexOf('all') != -1) { // click on all checkbox
                
                var tr = evt.element().up('tr');
                var children = tr.select('input.store_cbx');
                
                children.each(function(cbx) {
                    cbx.checked = checked;
                    cbx.disabled = checked;
                });
                
            }
            POSPermissions.update_permissions();
        });
        
    });
    
    
    
    
    
});