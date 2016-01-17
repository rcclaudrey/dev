<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Model_Rule_Condition_Carts extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('amacart');
        $attributes = array(
            'cards_num'    => $hlp->__('Number of recovered cards this month'),
            //'sales_amount' => $hlp->__('Total Sales Amount'),
            //'prods_qty'    => $hlp->__('Number of Purchased Products'),
        );
        
        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }
    
    public function validate(Varien_Object $object)
    {
        $quote = $object;
        if (!$quote instanceof Mage_Sales_Model_Quote) {
            $quote = $object->getQuote();
        }
        
        $num = Mage::getModel('amacart/history')
                ->getRecoveredNumberByEmail($quote->getCustomerEmail());
        
        return $this->validateAttribute($num);
    }
}
?>