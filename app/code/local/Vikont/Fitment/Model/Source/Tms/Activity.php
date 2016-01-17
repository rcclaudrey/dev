<?php

class Vikont_Fitment_Model_Source_Tms_Activity extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

	public function getAllOptions($withEmpty = true)
	{
		if(!$this->_options) {
			$this->_options = array();
			foreach(Vikont_Fitment_Helper_Data::getTmsActivities() as $id => $item) {
				$this->_options[] = array(
					'value' => $id,
					'label' => $item['name'],
				);
			}
		}

		if ($withEmpty) {
			$options = $this->_options;

			array_unshift($options, array(
				'value' => '',
				'label' => Mage::helper('core')->__('-- Please Select --'),
			));

			return $options;
        }

		return $this->_options;
	}



	public function toOptionArray()
	{
		return $this->getAllOptions();
	}

}