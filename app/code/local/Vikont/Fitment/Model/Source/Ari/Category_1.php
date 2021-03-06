<?php

class Vikont_Fitment_Model_Source_Ari_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const CACHE_KEY = 'categories-all';


	public function getErrorMessageOptions()
	{
		return array(array(
					'value' => 0,
					'label' => Mage::helper('core')->__('-- Cannot get category list --'),
				)
			);
	}



	public function getAllOptions($withEmpty = true)
	{
		if(!$this->_options) {
			$cachedValue = Mage::getStoreConfig('fitment/cache/' . self::CACHE_KEY);

			if($cachedValue) {
				$this->_options = json_decode($cachedValue, true);
			} else {
				$this->_options = array();
				$helper = Mage::helper('fitment');
				$activities = $helper->getAriActivities();

				if(!is_array($activities)) {	return $this->getErrorMessageOptions();	}

				foreach($activities as $activity) {
					$categories = Mage::helper('fitment/api')->request('categories', array($activity['Id']));
					if(!$categories) {	return $this->getErrorMessageOptions();	}

					foreach($categories as $category) {
						$this->_options[] = array(
							'value' => $category['Id'],
							'label' => sprintf('%s: %s', $activity['Name'], $category['Name']),
						);
					}
					Mage::getConfig()->saveConfig('fitment/cache/' . self::CACHE_KEY, json_encode($this->_options));
				}
			}
		}

		if ($withEmpty) {
			$options = $this->_options;

			if(is_array($options)) {
				array_unshift($options, array(
					'value' => 0,
					'label' => Mage::helper('core')->__('-- Please Select --'),
				));
			} else {
				$options = $this->getErrorMessageOptions();
			}

			return $options;
        }

		return $this->_options;
	}



	public function toOptionArray()
	{
		return $this->getAllOptions();
	}

}