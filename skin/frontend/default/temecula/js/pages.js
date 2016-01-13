jQuery(window).load(function () {
    var aboutBoxesHeight = 0;

    jQuery("ul#about-boxes").children("li").each(function () {
        if (jQuery(this).height() > aboutBoxesHeight) {
            aboutBoxesHeight = jQuery(this).height();

            jQuery("ul#about-boxes").children("li").each(function () {                
                    jQuery(this).css({
                        minHeight: aboutBoxesHeight + 'px'
                    });                
            });
        }
    });


});