<?php

class Vikont_Wholesale_Model_Source_Dealer_Status extends Vikont_Wholesale_Model_Source_Abstract
{
	const NONE = 0; // no business retationship ever established
	const CANDIDATE = 1; // application sent
	const APPROVED = 2; // application approved
	const DECLINED = 3; // application declined
	const TERMINATED = 4; // cooperation terminated


	public static function toShortOptionArray()
	{
		$helper = Mage::helper('wholesale');

		return array(
			self::NONE => $helper->__('Not ever tried'),
			self::CANDIDATE => $helper->__('Application sent'),
			self::APPROVED => $helper->__('Active dealer (Partnership approved)'),
			self::DECLINED => $helper->__('Application declined'),
			self::TERMINATED => $helper->__('Terminated'),
		);
	}

}