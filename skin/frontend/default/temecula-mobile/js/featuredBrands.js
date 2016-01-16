var featuredBrands = new Class.create({
    slideDistance: 0,
    _curPosition: 0,
    _nextPosition: 0,
    _processing: false,
    visibleSlideContainer: 0,
    _speed: 200,
    slidesContainerWidth: 0,
    _totalSlides: 0,
    viewedItemWidth: 0,
    init: function () {
        this.slideDistance = 0;

        this._totalSlides = jQuery(".brand-grid-inner-wrap").children(".featured-brand-list").length;

//.featured-brand-list there's an existing on menu brand
        this.slidesContainerWidth = (this._totalSlides * jQuery(".brand-grid-inner-wrap .featured-brand-list").outerWidth(true));

        console.log(this._totalSlides);
        console.log(jQuery(".brand-grid-inner-wrap .featured-brand-list").outerWidth(true));

        jQuery(".brand-grid-inner-wrap").css({
            width: this.slidesContainerWidth + "px"
        });
        //must declare before this.visibleSlideContainer = jQuery(".brand-grid-inner-wrap").parent().innerWidth();
        jQuery(".brands-grid-featured").css({
            width: (jQuery("#slider_featured_brands").width() - 56) + 'px' //56 for left and right arrow width
        });

        this.viewedItemWidth = jQuery(".brand-grid-inner-wrap .featured-brand-list").outerWidth(true);
        this.slidesContainer = jQuery(".brand-grid-inner-wrap");

        jQuery("#slider_featured_brands").css({
            visibility: 'visible'
        });
    },
    getVisibleSlideContainerWidth: function () {
    },
    sliderLeft: function () {
        this.visibleSlideContainer = jQuery(".brand-grid-inner-wrap").parent().innerWidth();

        jQuery(".feat-right").attr('disabled', 'disabled');
        this._curPosition = parseInt(jQuery(".brand-grid-inner-wrap").css('marginLeft'));

        if ((this.slidesContainerWidth + this._curPosition) <= this.visibleSlideContainer) {
            return false;
        }

        if ((this.slidesContainerWidth - parseInt(this.slidesContainer.css('marginLeft'))) <= this.visibleSlideContainer) {
            this.slideDistance = 0;
        } else {
            this.slideDistance = Math.floor(this.visibleSlideContainer / this.viewedItemWidth) * this.viewedItemWidth;
        }

        jQuery(".brand-grid-inner-wrap").animate({
            marginLeft: "-=" + (this.slideDistance > 0 ? this.slideDistance : 0)
        }, this._speed, function () {
            jQuery(".feat-left").removeAttr('disabled');
        });

    },
    sliderRight: function () {
        jQuery(".feat-left").attr('disabled', 'disabled');
        this.visibleSlideContainer = jQuery(".brand-grid-inner-wrap").parent().innerWidth();
        this.slideDistance = Math.floor(this.visibleSlideContainer / this.viewedItemWidth) * this.viewedItemWidth;

        this._curPosition = parseInt(jQuery(".brand-grid-inner-wrap").css('marginLeft'));
        this._nextPosition = this._curPosition + this.slideDistance;

        jQuery(".brand-grid-inner-wrap").animate({
            marginLeft: "+=" + (this._nextPosition > 0 ? (0 - this._curPosition) : this.slideDistance)
        }, this._speed, function () {
            jQuery(".feat-right").removeAttr('disabled');
        });

    }
});

jQuery(document).ready(function () {

    _featuredBrands = new featuredBrands();
    _featuredBrands.init();

    jQuery(window).bind('resize', _featuredBrands.init);

    jQuery(".feat-right").click(function () {
        _featuredBrands.sliderLeft();
    });

    jQuery(".feat-left").click(function () {
        _featuredBrands.sliderRight();
    });
});

jQuery(window).load(function () {
    jQuery(".brand-grid-inner-wrap div.featured-brand-list").each(function () {
        jQuery(this).css({
            paddingTop: ((jQuery(this).parent().outerHeight() - jQuery(this).outerHeight()) / 2) + 'px'
        });
    });
});
