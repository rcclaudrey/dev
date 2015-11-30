<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Autoshipping
 */
class Amasty_Autoshipping_Model_Source_AllowedFields extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array(
            array('value' => 'region', 'label' => 'State'),
            array('value' => 'postcode', 'label' => 'ZIP'),
            array('value' => 'city', 'label' => 'City'),
            array('value' => 'empty', 'label' => ''),
        );

        return $options;
    }
}