<?php
//
class Balanced_MassCategories_Block_Catalog_Product_Edit_Action_Attribute_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected function _construct()
    {
        parent::_construct();

        $this->setId('attributes_update_tabs');
        $this->setDestElementId('attributes_edit_form');
        $this->setTitle(Mage::helper('catalog')->__(''));
    }
}
