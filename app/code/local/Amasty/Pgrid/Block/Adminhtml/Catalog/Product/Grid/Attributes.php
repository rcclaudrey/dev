<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * Class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Attributes
 *
 * @method string getChangeGroupUrl
 * @method array getStandardColumns
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Attributes extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->initVariables();
        $this->setTemplate('ampgrid/columns.phtml');
    }

    protected function initVariables()
    {

        $attributesKey = $this->getAttributesKey();
        $groupId = Mage::helper('ampgrid')->getSelectedGroupId($attributesKey);
        $standardColumns = Mage::getModel('ampgrid/column')->getCollectionStandard($groupId);
        $extraColumns = Mage::getModel('ampgrid/column')->getCollectionExtra($groupId);
        $attributeColumns = Mage::getModel('ampgrid/groupattribute')->getCollectionAttribute($groupId);

        $variables = array(
            'group_id'           => $groupId,
            'groups'            => Mage::helper('ampgrid')->getGroupsByUserId(),
            'change_group_url'  => $this->getUrl(
                'adminhtml/ampgrid_attribute/changeGroup'
            ),
            'standard_columns'  => $standardColumns,
            'extra_columns'     => $extraColumns,
            'attribute_columns' => $attributeColumns,
        );

        foreach ($variables as $varName => $value) {
            $this->setData($varName, $value);
        }
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    public function getAttributes()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('main_table.frontend_input', array('in' => array('text', 'select', 'multiselect', 'boolean', 'textarea', 'price', 'weight', 'date')))
            ->addFieldToFilter('main_table.attribute_code', array('nin' => Mage::helper('ampgrid')->getDefaultColumns()))
            ->setOrder('main_table.frontend_label', "ASC");
        return $collection;
    }

    /**
     * @deprecated from 4.11
     * @return array
     */
    public function getSelectedAttributes()
    {
        return Mage::helper('ampgrid')->getGridAttributes($this->getAttributesKey());
    }

    public function getSaveUrl()
    {
        $url = $this->getUrl('adminhtml/ampgrid_attribute/save');
        if (Mage::getStoreConfig('web/secure/use_in_adminhtml'))
        {
            $url = str_replace(Mage::getStoreConfig('web/unsecure/base_url'), Mage::getStoreConfig('web/secure/base_url'), $url);
        }
        return $url;
    }

}