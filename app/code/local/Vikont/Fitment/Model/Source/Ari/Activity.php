<?php

class Vikont_Fitment_Model_Source_Ari_Activity extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

	public function getAllOptions($withEmpty = true)
	{
		if(!$this->_options) {
			$helper = Mage::helper('fitment');
			$data = $helper->getAriActivities();

			if(is_array($data)) {
				$this->_options = array();

				foreach($data as $item) {
					$this->_options[] = array(
						'value' => $item['Id'],
						'label' => $item['Name'],
					);
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
				$options = array(array(
						'value' => 0,
						'label' => Mage::helper('core')->__('-- Cannot get activity list --'),
					)
				);
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