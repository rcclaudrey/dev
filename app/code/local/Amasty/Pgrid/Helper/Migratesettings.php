<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Helper_Migratesettings extends Mage_Core_Helper_Abstract
{
    public function exportOdlColumnSettings()
    {

        $selectedGroupId = Mage::helper('ampgrid')->getSelectedGroupId();
        $currentGroup = Mage::getModel('ampgrid/group')->load($selectedGroupId);

        //Export Old parameters
        if (!$currentGroup->getId()) {
            $this->_exportOldAttributes($currentGroup);
        }

        $selectedCategoryGroup = Mage::helper('ampgrid')->getSelectedGroupId('categories');
        $currentCategoryGroup = Mage::getModel('ampgrid/group')->load($selectedCategoryGroup);

        //Export Old parameters
        if (!$currentCategoryGroup->getId()) {
            $this->_exportOldAttributes($currentCategoryGroup, 'categories');
        }

        $groups = Mage::getModel('ampgrid/group')->getCollection();
        foreach($groups as $group) {
            //export Attributes
            $attributes = $group->getData('attributes');
            $attributes = $attributes ? explode(',',$attributes) : array();
            $canEditAttribute = Mage::getStoreConfig('ampgrid/attr/cols');

            foreach ($attributes as $attribute) {
                $attributeModel = Mage::getModel('ampgrid/groupattribute')
                                      ->getCollection()
                                      ->addFieldToFilter('attribute_id', $attribute)
                                      ->getFirstItem();

                $attributeModel->setAttributeId($attribute);
                $attributeModel->setGroupId($group->getId());
                $attributeModel->setIsEditable($canEditAttribute);
                $attributeModel->save();
            }

            //export columns settings
            $colsToRemove = Mage::getStoreConfig('ampgrid/additional/remove');
            $allColumns = Mage::getModel('ampgrid/column')
                              ->getCollectionAll($group->getId());

            foreach ($allColumns as $column) {
                $groupColumn = Mage::getModel('ampgrid/groupcolumn')
                                   ->load($column->getGroupColumnId());

                if ($column->getColumnType() == 'standard') {
                    $visible = (!$colsToRemove || strpos($column->getCode(), $colsToRemove) === false)
                        ? 1 : 0;
                    if ($column->getCode() == 'is_in_stock') {
                        $editable = (int)Mage::getStoreConfig('ampgrid/cols/vis');

                    } else {
                        $editable = (int)Mage::getStoreConfig(
                            'ampgrid/cols/' . $column->getCode()
                        );
                    }
                } elseif($column->getColumnType() == 'extra') {
                    $groupColumn->setColumnId($column->getId());
                    switch($column->getCode()) {
                        case 'is_in_stock':
                            $visible = (int)Mage::getStoreConfig('ampgrid/additional/avail');
                            break;
                        case 'am_special_from_date':
                        case 'am_special_to_date':
                            $visible = (int)Mage::getStoreConfig('ampgrid/additional/special_price_dates');
                            break;
                        case 'thumb':
                        case 'qty_sold':
                        case 'created_at':
                        case 'related_products':
                        case 'up_sells':
                        case 'cross_sells':
                        case 'low_stock':
                        case 'link':
                            $visible = Mage::getStoreConfig(
                                'ampgrid/additional/' . $column->getCode()
                            );
                            break;
                        case 'updated_at':
                            $visible = Mage::getStoreConfig(
                                'ampgrid/additional/modified_at'
                            );
                            break;
                        case 'categories':
                            $visible = Mage::getStoreConfig('ampgrid/additional/category');
                            break;
                    }

                }
                $groupColumn->setIsVisible($visible);
                $groupColumn->setIsEditable($editable);
                $groupColumn->setColumnId($column->getId());
                $groupColumn->setGroupId($group->getId());
                $groupColumn->save();

            }
        }

        foreach ($allColumns as $column) {
            Mage::getConfig()->deleteConfig('ampgrid/additional/' . $column->getCode());
            Mage::getConfig()->deleteConfig('ampgrid/cols/' . $column->getCode());

        }

        Mage::getConfig()->deleteConfig('ampgrid/attr/cols');
        Mage::getConfig()->deleteConfig('ampgrid/additional/remove');
        Mage::getConfig()->deleteConfig('ampgrid/cols/vis');
        Mage::getConfig()->deleteConfig('ampgrid/additional/avail');
        Mage::getConfig()->deleteConfig('ampgrid/additional/special_price_dates');
        Mage::getConfig()->deleteConfig('ampgrid/additional/modified_at');
        Mage::getConfig()->deleteConfig('ampgrid/attr/additional/category/thumb');
        Mage::getConfig()->saveConfig('ampgrid/general/exported_columns_from_system', 1);
        Mage::getConfig()->saveConfig('ampgrid/general/just_installed', 0);
        Mage::getConfig()->cleanCache();
    }

    /**
     * export old attributes from 4.9 to 4.10
     * @param Amasty_Pgrid_Model_Group $group
     * @param string                   $attributesKey
     *
     * @throws Exception
     */
    protected function _exportOldAttributes(Amasty_Pgrid_Model_Group $group, $attributesKey='')
    {
        $extraKey = $attributesKey;
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $extraKey .= $userId;

        //first run module after update
        $defAttributes = Mage::getStoreConfig('ampgrid/attributes/ongrid'.$attributesKey);
        $defCategory   = Mage::getStoreConfig('ampgrid/attributes/category'.$attributesKey);
        $attributes = Mage::getStoreConfig('ampgrid/attributes/ongrid' . $extraKey);
        $category = Mage::getStoreConfig('ampgrid/attributes/category'. $extraKey);
        if ($category == null) {
            //OLD OPTION BY DEFAULT
            $category = Mage::getStoreConfig('ampgrid/additional/category');
        }

        $defGroup = Mage::getModel('ampgrid/group');
        $defGroup->setData('title', $attributesKey ? 'Default('.$attributesKey.')' : 'Default');
        if($defAttributes) {
            $realAttributes = array();
            $defAttributesRow = explode(',',$defAttributes);
            foreach ($defAttributesRow as $attribute) {
                $attr = Mage::getModel('eav/entity_attribute')->load($attribute);
                if($attr->getId()) {
                    $realAttributes[] = $attribute;
                }
            }
            $defGroup->setData('attributes', implode(',',$realAttributes));
        }
        $defGroup->setData('additional_columns', $defCategory ? Mage::getModel('ampgrid/group')->getCategoriesKey(): '');
        $defGroup->setData('user_id', $userId);
        $defGroup->setData('is_default', 1);
        $defGroup->save();

        Mage::app()->getConfig()->saveConfig(
            'ampgrid/attributes/ongrid' . $attributesKey, $defGroup->getId()
        );

        Mage::getConfig()->saveConfig('ampgrid/attributes/ongrid' . $extraKey, $defGroup->getId());
        Mage::app()->getStore()->setConfig('ampgrid/attributes/ongrid' . $extraKey, $defGroup->getId());

        if($attributes || $category == 1) {
            $group->setData('title', sprintf('Default(%s)',
                    Mage::getSingleton('admin/session')->getUser()->getUsername())
            );
            if($attributes) {
                $realAttributes = array();
                $attributesRow = explode(',', $attributes);
                foreach ($attributesRow as $attribute) {
                    $attr = Mage::getModel('eav/entity_attribute')->load($attribute);
                    if($attr->getId()) {
                        $realAttributes[] = $attribute;
                    }
                }
                $group->setData('attributes', implode(',',$realAttributes));
            }
            $group->setData('additional_columns', $defCategory ? Mage::getModel('ampgrid/group')->getCategoriesKey(): '');
            $group->setData('user_id', $userId);
            $group->save();
        }
        //Group successfully saved, drop and rewrite config
        if ($group->getId()) {
            Mage::getConfig()->deleteConfig('ampgrid/attributes/category' . $extraKey);
            Mage::getConfig()->saveConfig('ampgrid/attributes/ongrid' . $extraKey, $group->getId());
            Mage::app()->getStore()->setConfig('ampgrid/attributes/ongrid' . $extraKey, $group->getId());
        }
    }
}