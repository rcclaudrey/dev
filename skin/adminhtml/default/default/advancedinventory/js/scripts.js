var trees = new Array();
var InventoryManager = {
    debug: false,
    log: function(method, arguments) {
        console.log("InventoryManager." + method + "()", arguments);
    },
    apply: function() {
        if (this.debug)
            this.log('apply', arguments);
    },
    saveStocks: function(url, id) {
        if (InventoryManager.debug)
            InventoryManager.log('saveStocks', arguments);
        ids = new Array;
        if (id != "all") {
            ids.push(id);
        }
        else {
            $$('.GlobalQty').each(function(g) {
                ids.push(g.id.replace("GlobalQty_", ""));
            })
        }

        inventory = {};
        ids.each(function(id) {
            tr = InventoryManager.getRow(id)

            status = $('GlobalQty_' + id).readAttribute('multistock');
            
            if (tr.select('INPUT.StockStatus').length)
                is_in_stock = tr.select('INPUT.StockStatus')[0].checked;
            else
                is_in_stock = false;
            if (status === 'enabled') {
                pos_wh = {};
                qty = null;



                tr.select(".PosQty").each(function(i) {

                    data = i.id.split('_');
                    if (typeof i.select('INPUT')[0] != "undefined") {
                        pos_wh[data[2]] = {qty: i.select('INPUT')[0].value}
                    }
                })

                inventory[id] = {multistock: true, pos_wh: pos_wh, qty: null, is_in_stock: is_in_stock};

            }
            else
                inventory[id] = {multistock: false, pos_wh: null, qty: tr.select('.GlobalQty INPUT')[0].value, is_in_stock: is_in_stock};

        })


        new Ajax.Request(url, {
            method: 'post',
            parameters: {data: Object.toJSON(inventory)},
            onFailure: function() {
                alert('An error occurred while saving the data.');
            },
            onSuccess: function(response) {
                data = response.responseText.evalJSON();

                if (data.error == 'true')
                    alert("Error : " + data.message);

            }
        });

        if (id != "all")
            this.resetAction(id);
    },
    updateQty: function() {
        if (this.debug)
            this.log('updateQty', arguments);
        qty = 0;
        $$('#advancedinventory_stocks TABLE TR DIV[visibility!=hidden] INPUT.validate-number').each(function(i) {
            qty += parseInt(i.value)
        })
        $('inventory_qty').value = qty;
    },
    updateStocks: function(e) {
        if (this.debug)
            this.log('updateStocks', arguments);

        if (e.ancestors()[0].hasClassName("PosQty")) {
            qty = 0;
            e.ancestors()[2].select(".PosQty INPUT").each(function(i) {
                qty += Math.round(i.value);
            })
            e.ancestors()[2].select('.GlobalQty')[0].innerHTML = qty
        }

    },
    updateRemainingStock: function(e) {
        qty = parseInt(e.ancestors()[1].readAttribute("qty"));
        total = 0;
        e.ancestors()[1].select('INPUT').each(function(i) {
            total += parseInt(i.value);
        })
        origin = parseInt(e.readAttribute('origin'));
        if ((total > qty || isNaN(e.value)) && arguments[1] != false) {
            e.value = origin;
        }


        //if (e.value != origin) {
        if (e.value < 0 || e.value == '')
            e.value = 0;
        else if (!e.ancestors()[0].previous().hasClassName('multistock_disabled')) {

            maximum = parseInt(e.readAttribute('maximum'));
            if (e.value <= maximum || e.ancestors()[0].previous().hasClassName('backorder'))
                e.ancestors()[0].previous().innerHTML = maximum - e.value;
            else {
                e.value = maximum;
                e.ancestors()[0].previous().innerHTML = 0;
            }

            e.writeAttribute('origin', e.value);
        }
        if (e.value > 0)
            e.addClassName('valid');
        else
            e.removeClassName('valid');

        //}
        this.validateStockData()
    },
    validateStockData: function() {
        if (this.debug)
            this.log('validateStockData', arguments);

        $$('.ai-stock-inner TBODY TR').each(function(tr) {
            if (tr.select('INPUT').length > 0) {
                total = 0;
                qty = tr.readAttribute("qty");
                tr.select('INPUT').each(function(i) {
                    total += parseInt(i.value);
                })

                if (total != qty) {
                    tr.addClassName('alert')

                }
                else {
                    tr.removeClassName('alert')

                }
            }
        })
        if (this.locked) {
            if ($$('.ai-stock-inner TBODY TR.alert').length > 0) {
                $('assignation_button').addClassName('disabled');
                $('assignation_button').disabled = true;
            }
            else {
                $('assignation_button').removeClassName('disabled');
                $('assignation_button').disabled = false;
            }
        }

        inventory = {};
        $$('.ai-stock-inner TBODY TR').each(function(tr) {
            eval("inventory[" + tr.id.replace('product_', '') + "]={};")
            tr.select('INPUT').each(function(e) {
                eval(e.name + "=" + e.value);
            })
        })
        $('assignation_to_json').value = Object.toJSON(inventory);



    },
    setAssignation: function() {
        if (this.debug)
            this.log('assignation', arguments);
        new Ajax.Request($('assignation_action').value, {
            method: 'post',
            parameters: {data: $('assignation_to_json').value, data_origin: $('assignation_to_json_origin').value, order_id: $('order_id').value},
            onFailure: function() {
                alert('An error occurred!');
            },
            onSuccess: function(response) {

                $('assignation_to_json_origin').value = $('assignation_to_json').value;
                $('order_summary_' + $('order_id').value).replace(response.responseText);
            }
        });
    },
    enableMultiStock: function(type, from) {
        if (this.debug)
            this.log('enableMultiStock', arguments);
        switch (type) {
            case "inventory":
                if (from.value === '0') {
                    $('inventory_qty').removeClassName("disabled");
                    $('inventory_qty').disabled = false;
                    $("backorders_settings").show();
                }
                else {
                    $('inventory_qty').addClassName("disabled");
                    $('inventory_qty').disabled = true;
                    $("backorders_settings").hide();
                }

                i = 0
                $$('#advancedinventory_stocks TABLE TR').each(function(tr) {
                    if (i)
                        tr.setStyle({display: (from.value === '0') ? 'none' : 'block'});

                    i++;
                })
                break;
            case "grid":

                status = $('GlobalQty_' + from).readAttribute('multistock');
                if (status === 'enabled') {
                    msg = this.enableMsg;

                    $('GlobalQty_' + from).update("<input class='keydown inventory_input' type='text' value='" + $('GlobalQty_' + from).innerHTML + "' />");
                    $('GlobalQty_' + from).writeAttribute('multistock', 'disabled')
                    this.getRow(from).select('.PosQty').each(function(i) {
                        i.update("-")
                    })
                }
                else {
                    msg = this.disableMsg;
                    qty = $('GlobalQty_' + from).select('INPUT')[0].value;
                    $('GlobalQty_' + from).update(qty)
                    $('GlobalQty_' + from).writeAttribute('multistock', 'enabled')
                    g = 0;
                    this.getRow(from).select('.PosQty').each(function(i) {
                        if (g == 0)
                            q = qty;
                        else
                            q = 0;
                        i.update("<input class='keydown inventory_input' value='" + q + "' / >")
                        g++;
                    })
                }
                this.getRow(from).select('OPTION[selected]')[0].update(msg)
                this.resetAction(from);
                break;
        }
    },
    disableMultiStock: function() {
        if (this.debug)
            this.log('enableMultiStock', arguments);
    },
    evalEvent: function(elt, event) {
        if (this.debug)
            this.log('evalEvent', arguments);
        eval(elt.readAttribute(event).replace('this', "elt"))
    },
    keydown: function(e) {
        if (this.debug)
            this.log('keydown', arguments);

        if (e.keyCode == 38) {
            e.findElement('INPUT').value = parseNumber(e.findElement('INPUT').value) + 1;

        }
        if (e.keyCode == 40) {
            e.findElement('INPUT').value = parseNumber(e.findElement('INPUT').value) - 1;

        }
        if (e.keyCode == 13) {
            ;
            if (e.findElement('TR').select('SELECT.action-select OPTION').length)
                action = e.findElement('TR').select('SELECT.action-select OPTION')[1].value.evalJSON();
            else
                action = e.findElement('TR').select('#save')[0];
            eval(action.href.replace('javascript:', ''))

        }

    },
    showDetails: function(elt) {
        if (this.debug)
            this.log('showDetails', arguments);
        elt.next().setStyle({"display": (elt.value === "1" ? "block" : "none")});
        elt.next().writeAttribute('visibility', (elt.value === '0') ? 'hidden' : 'visible');
        InventoryManager.updateQty()
    },
    resetAction: function(id) {
        if (this.debug)
            this.log('resetAction', arguments);
        action = this.getRow(id);
        if (action.select('SELECT').length)
            action.select('SELECT')[0].selectedIndex = 0;

    },
    getRow: function(id) {
        if (this.debug)
            this.log('getRow', arguments);
        return $('GlobalQty_' + id).ancestors()[1]
    },
    changeAssignation: function(id, store_id, url) {
        if (this.debug)
            this.log('changeAssignation', arguments);
        new Ajax.Request(url, {
            method: 'get',
            parameters: {order_id: id},
            onFailure: function() {
                alert('An error occurred!');
            },
            onSuccess: function(response) {
                data = response.responseText.evalJSON();

                if (data.error == 'true')
                    alert("Error : " + data.content);
                else {
                    overlay = Builder.node('DIV', {id: 'ai-overlay'}, Builder.node("DIV", {id: 'ai-details'}))

                    $$('.hor-scroll')[0].insert({bottom: overlay});
                    $('ai-details').update(data.content);
                }
            }
        });
    },
    closePopup: function() {
        $('ai-overlay').remove();
    }

}
document.observe('dom:loaded', function() {
    /* ONLOAD :: UPDATE MASS ACTION BUTTON*/
    //if (typeof $$('#stocksGrid_massaction-form BUTTON')[0] != "undefined")
    //  $$('#stocksGrid_massaction-form BUTTON')[0].setAttribute('onclick', "InventoryManager.apply()");

    /*ONKEYDOWN :: INPUT.keydown */
    document.observe('keydown', function(e) {
        if (e.findElement('INPUT.keydown')) {
            InventoryManager.keydown(e)
            if (e.findElement('INPUT').readAttribute('onChange'))
                InventoryManager.evalEvent(e.findElement('INPUT'), 'onChange');
        }
    })
    document.observe("change", function(e) {
        if (e.findElement('INPUT.inventory_input')) {
            i = e.findElement('INPUT.inventory_input');
            if (isNaN(i.value)) {
                i.value = 0;
            }
            InventoryManager.updateStocks(e.findElement('INPUT.inventory_input'))

        }


    })
    document.observe("keydown", function(e) {
        if (e.findElement('INPUT.inventory_input')) {
            InventoryManager.updateStocks(e.findElement('INPUT.inventory_input'))
        }
    });

    document.observe("click", function(e) {
        if (e.findElement('INPUT.choose_assignation')) {
            radio = e.findElement('INPUT.choose_assignation');
            i = 0;

            $$('#ai-scroller TABLE TR').each(function(tr) {

                if (i) {
                    qty = tr.readAttribute('qty')
                    ii = 0;
                    tr.select("INPUT.keydown").each(function(input) {
                        maximum = input.readAttribute('maximum');
                        if (ii == radio.value) {
                            if (input.ancestors()[0].previous().hasClassName("backorder")) {
                                input.value = qty;
                            }
                            else if (parseInt(qty) > parseInt(maximum)) {
                                input.value = maximum;
                            }
                            else {
                                input.value = qty;
                            }
                        }
                        else {
                            input.value = 0;
                        }

                        ii++;
                    });
                }
                i++;
            })
            $$('#ai-scroller TABLE TR').each(function(tr) {
                tr.select("INPUT.keydown").each(function(input) {
                    console.log(input)
                    InventoryManager.updateRemainingStock(input, false)
                })
            })


        }
    });

    function TafelTreeInit() {
        trees.each(function(tree) {
            return tree;
        })
    }

    if ($("content"))
        $("ai-scroller").setStyle({'width': $("content").getWidth() - 380 + "px"});

});

 