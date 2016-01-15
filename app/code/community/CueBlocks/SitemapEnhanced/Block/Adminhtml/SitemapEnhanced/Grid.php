<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
        $this->setId('sitemapEnhancedGrid');
        $this->setDefaultSort('sitemap_id');

        $urlNew    = $this->getUrl('*/sitemapEnhanced/new');
        $urlConfig = $this->getUrl('*/system_config/edit/section/sitemapEnhanced');

        $this->_emptyText = Mage::helper('adminhtml')->__('No XML Sitemaps to show here. <br /> You can add a sitemap by clicking on <a href="' . $urlNew . '">\'Add Sitemap\'</a>, which will create XML Sitemaps based on the default configuration setting of this extenion. <br /> If you want to change the default settings, please go to <a href="' . $urlConfig . '">\'Configuration\'</a> and make the desired changes');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sitemapEnhanced/sitemapEnhanced')->getCollection();
        /* @var $collection Mage_Sitemap_Model_Mysql4_Sitemap_Collection */

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('sitemap_id', array(
            'header' => Mage::helper('sitemapEnhanced')->__('ID'),
            'index'  => 'sitemap_id',
            'width'  => '16px',
        ));

        $this->addColumn('sitemap_filename', array(
            'header'   => Mage::helper('sitemapEnhanced')->__('XML Sitemap(s)'),
            'index'    => 'sitemap_filename',
            'renderer' => 'sitemapEnhanced/adminhtml_sitemapEnhanced_grid_renderer_files'
        ));

        $this->addColumn('sitemap_path', array(
            'header' => Mage::helper('sitemapEnhanced')->__('Path'),
            'index'  => 'sitemap_path',
        ));

        $this->addColumn('link', array(
            'header'   => Mage::helper('sitemapEnhanced')->__('Sitemap Location'),
            'index'    => 'concat(sitemap_path, sitemap_filename)',
            'renderer' => 'sitemapEnhanced/adminhtml_sitemapEnhanced_grid_renderer_link',
            'filter'   => false,
            'sortable' => false
        ));

        $this->addColumn('sitemap_tot_links', array(
            'header'   => Mage::helper('sitemapEnhanced')->__('Number of Pages'),
            'renderer' => 'sitemapEnhanced/adminhtml_sitemapEnhanced_grid_renderer_pages',
            'filter'   => false,
            'sortable' => false,
            'width'    => '200px',
        ));

        $this->addColumn('sitemap_time', array(
            'header' => Mage::helper('sitemapEnhanced')->__('Last Time Generated'),
            'index'  => 'sitemap_time',
            'type'   => 'datetime',
            'width'  => '156px',
        ));


        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header' => Mage::helper('sitemapEnhanced')->__('Store View'),
                'index'  => 'store_id',
                'type'   => 'store',
            ));
        }

        $this->addColumn('action', array(
            'header'   => Mage::helper('sitemapEnhanced')->__('Action'),
            'filter'   => false,
            'sortable' => false,
            'renderer' => 'sitemapEnhanced/adminhtml_sitemapEnhanced_grid_renderer_action'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('sitemap_id' => $row->getId()));
    }

}
