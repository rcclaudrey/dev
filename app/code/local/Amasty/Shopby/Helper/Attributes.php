<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
class Amasty_Shopby_Helper_Attributes extends Amasty_Shopby_Helper_Cached
{
    protected $_options;
    protected $_requestedFilterCodes;

    public $_appliedFilterCodes = array();

    /**
     * @return array
     */
    public function getAllFilterableOptionsAsHash()
    {
        $cacheId = 'filterable_options_hash';

        $result = $this->load($cacheId);
        if ($result) {
            return $result;
        }

        $requireUniqueOptions = Mage::getStoreConfig('amshopby/seo/hide_attributes') || Mage::getStoreConfig('amshopby/seo/urls') == Amasty_Shopby_Model_Source_Url_Mode::MODE_SHORT;
        $xAttributeValuesUnique = array();
        $hash = array();
        $attributes = $this->getFilterableAttributes();

        $options = $this->getAllOptions();

        foreach ($attributes as $a){
            $code        = $a->getAttributeCode();
            $code = str_replace(array('_', '-'), Mage::getStoreConfig('amshopby/seo/special_char'), $code);
            $hash[$code] = array();
            foreach ($options as $o){
                if (($o['value'] || $o['value'] === '0') && $o['attribute_id'] == $a->getId()) { // skip first empty
                    $nonUniqueValue = $o['url_alias'] ? $o['url_alias'] : $o['value'];
                    $unKey = $this->createKey($nonUniqueValue);

                    while (isset($hash[$code][$unKey])
                        || ($requireUniqueOptions && isset($xAttributeValuesUnique[$unKey]))
                    ) {
                        $unKey .= Mage::getStoreConfig('amshopby/seo/special_char');
                    }
                    $hash[$code][$unKey] = $o['option_id'];
                    $xAttributeValuesUnique[$unKey] = true;
                }
            }
        }
        $xAttributeValuesUnique = null;

        $this->save($hash, $cacheId);
        return $hash;
    }

    public function getFilterableAttributes()
    {
        $cacheId = 'filterable_attributes';

        $result = $this->load($cacheId);
        if ($result) {
            return $result;
        }

        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        $collection
            ->setItemObjectClass('catalog/resource_eav_attribute')
            ->addStoreLabel(Mage::app()->getStore()->getId())
            ->setOrder('position', 'ASC');

        $collection->getSelect()->group('main_table.attribute_id');

        if (Mage::app()->getRequest()->getModuleName() == 'catalogsearch') {
            $collection->addIsFilterableInSearchFilter();
        } else {
            $collection->addIsFilterableFilter();
        }

        $collection->load();

        $result = array();
        foreach ($collection as $attribute) {
            /** @var Mage_Eav_Model_Attribute $attribute */
            $result[$attribute->getAttributeId()]  = $attribute;
        }

        $this->save($result, $cacheId);
        return $result;
    }

    public function createKey($optionLabel)
    {
        $key = Mage::helper('catalog/product_url')->format($optionLabel);
        $key = preg_replace('/[^0-9a-z,]+/i', Mage::getStoreConfig('amshopby/seo/special_char'), $key);
        $key = strtolower($key);
        $key = trim($key, Mage::getStoreConfig('amshopby/seo/special_char') . Mage::getStoreConfig('amshopby/seo/option_char'));

        if ($key == '') {
            $key = Mage::getStoreConfig('amshopby/seo/special_char');
        }

        return $key;
    }

    public function getDecimalAttributeCodeMap()
    {
        $cacheId = 'decimal_attribute_code_map';

        $result = $this->load($cacheId);
        if ($result) {
            return $result;
        }

        $map = array();
        $attributes = $this->getFilterableAttributes();
        foreach ($attributes as $attribute) {
            /** @var Mage_Eav_Model_Attribute $attribute */
            $map[$attribute->getAttributeCode()] = $attribute->getBackendType() == 'decimal';
        }

        $this->save($map, $cacheId);
        return $map;
    }

    /**
     * Get option for specific attribute
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOptions($attributeCode)
    {
        $cacheId = 'attribute_options_by_attribute_code';

        $hash = $this->load($cacheId);
        if (!$hash) {
            $hash = array();
            $attributes = $this->getFilterableAttributes();
            $options = $this->getAllOptions();
            foreach ($attributes as $attribute)
            {
                $code = $attribute->getAttributeCode();
                $hash[$code] = array();

                foreach ($options as $option) {
                    if ($option['attribute_id'] == $attribute->getAttributeId()) {
                        $hash[$code][] = array(
                            'value' => $option['option_id'],
                            'label' => $option['value'],
                        );
                    }
                }
            }
            $this->save($hash, $cacheId);
        }

        return isset($hash[$attributeCode]) ? $hash[$attributeCode] : array();
    }

    protected function getAllOptions()
    {
        /** @var Mage_Eav_Model_Resource_Entity_Attribute_option_Collection $valuesCollection */
        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setStoreFilter();

        $select = clone $valuesCollection->getSelect();
        $select->order('sort_order', 'ASC');

        $select->joinLeft(
            array('s' => $valuesCollection->getTable('amshopby/value')),
            's.option_id = main_table.option_id',
            array('url_alias', 'title')
        );

        //Only catalog filterable attributes
        $select->joinInner(
            array('a' => $valuesCollection->getTable('catalog/eav_attribute')),
            'a.attribute_id = main_table.attribute_id',
            array()
        );
        $select->where('is_filterable IN (1,2)');

        $options = $valuesCollection->getConnection()->fetchAll($select);

        return $options;
    }


    public function getRequestedFilterCodes()
    {
        if (!isset($this->_requestedFilterCodes)) {
            $this->_requestedFilterCodes = array();
            $requestedParams = Mage::app()->getRequest()->getParams();

            $attributes = $this->getFilterableAttributes();

            foreach ($attributes as $attribute) {
                /** @var Mage_Eav_Model_Attribute $attribute*/

                $code = $attribute->getData('attribute_code');
                if (array_key_exists($code, $requestedParams)) {
                    $this->_requestedFilterCodes[$code] = $requestedParams[$code];
                }
            }
        }
        return $this->_requestedFilterCodes;
    }

    public function getRequestedOptionIds()
    {
        $optionIds = implode(',', $this->getRequestedFilterCodes());
        return strlen($optionIds) ? explode(',', $optionIds) : array();
    }

    public function lockApplyFilter($code, $type)
    {
        $hash = $type . '*' . $code;
        if (in_array($hash, $this->_appliedFilterCodes)) {
            return false;
        } else {
            $this->_appliedFilterCodes[] = $hash;
            return true;
        }
    }

    /**
     * @return Amasty_Shopby_Model_Value|null
     */
    public function getRequestedBrandOption()
    {
        $brandAttributeCode = trim(Mage::getStoreConfig('amshopby/brands/attr'));
        $query = Mage::app()->getRequest()->getQuery();
        if (!isset($query[$brandAttributeCode])) {
            return null;
        }

        $value = Mage::getModel('amshopby/value')->load($query[$brandAttributeCode], 'option_id');
        if (!$value->getId()) {
            return null;
        }

        return $value;
    }
}
