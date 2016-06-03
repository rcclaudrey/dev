<?php

class Vikont_OEMGrid_Block_Adminhtml_Part_Import extends Mage_Adminhtml_Block_Template
{

    public function _construct()
    {
        parent::_construct();
		$this->setTemplate('oemgrid/import.phtml');
    }

}