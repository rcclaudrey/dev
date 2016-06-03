<?php

class Vikont_ARIOEM_Model_Source_Oemavailability extends Vikont_ARIOEM_Model_Source_Abstract
{

	public static function getLanguage($manufacturer, $stockQtyAvailable, $stockQtyRequested = 0)
	{
		
/*
 * at part list:
 * if avail, then draw a green rect IN STOCK
 * if not, say "Usually ships in 1-3 business days"
 * 
 * at shipping list:
 * if avail, say IN STOCK | SHIP IN 24 HOURS!
 * if not enough, say %QTY% ON WAREHOUSE TRANSFER
 */
		if($stockQtyRequested) {
			if($stockQtyAvailable >= $stockQtyRequested) {
				return Mage::helper('arioem')->__('Ships in 1 business day');
			} else {
				switch ($manufacturer) {
					default:
						return Mage::helper('arioem')->__('Ships in 5-7 business days');
				}
			}
		} else {
			return $stockQtyAvailable
				?	Mage::helper('arioem')->__('Ships in 1 business day')
				:	Mage::helper('arioem')->__('Out of stock');
		}
/*
		if($stockQtyRequested) {
			if($stockQtyAvailable >= $stockQtyRequested) {
				return Mage::helper('arioem')->__('Ships in 1 business day');
			} else {
				switch ($manufacturer) {
					default:
						return Mage::helper('arioem')->__('Ships in 5-7 business days');
				}
			}
		} else {
			return $stockQtyAvailable
				?	Mage::helper('arioem')->__('Ships in 1 business day')
				:	Mage::helper('arioem')->__('Out of stock');
		}
/**/
	}

}