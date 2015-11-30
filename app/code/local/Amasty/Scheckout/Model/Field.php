<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Field extends Mage_Core_Model_Abstract
{
    protected static $customerAttributePrefix = 'ca_';
    protected static $orderAttributePrefix = 'oa_';
    
    public function _construct()
    {
        $this->_init('amscheckout/field');
    }
    
    protected function getEAVFieldKey($item){
        $fieldKey = $item['default_field_label'];
        if ($item['is_eav_attribute'] == 1){
            if ($item['is_order_attribute'] == '1'){
                $fieldKey = str_replace(self::$orderAttributePrefix, '', $fieldKey);
            } else if ($item['is_customer_attribute'] == '1') {
                $fieldKey = str_replace(self::$customerAttributePrefix, '', $fieldKey);
            }
        }
        return $fieldKey;
    }
    
    protected function getDefaultLabel($storeId, $item, $area){
        $defaultLabel = $item['default_field_label'];
        $fieldKey = $item['field_key'];
        
        if ($item['is_eav_attribute'] == 1){
            $attribute = $this->getAttribute($item);
            
//            print_r($attribute);
//            exit(1);
//            $defaultLabel = $attribute->getStoreLabel();
            if ($attribute)
            $defaultLabel = $attribute->getFrontendLabel();
        }
        
        switch ($area->getAreaKey()){
            case "payment":
                if ($item['is_eav_attribute'] != '1'){
                    $defaultLabel =  Mage::helper('payment')->
                                        getMethodInstance($fieldKey)->
                                        getConfigData('title', $storeId);
                }
                break;
            case "shipping_method":
                if ($item['is_eav_attribute'] != '1'){
                    $defaultLabel = $this->getDefaultShippingMethodTitle($fieldKey, $storeId);
                }
                
                break;
            
        }
        
        return $defaultLabel;
    }
    
    protected function getAttribute($item){
        $fieldKey = $this->getEAVFieldKey($item);
        
        $attribute = null;
        
        if ($item['is_order_attribute'] == '1'){
            $attributesCollection = Mage::getModel('eav/entity_attribute')->getCollection()
                ->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId())
                ->addFieldToFilter('attribute_code', $fieldKey);


            $items = array_values($attributesCollection->getItems());

            if (count($items) > 0)
                $attribute = $items[0];
            
        } else if ($item['is_customer_attribute'] == '1') {
            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $fieldKey);

        } else {
            $attribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', $fieldKey);
        }
        
        return $attribute;
//                
//        
//        
//        $ret = NULL;
//        $attributesCollection = Mage::getModel('eav/entity_attribute')->getCollection()
//            ->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId())
//            ->addFieldToFilter('attribute_code', $fieldKey);
//
//
//        $items = array_values($attributesCollection->getItems());
//        
//        if (count($items) > 0)
//            $ret = $items[0];
//        
//        return $ret;
    }
    
    protected function getDefaultOrderAttributeLabel($item){
        $fieldKey = $this->getEAVFieldKey($item);
        
        $ret = $fieldKey;
        
        $orderAttribute = $this->getOrderAttribute($item);

        if ($orderAttribute)
            $ret = $orderAttribute->getFrontendLabel();
        
        return $ret;
    }
    protected function getLabel($storeId, $item, $area){
        $fieldLabel = $item['field_label'];
        
        if (isset($item['field_store_id'])){
            $fieldLabel = $item['st_field_label'];
        } 
        
        if ($item['is_eav_attribute'] == 1){
            
            if ($fieldLabel == $item['default_field_label']){
                $attribute = $this->getAttribute($item);
                
//                $fieldLabel = $attribute->getStoreLabel();
                if ($attribute)
                $fieldLabel = $attribute->getFrontendLabel();
            }
        }
        
        switch ($area->getAreaKey()){
            case "payment":
                if ($item['is_eav_attribute'] != '1'){
                    
                    if (!isset($item['field_store_id'])){
                        
                        if ($item['field_key'] != $item['field_label']){
                            $fieldLabel = $item['field_label'];
                        } else {
                            $fieldLabel =  Mage::helper('payment')->
                                        getMethodInstance($item['field_key'])->
                                        getConfigData('title', $storeId);
                        }
                    } else {
                        $fieldLabel = $item['st_field_label'];
                        
                    }
                }

                break;
            case "shipping_method":
                if ($item['is_order_attribute'] != '1'){
                    
                    if (!isset($item['field_store_id'])){
                        
                        if ($item['field_key'] != $item['field_label']){
                            $fieldLabel = $item['field_label'];
                        } else {
                            $fieldLabel =  $this->getDefaultShippingMethodTitle($item['field_key'], $storeId);
                        }
                    } else {
                        $fieldLabel = $item['st_field_label'];
                    }
                }
                
                break;
        }
        
        return $fieldLabel;
    }
    
    protected function getDefaultShippingMethodTitle($fieldKey, $storeId){
        $_title = NULL;

        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers($storeId);
        
        foreach($methods as $_ccode => $_carrier) {
            if($_methods = $_carrier->getAllowedMethods())  {

                foreach($_methods as $method => $m_title){

                    if ($fieldKey == $_ccode.'_'.$method){
                        $_title = $m_title;
                        break;
                    }
                }
            }
        }
        
        return $_title;
    }
    
    
    protected function getFieldFormKey($storeId, $item, $area){
        $fieldKey = $item['field_key'];
        
        if ($item['is_eav_attribute'] != 1){
            switch ($area->getAreaKey()){
                case "payment":
                        $fieldKey = 'p_method_'.$fieldKey;
                    break;
                case "shipping_method":
                    $fieldKey = 's_method_'.$fieldKey;
                    break;

            }
        } else {
            //handle for default fields
            if (strpos($fieldKey, 'shipping:') === FALSE && strpos($fieldKey, 'billing:') === FALSE)
                $fieldKey = $this->getEAVFieldKey($item);
        }
        
        
        return $fieldKey;
    }
    
    protected function refreshCustomerAttributes(){
        if (Mage::helper('core')->isModuleEnabled('Amasty_Customerattr')){
            $maxOrder = $this->getMaxOrder('billing');
            $area = Mage::getModel('amscheckout/area')->load('billing', 'area_key');;
            
            $collection = Mage::getModel('customer/attribute')->getCollection();
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
            
            $collection->addFieldToFilter($alias . 'is_user_defined', 1);
            $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'customer_eav_attribute');
            
            // Show on Billing During Checkout
            
            $collection->addFieldToFilter($alias . 'used_in_product_listing', 1);
            
            $attributes = $collection->load();
            $fieldsIds = array();
            
            foreach($attributes as $attribute){
                $attributeCode = $attribute->getAttributeCode();
                
                $field = Mage::getModel('amscheckout/field')->load(self::$customerAttributePrefix.$attributeCode, 'field_key');
                    
                if (!$field->getId()) {
                    $maxOrder += 100;
                    $field->setData(array(
                        'field_key' => self::$customerAttributePrefix.$attributeCode,
                        'area_id' => $area->getId(),
                        'default_field_label' => $attributeCode,
                        'default_field_order' => $maxOrder,
                        'default_field_required' => '0',
                        'default_column_position' => '100',
                        'field_label' => $attributeCode,
                        'field_order' => $maxOrder,
                        'field_required' => '0',
                        'column_position' => '100',
                        'field_disabled' => '1',
                        'is_customer_attribute' => '1',
                        'is_eav_attribute' => '1'
                    ));
                    $field->save();
                }
                
                $fieldsIds[] = $field->getId();
            }
            
            $this->removeCustomerAttributeFields($fieldsIds);
        }
    }
    
    protected function refreshOrderAttributes(){
        if (Mage::helper('core')->isModuleEnabled('Amasty_Orderattr')){
            
            $keys2steps = array(
                2 => 'billing',
                3 => 'shipping',
                4 => 'shipping_method',
                5 => 'payment',
                6 => 'review'
            );
                
            $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('order')->getTypeId() );
            
            $collection->getSelect()
                ->where(' main_table.is_user_defined = ?', 1);

            $fieldsIds = array();

            foreach ($collection as $attribute){
                $attributeCode = $attribute->getAttributeCode();
                
                $attributeStep = $attribute->getCheckoutStep();
                if (isset($keys2steps[$attributeStep])){
                    $field = Mage::getModel('amscheckout/field')->load(self::$orderAttributePrefix.$attributeCode, 'field_key');
                    
                        if (is_string($keys2steps[$attributeStep]))
                            $keys2steps[$attributeStep] = Mage::getModel('amscheckout/area')->load($keys2steps[$attributeStep], 'area_key');

                    if (!$field->getId()) {

                        $maxOrder = $this->getMaxOrder($keys2steps[$attributeStep]->getId());

                        $maxOrder += 100;
                        $field->setData(array(
                            'field_key' => self::$orderAttributePrefix.$attributeCode,
                            'area_id' => $keys2steps[$attributeStep]->getId(),
                            'default_field_label' => $attributeCode,
                            'default_field_order' => $maxOrder,
                            'default_field_required' => '0',
                            'default_column_position' => '100',
                            'field_label' => $attributeCode,
                            'field_order' => $maxOrder,
                            'field_required' => '0',
                            'column_position' => '100',
                            'field_disabled' => '1',
                            'is_order_attribute' => '1',
                            'is_eav_attribute' => '1'
                        ));
                        $field->save();
                    } else if($field->getId() && (string)$keys2steps[$attributeStep]->getId() != (string)$field->getAreaId()){
                        $field->setAreaId((string)$keys2steps[$attributeStep]->getId());
                        $field->save();
                    }
                    
                    $fieldsIds[] = $field->getId();
                }
            }
            
            $this->removeOrderAttributeFields($fieldsIds);
        }
    }
    
    public function getFields($storeId, $areaKey = NULL){
        
        $this->refreshDynamicFields();
        
        $fields = array();
        $collection = $this->getCollection();
        $items = $collection->byStore($storeId)->getData();
        foreach($items as $item){
          $area_id = $item['area_id'];
          
          $area = Mage::getModel('amscheckout/area')->load($area_id);
          
          if ($areaKey == NULL || $area->getAreaKey() == $areaKey){
          
            if ($area_id === NULL)
                $area_id = "0";

            if (!isset($fields[$area_id]))
                $fields[$area_id] = array();

            $defaultLabel = $this->getDefaultLabel($storeId, $item, $area);
            $fieldLabel = $this->getLabel($storeId, $item, $area);
            $fieldKey = $this->getFieldFormKey($storeId, $item, $area);

            $fieldEAVType = NULL;

            if ($item['is_eav_attribute'] == 1){
                $attribute = $this->getAttribute($item);
                if ($attribute)
                $fieldEAVType = $attribute->getFrontendInput();
            }

            $fields[$area_id][] = array(
                'field_id' => $item['field_id'],
                'field_db_key' => $item['field_key'],
                'field_key' => $fieldKey,
                'is_eav_attribute' => $item['is_eav_attribute'],
                'field_eav_type' => $fieldEAVType,
                'field_store_id' => $item['field_store_id'],
                'area_id' => $area_id,
                'area_key' => $area->getAreaKey(),
                'default_field_label' => $defaultLabel,
                'field_label' => $fieldLabel,
                'field_order' => isset($item['field_store_id']) ? $item['st_field_order'] : $item['field_order'],
                'field_required' => isset($item['field_store_id']) ? $item['st_field_required'] : $item['field_required'],
                'field_disabled' => isset($item['field_store_id']) ? $item['st_field_disabled'] : $item['field_disabled'],
                'column_position' => isset($item['field_store_id']) ? $item['st_column_position'] : $item['column_position']
            );
          }

        }
        
        if (!function_exists("fieldsCmp"))
        {
            function fieldsCmp($aItem, $bItem)
            {
                $a = $aItem['field_order'];
                $b = $bItem['field_order'];
                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            }
        }
        
        foreach($fields as &$area){
            usort($area, "fieldsCmp");
        }
        
        return $fields;
    }
    
    function getAreaFields($storeId, $areaKey){
        $areaFields = array_values($this->getFields($storeId, $areaKey));
        return count($areaFields) > 0 ? $areaFields[0] : NULL;
    }
    
    function updateDefaultOrders(){
        $fieldCollection = Mage::getResourceModel('amscheckout/field_collection');
        $fieldCollection->getSelect()->order(
            array('area_id', 'field_order')
        );
        
        $fields = $fieldCollection->getItems();
        
        $startOrder = 0;
        $lastAreaId = 0;
        
        foreach($fields as $field){
            if ($lastAreaId != $field->getAreaId()){
                $startOrder = 0;
            }
            if ($field->getFieldOrder() != $startOrder){
                $field->setFieldOrder($startOrder);
                $field->save();
            }

            $startOrder += 100;
            $lastAreaId = $field->getAreaId();
        }
        
    }
    
    function updateByFields($fields, $storeId = NULL){
        
        if (isset($fields['area_id']) && $fields['area_id'] == 0)
                $fields['area_id'] = NULL;
        
        if (!empty($storeId)){
            
            $fieldStoreCollection = Mage::getResourceModel('amscheckout/field_store_collection');
            $fieldStoreCollection->getSelect()->where(
                $fieldStoreCollection->getConnection()->quoteInto(
                        'store_id = ?', 
                        $storeId
                )
            );
            
            $fieldStoreCollection->getSelect()->where(
                $fieldStoreCollection->getConnection()->quoteInto(
                        'field_id = ?', 
                        $this->getFieldId()
                )
            );
            
            $fieldStoreData = $fieldStoreCollection->getData();
            
            $fieldStoreId = isset($fieldStoreData[0]) ? $fieldStoreData[0]['field_store_id'] : NULL;
            
            $fieldStore = Mage::getModel('amscheckout/field_store')->load($fieldStoreId);
            
            if (
                isset($fields['use_default']) && 
                $fields['use_default'] == 1 && 
                $fieldStoreId
            ){
                $fieldStore->delete();
            } else {
                if (!$fieldStoreId){
                    $fieldStore->setData(array(
                        'field_id' => $this->getFieldId(),
                        'store_id' => $storeId,
                        'area_id' => $this->getAreaId(),
                        'field_label' => $this->getFieldLabel(),
                        'field_order' => $this->getFieldOrder(),
                        'field_required' => $this->getFieldRequired(),
                        'column_position' => $this->getColumnPosition()
                    ));
                }

                foreach($fields as $key => $field)
                    $fieldStore->setData($key, $field);

                $fieldStore->save();

            }

        } else {
            foreach($fields as $key => $field)
                $this->setData($key, $field);
            
            $this->save();
        }
    }
    
    function getMaxOrder($areaId){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = '
            SELECT MAX(field_order) as max_field_order 
            FROM ' . $resource->getTableName('amscheckout/field') . '
            WHERE ' . $readConnection->quoteInto('area_id = ?', $areaId) . '
            ';
        
        return $readConnection->fetchOne($query);
    }
    
    function removeFields($areaId, array $fieldsIds){
        if (count($fieldsIds) > 0){
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $query = '
                DELETE
                FROM ' . $resource->getTableName('amscheckout/field') . '
                WHERE 
                is_order_attribute <> 1 AND
                ' . $readConnection->quoteInto('area_id = ?', $areaId) . ' AND
                ' . $readConnection->quoteInto('field_id NOT IN (?)', $fieldsIds) . '
                ';

            $readConnection->query($query);
        }
    }
    
    function removeOrderAttributeFields(array $fieldsIds){
        if (count($fieldsIds) > 0){
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $query = '
                DELETE
                FROM ' . $resource->getTableName('amscheckout/field') . '
                WHERE is_order_attribute = 1 AND
                ' . $readConnection->quoteInto('field_id NOT IN (?)', $fieldsIds) . '
                ';

            $readConnection->query($query);
        }
    }
    
    function removeCustomerAttributeFields(array $fieldsIds){
        if (count($fieldsIds) > 0){
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $query = '
                DELETE
                FROM ' . $resource->getTableName('amscheckout/field') . '
                WHERE is_customer_attribute = 1 AND
                ' . $readConnection->quoteInto('field_id NOT IN (?)', $fieldsIds) . '
                ';

            $readConnection->query($query);
        }
    }
    
    protected function refreshDynamicFields(){
        $allStores = Mage::app()->getStores();
        
        $this->refreshOrderAttributes();
        $this->refreshCustomerAttributes();
//        $this->refreshPaymentFields($allStores);
//        $this->refreshShippingMethodsFields($allStores);
    }
    
    protected function refreshDefaultShippingMethods($allStores, $area, $maxOrder, &$fieldsIds ){
        foreach($allStores as $store){
            $shippingMethods = Mage::getSingleton('shipping/config')
                    ->getActiveCarriers($store->getId());
            
            foreach($shippingMethods as $carrierCode => $_carrier){
                if($_methods = $_carrier->getAllowedMethods())  {
                    foreach($_methods as $method => $title){
                        $fieldKey = $carrierCode."_".$method;
                        $field = Mage::getModel('amscheckout/field')->load($fieldKey, 'field_key');
                        if (!$field->getId()) {
                            $maxOrder += 100;
                            $field->setData(array(
                                'field_key' => $fieldKey,
                                'area_id' => $area->getId(),
                                'default_field_label' => $fieldKey,
                                'default_field_order' => $maxOrder,
                                'default_field_required' => '0',
                                'default_column_position' => '100',
                                'field_label' => $fieldKey,
                                'field_order' => $maxOrder,
                                'field_required' => '0',
                                'column_position' => '100'
                            ));
                            $field->save();
                        }

                        $fieldsIds[] = $field->getId();
                    }  
                }
            }
        }
    }
    
    protected function refreshShippingMethodsFields($allStores){
        
        $area = Mage::getModel('amscheckout/area')->load('shipping_method', 'area_key');
        
        $maxOrder = $this->getMaxOrder($area->getId());
        $fieldsIds = array();
        
        $this->refreshDefaultShippingMethods($allStores, $area, $maxOrder, $fieldsIds);
        
        $this->removeFields($area->getId(), $fieldsIds);
    }
    
    protected function refreshPaymentFields($allStores){
        $area = Mage::getModel('amscheckout/area')->load('payment', 'area_key');
        
        $maxOrder = $this->getMaxOrder($area->getId());
        $fieldsIds = array();
        
        foreach($allStores as $store){
            $paymentMethods = Mage::helper('payment')
                    ->getStoreMethods($store->getId());

            foreach($paymentMethods as $paymentMethod){
                $fieldKey = $paymentMethod->getCode();

                $field = Mage::getModel('amscheckout/field')->load($fieldKey, 'field_key');
                if (!$field->getId()) {
                    $maxOrder += 100;
                    $field->setData(array(
                        'field_key' => $fieldKey,
                        'area_id' => $area->getId(),
                        'default_field_label' => $fieldKey,
                        'default_field_order' => $maxOrder,
                        'default_field_required' => '0',
                        'default_column_position' => '100',
                        'field_label' => $fieldKey,
                        'field_order' => $maxOrder,
                        'field_required' => '0',
                        'column_position' => '100'
                    ));
                    $field->save();
                }
                
                $fieldsIds[] = $field->getId();
            }
        }
        
        $this->removeFields($area->getId(), $fieldsIds);
    }
        
    
}
?>