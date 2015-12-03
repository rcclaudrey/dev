<?php 
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Celexport
 */
class Celebros_Celexport_Model_Mapping extends Mage_Core_Model_Abstract
{
    private $_fieldsArray; 
    
    protected function _construct()
    {
        $this->_init('celexport/mapping');
    }
    
    protected function _loadFieldsArray()
    {
        $fieldsCollection = Mage::getSingleton('celexport/mapping')->getCollection();
        $this->_fieldsArray = array();
        foreach ($fieldsCollection as $field) {
            $this->_fieldsArray[$field->getCodeField()] = $field->getXmlField();
        }
    }
    
    /**
     * Get Fields Array 
     * 
     * @return array
     */
    public function getFieldsArray()
    {
        if (!$this->_fieldsArray) {
            $this->_loadFieldsArray();
        }
        
        return $this->_fieldsArray;
    }
    
} 