<?php

class Vikont_Fitment_Block_Abstract extends Mage_Core_Block_Template
{
	protected static $_activityId = null; // TMS activity ID



	/*
	 * Returns TMS activity ID currently selected
	 */
	public function getTmsActivityId()
	{
		if(null === self::$_activityId) {
			self::$_activityId = $this->getData('tms_activity_id');

			if(null === self::$_activityId) {
				self::$_activityId = Vikont_Fitment_Helper_Data::getActivityId();
			}
		}
		return self::$_activityId;
	}



	/*
	 * Returns makes for current activity
	 */
	public function getMakes($filter = array())
	{
		$ariActivityId = Vikont_Fitment_Helper_Data::getTmsActivity($this->getTmsActivityId(), 'ari_activity');

		if(count($filter)) {
			$params = array(
				'subject' => 'makes',
				'activity' => $ariActivityId,
			);
			$params = array_merge($params, $filter);
			$result = Mage::helper('fitment')->getFitmentValues($params);
		} else {
			$result = Mage::helper('fitment')->getMakes($ariActivityId);
		}

		return is_array($result) ? $result : array();
	}

}