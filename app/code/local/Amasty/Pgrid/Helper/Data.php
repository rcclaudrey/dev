<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected function _getStore()
    {
        $ret = NULL;
        
        $storeId = $this->_getStoreId();

        if ($storeId === 0){
            
            $ret = Mage::app()->getWebsite(true) ? 
                    Mage::app()->getWebsite(true)->getDefaultStore() : Mage::app()->getStore();
        }
        else
            $ret = Mage::app()->getStore($storeId);
        
        return $ret;
    }
    
    protected function _getStoreId(){
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        return $storeId;
    }

    public function getColumnsProperties($json = true, $reloadAttributes = false)
    {
        $prop = array();

        $allColumns = Mage::getModel('ampgrid/column')->getCollectionAll($this->getSelectedGroupId());
        foreach ($allColumns as $column) {
            /**
             * @var Amasty_Pgrid_Model_Column $column
             */
            if (!$column->isEditable()) {
                continue;
            }
            switch ($column->getCode()) {

                case 'name':
                    $prop['name'] = array(
                        'type'      => 'text',
                        'col'       => 'name',
                        'class' => Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name')->getFrontend()->getClass()
                    );

                    $prop['custom_name'] = array(
                        'type'      => 'text',
                        'col'       => 'custom_name',
                    );
                    break;
                case 'sku':
                    $prop['sku'] = array(
                        'type'      => 'text',
                        'col'       => 'sku',
                        'class' => Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'sku')->getFrontend()->getClass()
                    );
                    break;
                case 'price':
                    $prop['price'] = array(
                        'type'      => 'price',
                        'col'       => 'price',
                        'format'    => 'numeric',
                        'class' => Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'price')->getFrontend()->getClass()
                    );
                    break;
                case 'qty':
                    $prop['qty'] = array(
                        'type'      => 'text',
                        'col'       => 'qty',
                        'obj'       => 'stock_item',
                        'format'    => 'numeric',
                        'class' => Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'qty')->getFrontend()->getClass()
                    );
                    break;
                case 'is_in_stock':
                    $prop['is_in_stock'] = array(
                        'type'      => 'select',
                        'options'   => array(0 => $this->__('Out of stock'), 1 => $this->__('In stock')),
                        'col'       => 'is_in_stock',
                        'obj'       => 'stock_item',
                    );
                    break;
                case 'visibility':
                    $visibilityOptions = Mage::getModel('catalog/product_visibility')->getOptionArray();
                    $prop['visibility'] = array(
                        'type'      => 'select',
                        'options'   => $visibilityOptions,
                        'col'       => 'visibility',
                    );
                    break;
                case 'status':
                    $statusOptions = Mage::getSingleton('catalog/product_status')->getOptionArray();
                    $prop['status'] = array(
                        'type'      => 'select',
                        'options'   => $statusOptions,
                        'col'       => 'status',
                    );
                    break;
                case 'special_price':
                    $prop['special_price'] = array(
                        'type'      => 'price',
                        'col'       => 'special_price',
                        'format'    => 'numeric',
                    );
                    break;
                case 'am_special_from_date':
                    $prop['am_special_from_date'] = array(
                        'type'      => 'date',
                        'col'       => 'am_special_from_date',
                    );
                    break;
                case 'am_special_to_date':
                    $prop['am_special_to_date'] = array(
                        'type'      => 'date',
                        'col'       => 'am_special_to_date',
                    );
                    break;
                case 'cost':
                    $prop['cost'] = array(
                        'type'      => 'price',
                        'col'       => 'cost',
                        'format'    => 'numeric',
                    );
                    break;
            }
        }

            if ($reloadAttributes)
            {
                $attributes = $this->prepareGridAttributesCollection($this->getSelectedGroupId());
                Mage::register('ampgrid_grid_attributes', $attributes);
            }
            
            // adding grid attributes to editable columns
            // @see Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid::_prepareColumns for registry param
            if ($attributes = Mage::registry('ampgrid_grid_attributes'))
            {
                foreach ($attributes as $attribute)
                {
                    /**
                     * @var Amasty_Pgrid_Model_Groupattribute $attribute
                     */
                    if(!$attribute->getIsEditable()) {
                        continue;
                    }
                    $prop[$attribute->getAttributeCode()] = array(
                        'col'       => $attribute->getAttributeCode(),
                        'class' => $attribute->getFrontend()->getClass(),
                        'source'    => 'attribute', // will be used to make difference between default columns and attribute columns
                    );
                    if ('select' == $attribute->getFrontendInput() || 'multiselect' == $attribute->getFrontendInput() || 'boolean'  == $attribute->getFrontendInput())
                    {
                        if ('multiselect' == $attribute->getFrontendInput())
                        {
                            $prop[$attribute->getAttributeCode()]['type'] = 'multiselect';
                        } else 
                        {
                            $prop[$attribute->getAttributeCode()]['type'] = 'select';
                        }
                        $propOptions = array();
                        
                        if ('custom_design' == $attribute->getAttributeCode())
                        {
                            $allOptions = $attribute->getSource()->getAllOptions();
                            if (is_array($allOptions) && !empty($allOptions))
                            {
                                foreach ($allOptions as $option)
                                {
                                    if (!is_array($option['value']))
                                    {
                                        $propOptions[$option['value']] = $option['value'];
                                    } else 
                                    {
                                        foreach ($option['value'] as $option2)
                                        {
                                            if (isset($option2['value']))
                                            {
                                                $propOptions[$option2['value']] = $option2['value'];
                                            }
                                        }
                                    }
                                }
                            }
                        } else 
                        {
                            // getting attribute values with translation
                            $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                ->setAttributeFilter($attribute->getId())
                                ->setStoreFilter($this->_getStoreId(), false)
                                ->load();
                            if ($valuesCollection->getSize() > 0)
                            {
                                $propOptions[''] = '';
                                foreach ($valuesCollection as $item) {
                                    $propOptions[$item->getId()] = $item->getValue();
                                }
                            } else 
                            {
                                $selectOptions = $attribute->getFrontend()->getSelectOptions();
                                if ($selectOptions)
                                {
                                    foreach ($selectOptions as $selectOption)
                                    {
                                        $propOptions[$selectOption['value']] = $selectOption['label'];
                                    }
                                }
                            }
                        }
                        
                        if ($attribute->getFrontendInput() == 'boolean'){
                            $propOptions = array(
                                '1' => $this->__('Yes'),
                                '0' => $this->__('No')
                            );
                        }
                        
                        $prop[$attribute->getAttributeCode()]['options'] = $propOptions;
                        
                        if (!$propOptions)
                        {
                            unset($prop[$attribute->getAttributeCode()]); // we should not make attribute editable, if it has no options
                        }
                    } elseif ('textarea' == $attribute->getFrontendInput()) 
                    {
                        $prop[$attribute->getAttributeCode()]['type'] = 'textarea';
                    } elseif ('price' == $attribute->getFrontendInput())
                    {
                        $prop[$attribute->getAttributeCode()]['type']          = 'price';
                        $prop[$attribute->getAttributeCode()]['currency_code'] = $this->_getStore()->getBaseCurrency()->getCode();
                        $prop[$attribute->getAttributeCode()]['format']        = 'numeric';
                    }elseif ('date' == $attribute->getFrontendInput()){
                        $prop[$attribute->getAttributeCode()]['type'] = 'date'; 
                    }
                    else 
                    {
                        $prop[$attribute->getAttributeCode()]['type'] = 'text';
                    }
                }
            }

        if (!$json)
        {
            return $prop;
        }

        return Mage::helper('core')->jsonEncode($prop);
    }
    
    public function getDefaultColumns()
    {
        return array('name', 'sku', 'price', 'qty', 'visibility', 'status');
    }
    
    public function attachGridColumns(&$grid, &$gridAttributes, $store){
        foreach ($gridAttributes as $attribute)
        {
            $props = array(
                'header'=> $attribute->getCustomTitle() ? $attribute->getCustomTitle() : $attribute->getStoreLabel(),
                'index' => $attribute->getAttributeCode(),
                'filter_index' => 'am_attribute_'.$attribute->getAttributeCode()
            );
            if ('price' == $attribute->getFrontendInput())
            {
                $props['type']          = 'price';
                $props['currency_code'] = $store->getBaseCurrency()->getCode();
                
                if ($attribute->getAttributeCode() == "special_price")
                    $props['renderer'] = 'ampgrid/adminhtml_catalog_product_grid_renderer_sprice';
            }
            
            if ($attribute->getFrontendInput() == 'weight'){
                $props['type'] = 'number';
            }
            
            if ($attribute->getFrontendInput() == 'date'){
                $props['type'] = 'date';
            }
            
            if ('select' == $attribute->getFrontendInput() || 'multiselect' == $attribute->getFrontendInput() || 'boolean' == $attribute->getFrontendInput())
            {
                $propOptions = array();

                if ('multiselect' == $attribute->getFrontendInput())
                {
                    $propOptions['null'] = $this->__('- No value specified -');
                }

                if ('custom_design' == $attribute->getAttributeCode())
                {
                    $allOptions = $attribute->getSource()->getAllOptions();
                    if (is_array($allOptions) && !empty($allOptions))
                    {
                        foreach ($allOptions as $option)
                        {
                            if (!is_array($option['value']))
                            {
                                if ($option['value'])
                                {
                                    $propOptions[$option['value']] = $option['value'];
                                }
                            } else 
                            {
                                foreach ($option['value'] as $option2)
                                {
                                    if (isset($option2['value']))
                                    {
                                        $propOptions[$option2['value']] = $option2['value'];
                                    }
                                }
                            }
                        }
                    }
                } else 
                {
                    // getting attribute values with translation
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter($store->getId(), false)
                        ->load();
                    if ($valuesCollection->getSize() > 0)
                    {
                        foreach ($valuesCollection as $item) {
                            $propOptions[$item->getId()] = $item->getValue();
                        }
                    } else 
                    {
                        $selectOptions = $attribute->getFrontend()->getSelectOptions();
                        if ($selectOptions)
                        {
                            foreach ($selectOptions as $selectOption)
                            {
                                $propOptions[$selectOption['value']] = $selectOption['label'];
                            }
                        }
                    }
                }

                if ($attribute->getFrontendInput() == 'boolean'){
                    $propOptions = array(
                        '1' => $this->__('Yes'),
                        '0' => $this->__('No')
                    );
                }

                if ('multiselect' == $attribute->getFrontendInput())
                {
                    $props['renderer'] = 'ampgrid/adminhtml_catalog_product_grid_renderer_multiselect';
                    $props['filter']   = 'ampgrid/adminhtml_catalog_product_grid_filter_multiselect';
                }

                $props['type'] = 'options';
                $props['options'] = $propOptions;
            }

            $grid->addColumn($attribute->getAttributeCode(), $props);
        }
    }
    
    public function getGridAttributes($groupId)
    {
        /**
         * @var Amasty_Pgrid_Model_Group $group
         */
        $group = $this->getCurrentGridGroup($groupId);

        $selected = $group->getAttributes();

        return $selected;
    }

    public function prepareGridAttributesCollection($groupId)
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                         ->addVisibleFilter()
                         ->addStoreLabel($this->getStore()->getId());

        $conditions = array(
            'main_table.attribute_id = attribute_columns.attribute_id',
            $attributes->getConnection()->quoteInto(
                'attribute_columns.attribute_id IN (?)',
                Mage::helper('ampgrid')->getGridAttributes($groupId)
            ),
            $attributes->getConnection()->quoteInto('attribute_columns.group_id = ?',$groupId),
        );
        $attributes->getSelect()->joinInner(
            array('attribute_columns' => $attributes->getTable(
                'ampgrid/grid_group_attribute'
            )), implode(' AND ', $conditions),
            array('group_id', 'is_editable', 'custom_title')
        );
        return $attributes;
    }
    
    public function getStore()
    {
        return $this->_getStore();
    }
    
    public function getGridThumbSize()
    {
        return 70;
    }
    
    public function getAllowedQtyMath()
    {
        return 'true';
    }

    public function addNoticeIndex() {
        $process = Mage::getSingleton('index/indexer')->getProcessByCode('ampgrid_sold');
        $process->setStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        $process->save();
    }

    /**
     * Get selected Group for user
     *
     * @param string $attributesKey
     * @return int
     */
    public function getSelectedGroupId($attributesKey = '')
    {
        // will load columns by admin users, if necessary
        $extraKey = $attributesKey;
        if (Mage::getStoreConfig('ampgrid/attr/byadmin'))
        {
            $extraKey .= Mage::getSingleton('admin/session')->getUser()->getId();
        }
        $groupId = Mage::getStoreConfig('ampgrid/attributes/ongrid' . $extraKey)
            ? Mage::getStoreConfig('ampgrid/attributes/ongrid' . $extraKey) : 0;

        return (int)$groupId;
    }

    public function getDefaultGroup($attributesKey = '')
    {
       return Mage::getStoreConfig('ampgrid/attributes/ongrid'. $attributesKey);
    }
    /**
     * Get selected Sorting for user
     *
     * @param int $groupId
     * @return array
     */
    public function getSelectedSorting($groupId)
    {

        $sorting = Mage::getStoreConfig('ampgrid/group/sorting' . $groupId)
            ? Mage::getStoreConfig('ampgrid/group/sorting' . $groupId)
            : Mage::getStoreConfig('ampgrid/group/sorting');

        return $sorting ? explode(',', $sorting) : array();
    }

    /**
     *
     * @param int $groupId
     *
     * @return Amasty_Pgrid_Model_Group
     */
    public function getCurrentGridGroup($groupId)
    {
        return Mage::getModel('ampgrid/group')->load($groupId);
    }

    /**
     * @param int $userId
     * @return Amasty_Pgrid_Model_Mysql4_Group_Collection
     */
    public function getGroupsByUserId($userId = null)
    {
        $groups = Mage::getModel('ampgrid/group')->getCollection();
        $userId = $userId ? $userId : Mage::getSingleton('admin/session')->getUser()->getId();

        if(!Mage::getStoreConfig('ampgrid/additional/share_attribute_templates')) {
            $groupIds = array();
            $groupIds[] = Mage::getStoreConfig('ampgrid/attributes/ongrid');
            if(!empty($groupIds)) {
                $groups->addFieldToFilter(
                    array('is_default', 'user_id', 'entity_id'),
                    array(1, $userId, array('in' => $groupIds))
                );
            } else {
                $groups->addFieldToFilter(
                    array('is_default', 'user_id'),
                    array(1, $userId)
                );
            }
        }

        return $groups;
    }
}