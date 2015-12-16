// For scroll to top button.
jQuery(window).scroll(function () {
    if (jQuery(this).scrollTop() > 100) {
        jQuery('.scrollToTop').fadeIn('fast');
    } else {
        jQuery('.scrollToTop').fadeOut('fast');
    }
});

//Click event to scroll to top
jQuery('.scrollToTop').click(function () {
    jQuery('html, body').animate({scrollTop: 0}, 800);
    return false;
});