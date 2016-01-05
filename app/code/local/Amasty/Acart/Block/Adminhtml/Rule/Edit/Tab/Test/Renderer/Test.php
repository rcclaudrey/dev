<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tab_Test_Renderer_Test extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amacart'); 
        $id = $row->getId();
        return '<button type="button" class="scalable task" onclick="runRuleTesting(this, ' . $id . ', \'' . $row->getCustomerEmail() . '\')"><span><span><span>' . $hlp->__('Run') . '</span></span></span></button>';

    }
}