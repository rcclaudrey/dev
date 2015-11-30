<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Model_Method_Commented extends Amasty_Sorting_Model_Method_Toprated
{
    public function getCode()
    {
        return 'reviews_count';
    }    
    
    public function getName()
    {
        return 'Reviews Count';
    }
   
}