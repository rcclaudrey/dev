var recentlyViewed = new Class.create({
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
        
        this._totalSlides = jQuery(".products-grid-inner-wrap").children(".recently-viewed-item").length;
        this.slidesContainerWidth = (this._totalSlides * jQuery(".recently-viewed-item").outerWidth(true));
        jQuery(".products-grid-inner-wrap").css({
            width: this.slidesContainerWidth + "px"
        });
        //must declare before this.visibleSlideContainer = jQuery(".products-grid-inner-wrap").parent().innerWidth();
        jQuery(".recently-viewed-items").css({
            width: (jQuery(".products-grid.recently-viewed").width() - 56) + 'px' //56 for left and right arrow width
        });

        this.viewedItemWidth = jQuery(".recently-viewed-item").outerWidth(true);
        this.slidesContainer = jQuery(".products-grid-inner-wrap");           
    },
    getVisibleSlideContainerWidth: function(){},
    sliderLeft: function () {        
        this.visibleSlideContainer = jQuery(".products-grid-inner-wrap").parent().innerWidth();  

        jQuery(".feat-right").attr('disabled', 'disabled');
        this._curPosition = parseInt(jQuery(".products-grid-inner-wrap").css('marginLeft'));

        if ((this.slidesContainerWidth + this._curPosition) <= this.visibleSlideContainer) {
            return false;
        }

        if ((this.slidesContainerWidth - parseInt(this.slidesContainer.css('marginLeft'))) <= this.visibleSlideContainer) {
            this.slideDistance = 0;
        }else{
            this.slideDistance = Math.floor(this.visibleSlideContainer / this.viewedItemWidth) * this.viewedItemWidth;
        }                                

        jQuery(".products-grid-inner-wrap").animate({
            marginLeft: "-=" + (this.slideDistance > 0 ? this.slideDistance : 0)
        }, this._speed, function () {
            jQuery(".feat-left").removeAttr('disabled');
        });

    },
    sliderRight: function () {
        jQuery(".feat-left").attr('disabled', 'disabled');
        this.visibleSlideContainer = jQuery(".products-grid-inner-wrap").parent().innerWidth();  
        this.slideDistance = Math.floor(this.visibleSlideContainer / this.viewedItemWidth) * this.viewedItemWidth;

        this._curPosition = parseInt(jQuery(".products-grid-inner-wrap").css('marginLeft'));
        this._nextPosition = this._curPosition + this.slideDistance;

        jQuery(".products-grid-inner-wrap").animate({
            marginLeft: "+=" + (this._nextPosition > 0 ? (0 - this._curPosition) : this.slideDistance)
        }, this._speed, function () {
            jQuery(".feat-right").removeAttr('disabled');
        });

    }
});

jQuery(document).ready(function () {

    recentlyViewedSlider = new recentlyViewed();
    recentlyViewedSlider.init();

    jQuery(window).bind('resize', recentlyViewedSlider.init);

    jQuery(".feat-right").click(function () {
        recentlyViewedSlider.sliderLeft();
    });

    jQuery(".feat-left").click(function () {
        recentlyViewedSlider.sliderRight();
    });
});
