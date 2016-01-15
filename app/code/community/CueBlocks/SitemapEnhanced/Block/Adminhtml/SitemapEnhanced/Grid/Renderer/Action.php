<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

    public function render(Varien_Object $row)
    {
        /* @var $row CueBlocks_SitemapEnhanced_Model_SitemapEnhanced */
        
        $this->getColumn()->setActions(
                array(
                    array(
                        'url' => $this->getUrl("*/sitemapEnhanced/ping", array("sitemap_id" => $row->getSitemapId())),
                        'caption'    => Mage::helper('adminhtml')->__('Ping Sitemap'),
                    ), array(
                        'url' => $this->getUrl('*/sitemapEnhanced/generate', array('sitemap_id' => $row->getSitemapId())),
                        'caption'    => Mage::helper('sitemapEnhanced')->__('Generate'),
                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to update/generate this XML Sitemap?'),
                    )
//                    , array(
//                        'url' => $this->getUrl('*/sitemapEnhanced/generatepopup', array('sitemap_id' => $row->getSitemapId())),
//                        'caption'    => Mage::helper('sitemapEnhanced')->__('Generate Pop Up'),
//                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to update/generate this XML Sitemap?'),
//                        'popup'      => true
//                    )
                    , array(
                        'url' => $this->getUrl('*/sitemapEnhanced/delete', array('sitemap_id' => $row->getSitemapId())),
                        'caption'    => Mage::helper('sitemapEnhanced')->__('Delete'),
                        'confirm'    => Mage::helper('adminhtml')->__('Are you sure you want to delete this XML Sitemap?'),
                    )
//                    , array(
//                        'url' => $this->getUrl('*/sitemapEnhanced/addRobots', array('sitemap_id' => $row->getSitemapId())),
//                        'caption'    => Mage::helper('sitemapEnhanced')->__('Add to Robots.txt'),
//                    )
                )
        );
        return parent::render($row);
    }

}
