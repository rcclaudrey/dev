jQuery(document).ready(function () {

    jQuery("ul.level1").each(function () {
        jQuery(this).children("li:nth-child(11)").before("<li class=\"menu-view-all-sub\"><a href=\"" + jQuery(this).siblings('a').attr('href') + "\"><span>view all</span></a></li>");
    });

    jQuery("ul.level1").each(function () {
        jQuery(this).children('li').slice(11).remove();
    });

    jQuery("#navv2 li").hover(function () {

        jQuery(this).siblings('li').removeClass("over");
        jQuery(this).siblings('li').children('a').removeClass("over");

        jQuery(this).addClass('over');
        jQuery(this).children('a').addClass('over');

        jQuery(this).children('ul').addClass("shown-sub");
        jQuery("li.parent").not('.over').children('ul').removeClass("shown-sub");

    }, function () {

        jQuery(this).removeClass('over');
        jQuery(this).children('a').removeClass('over');

        jQuery(this).children('ul').removeClass("shown-sub");

        if (jQuery(this).hasClass('active') && jQuery(this).hasClass('level0')) {
            jQuery(this).children('ul').addClass("shown-sub");
        }

        if (jQuery('li.parent.over').length < 1) {
            jQuery('li.level0.parent.active').children('ul').addClass("shown-sub");
        }
    });


    jQuery("ul.featured-category-level-0 li.level-3 a").click(function (event) {
        if (!jQuery(this).hasClass('active-menu')) {
            event.preventDefault();
        }
        
        jQuery(this).parent().toggleClass('active');
        jQuery(this).toggleClass('active-menu');
    });


    subcats = {
        show: function (ths) {
            jQuery(ths).addClass('over');
            jQuery(ths).children('ul').addClass("shown-sub");

        }, hide: function (ths) {
            jQuery(ths).removeClass('over');
            jQuery(ths).children('a').removeClass('over');

        }
    };
});