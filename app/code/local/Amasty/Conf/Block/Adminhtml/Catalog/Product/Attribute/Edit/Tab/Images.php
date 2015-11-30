<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Adminhtml_Catalog_Product_Attribute_Edit_Tab_Images extends Mage_Core_Block_Template
{
    private $_confAttr;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amconf/icons.phtml');
        $this->_doUpload();
        $this->_confAttr = Mage::getModel('amconf/attribute')->load(Mage::registry('entity_attribute')->getId(), 'attribute_id');
    }
    
    protected function _doUpload()
    {
        
        if (Mage::app()->getRequest()->isPost())
        {
            // saving attribute 'use_image' property
            $confAttr = Mage::getModel('amconf/attribute')->load(Mage::registry('entity_attribute')->getId(), 'attribute_id');
            if (!$confAttr->getId())
            {
                $confAttr->setAttributeId(Mage::registry('entity_attribute')->getId());
            }

            $confAttr->setUseImage(intval(Mage::app()->getRequest()->getPost('amconf_useimages')));
            
            $confAttr->setSmallWidth(intval(Mage::app()->getRequest()->getPost('small_width')));
            $confAttr->setSmallHeight(intval(Mage::app()->getRequest()->getPost('small_height')));
            
            $confAttr->setUseTooltip(intval(Mage::app()->getRequest()->getPost('amconf_usetooltip')));
            
            if(intval(Mage::app()->getRequest()->getPost('amconf_usetooltip'))) {
                $confAttr->setBigWidth(intval(Mage::app()->getRequest()->getPost('big_width')));
                $confAttr->setBigHeight(intval(Mage::app()->getRequest()->getPost('big_height')));
            }
            
            $confAttr->save();
        }

        $uploadDir = Mage::getBaseDir('media') . DS . 'amconf' . DS . 'images' . DS;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
                                                    
        /**
        * Deleting
        */
        $toDelete = Mage::app()->getRequest()->getPost('amconf_icon_delete');
        if ($toDelete)
        {
            foreach ($toDelete as $optionId => $del)
            {
                if ($del)
                {
                    @unlink($uploadDir . $optionId . '.jpg');
                }
            }
        }
        
        /**
        * Uploading files
        */
        if (isset($_FILES['amconf_icon']) && isset($_FILES['amconf_icon']['error']))
        {
            foreach ($_FILES['amconf_icon']['error'] as $optionId => $errorCode)
            {
                if (UPLOAD_ERR_OK == $errorCode && $confAttr && $confAttr->getId())
                {
                    move_uploaded_file($_FILES['amconf_icon']['tmp_name'][$optionId], $uploadDir . $optionId . '.jpg');
                    if (!file_exists($uploadDir . $optionId . '.jpg'))
                    {
                        Mage::getSingleton('catalog/session')->addSuccess($this->__('File was not uploaded. Please check permissions to folder media/amconf/images(need 0777 recursively)'));
                    }                    
                }
            }
        }

    }
    
    public function getConfAttr()
    {
        return $this->_confAttr;
    }
    
    private function _resizeImage($basePath, $newPath, $width, $height)
    {
        //$basePath - origin file location
        $imageObj = new Varien_Image($basePath);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(FALSE);
        $imageObj->keepFrame(FALSE);
        //$width, $height - sizes you need (Note: when keepAspectRatio(TRUE), height would be ignored)
        $imageObj->resize($width, $height);
        //$newPath - name of resized image
        $imageObj->save($newPath);
    }
    
    public function getOptionsCollection()
    {
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter(Mage::registry('entity_attribute')->getId())
                ->setPositionOrder('desc', true)
                ->load();
        return $optionCollection;
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
