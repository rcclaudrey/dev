
cartheaderItems = {
    removeItem: function (event, ths) {
        event.preventDefault();
        jQuery.ajax({
            url: jQuery(ths).attr("href"),
            dataType: 'html',
            cache: false,
            beforeSend: function () {
                if (!confirm('Are you sure you would like to remove this item from the shopping cart?')) {
                    return false;
                }
            },
            success: function (response) {
                jQuery('#minicart').html(response);
            }
        });
    }
};