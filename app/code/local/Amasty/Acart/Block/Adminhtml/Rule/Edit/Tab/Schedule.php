<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tab_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_salerRuleCollection;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amacart/schedule.phtml');        
    }
    
    
    function getNumberOptions($number){
        $ret = array('<option value="">-</option>');
        for($index = 1; $index <= $number; $index++){
            $ret[] = '<option value="' . $index . '" >' . $index . '</option>';
        }
        return implode('', $ret);
    }
    
    function getScheduleCollection(){
        $scheduleCollection = Mage::getModel('amacart/schedule')->getCollection();
        $scheduleCollection->addFilter('rule_id', $this->getModel()->getId());

        return $scheduleCollection;
    }
    
    function getEmailTemplatesOptions(){
        return Mage::getModel('adminhtml/system_config_source_email_template')->toOptionArray();
    }
    
    function getCouponTypesOptions(){
        $ret = array();
        $types = Mage::helper('amacart')->getCouponTypes();
        
        foreach($types as $val => $lb){
            $ret[] = array(
                'value' => $val,
                'label' => $lb
            );
        }
        
        return $ret;
    }
    
    function getDefaultTemplateId(){

        $template = Mage::getModel('adminhtml/email_template')->load(Amasty_Acart_Model_Schedule::DEFAULT_TEMPLATE_CODE, 'template_code');
        return $template->getId() ? $template->getId() : NULL;

    }

    function getRules(){
        if (!$this->_salerRuleCollection)
            $this->_salerRuleCollection = Mage::getModel('salesrule/rule')->getCollection()
            ->addFilter('use_auto_generation', 1);

        return $this->_salerRuleCollection;
    }

    function getRulesCount(){
        return $this->getRules()->getSize();
    }
    
    
}