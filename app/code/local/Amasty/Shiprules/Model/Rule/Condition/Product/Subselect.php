<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */
class Amasty_Shiprules_Model_Rule_Condition_Product_Subselect extends Mage_SalesRule_Model_Rule_Condition_Product_Subselect
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('amshiprules/rule_condition_product_subselect')
            ->setValue(null);
    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('salesrule');
        $this->setAttributeOption(array(
            'qty'             => $hlp->__('total quantity'),
            'base_row_total'  => $hlp->__('total amount'),
            'row_weight'      => $hlp->__('total weight'),
        ));
        return $this;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

        $attr = $this->getAttribute();
        $total = 0;
        if ($object->getAllItems()){
            foreach ($object->getAllItems() as $item) {

                if ($item->getProductType() == 'configurable'){
                    continue;
                }
                
                //can't use parent here
                if (Mage_SalesRule_Model_Rule_Condition_Product_Combine::validate($item)) {
                    $total += $item->getData($attr);
                }
            }
        }

        return $this->validateAttribute($total);
    }
}