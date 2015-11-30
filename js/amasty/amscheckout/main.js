/**
* @author Amasty Team
* @copyright Copyright
* @package Amasty_Scheckout
*/ 

var amScheckout = new Class.create();

amScheckout.prototype = {
    _eventsHanders: {
        inputKeyUpHandler: function(){
            var areaContent = this.up("#area_content");
            var fieldRow = this.up("#field_row");
            var fieldKey = this.id;
            
            var areaId, fieldId;
            
            if (areaContent)
                areaId = areaContent.getAttribute("area_id");
            
            if (fieldRow)
                fieldId = fieldRow.getAttribute("field_id");
            
            this._caller._eventsHanders.onChangeValueHandler.call(this._caller, this.value, fieldKey, areaId, fieldId)
        },
        onChangeValueHandler: function(value, fieldKey, areaId, fieldId){
            if (fieldId && areaId){
                if (!this.saveData['field_' + fieldId])
                    this.saveData['field_' + fieldId] = {
                            'field_id': fieldId
                        };
                if (this.defData['field_' + fieldId][fieldKey] != value){
                    this.saveData['field_' + fieldId][fieldKey] = value;
                } else if (this.saveData['field_' + fieldId][fieldKey]){
                    delete this.saveData['field_' + fieldId][fieldKey];
                }

            } else if (areaId) {
                if (!this.saveData['area_' + areaId])
                    this.saveData['area_' + areaId] = {
                            'area_id': areaId
                        };
                if (this.defData['area_' + areaId][fieldKey] != value){
                    this.saveData['area_' + areaId][fieldKey] = value;
                } else if (this.saveData['area_' + areaId][fieldKey]){
                    delete this.saveData['area_' + areaId][fieldKey];
                }
            }
        }
    },
    defData: {},
    saveData: {},
    sliders: {},
    initDefData: function(){
        var defData = this.defData;
        
        this.tabsContainer.select("#area_content").each(function(areaContent){
            var areaId = areaContent.getAttribute("area_id");
            var useDefault = areaContent.select("[rel=area_use_default]")[0];
            
            if (!defData['area_' + areaId]){
                defData['area_' + areaId] = {
                    'area_id': areaId,
                    'fields': {},
                    'area_label': areaContent.select('#area_label')[0].value,
                    'use_default': useDefault ? (useDefault.checked ? "1" : "0") : null
                };
            }
        
            areaContent.select("#field_row").each(function(fieldRow){
                var fieldId = fieldRow.getAttribute("field_id");
                var useDefault = fieldRow.select("[rel=use_default]")[0];
                
                defData['field_' + fieldId] = {
                    'area_id': areaId, 
                    'field_id': fieldId,
                    'field_key': fieldRow.select("#field_key")[0].value,
                    'field_label': fieldRow.select("#field_label")[0].value,
                    'column_position': fieldRow.select("#column_position")[0].value,
                    'field_required': fieldRow.select("#field_required")[0].checked ? "1" : "0",
                    'field_order': fieldRow.select("#field_order")[0].value,
                    'field_disabled': fieldRow.select("#field_disabled")[0].value,
                    'use_default': useDefault ? (useDefault.checked ? "1" : "0") : null
                }
            })            
        });
    },
    initDrag: function(){
        var _caller = this;
        this.tabsContainer.select("#field_row").each(function(row){
            
            new Draggable(row, {
                
//                ghosting: true
            });
            
            Droppables.add(
                row,
                {
                    hoverclass: 'am_hover_active',
                    
                    onDrop: function( draggable,droparea){
                        draggable.parentNode.removeChild(draggable);
                        
                        _caller.changeOrder(draggable, droparea);

                        if (droparea.nextSiblings().length > 0)
                            droparea.parentNode.insertBefore(draggable, droparea.nextSibling);
                        else
                            droparea.parentNode.appendChild(draggable);
                        
//                        draggable.setStyle({
//                            'position': ''
//                        });
                     }
                }
            );
                
        });
    },
    initColumnPositionSlider: function(){
        var _caller = this;
        for (var key in this.defData){
            if (key.indexOf("field_") != -1){
                var fieldId = this.defData[key].field_id;
                
                var changeHandler = function(fieldId, v){
                    
                    var row = _caller.tabsContainer.down('#field_row[field_id="' + fieldId + '"]');
                    var input = row.down('input#column_position');
                    
                    input.value = v;
                    
                    _caller._eventsHanders.inputKeyUpHandler.call(input);
                }
                
                var row = _caller.tabsContainer.down('#field_row[field_id="' + fieldId + '"]');
                var input = row.down('input#column_position');
                
                _caller.sliders[fieldId] = new Control.Slider('field_handle_' + fieldId, 'field_track_' + fieldId, {
                    _field_id: fieldId,
                    range: $R(10, 100),
                    values: [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                    sliderValue: input.value, 
                    onSlide: function(v){
                       changeHandler(this._field_id, v);
                    },
                    onChange: function(v){
                        changeHandler(this._field_id, v);
                    }
                })
                var useDefault = $('use_default_' + fieldId);
                
                if (useDefault){

                    if (useDefault.checked)
                        _caller.sliders[fieldId].setDisabled();
                    else
                        _caller.sliders[fieldId].setEnabled();
                    
                    
                }
                    
            }
        }
    },
    changeOrder: function(draggable, droparea){
        
        var draggableFieldId = draggable.getAttribute('field_id');
        var draggableOrder = 0;
        
        var nextDropAreaItem = droparea.nextSiblings()[0];
        var areaContent = droparea.up("#area_content");
        
        if (!nextDropAreaItem){
             var areaId = areaContent.getAttribute('area_id');
             
             draggableOrder = this.getMaxFieldOrder(areaId) + 100;
        } else {
            var droppableFieldId = droparea.getAttribute('field_id');
            var nextDroppableFieldId = nextDropAreaItem.getAttribute('field_id');
            
            var droppableOrder = this.getFieldValue(droppableFieldId, 'field_order');
            var nextDroppableOrder = this.getFieldValue(nextDroppableFieldId, 'field_order');
            
            draggableOrder = (parseInt(nextDroppableOrder) - parseInt(droppableOrder)) / 2 + parseInt(droppableOrder);
        }
        
        
        this.addSaveFieldItem(draggableFieldId, 'field_order', draggableOrder);
        
    },
    initEvents: function(){
        var saveData = this.saveData;
        var _caller = this;

        var chKeyUpHandler = function(){
            var areaContent = this.up("#area_content");
            var fieldRow = this.up("#field_row");
            var fieldKey = this.id;
            
             var areaId, fieldId;
            
            if (areaContent)
                areaId = areaContent.getAttribute("area_id");
            
            if (fieldRow)
                fieldId = fieldRow.getAttribute("field_id");
            
            _caller._eventsHanders.onChangeValueHandler.call(_caller, this.checked ? "1" : "0", fieldKey, areaId, fieldId)
        }
        
        this.tabsContainer.select("input#area_label").each(function(input){
            input._caller = _caller;
            input.observe('keyup', _caller._eventsHanders.inputKeyUpHandler);
        });
        this.tabsContainer.select("input#field_label").each(function(input){
            input._caller = _caller;
            input.observe('keyup', _caller._eventsHanders.inputKeyUpHandler);
        });
        this.tabsContainer.select("input#column_position").each(function(input){
            input._caller = _caller;
            input.observe('keyup', _caller._eventsHanders.inputKeyUpHandler);
        });
        this.tabsContainer.select("input#field_required").each(function(input){
            input.observe('click', chKeyUpHandler);
        });
        this.tabsContainer.select("button#remove_btn").each(function(input){
            input.observe('click', function(){
                var fieldRow = this.up("#field_row");
                
                var fieldId = fieldRow.getAttribute("field_id");
                
                if (!saveData['field_' + fieldId]){
                    saveData['field_' + fieldId] = {
                        'field_id': fieldId
                    };
                }
                
                saveData['field_' + fieldId]['field_disabled'] = 1;
                
                fieldRow.setStyle({
                   'display': 'none'
                });
                return false;
            });
        });
        
        this.tabsContainer.select("a#add_fields").each(function(a){
            a.observe('click', function(){
                var areaContent = this.up("#area_content");
                var areaId = areaContent.getAttribute("area_id");
                
                var remFields = _caller.getRemovedFields();
                
                var html = ["<div>"];
                var fieldsCount = 0;
                for (var key in remFields){
                    var field = remFields[key];
                    if (field.area_id == areaId) {
                        html.push("<br/><div>");
                        html.push("<input rel='field_ch' field_id='" + field.field_id + "' type='checkbox' id='ch_" , key , "'/>&nbsp;");
                        html.push("<label for='ch_" , key , "'>", field.field_label ,"</label>");
                        html.push("</div>");
                        fieldsCount++;
                    }
                }
                
                if (fieldsCount == 0){
                    html.push("<br/><center>no available fields</center><br/>");
                }
                
                html.push("<center><button type='button' id='add_fields' type='button'/>Submit</button>");
                html.push("</div>");
                
                _caller.showAddFieldsPopup(html.join(''), areaId);
             
                return false;
            })
        });
        
        this.tabsContainer.select("input[rel=use_default]").each(function(input){
            
            var disable = function(val){
                var fieldRow = input.up("#field_row");
                var fieldId = fieldRow.getAttribute("field_id");
                
                if (_caller.sliders[fieldId]){

                    if (val)
                        _caller.sliders[fieldId].setDisabled();
                    else
                        _caller.sliders[fieldId].setEnabled();
                }
                    
                
                var inputDisable = function(dinput){
                    if (dinput != input){
                        dinput.disabled = val;        
                    }
                }
                fieldRow.select("input[type=text]").each(inputDisable);
                fieldRow.select("input[type=checkbox]").each(inputDisable);
            }

            input.observe('click', function(){
                var fieldRow = this.up("#field_row");
                var fieldId = fieldRow.getAttribute("field_id");
                disable(this.checked);
                
                _caller.addSaveFieldItem(fieldId, "use_default", this.checked ? "1" : "0");
            });
            
            disable(input.checked);
        });
        
        this.tabsContainer.select("input[rel=area_use_default]").each(function(input){
            var disable = function(val){
                var areaLabel = input.up("#area_content");
                
                areaLabel.select("input[type=text]").each(function(dinput){
                    if (dinput != input){
                        dinput.disabled = val;
                    }
                });
            }
            
            input.observe('click', function(){
                var areaContent = this.up("#area_content");
                var areaId = areaContent.getAttribute("area_id");
                disable(this.checked);

                _caller.addSaveAreaItem(areaId, "use_default", this.checked ? "1" : "0");
            });
            
            disable(input.checked);
        });
        
        $("amscheckoutsettings_tabs_fields_section").observe('click', function(){
            window.setTimeout(function(){
                _caller.tabsContainer.select("li a#tab")[0].click();
            }, 50)
            
        });
    },
    showAddFieldsPopup: function(html, areaId){
        var saveData = this.saveData;
        var _caller = this;
        
        var dialog = Dialog.info(html, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Fields',
            width: 300,
            height: 400,
            zIndex: 1000,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            id: 'attributeDialog'
        });
        
        dialog.element.select('button#add_fields')[0].observe('click', function(){
            dialog.element.select("[rel=field_ch]").each(function(checkbox){
                if (checkbox.checked){
                    var fieldId = checkbox.getAttribute('field_id');
                    
                    var fieldRow = _caller.tabsContainer.down("#field_row[field_id='" + fieldId + "']");
                    var container = fieldRow.up('#am_field_container');
                    
                    fieldRow.remove();
                    container.appendChild(fieldRow);
                    
                    fieldRow.setStyle({
                        'display': 'table-row'
                    });
                    
                    var order = _caller.getMaxFieldOrder(areaId) + 100;
                    
                    _caller.addSaveFieldItem(fieldId, 'field_disabled', 0);
                    _caller.addSaveFieldItem(fieldId, 'field_order', order);
                }
            })
            dialog.close();
            
            _caller.initColumnPositionSlider();
        })
    },
    getMaxFieldOrder: function(areaId){
        
        var maxOrder = 0;
        for (var key in this.defData){
            if (key.indexOf("field_") != -1 &&
                this.defData[key]['area_id'] == areaId) {
                var fieldOrder = parseInt(this.defData[key]['field_order']);
                
                maxOrder =  fieldOrder > maxOrder ?
                    fieldOrder : maxOrder;
                
            }
        }
        for (var key in this.saveData){
            if (key.indexOf("field_") != -1 &&
                (
                    this.saveData[key]['area_id'] == areaId ||
                    (!this.saveData[key]['area_id'] && this.defData[key]['area_id'] == areaId)
                ) &&
                this.saveData[key]['field_order']) {
                
                var fieldOrder = parseInt(this.saveData[key]['field_order']);
                
                maxOrder = fieldOrder > maxOrder ?
                    fieldOrder : maxOrder;
                
            }
        }
        
        return parseInt(maxOrder);
        
    },
    getFieldValue: function(field_id, field_val){
        var ret = null;
        if (this.defData['field_' + field_id])
            ret = this.defData['field_' + field_id][field_val];
        
        if (this.saveData['field_' + field_id])
            ret = this.saveData['field_' + field_id][field_val];
        
        return ret;
    },
    getRemovedFields: function(){
        
        var remFields = {};
        for (var key in this.defData){
            if (key.indexOf("field_") != -1 &&
                this.defData[key]['field_disabled'] == 1){
                remFields[key] = this.defData[key];
            }
        }

        for (var key in this.saveData){
            if (remFields[key] && this.saveData[key]["field_disabled"] != 1){
                delete remFields[key];
            } else if (this.saveData[key]["field_disabled"] == 1){
                remFields[key] = $H(this.defData[key]).merge(this.saveData[key])._object;
            }

        }
        return remFields;
    },
    initialize: function()
    {
        var _caller = this;
        window.setTimeout(function(){
            var tabsContainer = $('scheckoutTabsContainer');

            _caller.tabsContainer = tabsContainer;

            _caller.tabsContainer.select("li a#tab").each(function(a){
                a.observe('click', function(){

                    var order = this.getAttribute('order');
                    var tab = tabsContainer.select("#tab_" + order)[0];

                    tabsContainer.select("#scheckoutContent li").each(function(li){
                        li.setStyle({
                            'display': 'none'
                        });
                    });     
                    tab.setStyle({
                        'display': 'block'
                    });

                    tabsContainer.select("li a").each(function(a){ a.removeClassName('selected'); });
                    a.addClassName('selected');

                    _caller.initColumnPositionSlider();

                    return false;
                });
            });

            _caller.initDefData();
            _caller.initDrag();
            _caller.initEvents();

            _caller.tabsContainer.select("li a#tab")[0].click();
        }, 500)
        
        
    },
    getSaveData: function(){
        var ret = {};
        
        for(var key in this.saveData){
            var saveElement = this.saveData[key];
            for(var elKey in saveElement){
                if (this.defData[key][elKey] != saveElement[elKey]){
                    if (!ret[key])
                        ret[key] = {};
                    
                    ret[key][elKey] = saveElement[elKey];
                }
            }
        }
        
        return ret;
    },
    submit: function(){
        var saveData = this.getSaveData();
        var html = [];
        
        for (var key in saveData){
            for (var keyEl in saveData[key]){
                html.push('<input type=hidden value="' , saveData[key][keyEl] , '" name="saveData[' , key , '][' , keyEl , ']"/>');
            }
        }
        var tmpDiv = new Element('div');
        tmpDiv.innerHTML = html.join('');
        var form = $('checkoutFieldsForm');
        
        form.appendChild(tmpDiv);
        form.down('#active_tab').value = amscheckoutsettings_tabsJsTabs.activeTab.getAttribute('name');
        form.submit();
    },
    addSaveFieldItem: function(fieldId, key, val){
        if (!this.saveData['field_' + fieldId]){
            this.saveData['field_' + fieldId] = {
                'field_id': fieldId
            };
        }
        
        this.saveData['field_' + fieldId][key] = val;
    },
    addSaveAreaItem: function(areaId, key, val){
        if (!this.saveData['area_' + areaId]){
            this.saveData['area_' + areaId] = {
                'area_id': areaId
            };
        }
        
        this.saveData['area_' + areaId][key] = val;
    }
}


var amImport = new Class.create();

amImport.prototype = {
    initialize: function(){
        
    },
    error: function(error, processer){
//        console.log('error: ', error);
        
        if (processer)
            $(processer.parentNode).remove();
        
    },
    tracePosition: function(position, processer){
        
      processer.setStyle({
            'width': position + '%'
      });
      
      processer.down('span').innerHTML = '&nbsp;' + position + '%';
      
//      console.log('position:', position, '%');
    },
    done: function(response, processer){
//        console.log('done');
        
        if (processer)
            $(processer.parentNode).remove();
        
        if (response.full_import_done == 1){
            $('useGeoip').removeAttribute('disabled');
        }
    },
    start: function(response, input){
        var container = new Element('div');
        var processer = new Element('div');
        var position = new Element('span');
        
        processer.addClassName('am_processer');
        container.addClassName('am_processer_container');
        
        processer.setStyle({
            'width': '0%'
        });
        
        container.appendChild(processer);
        
        input.parentNode.appendChild(container);
        
        processer.innerHTML = response.file;
        processer.appendChild(position);
        
        return processer;
        
    },
    commit: function(commitUrl, processer){
        var _caller = this;
        
        var request = new Ajax.Request(
            commitUrl,
            {
                method: 'post',
                onSuccess: function(transport){
                    var response = eval('(' + transport.responseText + ')');
                    
                    if (response.status == 'done'){
                        _caller.done(response, processer)
                    } else if (response.error){
                        _caller.error(response.error, processer);
                    }
                }
            }
        );
    },
    process: function(processUrl, commitUrl, processer){
        var _caller = this;
        
        var request = new Ajax.Request(
            processUrl,
            {
                method: 'post',
                onSuccess: function(transport){
                    var response = eval('(' + transport.responseText + ')');
                    
                    if (response.status == 'processing'){
                        
                        _caller.tracePosition(response.position, processer);
                        
                        if (response.position == 100){
                            _caller.commit(commitUrl, processer);
                        } else {
                            _caller.process(processUrl, commitUrl, processer);
                        }
                        
                        
                    } else if (response.error){
                        _caller.error(response.error, processer);
                    }
                }
            }
        );
    },
    run: function(startUrl, processUrl, commitUrl, input){
        var _caller = this;
        
        var request = new Ajax.Request(
            startUrl,
            {
                method: 'post',
                onSuccess: function(transport){
                    var response = eval('(' + transport.responseText + ')');
                    
                    if (response.status == 'started'){
                        var processer = _caller.start(response, input);
                        
                        _caller.process(processUrl, commitUrl, processer);
                        
                    } else if (response.error){
                        _caller.error(response.error);
                    }
                }
            }
        );
    }
}