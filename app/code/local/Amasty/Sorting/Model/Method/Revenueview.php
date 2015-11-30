<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

class Amasty_Sorting_Model_Method_Revenueview extends Amasty_Sorting_Model_Method_Orderview
{
    public function getCode()
    {
        return 'revenueview';
    }

    public function getName()
    {
        return 'Revenue per View';
    }

    protected function getSorters()
    {
        return array(
            'dividend' => Mage::getModel('amsorting/method_revenue'),
            'divider'  => Mage::getModel('amsorting/method_mostviewed'),
        );
    }

}