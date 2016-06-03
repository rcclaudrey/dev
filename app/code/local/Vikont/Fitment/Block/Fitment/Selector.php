<?php

class Vikont_Fitment_Block_Fitment_Selector extends Vikont_Fitment_Block_Fitment_Abstract
{

	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/selector.phtml');
	}



	public function getRide()
	{
		$tmsActivityId = $this->getActivityId();

		$fitmentId = isset(self::$_ariParams['options']['fitmentId'])
				?	self::$_ariParams['options']['fitmentId']
				:	null;

		if($fitmentId) {
			$vehicle = isset(self::$_ariParams['vehicle']) ? self::$_ariParams['vehicle'] : '';
			return Mage::helper('fitment')->completeRideInfo($tmsActivityId, $fitmentId, $vehicle);
		} else {
			return Mage::helper('fitment')->getCurrentRide($tmsActivityId);
		}
	}

}