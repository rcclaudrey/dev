<?php

class Vikont_Fitment_Block_Fitment_Activity extends Vikont_Fitment_Block_Fitment_Abstract
{

	protected function _construct()
	{
		parent::_construct();

		$this->setTemplate('vk_fitment/fitment/activity.phtml');
	}

}