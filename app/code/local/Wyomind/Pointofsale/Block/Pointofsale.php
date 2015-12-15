<?php

class Wyomind_Pointofsale_Block_Pointofsale extends Mage_Core_Block_Template {

    public function getPointofsale() {
        if (Mage::app()->getStore()->getStoreId())
            $collection = Mage::getModel('pointofsale/pointofsale')->getPlacesByStoreId(Mage::app()->getStore()->getStoreId())->addFieldToFilter('status', Array('status' => 1))->setOrder('`position`', 'ASC');
        else
            $collection = Mage::getModel('pointofsale/pointofsale')->getCollection();
        return $collection;
    }

    public function getCountries() {
        $collection = Mage::getModel('pointofsale/pointofsale')->getCountries(Mage::app()->getStore()->getStoreId())->addFieldToFilter('status', Array('status' => 1));
        $countries = array();
        foreach ($collection as $country) {
            if ($country->getCountryCode()) {
                $countryModel = Mage::getModel('directory/country')->loadByCode($country->getCountryCode());
                $countryName = $countryModel->getName();
                $countries[] = array(
                    'code' => $country->getCountryCode(),
                    'name' => $countryName,
                );
            }
        }
        return $countries;
    }

    public function renderJs() {
        $html = " 
                 W_GP = {
                strings:{
                        getDirections:             '" . Mage::helper('pointofsale')->__("Get Directions") . "',
                        showOnGoogleMap:           '" . Mage::helper('pointofsale')->__("Show on Google Map") . "',
                        from:                      '" . Mage::helper('pointofsale')->__("From") . "',
                        noStoreLocated:            '" . Mage::helper('pointofsale')->__("No store located") . "',
                        youAreHere:                '" . Mage::helper('pointofsale')->__("You are here") . "',
                        theClosestStoreIs:         '" . Mage::helper('pointofsale')->__("The nearest store is") . "',
                        noResultFound:             '" . Mage::helper('pointofsale')->__("No result found.") . "',
                        unableToFindYourLocation:  '" . Mage::helper('pointofsale')->__("Unable to find your location") . "',
                        distanceCalculationFailed: '" . Mage::helper('pointofsale')->__("Distance calculation failed") . "',
                        showMyLocation:            '" . Mage::helper('pointofsale')->__("show my location") . "',
                        changeMyLocation:          '" . Mage::helper('pointofsale')->__("change my location") . "',
                        yourLocation:              '" . Mage::helper('pointofsale')->__("Your location") . "',
                        findMe:                    '" . Mage::helper('pointofsale')->__("Find me") . "',
                        enterYourLocation:         '" . Mage::helper('pointofsale')->__("Enter your location (city, address, zipcode...)") . "',
                        setANewLocation:           '" . Mage::helper('pointofsale')->__("Set a new location") . "',
                        youDirections :            '" . Mage::helper('pointofsale')->__("Your directions") . "',
                        close:                     '" . Mage::helper('pointofsale')->__("Close") . "',
                        close:                     '" . Mage::helper('pointofsale')->__("Print") . "',
                        searchingYourLocation:     '" . Mage::helper('pointofsale')->__("Searching your location") . "',
                        allOurStoreLocations:      '" . Mage::helper('pointofsale')->__("All our store locations") . "',
                        selectACountry:            '" . Mage::helper('pointofsale')->__("Select a country") . "',
                        allCountries:              '" . Mage::helper('pointofsale')->__("All countries") . "'


                },
                        myAddress:null

                }

";
        $i = 0;
        $data = array();
        foreach ($this->getPointofsale() as $place) {


            $fullAdress = $place->getAddressLine_1();
            if ($place->getAddressLine_2())
                $fullAdress.="," . $place->getAddressLine_2();
            $fullAdress.="," . $place->getCity();
            if ($place->getCountryCode())
                $fullAdress.="," . Mage::getModel('directory/country')->loadByCode($place->getCountryCode())->getName();
            if (!$place->getGoogleRequest())
                $request = $fullAdress;
            else
                $request = $place->getGoogleRequest();

            $data[] = array(
                "id" => $place->getPlaceId(),
                "title" => "<h4><b>" . $place->getName() . "</b></h4>",
                "links" => array(
                    "directions" => "<a href='javascript:getDirections(" . $i . ")'>" . Mage::helper('pointofsale')->__("Get Directions") . "</a>",
                    "showOnMap" => "<a target='_blank' href='http://maps.google.com/maps?q=" . $request . "'>" . Mage::helper('pointofsale')->__("Show on Google Map") . "</a>"
                ),
                "name" => $place->getName(),
                "lat" => $place->getLatitude(),
                "lng" => $place->getlongitude(),
                "country" => $place->getCountryCode(),
                "duration" => array("text" => null, "value" => null),
                "distance" => array("text" => null, "value" => null)
            );


            $i++;
        }
        $html.= ' places=' . json_encode($data) . "\r\n";
        $html.="    $$('.go-to-place').each(function(p) {

                        $('place_' + p.id).hide();
                        p.observe('click', function() {
                            displayStore(getStoreIndexById(p.id))
                        })
                    })
                    if ($('country_place')) {
                        $('country_place').observe('change', function() {
                            updateList()
                        })
                    }

                    __initialize()";
        return $html;
    }

    public function renderScroller() {
        $html = null;
        foreach ($this->getPointofsale() as $place) {

            $html.= '<div class="place ' . $place->getCountryCode() . '" >';

            $html.= '<h3><a href="javascript:void(0)" class="go-to-place" id="' . $place->getPlaceId() . '">' . $place->getName() . '</a></h3>';
            $html.= '<div id="place_' . $place->getPlaceId() . '" class="details">';
            $html.= Mage::helper('pointofsale')->getStoreDescription($place);
            $html.= '</div>
                </div>';
        }

        return $html;
    }

}
