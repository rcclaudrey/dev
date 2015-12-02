<?php

class Vikont_ARIOEM_Model_Source_Abstract extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

	public function getAllOptions()
	{
		if (is_null($this->_options)) {
			$this->_options = $this->toOptionArray();
		}
		return $this->_options;
	}


	public function getOptionArray()
	{
		$res = array();

		foreach ($this->getAllOptions() as $option) {
			$res[$option['value']] = $option['label'];
		}

		return $res;
	}


	public function getOptionText($value)
	{
		$options = $this->getAllOptions();

		foreach ($options as $option) {
			if ($option['value'] == $value) {
				return $option['label'];
			}
		}
		return false;
	}


	public static function toShortOptionArray()
	{
		return array();
	}


	public function toOptionArray()
	{
		$res = array();

		foreach($this->toShortOptionArray() as $key => $value)
			$res[] = array(
				'value' => $key,
				'label' => $value
			);

		return $res;
	}


	public function getLabel($value)
	{
		return $this->getOptionText($value);
	}

}