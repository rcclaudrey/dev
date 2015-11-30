PickupAtStore = {
    debug: false,
    days: [],
    hours: [],
    resetDays: function() {
        if (PickupAtStore.debug)
            if (PickupAtStore.debug)
                console.log("resetDays")
        $("pickupatstore_days").selectedIndex = 0;
        $("pickupatstore_days").select('OPTION').each(function(o) {
            if (o.value)
                o.remove();
        })
        PickupAtStore.date = null;
    },
    resetHours: function() {
        if (PickupAtStore.debug)
            console.log("resetHours")
        if (PickupAtStore.timeEnabled) {
            $("pickupatstore_hours").selectedIndex = 0;

            $("pickupatstore_hours").select('OPTION').each(function(o) {

                if (o.value)
                    o.remove();
            })
            PickupAtStore.time = null;
        }
    },
    resetAll: function() {
        if (PickupAtStore.debug)
            console.log("resetAll")
        if (PickupAtStore.dateEnabled) {
            PickupAtStore.resetDays();
            if (PickupAtStore.timeEnabled) {
                PickupAtStore.resetHours();
            }
        }
    },
    getStoreIndex: function(id) {
        i = 0;
        places.each(function(p) {

            if (p.id == id) {
                output = i;
            }
            i++;
        })
        return output;
    },
    observer: function() {

        if (PickupAtStore.debug)
            console.log("observer")
        if ($('pickupatstore') != null) {
            if (PickupAtStore.dropdownEnabled) {
                $('pickupatstore').observe("change", function() {
                    if (PickupAtStore.gmapEnabled) {
                        if (PickupAtStore.getStoreId()) {
                            displayStore(PickupAtStore.getStoreIndex(PickupAtStore.getStoreId()))

                        }
                    }
                    PickupAtStore.update();
                })
            }

            if (PickupAtStore.dateEnabled) {
                $('pickupatstore_days').observe("change", function() {
                    if (PickupAtStore.timeEnabled) {
                        PickupAtStore.resetHours();
                        if ($('pickupatstore_days').value) {
                            PickupAtStore.updateHours()
                        }

                    }
                    else {
                        if (PickupAtStore.getStoreId() == '' || $('pickupatstore_days').value == '') {
                            $$('input[type="radio"][name="shipping_method"]').each(function(el) {
                                el.checked = false;
                            })

                        }
                        else {
                            $('s_method_pickupatstore_' + PickupAtStore.getStoreId()).checked = true;
                        }
                    }

                })
                if (PickupAtStore.timeEnabled) {
                    $('pickupatstore_hours').observe("change", function(el) {
                        if (PickupAtStore.getStoreId() == '' || $('pickupatstore_hours').getValue() == '' || $('pickupatstore_days').getValue() == '') {
                            $$('input[type="radio"][name="shipping_method"]').each(function(el) {
                                el.checked = false;
                            })
                        }
                        else {
                            $('s_method_pickupatstore_' + PickupAtStore.getStoreId()).checked = true;
                        }
                    })
                }

            }
            //PickupAtStore.resetAll();
        }

    },
    getStoreId: function() {
        if (PickupAtStore.debug)
            console.log("getStoreId")
        return $('pickupatstore').getValue().replace('pickupatstore_', '');
    },
    setStoreId: function(id) {
        if (PickupAtStore.debug)
            console.log("setStoreId", PickupAtStore)
        $('pickupatstore').setValue('pickupatstore_' + id);
    },
    getData: function() {
        if (PickupAtStore.debug)
            console.log("getData")
        eval("PickupAtStore.datetime =PickupAtStore.store_" + PickupAtStore.getStoreId() + ".datetime");
    },
    update: function() {
        if (PickupAtStore.debug)
            console.log("update")
        $$('input[type="radio"][name="shipping_method"]').each(function(el) {
            el.checked = false;
        })
        PickupAtStore.resetAll();
        if (PickupAtStore.dateEnabled) {

            if (PickupAtStore.getStoreId())
                PickupAtStore.updateDays();
        } else {

            if (PickupAtStore.getStoreId()) {
                $('s_method_pickupatstore_' + PickupAtStore.getStoreId()).checked = true;
            }

        }
    },
    updateDays: function(reset) {
        if (PickupAtStore.debug)
            console.log("updateDays", PickupAtStore)

        if (typeof reset == 'undefined') {
            $$('input[type="radio"][name="shipping_method"]').each(function(el) {
                el.checked = false;
            })
        }
        PickupAtStore.getData()
        found = false;
        cnt = 1;
        PickupAtStore.days.each(function(d) {
            if (PickupAtStore.datetime[d.value] && PickupAtStore.datetime != false) {
                if (PickupAtStore.debug)
                    console.log(PickupAtStore.date + "=?=" + d.value)

                if (PickupAtStore.date == d.value) {
                    index = cnt;
                    found = true;
                }

                option = Builder.node('OPTION', {value: d.value}, d.label);
                $("pickupatstore_days").insert({"bottom": option})
                cnt++;
            }
        })

        if (!found)
            $("pickupatstore_days").selectedIndex = 0;
        else
            $("pickupatstore_days").selectedIndex = index;


    },
    updateHours: function(reset) {
        if (PickupAtStore.debug)
            console.log("updateHours")

        if (typeof reset == 'undefined') {
            $$('input[type="radio"][name="shipping_method"]').each(function(el) {
                el.checked = false;
            })
        }
        PickupAtStore.getData()
        options_show = new Array;
        options_hide = new Array;
        if ($('pickupatstore_days').value != '') {
            found = false;
            cnt = 1;
            PickupAtStore.hours.each(function(h) {

                if (PickupAtStore.datetime[ $('pickupatstore_days').value][0] <= h.value && h.value <= PickupAtStore.datetime[ $('pickupatstore_days').value][1]) {

                    if (PickupAtStore.debug)
                        console.log(PickupAtStore.time + '=?=' + h.value)
                    if (PickupAtStore.time == h.value) {

                        index = cnt;
                        found = true;

                    }

                    $("pickupatstore_hours").insert({"bottom": Builder.node('OPTION', {value: h.value}, h.label)})
                    cnt++;
                }
            })

            if (!found)
                $("pickupatstore_hours").selectedIndex = 0;
            else
                $("pickupatstore_hours").selectedIndex = index;

        }
    }
}

