jQuery(document).ready(function () {
    jQuery("#nav").children("li.level0.level-top").each(function () {
        if (jQuery(this).hasClass("active")) {
            jQuery(this).removeClass("active");
            jQuery(this).children("ul").removeClass("shown-sub");
        }
    });

    jQuery(".breadcrumbs ul").children("li").each(function (indx) {
        if (indx > 0) {
            var _class = jQuery(this).attr('class').split(" ");

            for (var x = 0; x < _class.length; x++) {
                var catid = _class[x].split("category");

                if (catid.length > 0 && jQuery.isNumeric(catid[1])) {
                    if (!jQuery(".category-node-" + catid[1]).hasClass("active")) {
                        jQuery(".category-node-" + catid[1]).addClass("active");
                        jQuery(".category-node-" + catid[1]).children("a").addClass("active-a");
                        jQuery(".category-node-" + catid[1]).children("ul.level0").addClass("shown-sub");
                    }
                }
            }
        }
    });
});