<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */
class Amasty_Scheckout_Model_Area extends Mage_Core_Model_Abstract
{
    
    public function _construct()
    {
        $this->_init('amscheckout/area');
    }
    
    public function getAreas($storeId){
        
        $areas = array();
        $collection = $this->getCollection();
        $items = $collection->byStore($storeId)->getData();
        
        foreach($items as $index => $item){
            $areas[$index] = array(
                'area_id' => $item['area_id'],
                'area_key' => $item['area_key'],
                'area_store_id' => $item['area_store_id'],
                'default_area_label' => $item['default_area_label'],
                'area_label' => $item['area_store_id'] !== NULL ? $item['st_area_label'] : $item['area_label']
            );
        }
        
        return $areas;
    }
    
    function updateByFields($fields, $storeId = NULL){
        
        if (!empty($storeId)){
            
            $areaStoreCollection = Mage::getResourceModel('amscheckout/area_store_collection');
            $areaStoreCollection->getSelect()->where(
                $areaStoreCollection->getConnection()->quoteInto(
                        'store_id = ?', 
                        $storeId
                )
            );
            
            $areaStoreCollection->getSelect()->where(
                $areaStoreCollection->getConnection()->quoteInto(
                        'area_id = ?', 
                        $this->getAreaId()
                )
            );
                        
            $areaStoreItems = $areaStoreCollection->getItems();
            
            $areaStore = NULL;
            
            if (count($areaStoreItems) > 0){
                $areaStoreItems = array_values($areaStoreItems);
                $areaStore = $areaStoreItems[0];
            }
            else
                $areaStore = Mage::getModel('amscheckout/area_store');
                    
            if (
                    isset($fields['use_default']) && 
                    $fields['use_default'] == 1 && 
                    $areaStore->getAreaStoreId() !== NULL
                ){
                    $areaStore->delete();
                } else {
                    if (!$areaStore->getAreaStoreId()){
                        $areaStore->setData(array(
                            'store_id' => $storeId,
                            'area_id' => $this->getAreaId(),
                            'area_label' => $this->getAreaLabel(),
                            'area_order' => $this->getAreaOrder()
                        ));
                    }

                    foreach($fields as $key => $field)
                        $areaStore->setData($key, $field);

                    $areaStore->save();

                }
        } else {
            foreach($fields as $key => $field)
                $this->setData($key, $field);

            $this->save();
        }
    }
    
    
}
?>