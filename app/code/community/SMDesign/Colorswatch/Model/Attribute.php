<?php
class SMDesign_Colorswatch_Model_Attribute extends Mage_Core_Model_Abstract {
	protected $_optionCollection;
	protected $_model;
	protected $_sort = 'asc';
	
	protected function getOptionsCollection() {
		if (empty($this->_optionCollection)) {
			$this->_optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection');
		}
		return $this->_optionCollection;
	}
	
	public function setModel($model) {
		$this->_model = $model;
		return $this;
	}
	
	function getOptions() {
		if ($this->_model instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
			return $this->getOptionsCollection()
				->setPositionOrder($this->_sort)
				->setAttributeFilter($this->_model->getId());
		}
	}

}