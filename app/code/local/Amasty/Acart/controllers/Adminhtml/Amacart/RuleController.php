<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Adminhtml_Amacart_RuleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 
        $this->_setActiveMenu('promo/amacart/rule');
        $this->_addContent($this->getLayout()->createBlock('amacart/adminhtml_rule')); 
        $this->renderLayout();

    }
    
    
    public function newAction() 
    {
        $this->editAction();
    }
    
    public function deleteAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amacart/rule')->load($id);

        if ($model->getId()) {
            $model->delete();
            $msg = Mage::helper('amacart')->__('Rule has been successfully deleted');
                
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            $this->_redirect('*/*/');
        }
    }
	
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amacart/rule')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amacart')->__('Record does not exist'));
            $this->_redirect('*/*/');
        } else {

            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            
            if (!empty($data)) {
                $model->setData($data);
            }
            else 
            {
                $this->prepareForEdit($model);
            }

            $this->loadLayout();

            $this->_setActiveMenu('promo/amacart/rule');
            
//            $this->_setActiveMenu('sales/amacart/' . $this->_modelName . 's');
            $this->_title($this->__('Edit'));

            $head = $this->getLayout()->getBlock('head');
            $head->setCanLoadExtJs(1);
            $head->setCanLoadRulesJs(1);
            
            $editBlock = $this->getLayout()->createBlock('amacart/adminhtml_rule_edit');
            $tabsBlock = $this->getLayout()->createBlock('amacart/adminhtml_rule_edit_tabs');
            
            $editBlock->setModel($model);
            $tabsBlock->setModel($model);
            
            
            $this->_addContent($editBlock);
            $this->_addLeft($tabsBlock);

            $this->renderLayout();
        }
    }
    
    protected function prepareForEdit($model)
    {
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
    }
    
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('amacart/rule'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
        
    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amacart/rule');
        $data = $this->getRequest()->getPost();
        
        if ($data) {
		    
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            
            unset($data['rule']);
            $model->setData($data);  // common fields
            $model->loadPost($data); // rules

            $model->setId($id);
            try {
                $this->prepareForSave($model);

                $model->save();

                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $msg = Mage::helper('amacart')->__('Rule has been successfully saved');
                
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
                if ($this->getRequest()->getParam('continue')){
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }
                else {
                    $this->_redirect('*/*');
                }
            } 
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }	
            return;
        }
        
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amacart')->__('Unable to find a record to save'));
        $this->_redirect('*/*');
	
    }
    
    public function prepareForSave($model)
    {
        $fields = array('stores', 'cust_groups', 'methods');
        foreach ($fields as $f){
            // convert data from array to string
            $val = $model->getData($f);
            $model->setData($f, '');
            if (is_array($val)){
                // need commas to simplify sql query
                $model->setData($f, ',' . implode(',', $val) . ',');    
            } 
        }
        
        return true;
    }
    
    public function runAction(){
        Mage::getModel('amacart/schedule')->run();
        
//        $msg = Mage::helper('amacart')->__('Process has been successfully runned.');
//                
//        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        
        $this->_redirect('amacart/adminhtml_history/index');
        
    }
    
    public function testGridAction(){
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('amacart/adminhtml_rule_edit_tab_test')
                ->toHtml()
        );
    }
    
    protected function _loadQuote($quote_id){
        
        $resource = Mage::getSingleton('core/resource');
        
        $quoteCollection = Mage::getModel('sales/quote')->getCollection();
        $quoteCollection->getSelect()->joinLeft(
            array('quote2email' => $resource->getTableName('amacart/quote2email')), 
            'main_table.entity_id = quote2email.quote_id', 
            array('ifnull(main_table.customer_email, quote2email.email) as target_email')
        );
        
        $quoteCollection->getSelect()->limit(1);
        $quoteCollection->addFieldToFilter('entity_id', array(
                'eq' => $quote_id
            ));
        
        $items = $quoteCollection->getItems();
        
        return isset($items[$quote_id]) ? $items[$quote_id] : null;
    }
    
    public function testRuleAction(){
        $resource = Mage::getSingleton('core/resource');
        
        $quote_id = $this->getRequest()->getParam('quote_id');
        $rule_id = $this->getRequest()->getParam('rule_id');
        
        
        $rule = Mage::getModel('amacart/rule')->load($rule_id);
        $quote = $this->_loadQuote($quote_id); //Mage::getModel('sales/quote')->loadByIdWithoutStore($quote_id);

        if ($rule->getId() && $quote->getId()){
           
            $canceledCollection = Mage::getModel('amacart/canceled')->getCollection();
            $canceledCollection->addFieldToFilter('quote_id', array(
                'eq' => $quote_id
            ));

            foreach($canceledCollection as $canceled){
                $canceled->delete();
            }

            $historyCollection = Mage::getModel('amacart/history')->getCollection();
            $historyCollection->addFieldToFilter('quote_id', array(
                'eq' => $quote_id
            ));

            foreach($historyCollection as $history){
                $history->delete();
            }
            
            $scheduleCollection = Mage::getModel('amacart/schedule')->getCollection();
            $scheduleCollection->getSelect()->joinLeft(
                array('rule' => $resource->getTableName('amacart/rule')), 
                'main_table.rule_id = rule.rule_id', 
                array('rule.rule_id')
            );
            
            $scheduleCollection->addFieldToFilter('rule.rule_id', array('eq' => $rule->getId()));
            
            
            foreach($scheduleCollection as $schedule){
                $history = $schedule->createHistoryItem($quote, $schedule);
                $schedule->processHistoryItem($history);
            }
            
        }
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amacart');
    }
}
