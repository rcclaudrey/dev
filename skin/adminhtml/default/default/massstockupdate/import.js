document.observe("dom:loaded", function() {

    file_path = $("file_path").value;
    sku_offset = $('sku_offset').value;
    custom_rules = $('custom_rules').value;
    $('import-preview').hide();
    $('import-error').hide()
    $('massstockupdate_import_form_mapping').observe('click', function() {
        if ($("file_path").value != file_path) {
            file_path = $("file_path").value;
            previewer();
        }
        if ($("sku_offset").value != sku_offset) {
            sku_offset = $("sku_offset").value;
            previewer();
        }
        if ($("custom_rules").value != custom_rules) {
            custom_rules = $("custom_rules").value;
            previewer();
        }
    });
    $('use_custom_rules').observe('change', function() {
        previewer();
    })
    $('file_enclosure').observe('change', function() {
        previewer();
    })
    $('file_separator').observe('change', function() {
        previewer();
    })
    $('auto_set_total').observe('change', function() {
        previewer();
    })
    $('auto_set_instock').observe('change', function() {
        previewer();
    });
    $('file_system_type').observe('change', function() {
        previewer();
    });
    $('use_sftp').observe('change', function() {
        previewer();
    });
    $('ftp_active').observe('change', function() {
        previewer();
    });
    $('ftp_host').observe('change', function() {
        previewer();
    });
    $('ftp_login').observe('change', function() {
        previewer();
    });
    $('ftp_password').observe('change', function() {
        previewer();
    });
    $('ftp_dir').observe('change', function() {
        previewer();
    });
    $('file_type').observe('change', function() {
        previewer();
    });
    $('xpath_to_product').observe('change', function() {
        previewer();
    });
    /*$('auto_set_managestock').observe('change', function(){
     previewer();
     })*/

    if (!$('cron_setting').value.isJSON())
        $('cron_setting').value = '{"days":[],"hours":[]}';
    cron = $('cron_setting').value.evalJSON();


    cron.days.each(function(d) {
        if ($('d-' + d)) {
            $('d-' + d).checked = true;
            $('d-' + d).ancestors()[0].addClassName('checked');
        }

    })
    cron.hours.each(function(h) {
        if ($('h-' + h.replace(':', ''))) {
            $('h-' + h.replace(':', '')).checked = true;
            $('h-' + h.replace(':', '')).ancestors()[0].addClassName('checked');
        }
    })

    $$('.cron-box').each(function(e) {
        e.observe('click', function() {

            if (e.checked)
                e.ancestors()[0].addClassName('checked');
            else
                e.ancestors()[0].removeClassName('checked');

            d = new Array
            $$('.cron-d-box INPUT').each(function(e) {
                if (e.checked)
                    d.push(e.value)
            })
            h = new Array;
            $$('.cron-h-box INPUT').each(function(e) {
                if (e.checked)
                    h.push(e.value)
            })

            $('cron_setting').value = Object.toJSON({
                days: d,
                hours: h
            })

        })
    })


    previewer();

})

var massstockupdate = {
    testFtp: function(url) {
        data = Form.serialize($$('FORM')[0], true);
        new Ajax.Request(url, {
            parameters: data,
            method: 'post',
            onSuccess: function(response) {
                alert(response.responseText);
            }
        });
    }
}

function udpateFileMapping() {

    mapping.columns = [];
    $('import-preview').select('SELECT').each(function(s) {
        if (s.selectedIndex > -1) {
            d = {};
            d.label = s.select('OPTION')[s.selectedIndex].innerHTML;
            d.value = s.value;
            d.id = s.select('OPTION')[s.selectedIndex].id;
        }

        mapping.columns.push(d);
    })

    $('mapping').value = Object.toJSON(mapping);


}

function simulateClick(control)
{
    if (document.all)
    {
        control.click();
    }
    else
    {
        var evObj = document.createEvent('MouseEvents');
        evObj.initMouseEvent('click', true, true, window, 1, 12, 345, 7, 220, false, false, true, false, 0, null);
        control.dispatchEvent(evObj);
    }
}

function previewer() {

    if (!$('mapping').value.isJSON())
        $('mapping').value = '{"columns":[]}';
    mapping = $('mapping').value.evalJSON();

    file = $('file_path').value;
    
    data = {};
    data.file = file;
    data.file_system_type = $('file_system_type').value;
    data.file_path = $('file_path').value;
    data.xpath = $('xpath_to_product').value;
    data.file_type = $('file_type').value;
    data.ftp_host = $('ftp_host').value;
    data.ftp_login = $('ftp_login').value;
    data.ftp_password = $('ftp_password').value;
    data.ftp_dir = $('ftp_dir').value;
    data.use_sftp = $('use_sftp').value;
    data.ftp_active = $('ftp_active').value;
    data.separator = $('file_separator').value;
    data.enclosure = $('file_enclosure').value;
    data.autoSetTotal = $('auto_set_total').value;
    data.autoSetInStock = $('auto_set_instock').value;
    data.useCustomRules = $('use_custom_rules').value;
    data.customRules = $('custom_rules').value;
    data.skuOffset = $('sku_offset').value;
    
    new Ajax.Request(url, {
        parameters: data,
        method: 'post',
        onSuccess: function(response) {


            data = response.responseText.evalJSON();
            if (data.status == "error") {
                $('import-preview').hide();
                $('import-error').show().update(data.body)

            }
            else {
                
                places = '';
                data.places.each(function(p) {
                    places += '<option class="' + p.style + '" value="' + p.value + '" id="' + p.id + '">' + p.label + '</option>';
                })
                head = '<tr>';
                head += '<th>'+$('identifier_code').options[$('identifier_code').selectedIndex].text+'</th>';
                i = 0;


                while (i < data.body[0].length - 2) {
                    if ($('use_custom_rules').value == 1)
                        Col = '$C[' + (i + 1) + "]";
                    else
                        Col = '';
                    head += '<th>' + Col + '<select class="update">';
                    head += places;
                    head += '</select></th>';
                    i++;
                }

                head += '<th>';
                head += "Global stock";
                head += '</th>';

                i++;

                head += '</tr>';
                $('import-preview').select('THEAD')[0].update(head)


                a = 1;
                $('import-preview').select('THEAD')[0].select('SELECT').each(function(s) {
                    found = false;
                    if (typeof mapping.columns[a - 1] != "undefined") {
                        i = 0;
                        data.places.each(function(p) {

                            if (p.value == mapping.columns[a - 1].value) {
                                s.selectedIndex = i
                                found = true;
                            }
                            ;
                            i++;
                        })

                    }
                    if (!found) {
                        if (a < data.places.length) {
                            s.selectedIndex = a - 1;
                        }
                        else {
                            s.selectedIndex = data.places.length - 1;
                        }
                    }
                    a++;

                })

                updateOutput();
                updateSelectColor();
                udpateFileMapping();

            }
            $$('.update').each(function(select) {
                select.observe('change', function() {
                    updatePlaces(select);
                    updateSelectColor();
                    updateOutput();


                })
            })
        }


    })

    function updateSelectColor() {
        $$('SELECT').each(function(s) {
            s.removeClassName('store');
            s.removeClassName('total')
            s.removeClassName('not-used')
            s.removeClassName('used')
            s.removeClassName('manage_stock')
            s.removeClassName('is_in_stock');
            s.addClassName(s.select('OPTION')[s.selectedIndex].className)
        })
    }
    function updatePlaces(s) {

        if (['not-used', 'is_in_stock', 'manage_stock', 'total', 'attribute'].indexOf(s.className) != -1 || s.value.match(/[0-9]+/) != null) {

            $$('.' + s.value).each(function(o) {

                if (o.ancestors()[0].value == s.value && o.ancestors()[0] != s)
                    o.ancestors()[0].selectedIndex = o.ancestors()[0].select('OPTION').length - 1;
                // o.hide();
            });
            //s.select('.'+s.value)[0].show();
        }
        udpateFileMapping();
    }
    function getUsed() {
        a = 1;
        used = [];
        $('import-preview').select('THEAD')[0].select('SELECT').each(function(s) {
            if (['not-used', 'is_in_stock', 'manage_stock', 'total', 'attribute'].indexOf(s.select('OPTION')[s.selectedIndex].className) == -1)
                used.push(a);
            a++;
        })
        a = 1;
        total = [];
        $('import-preview').select('THEAD')[0].select('SELECT').each(function(s) {
            if (['total'].indexOf(s.select('OPTION')[s.selectedIndex].value) != -1)
                total.push(a);
            a++;
        })
        a = 1;
        is_in_stock = [];
        $('import-preview').select('THEAD')[0].select('SELECT').each(function(s) {
            if (['is_in_stock'].indexOf(s.select('OPTION')[s.selectedIndex].value) != -1)
                is_in_stock.push(a);
            a++;
        })
//        console.log(used)

    }
    function updateOutput() {
        getUsed();
        body = '';
        data.body.each(function(tr) {
            if (tr[0] !== null) {
                if (tr[0] !== null) {

                    body += "<tr>";
                    f = 0;
                    qty = 0;
                    qtyinit = false
                    status = '';
                    statusinit = false
                    tr.each(function(td) {
                        td = "" + td;
                        if (f == 0)
                            classname = 'sku'
                        else
                            classname = '';
                        if ($('auto_set_total').value == 1) {

                            if (used.indexOf(f) >= 0) {
                                if (!isNaN(parseNumber(td))) {
                                    qty += parseNumber(td);
                                    qtyinit = true;
                                } else
                                    qty = "NaN";
                            }

                        }
                        else if ($('auto_set_total').value == 2) {

                            if (used.indexOf(f) >= 0) {
                                if (!isNaN(parseNumber(td))) {
                                    qty += parseNumber(td);
                                    qtyinit = true;
                                } else
                                    qty = "NaN";
                            }

                        }
                        else {

                            if (total.indexOf(f) >= 0 && !isNaN(parseNumber(td))) {
                                qty += parseNumber(td);
                                qtyinit = true;
                            }
                        }
                        if ($('auto_set_instock').value == 0) {
                            if (is_in_stock.indexOf(f) >= 0 && (['1', 'true', 'yes', 'on', 'in stock', 'instock', 'available'].indexOf(td.toLowerCase()) != -1)) {
                                status = ', <span class="instock">In Stock</span>';
                                statusinit = true;
                            }
                            else if (is_in_stock.indexOf(f) >= 0) {
                                status = ', <span class="outofstock">Out of Stock</span>';
                                statusinit = true;
                            }
                        }


                        if (f < tr.length - 1)
                            body += "<td class='" + classname + "'>" + td + "</td>"
                        else {

                            if (isNaN(qty) /*|| !used.length*/ || !qtyinit) {
                                qty = "Ignored";
                                classname = "ignored";
                                status = '';
                            }
                            else {
                                if ($('auto_set_instock').value == 1 && $('auto_set_total').value != 2) {

                                    if (qty > 0) {
                                        status = ', <span class="instock">In Stock</span>';
                                        statusinit = true;
                                    }
                                    else {
                                        status = ', <span class="outofstock">Out of Stock</span>';
                                        statusinit = true;
                                    }
                                }
                                if ($('auto_set_total').value == 2)
                                    add = '+';
                                else
                                    add = '';

                                if (qty > 1)
                                    qty = add + ' ' + qty + ' units';
                                else
                                    qty = add + ' ' + qty + ' unit';
                                classname = null;
                            }



                            body += "<td class='output " + classname + "' >" + qty + status + "</td>"

                        }
                        f++;
                    })

                    body += "</tr>"
                }
            }
        })



        $('import-preview').show();
        $('import-error').hide()
        $('import-preview').select('TBODY')[0].update(body);
    }

}