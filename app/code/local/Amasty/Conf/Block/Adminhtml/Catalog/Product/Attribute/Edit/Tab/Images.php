<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Images extends Mage_Core_Block_Template
{
    private $_confAttr;
    protected $_page = 1;
    protected $_collection;
    protected $_attrId;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amconf/icons/images.phtml');
        if (Mage::registry('entity_attribute') && Mage::registry('entity_attribute')->getId()) {
           $this->setAttribute(Mage::registry('entity_attribute')->getId());
        }
    }

    public function setPage($page){
        $this->_page = $page;
        return $this;
    }

    public function setAttribute($attributeId){
        $this->_confAttr = Mage::getModel('amconf/attribute')->load($attributeId, 'attribute_id');
        $this->_attrId = $attributeId;

        $this->getOptionsCollection()->setPageSize(Mage::helper('amconf')->getLimit());
        $this->getOptionsCollection()->setCurPage((int)$this->_page);

        return $this;
    }

    public function getOptionsCollection()
    {
        if(!$this->_collection){
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter( $this->_attrId )
                ->setPositionOrder('desc', true);

            $limit = Mage::helper('amconf')->getLimit();
            $optionCollection->getSelect()->limitPage($this->_page,  $limit);

            $this->_collection = $optionCollection;//->load();
        }
        return $this->_collection;
    }
    
    public function getIcon($option)
    {
        $width = $this->_confAttr->getSmallWidth()?  $this->_confAttr->getSmallWidth() : 50;
        $height = $this->_confAttr->getSmallHeight()? $this->_confAttr->getSmallHeight(): 50;
        return Mage::helper('amconf')->getImageUrl($option->getId(), $width, $height);
    }
    
    public function getBigIcon($option)
    {
        $width = $this->_confAttr->getBigWidth()?  $this->_confAttr->getBigWidth() : 100;
        $height = $this->_confAttr->getBigHeight()? $this->_confAttr->getBigHeight(): 100;
        return Mage::helper('amconf')->getImageUrl($option->getId(), $width, $height);
    }
    
    public function getSubmitUrl()
    {
        $url = Mage::helper('core/url')->getCurrentUrl();
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'] && '' != $_SERVER['HTTPS'])
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
}
