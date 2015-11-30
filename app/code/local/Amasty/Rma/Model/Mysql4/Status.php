<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */  
class Amasty_Rma_Model_Mysql4_Status extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('amrma/status', 'status_id');
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        $this->saveStoreTemplates($object->getId(), $object->getStoreTemplates());
    }
    
    public function getStoreTemplates($statusId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('amrma/template'), array('store_id', 'template'))
            ->where('status_id = :status_id');
        return $this->_getReadAdapter()->fetchPairs($select, array(':status_id' => $statusId));
    }
    
    public function saveStoreTemplates($statusId, $templates)
    {
        $deleteByStoreIds = array();
        $table   = $this->getTable('amrma/template');
        $adapter = $this->_getWriteAdapter();

        $data    = array();
        foreach ($templates as $storeId => $template) {
            if (Mage::helper('core/string')->strlen($template)) {
                $data[] = array('status_id' => $statusId, 'store_id' => $storeId, 'template' => $template);
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $adapter->beginTransaction();
        try {
            if (!empty($data)) {
                $adapter->insertOnDuplicate(
                    $table,
                    $data,
                    array('template')
                );
            }

            if (!empty($deleteByStoreIds)) {
                $adapter->delete($table, array(
                    'status_id=?'       => $statusId,
                    'store_id IN (?)' => $deleteByStoreIds
                ));
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;

        }
        $adapter->commit();

        return $this;
    }
    
    public function getStoreLabels($statusId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('amrma/label'), array('store_id', 'label'))
            ->where('status_id = :status_id');
        return $this->_getReadAdapter()->fetchPairs($select, array(':status_id' => $statusId));
    }
    
    public function saveStoreLabels($statusId, $labels)
    {
        $deleteByStoreIds = array();
        $table   = $this->getTable('amrma/label');
        $adapter = $this->_getWriteAdapter();

        $data    = array();
        foreach ($labels as $storeId => $label) {
            if (Mage::helper('core/string')->strlen($label)) {
                $data[] = array('status_id' => $statusId, 'store_id' => $storeId, 'label' => $label);
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $adapter->beginTransaction();
        try {
            if (!empty($data)) {
                $adapter->insertOnDuplicate(
                    $table,
                    $data,
                    array('label')
                );
            }

            if (!empty($deleteByStoreIds)) {
                $adapter->delete($table, array(
                    'status_id=?'       => $statusId,
                    'store_id IN (?)' => $deleteByStoreIds
                ));
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;

        }
        $adapter->commit();

        return $this;
    }
    
}
?>