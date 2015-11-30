/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/

var amPattribute = new Class.create();

amPattribute.prototype = {

    attributeColumnId: 1,

    notEditableAttributes: ['tier_price', 'gallery', 'media_gallery', 'recurring_profile',
        'group_price'],

    initialize: function(title)
    {
        this.title = title;
    },

    initializeTrGrid: function(attributeColumns)
    {
        var tbody = $('attribute-columns-table').down('tbody');
        var template = this.getAttributeColumnTemplate();
        var obj = this;

        attributeColumns.each(function(attr) {
            attr.attribute_list = $('attributes-select').innerHTML;
            attr.id = obj.attributeColumnId++;
            attr.editable = attr.is_editable == 1 && attr.allow_to_edit == 1 ? 'checked="checked"' : '';
            attr.is_disabled = attr.allow_to_edit != 1 ? 'disabled="disabled"' : '';

            var html = template.evaluate(attr);
            $('attribute-columns-table').down('tbody').insert(html);

            if ($('ampgrid-attribute-select_' + attr.id)
                    .down('select option[value=' + attr.attribute_id + ']')) {
                $('ampgrid-attribute-select_' + attr.id)
                    .down('select option[value=' + attr.attribute_id + ']').setAttribute('selected', 'selected');
            }
            obj.initCombobox($('ampgrid-attribute-select_' + attr.id).down('.chosen-select'));
        });

        $$('.am-delete-attribute').each(function(elem) {
            Event.observe(elem, 'click', function(event) {
                obj.deleteAttributeColumn(event);
            });
        });

        this.observeLastAttributeSelect();
        this.addAttributeColumn();

    },

    deleteTemplate: function(formSubmit) {
        var obj = this;
        Dialog.confirm($('delete-group-popup').innerHTML,
            {
                width:250,
                height: 80,
                okLabel: "Yes, I'm Sure",
                //cancelLabel: 'Cancel',
                buttonClass: "delete-group-button",
                windowClassName: "popup-window",
                id: "ampgrid-delete-template",
                cancel: function(win) {
                    return false;
                },
                ok: function(win) {
                    obj.disableSave();
                    $('delete_group').value = 1;
                    formSubmit.submit();
                    return true;
                }
            });
    },

    initCombobox: function(element) {
        new Chosen(element,{
            width: "250px",
            search_contains: true,
            allow_single_deselect:true
        });
    },

    observeLastAttributeSelect: function() {
        var obj = this;
        Event.observe( $('attribute-columns-table'), 'change', function(event) {

            if (Event.element(event) == event.findElement('select.ampgrid-attribute-select')) {
                Event.element(event).previous('.attribute_id').value = Event.element(event).value;
                if (obj.notEditableAttributes.indexOf(Event.element(event).down('option:selected').readAttribute('data-attribute')) != -1) {
                    event.findElement('tr').down('input.is-editable').checked = false;
                    event.findElement('tr').down('input.is-editable').disabled = true;
                } else {
                    event.findElement('tr').down('input.is-editable').disabled = false;
                }
            }
            if (Event.element(event)
                === $('attribute-columns-table').down('tbody').childElements().last().down('select.ampgrid-attribute-select'))
            {
                obj.addAttributeColumn();
            }
        });
    },

    addAttributeColumn: function(event) {
        var template = this.getAttributeColumnTemplate();
        var obj = this;
        var tbody = $('attribute-columns-table').down('tbody');
        tbody.insert(template.evaluate({
            'id' : obj.attributeColumnId++,
            'attribute_list': $('attributes-select').innerHTML
        }));
        if($('attribute-columns-table').down('tbody').childElements().last().previous()) {
            $('attribute-columns-table').down('tbody').childElements().last().previous().down('.am-delete-attribute').show();
        }
        $('attribute-columns-table').down('tbody').childElements().last().down('.am-delete-attribute').hide();
        var obj = this;

        this.initCombobox($('attribute-columns-table').down('tbody').childElements().last().down('.chosen-select'));
        Event.observe($('attribute-columns-table').down('tbody').childElements().last().down('.am-delete-attribute')
            , 'click', function(event) {
            obj.deleteAttributeColumn(event);
        });
    },

    deleteAttributeColumn: function(event) {
        Event.element(event).up('tr').remove();
    },

    getAttributeColumnTemplate: function()
    {
        return new Template(
          '<tr class="even" >\
            <td id="ampgrid-attribute-select_#{id}">\
                <input class="attribute_id" type="hidden" name="pattribute[#{id}][attribute_id]" value="#{attribute_id}" />\
                #{attribute_list}\
            </td>\
            <td><input type="text" class="custom-title" name="pattribute[#{id}][custom_title]" value="#{custom_title}" /></td>\
            <td>\
                <input type="hidden" name="pattribute[#{id}][is_editable]" value="0" />\
                <input type="checkbox" value="1" name="pattribute[#{id}][is_editable]" class="is-editable" #{editable} #{is_disabled} />\
            </td>\
            <td><span class="am-delete-attribute"></span></td>\
          </tr>'
        );
    },

    showConfig: function(url)
    {
        Window.keepMultiModalWindow = true;
        attributeDialog = new Window({
            draggable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: this.title,
            resizable: true,
            width: 720,
            height: 600,
            zIndex: 1000,
            recenterAuto: false,
            hideEffect: Element.hide,
            showEffect: Element.show,
            destroyOnClose: false,
            showProgress: true,
            minimizable:	false,
            maximizable:	false,
            destroyOnClose: true,
            recenterAuto: false,
            id: 'attributeDialog'
        });
        attributeDialog.setAjaxContent(
            url,
            {method: 'get'}, true, true
        );

    },

    unCheckAll: function()
    {
        $$(".pattribute:checked, .category:checked").each(function(obj){
            obj.checked = false;
        });
    },

    saveNewTemplate: function()
    {
        saveNewTemplatePopup = Dialog.info(
            $('new-group-popup').innerHTML, {
                draggable: true,
                closable: true,
                className: "magento",
                windowClassName: "popup-window",
                title: this.title,
                resizable: true,
                width: 340,
                height: 70,
                zIndex: 1100,
                top: 490,
                recenterAuto: false,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: 'saveNewTemplate',
                onShow: function()
                {
                    $('new-group-name').value = $('group-name').value;
                    $('is-new-group').value = 1;
                },
                onClose: function()
                {
                    $('is-new-group').value = 0;
                }
            });
    },

    closeConfig: function()
    {
        attributeDialog.close();
    },

    disableSave: function(init)
    {
        $('save-group').setAttribute('disabled', 'disabled');
        $('save-group').addClassName('disabled');
        if(!init) {
            $('save-new-group').setAttribute('disabled', 'disabled');
            $('save-new-group').addClassName('disabled');
        }
        $('delete-group').setAttribute('disabled', 'disabled');
        $('delete-group').addClassName('disabled');
    },

    changeTemplate: function()
    {
        this.disableSave();
        $('ampgrid-group-select').removeClassName('default-select-value');
        $('apmgrid-group').submit()
    }
};

pAttribute = new amPattribute('Grid Columns');