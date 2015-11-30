<?php

class Wyomind_Pickupatstore_Helper_Data extends Mage_Core_Helper_Abstract {

    function formatDatetime($date) {
        
        return $this->dateTranslate(Mage::getSingleton('core/date')->gmtDate(Mage::getStoreConfig("carriers/pickupatstore/dateformat").' '.Mage::getStoreConfig("carriers/pickupatstore/timeformat"), Mage::getSingleton('core/date')->timeStamp($date)));
    }
     function formatDate($date) {
        return $this->dateTranslate(Mage::getSingleton('core/date')->gmtDate(Mage::getStoreConfig("carriers/pickupatstore/dateformat"), Mage::getSingleton('core/date')->timeStamp($date)));
    }

    public function dateTranslate($date) {

        $longDays = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $longDaysLocale = array($this->__('Monday'), $this->__('Tuesday'), $this->__('Wednesday'), $this->__('Thursday'), $this->__('Friday'), $this->__('Saturday'), $this->__('Sunday'));

        $shortDays = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
        $shortDaysLocale = array($this->__('Mon'), $this->__('Tue'), $this->__('Wed'), $this->__('Thu'), $this->__('Fri'), $this->__('Sat'), $this->__('Sun'));

        $months = array("January", 'February', 'March', 'April', 'May', 'June', 'Jully', 'August', 'September', 'October', 'November', 'December');
        $monthsLocale = array($this->__('January'), $this->__('Februar'), $this->__('March'), $this->__('April'), $this->__('May'), $this->__('June'), $this->__('Jully'), $this->__('August'), $this->__('September'), $this->__('October'), $this->__('November'), $this->__('December'));

        $date = str_replace($longDays, $longDaysLocale, $date);
        $date = str_replace($shortDays, $shortDaysLocale, $date);
        $date = str_replace($months, $monthsLocale, $date);
        return $date;
    }

}
