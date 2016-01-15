<?php

/**

 */
class CueBlocks_SitemapEnhanced_Model_System_Config_Backend_Priority extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave()
    {
        $value = $this->getValue();
        $group = $this->getGroupId();

        switch ($group)
        {
            case 'product_out':
                $group = 'ouf of stock product';
                break;

            case 'page':
                $group = 'CMS pages';
                break;
        }


        if ($value < 0 || $value > 1) {
            throw new Exception(Mage::helper('sitemap')->__('The priority (' . $group . ') must be between 0 and 1.'));
        } elseif (trim($value) == '') {
             return $this;
        } elseif (($value == 0) && !($value === '0' || $value === '0.0')) {
            throw new Exception(Mage::helper('sitemap')->__('The priority (' . $group . ') must be between 0 and 1.'));
        }

        return $this;
    }

}
