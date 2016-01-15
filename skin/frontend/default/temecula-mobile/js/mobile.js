jQuery(document).ready(function () {

	jQuery(".account-select-arrow").click(function () {
		alert('test');
		jQuery("#my-account-selection").trigger("mousedown");
	});

	jQuery("li.parent").each(function () {
		jQuery(this).children('a').each(function () {
			jQuery(this).after("<span class=\"expander\"></span>");
		});
	});

	jQuery(".level0.level-top.parent").children('a').click(function (event) {
		tmsmobile.showSubMenu(this, event);
	});
	jQuery(".level1.parent").children('a').click(function (event) {
		tmsmobile.showSubMenu(this, event);
	});
	jQuery(".level2.parent").children('a').click(function (event) {
		tmsmobile.showSubMenu(this, event);
	});

	jQuery("ul#mobile-nav li.parent span").click(function (event) {
		tmsmobile.showSubMenu(this, event);
	});

	jQuery(".mobile-search-link").click(function () {
		tmsmobile.showSearch();
	});

	jQuery(".mobile-menu-link").click(function () {
		tmsmobile.init();
	});

	jQuery('.show-hide-right-nav').click(function () {
		tmsmobile.toggleRightSideBar();
	});

	jQuery('.layered-nav-label').click(function () {
		jQuery("#narrow-by-list").slideToggle(200, function () {
			if (jQuery(this).is(":visible")) {
				jQuery('.layered-nav-label').addClass("visible");
			} else {
				jQuery('.layered-nav-label').removeClass("visible");
			}
		});
	});

	tmsmobile = {
		init: function () {
			jQuery(".expander").each(function () {
				jQuery(this).css({
					width: (jQuery(this).parent().width() - jQuery(this).siblings("a").outerWidth() - parseInt(jQuery(this).siblings("a").css('marginLeft')) - 2) + "px"
				});
			});
		},
		toggleRightSideBar: function () {
			jQuery(".filter-option-wrap").fadeToggle(100, function () {
				if (jQuery(".filter-option-wrap").is(":visible")) {
					jQuery(".show-hide-right-nav").fadeOut(100, function () {
						jQuery(this).addClass("visible");
						jQuery(this).fadeIn(100);
					});

					jQuery(".filter-option-wrap").animate({
						width: "+=35"
					}, 100, function () {
						// Animation complete.
					});
				} else {

					jQuery(".show-hide-right-nav").fadeOut(100, function () {
						jQuery(this).removeClass("visible");
						jQuery(this).fadeIn(100);
					});

					jQuery(".filter-option-wrap").animate({
						width: "-=35"
					}, 100, function () {
						// Animation complete.
					});
				}

			});
		},
		showMenu: function (sender) {
			var mainMenuPosition = jQuery(".quick-access").position().top;
			jQuery('.nav-container.mobile-nav').css({top: mainMenuPosition + "px"});
			jQuery("#mobile-nav").fadeToggle("fast");
			jQuery(".mobile-featured-brands-wrap").fadeToggle("fast");
		},
		showSubMenu: function (ths, event) {

			if ((event.target.nodeName).toLowerCase() === 'span') {
				jQuery(ths).siblings('ul').fadeToggle("fast", function () {
					if (jQuery(this).is(":visible")) {
						jQuery(ths).parent().addClass("shown-sub");
						jQuery(ths).siblings('ul').children("li.parent").each(function () {
							jQuery(this).children("span.expander").css({
								width: (jQuery(this).width() - jQuery(this).children("a").outerWidth() - parseInt(jQuery(this).children("a").css('marginLeft')) - 2) + "px"
							});
						});

					} else {
						jQuery(ths).parent().removeClass("shown-sub");
					}
				});
			}


//            if (!jQuery(ths).parent().hasClass("shown-sub")) {
//                event.preventDefault();
//            }
//
//            jQuery(ths).siblings('ul').fadeToggle("fast", function () {
//                if (jQuery(this).is(":visible")) {
//                    jQuery(ths).parent().addClass("shown-sub");
//                } else {
//                    jQuery(ths).parent().removeClass("shown-sub");
//                }
//            });
		},
		showSearch: function () {
			jQuery(".mobile-search-form").fadeToggle("fast");
		},
		normalizeProductContainerHeights: function () {
			var containerHeight = jQuery(".category-products").outerHeight();
			var itemContainerHeight = 0;

			jQuery(".products-grid li.item").each(function () {
				if (jQuery(this).height() > itemContainerHeight) {
					itemContainerHeight = jQuery(this).height();
				}
			});

			jQuery(".products-grid li.item").each(function () {
				jQuery(this).css({
					height: itemContainerHeight + "px"
				});
			})
		}
	};

	jQuery(window).bind("resize", tmsmobile.normalizeProductContainerHeights);
	jQuery(window).bind("orientationchange", tmsmobile.normalizeProductContainerHeights);
});

jQuery(window).load(function () {
	tmsmobile.normalizeProductContainerHeights();
});