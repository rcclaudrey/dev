jQuery(document).ready(function () {
    //make the children of the main nav visibile
    jQuery('ul.level1, ul.level2').prepend('<li class="headrmenu"><h3>Categories:</h3></li>');
    jQuery('.featured-brands-menu').css({display: 'list-item'}).appendTo('ul.level1');

    jQuery("li.level0.active.parent").children("ul.level0").addClass("shown-sub");
    jQuery("li.level1.active").children("a").addClass("active-a");

    jQuery("#nav li").hover(function () {

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

    jQuery("#nav li.level1.parent").hover(function () {
        subcats.show(this);
    }, function () {
        subcats.hide(this);
    });

    subcats = {
        show: function (ths) {
            jQuery(ths).addClass('over');
            jQuery(ths).children('ul').addClass("shown-sub");

        }, hide: function (ths) {
            jQuery(ths).removeClass('over');
            jQuery(ths).children('a').removeClass('over');

        }
    }
});