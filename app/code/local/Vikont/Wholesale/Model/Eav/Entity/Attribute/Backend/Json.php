<?php

class Vikont_Wholesale_Model_Eav_Entity_Attribute_Backend_Json extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

	/**
	 * Prepare data for save
	 *
	 * @param Varien_Object $object
	 * @return Mage_Eav_Model_Entity_Attribute_Backend_Abstract
	 */
	public function beforeSave($object)
	{
		$attributeCode = $this->getAttribute()->getAttributeCode();
		$data = $object->getData($attributeCode);
		if($data instanceof Varien_Object) {
			$data = get_class($data) . '|' . json_encode($data->getData());
		}
		$object->setData($attributeCode, $data);

		return parent::beforeSave($object);
	}



	public function afterLoad($object)
	{
		$attributeCode = $this->getAttribute()->getAttributeCode();
		$str = $object->getData($attributeCode);
		$value = json_decode($str);
		if(null === $value || json_last_error()) {
			@list($className, $jsonedData) = explode('|', $str, 2);
			$data = json_decode($jsonedData, true);
			if(null !== $data) {
				$value = new $className;
				$value->setData($data);
			}
		}
		$object->setData($attributeCode, $value);

		return $this;
	}

}
