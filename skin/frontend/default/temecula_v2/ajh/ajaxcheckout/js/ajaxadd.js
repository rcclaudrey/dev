//function AjaxCart(options) {
//    this.formKey = options.formKey;
//}

AjaxCart = Class.create({
    buttonSelector: '.btn-cart',
    removeButtonSelector: '.remove',
    topLinksSelector: 'header-cart',
    qty: '.cart-count',
    urlMatch: /(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:/~\+#]*[\w\-\@?^=%&amp;/~\+#])?/,
    initialize: function () {
        this.isLoading = false;
        this._observeButtons();
    },
    _observeButtons: function () {
        var _this = this;
        $$(this.buttonSelector).each(function (th) {
            var href = null;
            if (th.onclick) {
                href = th.onclick.toString().match(_this.urlMatch);
                if (href) {
                    href = href[0];
                }

                if (href) {
                    th.onclick = null;
                }
            }
            if (!href) {
                var form = th.up('form');
                if (form) {
                    href = form.action;
                }
            }

            th.observe('click', function (e) {
                if (href !== null) {
                    Event.stop(e);
                    var form = th.up('form');
                    var params = '';
                    if (form) {
                        params = form.serialize();
                    }
                    var newHref = href.replace('checkout/cart/add', 'ajaxcheckout/cart/add');
                    _this.openCart(newHref, params);

                }
            });
        });
    },
    _updateTopLinks: function (data) {
        console.log("_updateTopLinks");
        var messages = data.success_message;
        var topLinksContent = data.content;
        var topLinks = $(this.topLinksSelector);
        console.log(this.topLinksSelector);
        if (topLinks) {
            topLinks.update(topLinksContent);
        }
        if (messages) {
            this.showMessage(messages);
        }
    },
    _updateCartQty: function (data) {
        console.log("_updateCartQty");
        var qty = $$(this.qty).first();
        if (qty) {
            console.log(qty);
            qty.innerHTML = data;
        }
    },
    openCart: function (href, params) {
        var _this = this;
        if (this.isLoading) {
            return false;
        }
        this.showLoader();
        new Ajax.Request(href, {
            method: 'post',
            parameters: params,
            onComplete: function (transport) {
                var response = transport.responseText.evalJSON();
                console.log(response);
                if (response.error == 0) {
                    var qty = response.qty;
                    //console.log(topLinks);
                    if (response) {
                        _this._updateCartQty(qty);
                        _this._updateTopLinks(response);
                    }
                    _this.hideLoader();
                    _this.showMiniCart();
                } else {
//                    alert('ERROR!');
                    _this.hideLoader();
                    var errorMessage = $('error-message');
                    if (errorMessage) {
                        $('error-message').update(response.error_message);
                    } else {
                        alert(response.error_message);
                    }
                }
            }
        });
    },
    removeCart: function (url) {
        var _this = this;
        if (this.isLoading) {
            return false;
        }
        this.showLoader();
        new Ajax.Request(url, {
            method: 'post',
//            parameters: params,
            onComplete: function (transport) {
                var response = transport.responseText.evalJSON();
                console.log(response);
                if (response.error == 0) {
                    var qty = response.qty;
                    //console.log(topLinks);
                    if (response) {
                        _this._updateCartQty(qty);
                        _this._updateTopLinks(response);
                        _this.showMiniCart();
                    }
                    _this.hideLoader();
                } else {
                    alert('ERROR!');
                }
            }
        });
    },
    showMessage: function (message) {
        var modal = $('success-message');
        if (modal) {
            $('success-message').update(message);
        } else {
            alert(message);
        }
    },
    showLoader: function () {
        this.isLoading = true;
        var loader = $('loader');
        if (loader) {
            $('loader').show();
        }
        var divContainer = document.createElement("div");
        divContainer.id = "container";
        var divLoader = document.createElement("div");
        divLoader.id = "loader";
        divContainer.appendChild(divLoader);
        var divSuccessMessage = document.createElement("div");
        divSuccessMessage.id = "success-message";
        divContainer.appendChild(divSuccessMessage);
        var divErrorMessage = document.createElement("div");
        divErrorMessage.id = "error-message";
        divContainer.appendChild(divErrorMessage);

//        try {
//            var winpop = new Window({
//                blurClassName: 'addnote_blur',
//                id: 'add_cart_popup_window',
//                className: 'magento',
//                title: '',
//                width: 450,
//                height: 65,
//                minimizable: false,
//                draggable: false,
//                maximizable: false,
//                recenterAuto: false,
//                center: false,
//                destroyOnClose: true,
//                closeOnEsc: false,
//                showEffectOptions: {duration: 0}
//            });
//        } catch (e) {
//
//        }
//
//        winpop.setHTMLContent(divContainer.innerHTML);
//        winpop.setZIndex(100);
//        winpop.showCenter(true);
    },
    hideLoader: function () {
        this.isLoading = false;
        var loader = $('loader');
        if (loader) {
            loader.hide();
        }
    },
    showMiniCart: function () {
        $(this.topLinksSelector).show();
    },
    hideMiniCart: function () {
        $(this.topLinksSelector).hide();
    }
});

document.observe("dom:loaded", function () {
    new AjaxCart;
    /**
     var parent = document.getElementsByTagName("body")[0];
     var divOverlay = document.createElement("div");
     divOverlay.className = "m-overlay";
     divOverlay.style.display = "none";
     divOverlay.style.left = "0px";
     divOverlay.style.top = "0px";
     divOverlay.style.width = "100%";
     divOverlay.style.height = "100%";
     divOverlay.style.position = "fixed";
     divOverlay.style.zIndex = "22";
     divOverlay.style.background = "#f2f2f2";
     divOverlay.style.opacity = "0.7";
     parent.appendChild(divOverlay);
     var divContainer = document.createElement("div");
     divContainer.id = "container";
     var divLoader = document.createElement("div");
     divLoader.id = "loader";
     divContainer.appendChild(divLoader);
     var divSuccessMessage = document.createElement("div");
     divSuccessMessage.id = "success-message";
     divContainer.appendChild(divSuccessMessage);
     var divErrorMessage = document.createElement("div");
     divErrorMessage.id = "error-message";
     divContainer.appendChild(divErrorMessage);
     parent.appendChild(divContainer);
     /**/
    var skipContents = '.skip-content';
    var skipLinks = '.skip-link';
    var cartHeaderContainer = 'header-cart';
    $$(skipLinks).each(function (skipLink) {
        skipLink.observe('click', function (e) {
            Event.stop(e);
            var elem = $(cartHeaderContainer);
            var isSkipContentOpen = elem.hasClassName('skip-active') ? 1 : 0;

            // Hide all stubs
            skipLink.removeClassName('skip-active');
            $$(skipContents).first().removeClassName('skip-active');

            // Toggle stubs
            if (isSkipContentOpen) {
                $('header-cart').hide();
                this.removeClassName('skip-active');
            } else {
                $('header-cart').show();
                this.addClassName('skip-active');
                elem.addClassName('skip-active');
            }
        });
    });

    $$('.skip-link-close').each(function (skipLinkClose) {
        skipLinkClose.observe('click', function (e) {
            Event.stop(e);
            $('header-cart').hide();
        });
    });
    $('minicart').on('click', '.skip-link-close', function (event, element) {
        Event.stop(e);
        $('header-cart').hide();
    });
});


cart = {
    updateTop: function (content, qty) {
        if (content) {
            jQuery('.top-cart').replaceWith(content);
        }
        if (qty) {
            jQuery('#recent_items_count').text(qty);
            jQuery('p.block-subtitle').show();
        }
        jQuery('#minicart .cart-table').click(function (eventObject) {
            jQuery('#topCartContent').css({visibility: 'visible'});
        });
        jQuery('#topCartContent').mouseleave(function (eventObject) {
            jQuery('#topCartContent').css({visibility: 'hidden'});
        });
    },
    show: function () {
        jQuery('#topCartContent').css({visibility: 'visible'});
    }
};

document.observe("dom:loaded", function () {
    cart.updateTop(false, false);
});

