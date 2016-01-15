<?php

/**
 * Description of Cms
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_System_Config_Source_Cms
{

    public function toOptionArray()
    {

        $collection = Mage::getResourceModel('cms/page_collection')->load();

        $options = array();

        foreach ($collection as $item)
        {
            $options[] = array(
                'label' => $item->getData('title'),
                'value' => $item->getData('page_id')
            );
        }

        return $options;
    }

}
