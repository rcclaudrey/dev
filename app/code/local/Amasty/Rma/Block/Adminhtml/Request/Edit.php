<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Request_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_rma_request;
    
    protected function _getRmaRequest(){
        if (!$this->_rma_request){
            $this->_rma_request = Mage::registry('amrma_request');
        }
        return $this->_rma_request;
    }
    
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amrma';
        $this->_controller = 'adminhtml_request';
        
        $this->_addButton('save_and_continue', array(
                'label'     => $this->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
        
        $allow = $this->_getRmaRequest()->getAllowCreateLabel() == 1;
        
        $this->_addButton('allow_print_label', array(
                'label'     => $this->__(($allow ? 'Remove' : 'Generate').' Shipping Label'),
                'onclick'   => 'sllowPrintLabel();',
                'style' => 'margin-left: 30px;'
            ), 10);
        
        
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";
        
        $allowUrl = Mage::helper("adminhtml")->getUrl("adminhtml/amrma_request/allow", array(
            'id' => $this->_getRmaRequest()->getId()
        ));
                
        $this->_formScripts[] = " function sllowPrintLabel() {document.location.href= '".$allowUrl."'}";
    }
    
    protected function _prepareLayout()
    {
        $this->_removeButton("delete");
        parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amrma')->__('New Status');
        $model = $this->getModel();
        
        if ($model->getId()){
            $header = Mage::helper('amrma')->__('RMA Request %s for order #%s', $model->getId(), $model->getIncrementId());
        }
        return $header;
    }
}