<?php

/**
 * Description of SitemapEnhancedFiles
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Mysql4_SitemapEnhancedFiles extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('sitemapEnhanced/sitemapEnhancedFiles', 'sitemap_file_id');
    }

}
