


document.observe('dom:loaded', function() {
    GtsValidator.notify = Builder.node('DIV', {id: 'GtsValidator'})
    if (typeof GtsValidator != 'undefined') {
        if (typeof GtsValidator.badge != "undefined") {
            if (GtsValidator.badge) {
                GtsValidator.notify.update(Translator.translate('Google Trusted Stores badge implemented!'));
                GtsValidator.notify.setStyle({"color": "green"});
            }
            else{
                GtsValidator.notify.update(Translator.translate('Google Trusted Stores badge can\'t be found!'));
                GtsValidator.notify.setStyle({"color": "red"});
            }
        }
        if (typeof GtsValidator.order != "undefined") {
            if (GtsValidator.order){
                GtsValidator.notify.update(Translator.translate('Google Trusted Stores confirmation module implemented!'));
                GtsValidator.notify.setStyle({"color": "green"});
            }
            else{
                GtsValidator.notify.update(Translator.translate('Google Trusted Stores confirmation module can\'t be found!'));
                GtsValidator.notify.setStyle({"color": "red"});
            }
        }

    }
    else {
        GtsValidator.notify(Translator.translate('Google Trusted Stores doesn\'t seem to be implemented!'));
        GtsValidator.notify.setStyle({"color": "red"});
    }
    $$('BODY')[0].insert({top: GtsValidator.notify});
})