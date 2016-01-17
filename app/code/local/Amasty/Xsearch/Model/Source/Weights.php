<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */  
class Amasty_Xsearch_Model_Source_Weights
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return array(
            array('value' => 1, 'label'=> '1'),
            array('value' => 2, 'label'=> '2'),
            array('value' => 3, 'label'=> '3'),
            array('value' => 4, 'label'=> '4'),
            array('value' => 5, 'label'=> '5'),
            array('value' => 6, 'label'=> '6'),
            array('value' => 7, 'label'=> '7'),
            array('value' => 8, 'label'=> '8'),
            array('value' => 9, 'label'=> '9'),
            array('value' => 10, 'label'=> '10')
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $arr  = array(array('' => '-'));
        $optionArray = $this->toOptionArray();
        foreach($optionArray as $option){
            $arr[$option['value']] = $option['label'];
        }
    }

}
