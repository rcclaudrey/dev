<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Status_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_status;
    
    public function _getStatus(){
        if (!$this->_status){
            $this->_status = Mage::registry('amrma_status');
        }
        return $this->_status;
    }
    
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amrma';
        $this->_controller = 'adminhtml_status';
        
        $this->_addButton('save_and_continue', array(
                'label'     => $this->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
        
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";         
    }
    
    protected function _prepareLayout()
    {
        if ($this->_getStatus()->status_key){
            $this->_removeButton("delete");
        }
        parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amrma')->__('New Status');
        $model = $this->getModel();
        
        if ($model->getId()){
            $header = Mage::helper('amrma')->__('Edit Status `%s`', $model->getStoreLabel());
        }
        return $header;
    }
}