<?php

class Vikont_Fitment_Block_Fitment_Abstract extends Vikont_Fitment_Block_Abstract
{
	protected static $_ariParams = null;
	protected static $_ariData = null;



	protected function _construct()
	{
		parent::_construct();

		if(!self::$_ariParams) {
			self::$_ariParams = Mage::registry('ari_params');
		}

		if(!self::$_ariData) {
			self::$_ariData = Mage::registry('ari_data');
		}
	}



	public function getRequestParams()
	{
		return self::$_ariParams;
	}



	public static function checkParamGroup($paramName)
	{
		return (self::$_ariParams && isset(self::$_ariParams['options'][$paramName]));
	}



	public static function checkParamValue($paramName, $paramValue)
	{
		if(self::$_ariParams && isset(self::$_ariParams['options'][$paramName])) {
			if(is_array(self::$_ariParams['options'][$paramName])) {
				return in_array($paramValue, self::$_ariParams['options'][$paramName]);
			} else {
				return $paramValue == self::$_ariParams['options'][$paramName];
			}
		}
		return false;
	}



	/*
	 * Returns current TMS activity ID
	 * @return int|null
	 */
	public function getActivityId()
	{
		if(null === self::$_activityId) {
			self::$_activityId = isset(self::$_ariParams['params']['activity'])
				?	self::$_ariParams['params']['activity']
				:	null;

			if(null === self::$_activityId) {
				return $this->getTmsActivityId();
			}
		}
		return self::$_activityId;
	}



	public function getPageMode()
	{
		return isset(self::$_ariParams['options']['pageMode']) ? self::$_ariParams['options']['pageMode'] : '';
	}



	public static function rideIsRequired()
	{
		return isset(self::$_ariParams['rideRequired']) ? self::$_ariParams['rideRequired'] : false;
	}

}