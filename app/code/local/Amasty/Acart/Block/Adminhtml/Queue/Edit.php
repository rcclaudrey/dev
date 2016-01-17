<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

class Amasty_Acart_Block_Adminhtml_Queue_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup = 'amacart'; 
        $this->_controller  = 'adminhtml_queue';
        
        parent::__construct();

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('salesrule')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') } ";   
        
        
    }
    
//    protected function _prepareLayout()
//    {
//        $ret = parent::_prepareLayout();
//        
//        $form = $this->getChild('form');
//        
//        if ($form){
//            $form->setModel($this->getModel());
//            var_dump(get_class($form));
////            $this->setChild('form', $form);
//        }
//        return $ret;
//    }
    
    public function getHeaderText()
    {
        $header = Mage::helper('amacart')->__('Edit Queue Item');
        
        return $header;
    }
    
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true));
    }
}
?>