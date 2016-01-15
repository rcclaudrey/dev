<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Init container
     */
    public function __construct()
    {
        $this->_objectId = 'sitemap_id';
        $this->_controller = 'adminhtml_sitemapEnhanced';
        $this->_blockGroup = 'sitemapEnhanced';

        parent::__construct();

//        $this->_addButton('ping', array(
//            'label'   => Mage::helper('adminhtml')->__('Save & Generate & Ping'),
//            'onclick' => "$('generate').value=2; editForm.submit();",
//            'class'   => 'add',
//        ));

        $this->_addButton('generate', array(
            'label'   => Mage::helper('adminhtml')->__('Save & Generate'),
            'onclick' => "$('generate').value=1; editForm.submit();",
            'class'   => 'add'
        ));
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('sitemapEnhanced_sitemap')->getId()) {
            return Mage::helper('sitemapEnhanced')->__('Edit Sitemap');
        } else {
            return Mage::helper('sitemapEnhanced')->__('Generate New Sitemap');
        }
    }

    /**
     * Return save url for edit form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'back'     => null));
    }

}
