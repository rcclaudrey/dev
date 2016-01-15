<?php

/**
 * Description of SitemapEnhanced
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_SitemapEnhanced extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('sitemapEnhanced/sitemapEnhanced', 'sitemap_id');
    }

}
