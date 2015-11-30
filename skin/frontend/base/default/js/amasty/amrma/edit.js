/**
 * @copyright   Copyright (c) 2009-2014 Amasty (http://www.amasty.com)
 */ 
var amRmaCreate = Class.create({
    config: $H(),
    template: null,
    initialize: function(config) {
        var first = null;
        for (var ind in config){
            this.config.set(config[ind].id, config[ind]);
            if (!first)
                first = config[ind].id;
        }

        this.template = new Template($('template_container').outerHTML, /(^|.|\r|\n)({{(\w+)}})/);
        this.addRow(first)
    },
    getObjectFromHtml: function(data){
        var $tmpDiv = new Element("div");
        $tmpDiv.update(data);

        return $tmpDiv.children[0];
    },
    addRow: function(id, $after){
        var data = this.config.get(id);
        var date_template = this.template.evaluate({});
        var html = date_template.gsub('_index_', id)
        html = html.replace(/_index_/g, id);
        var $row = this.getObjectFromHtml(html);
        $row.setAttribute('data-id', id)

        if ($after){
            $after.insert({
                'after': $row
            });
        } else {
            $('products').insert($row)
        }

        $row.down('#item option[value=' + id + ']').setAttribute('selected', true);
        $row.down('#remaining_quantity_' + id).innerHTML = data.qty;
        $row.down('.validate-digits-range').addClassName('digits-range-0-'+data.qty)
        $row.show();

        if (data.type == 'bundle'){
            $row.down('#bundle_items').show();
            $row.down('#qty_requested_block_' + id).hide();
        }

        this.events($row);

    },
    deleteRow: function($row){
        $row.remove();
    },
    event: function($row, id, event, handler){
        var $els = $row.select("#" + id);
        for (var $ind in $els){
            var $el = $($els[$ind]);
            if (typeof($el) == 'object'){
                $el.observe(event, function(event, element){
                    if (!element) element = event.element();

                    return handler(event, element);
                });
            }

        }
        return $el;
    },
    events: function($row){
        this.event($row, "item", "change", function(event, element){
            var id = element.options[element.options.selectedIndex].getAttribute('value');

            var $after = $(element).up('#template_container');
            this.addRow(id, $after);
            this.deleteRow($after);

        }.bind(this));

        this.event($row, "bundle_checkbox", "click", function(event, element){
            var product_id = element.value;
            var item = this.config.get(product_id);
            var $bundle_qty = $(element).up("tr").down("#bundle_qty");

            if (item.qty > 0){
                $bundle_qty.removeAttribute('disabled');
            } else {
                $bundle_qty.addAttribute('disabled', 'disabled');
            }

            if (element.checked){
                $bundle_qty.show();
            } else {
                $bundle_qty.hide();
            }

        }.bind(this));

        this.event($row, "add_item", "click", function(event, element){
            var product_id = $(element).up('#template_container').getAttribute('data-id');
            this.addRow(product_id);

        }.bind(this));

        this.event($row, "remove_item", "click", function(event, element){
            if ($$('#products #template_container').length > 1){
                this.deleteRow($(element).up('#template_container'));
            }


        }.bind(this));     
    }
});