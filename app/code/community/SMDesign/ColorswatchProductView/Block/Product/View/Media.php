<?php
class SMDesign_ColorswatchProductView_Block_Product_View_Media extends Mage_Catalog_Block_Product_View_Media {
	
	function _construct() {
		parent::_construct();
		if ( Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $this->getProduct()->getTypeId() ) {
			
			$usedAttributes = $this->getProduct()->getTypeInstance(true)->getUsedProductAttributes($this->getProduct());

			$productData = array();
			foreach ($usedAttributes as $attribute) {
				$optionValue = Mage::app()->getRequest()->getParam($attribute->getAttributeCode(), -1);
				if (-1 != $optionValue) {
					
					foreach ($this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct()) as $simpleProduct) {
						if ($simpleProduct->isSaleable() && $simpleProduct->getData($attribute->getAttributeCode()) == $optionValue) {
							
							$simpleProduct->load();
							if (count($simpleProduct->getMediaGalleryImages()) > 0 && $simpleProduct->getImage()) {
								$simpleProduct->setData('enable_zoom_plugin', Mage::registry('product')->getData('enable_zoom_plugin'));
								$products[] = $simpleProduct;
								
								// unset produt without assingend secound attribute
								foreach ($products as $key=>$val) {
									if ($val->getData($attribute->getAttributeCode()) != $optionValue) {
										unset($products[$key]);
									}
								}
							}
						}
					}
				}
			}
			
			if (isset($products) && is_array($products) && count($products) > 0) {
				$this->setProduct($products[0]);
			}
		}
		
	}
	
	
    public function getGalleryUrl($image=null) {
				$pid = Mage::getModel('catalog/session')->getCurrentSimpleProductId();
				$params = array('id'=> ($pid ? $pid : $this->getProduct()->getId()));

        if ($image) {
            $params['image'] = $image->getValueId();
            return $this->getUrl('*/*/gallery', $params);
        }
        return $this->getUrl('*/*/gallery', $params);
    }
    
    function changeTemplate($template) {
    	if ($this->getProduct()->getData('enable_zoom_plugin') == 1 && !defined('SMD_LICENSE_ERROR')) {
    		$this->setTemplate($template);
    	}
    }
    
}