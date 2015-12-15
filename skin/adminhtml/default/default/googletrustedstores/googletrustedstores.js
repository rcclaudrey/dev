
//function show_hide_certified_shops() {
//    if ($('googletrustedstores_gts_country')) {
//        var country = $('googletrustedstores_gts_country').value;
//        if ($('googletrustedstores_feed_update-head')) {
//            if (country == 'US') {
//                if ($('googletrustedstores_feed_update-head').up().up() && $('googletrustedstores_feed_update-head').up().up().hasClassName('section-config')) {
//                    $('googletrustedstores_feed_update-head').up().up().style.display = 'block';
//                    $('googletrustedstores_shipments_settings-head').up().up().style.display = 'block';
//                    $('googletrustedstores_cancellations_settings-head').up().up().style.display = 'block';
//                    $('googletrustedstores_carriers-head').up().up().style.display = 'block';
//                } else {
//                    $('googletrustedstores_feed_update-head').up().style.display = 'block';
//                    $('googletrustedstores_shipments_settings-head').up().style.display = 'block';
//                    $('googletrustedstores_cancellations_settings-head').up().style.display = 'block';
//                    $('googletrustedstores_carriers-head').up().style.display = 'block';
//                }
//                $('row_googletrustedstores_gts_badge_position').style.display = 'none';
//                $('row_googletrustedstores_gts_badge_container_css').style.display = 'none';
//            } else {
//                if ($('googletrustedstores_feed_update-head').up().up() && $('googletrustedstores_feed_update-head').up().up().hasClassName('section-config')) {
//                    $('googletrustedstores_feed_update-head').up().up().style.display = 'none';
//                    $('googletrustedstores_shipments_settings-head').up().up().style.display = 'none';
//                    $('googletrustedstores_cancellations_settings-head').up().up().style.display = 'none';
//                    $('googletrustedstores_carriers-head').up().up().style.display = 'none';
//                } else {
//                    $('googletrustedstores_feed_update-head').up().style.display = 'none';
//                    $('googletrustedstores_shipments_settings-head').up().style.display = 'none';
//                    $('googletrustedstores_cancellations_settings-head').up().style.display = 'none';
//                    $('googletrustedstores_carriers-head').up().style.display = 'none';
//                }
//                $('row_googletrustedstores_gts_badge_position').style.display = 'table-row';
//                if ($('row_googletrustedstores_gts_badge_position').value == "USER_DEFINED")
//                    $('row_googletrustedstores_gts_badge_container_css').style.display = 'table-row';
//            }
//        }
//    }
//}
//
//function show_hide_links(val) {
//    if (val == 0) { // dynamic
//        if ($('googletrustedstores_schedule-head')) {
//            if ($('googletrustedstores_schedule-head').up().up() && $('googletrustedstores_schedule-head').up().up().hasClassName('section-config'))
//                $('googletrustedstores_schedule-head').up().up().style.display = 'none';
//            else
//                $('googletrustedstores_schedule-head').up().style.display = 'none';
//        }
//        $('row_googletrustedstores_cancellations_settings_cancellation_link').style.display = 'none';
//        $('row_googletrustedstores_cancellations_settings_dcancellation_link').style.display = '';
//        $('row_googletrustedstores_cancellations_settings_filename').style.display = 'none';
//        $('row_googletrustedstores_cancellations_settings_filepath').style.display = 'none';
//        $('row_googletrustedstores_shipments_settings_shipment_link').style.display = 'none';
//        $('row_googletrustedstores_shipments_settings_dshipment_link').style.display = '';
//        $('row_googletrustedstores_shipments_settings_filename').style.display = 'none';
//        $('row_googletrustedstores_shipments_settings_filepath').style.display = 'none';
//    } else {
//        if ($('googletrustedstores_schedule-head')) {
//            if ($('googletrustedstores_schedule-head').up().up() && $('googletrustedstores_schedule-head').up().up().hasClassName('section-config'))
//                $('googletrustedstores_schedule-head').up().up().style.display = '';
//            else
//                $('googletrustedstores_schedule-head').up().style.display = '';
//        }
//        $('row_googletrustedstores_cancellations_settings_cancellation_link').style.display = '';
//        $('row_googletrustedstores_cancellations_settings_dcancellation_link').style.display = 'none';
//        $('row_googletrustedstores_cancellations_settings_filename').style.display = '';
//        $('row_googletrustedstores_cancellations_settings_filepath').style.display = '';
//        $('row_googletrustedstores_shipments_settings_shipment_link').style.display = '';
//        $('row_googletrustedstores_shipments_settings_dshipment_link').style.display = 'none';
//        $('row_googletrustedstores_shipments_settings_filename').style.display = '';
//        $('row_googletrustedstores_shipments_settings_filepath').style.display = '';
//    }
//}

var badgeCode = "";

function testBadge(website, url_ship) {
    var fieldset = $('googletrustedstores_gts');
    var data = {};
    fieldset.select('input,select,textarea').each(function(elt) {
        data[elt.id] = elt.value;
    });

    data.website = website;

    data['product-sku'] = $('product-sku').value;

    new Ajax.Request(url_ship, {
        parameters: data,
        method: "post",
        onSuccess: function(response) {
            $('GtsValidatorBadgeUrl').href = $('GtsValidatorBadgeUrl').readAttribute("base") + "id/" + $('product-sku').value;
            $('GtsValidatorBadgeUrl').update($('GtsValidatorBadgeUrl').readAttribute("base") + "id/" + $('product-sku').value)

            badgeCode.setValue(response.responseText);
        }
    });
}

function testOrder(website, url_ship) {
    var data = {};
    var fieldset = $('googletrustedstores_gts_orders');
    fieldset.select('input,select,textarea').each(function(elt) {
        data[elt.id] = elt.value;
    });
    fieldset = $('googletrustedstores_gts');
    fieldset.select('input,select,textarea').each(function(elt) {
        data[elt.id] = elt.value;
    });
    data['order-number'] = $('order-number').value;

    data.website = website;

    new Ajax.Request(url_ship, {
        parameters: data,
        method: "post",
        onSuccess: function(response) {
            $('GtsValidatorOrderUrl').href = $('GtsValidatorOrderUrl').readAttribute("base") + "id/" + $('order-number').value;
            $('GtsValidatorOrderUrl').update($('GtsValidatorOrderUrl').readAttribute("base") + "id/" + $('order-number').value)
            orderCode.setValue(response.responseText);
        }
    });
}



document.observe('dom:loaded', function() {
    if (!$('googletrustedstores_gts_gts_id')) {
        return;
    }

//    show_hide_certified_shops();
//    $('googletrustedstores_gts_country').observe('change', function (evt) {
//        show_hide_certified_shops();
//    });

//    show_hide_links($F('googletrustedstores_feed_update_gts_dynamic_link'));
//    $('googletrustedstores_feed_update_gts_dynamic_link').observe('change', function (evt) {
//        show_hide_links(evt.element().value);
//    });

    badgeCode = CodeMirror(function(elt) {
        $("gts-badge-test-page").parentNode.replaceChild(elt, $("gts-badge-test-page"));
    }, {
        value: "",
        mode: 'text/html',
        readOnly: true,
        lineNumbers: true
    });

    orderCode = CodeMirror(function(elt) {
        $("gts-order-test-page").parentNode.replaceChild(elt, $("gts-order-test-page"));
    }, {
        value: "",
        mode: 'text/html',
        readOnly: true,
        lineNumbers: true
    });


});