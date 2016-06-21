<?php

class Vikont_Pulliver_Helper_Service extends Mage_Core_Helper_Abstract
{

	/*
     * Returns whether module output is disabled from Advanced->Disable module output in Admin Config section
	 *
     * @return bool
     */
    public static function isModuleOutputDisabled()
    {
        return Mage::getStoreConfigFlag('advanced/modules_disable_output/Vikont_Pulliver');
    }


	public static function isModuleAllowed()
	{
		return Mage::getStoreConfigFlag('lspeedc/general/enabled') && !self::isModuleOutputDisabled();
	}

}