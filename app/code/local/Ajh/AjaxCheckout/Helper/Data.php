<?php
/**
 * Created by PhpStorm.
 * User: Camille
 * Date: 2/19/15
 * Time: 10:30 PM
 */

class Ajh_AjaxCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED = 'ajh_ajaxcheckout/settings/enabled';

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }
}