<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Grid_Renderer_Files extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhanced_Model_SitemapEnhanced */
        
        $html       = '';
        $pathmap    = $row->getHelper()->getGeneralConf($row->storeId, true)->getPathMap();
        $collection = $row->getFilesCollection();

        foreach ($collection as $item)
        {
            $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $item->getSitemapFileFilename());
            $url      = $this->htmlEscape(Mage::app()->getStore($row->getStoreId())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $fileName);

            if (file_exists(BP . DS . $pathmap . $fileName)) {
                $html .= sprintf('<div><a target="_blank" href="%1$s">%2$s</a></div>', $url, $item->getSitemapFileFilename());
            } else {
                $html .= sprintf('<div>%1$s</div>', $item->getSitemapFileFilename());
            }
        }

        return $html;
    }

}
