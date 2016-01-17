<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

abstract class Amasty_Shopby_Helper_Layer_View_Strategy_Modeled extends Amasty_Shopby_Helper_Layer_View_Strategy_Abstract
{
    /** @var  Mage_Catalog_Model_Resource_Eav_Attribute */
    protected $attribute;

    /** @var  Amasty_Shopby_Model_Filter */
    protected $model;

    public function setFilter(Mage_Catalog_Block_Layer_Filter_Abstract $filter)
    {
        parent::setFilter($filter);

        $this->attribute = $filter->getAttributeModel();
        $this->model = $this->getFilterModel();
    }

    public function prepare()
    {
        parent::prepare();

        $this->transferModelData();
        $this->filter->setData('hide_counts', !Mage::getStoreConfig('catalog/layered_navigation/display_product_count') || ($this->model && $this->model->getHideCounts()));
    }

    protected function setCollapsed()
    {
        return $this->isCollapseEnabled() && $this->model && $this->model->getCollapsed();
    }

    protected function setPosition()
    {
        return $this->attribute->getPosition();
    }

    protected function transferModelData()
    {
        if (!$this->model) {
            return;
        }

        $fields = $this->getTransferableFields();
        foreach ($fields as $field) {
            $this->filter->setData($field, $this->model->getData($field));
        }
    }

    protected function getTransferableFields()
    {
        return array();
    }

    protected function getFilterModel()
    {
        $settings = $this->_getDataHelper()->getAttributesSettings();
        $attributeId = $this->attribute->getId();
        /** @var Amasty_Shopby_Model_Filter $model */
        $model = isset($settings[$attributeId]) ? $settings[$attributeId] : null;
        return $model;
    }

    public function getIsExcluded()
    {
        if (!$this->model) {
            return true;
        }

        $moduleName = Mage::app()->getRequest()->getModuleName();
        if (in_array($moduleName, array('sqli_singlesearchresult', 'catalogsearch'))) {
            return false;
        }

        $categoryId = $this->getCurrentCategoryId();

        $exclude = false;

        $includeCategories = $this->model->getIncludeInArray();
        if ($includeCategories) {
            if (!in_array($categoryId, $includeCategories)) {
                $exclude = true;
            }
        }

        if (!$exclude) {
            $excludeCategories = $this->model->getExcludeFromArray();
            if (in_array($categoryId, $excludeCategories)) {
                $exclude = true;
            }
        }

        if (!$exclude) {
            $ids = trim(str_replace(' ', '', $this->model->getDependOn()));
            if (!empty($ids)) {
                $ids = explode(',', $ids);

                /** @var Amasty_Shopby_Helper_Attributes $attrHelper */
                $attrHelper = Mage::helper('amshopby/attributes');
                $allSelectedIds = $attrHelper->getRequestedOptionIds();

                if (!array_intersect($allSelectedIds, $ids)) {
                    $exclude = true;
                }
            }
        }

        return $exclude;
    }
}
