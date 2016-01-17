<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */

class Amasty_Fpc_Model_Config_Source_FlushType
{
    const FLUSH_PRODUCT_ONLY = 0;
    const FLUSH_ASSOCIATED = 1;

    public function toOptionArray()
    {
        $hlp = Mage::helper('amfpc');
        $vals = array(
            self::FLUSH_PRODUCT_ONLY   => $hlp->__('Flush only product page'),
            self::FLUSH_ASSOCIATED     => $hlp->__('Also flush associated pages'),
        );

        $options = array();
        foreach ($vals as $k => $v)
            $options[] = array(
                'value' => $k,
                'label' => $v
            );

        return $options;
    }
}
