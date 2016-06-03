<?php

class Vikont_Pulliver_Model_Mysql4_Sku extends Mage_Core_Model_Mysql4_Abstract
{

	public function _construct()
	{
		$this->_init('oemdb/sku', 'sku');
	}

}
