<?php

class Wyomind_Googletrustedstores_Block_Adminhtml_System_Config_Form_Field_Testbadgelink extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $code = Mage::app()->getRequest()->getParam('website');
        if (!empty($code)) {
            $website = Mage::app()->getWebsite(Mage::getConfig()->getNode('websites/' . $code)->system->website->id->asArray());
            $website = $website->getId();
        } else {
            $websites = Mage::app()->getWebsites();
            $ws = array();
            foreach ($websites as $website) {
                if (count($website->getStores()) > 0) {
                    $ws[] = $website;
                }
            }
            if (count($ws) >= 1) {
                $tmp = $ws[0];
                $website = Mage::app()->getWebsite($tmp->getId());
                $website = $website->getId();
            } else {
                $website = null;
            }
        }


        $url_ship = Mage::helper("adminhtml")->getUrl('adminhtml/googletrustedstores/badge/', array('website' => $website != null ? $website : -1));
        $url_validator = Mage::getUrl('googletrustedstores/validator/badge/', array('website' => $website != null ? $website : -1));

        $html .= "<input type='text' class='input-text' style='width:200px;' placeholder='Product sku' id='product-sku'/>"
                . "<button id='gts-test-badbge-btn' onclick='javascript:testBadge(\"$website\",\"$url_ship\");return false;'>"
                . Mage::helper('googletrustedstores')->__('Go') . "</button>"
                . "<br/>"
                . "<textarea id='gts-badge-test-page'></textarea>"
                . "<a target='_blank' id='GtsValidatorBadgeUrl' base='".$url_validator."' href=''></a>";

        return $html;
    }

}
