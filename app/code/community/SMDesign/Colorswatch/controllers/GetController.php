<?php
class SMDesign_Colorswatch_GetController extends Mage_Core_Controller_Front_Action {
	
	function mainImageAction() {
		
		$selection = Mage::helper('core')->jsonDecode($this->getRequest()->getParam('selection', '[]'));
		$attributeId = $this->getRequest()->getParam('attribute_id');
		$optionId = $this->getRequest()->getParam('option_id');
		$productId = $this->getRequest()->getParam('product_id');

		$_product = Mage::getModel('catalog/product')->load($productId);
		if (!$_product->getId()) {
			$this->_forward('noRoute');
			return;
		}
		
		$selectedAttributeCode = $_product->getTypeInstance(true)->getAttributeById($attributeId, $_product)->getAttributeCode();
		
		$colorswatch = Mage::getModel('colorswatch/product_swatch')->setProduct($_product);
		$allProducts = $colorswatch->getAllowProducts();
		
		foreach ($allProducts as $product) {
		    if ($product->isSaleable() && $product->getIsInStock()) {
		    	if (Mage::getModel('colorswatch/attribute_settings')->getConfig($attributeId, 'allow_attribute_to_change_main_image') == 1 ) {
		    		if ($product->getData($selectedAttributeCode) == $optionId) {
		    			 $products[] = $product;
		    		}
		    	} else {
		    		 $products[] = $product;
		    	}
		    }
		}

		$selected = array();
		foreach ($selection as $val) {
			if ($val['selected'] == 1 && Mage::getModel('colorswatch/attribute_settings')->getConfig($val['attribute_id'], 'allow_attribute_to_change_main_image') == 1) {
				$selected[$val['attribute_id']] = $val['option_id'];
			}
		}
		
		$allAvialableAttributeCode = $colorswatch->getAllAttributeCodes();
		foreach ($colorswatch->getAllAttributeIds() as $aKey=>$aId) {
			
			if (!isset($selected[$aId]) && Mage::getModel('colorswatch/attribute_settings')->getConfig($aId, 'allow_attribute_to_change_main_image') == 1) {
				$options = $colorswatch->getAttributeById($aId)->getColorswatchOptions()->getData();
				$optionCount = count($options);
				$optionIndex = 0;
				
				while ($optionIndex < $optionCount) {
					$option = $options[$optionIndex];
					
					if ($this->productExsist($products, $allAvialableAttributeCode[$aKey], $option['option_id'])) {
						$selected[$aId] = $option['option_id'];
						$optionIndex = count($options);
					}
					$optionIndex++;
				}
			}

			if (isset($selected[$aId])) {
				foreach ($products as $key=>$simpleProduct) {
						if ($simpleProduct->getData($allAvialableAttributeCode[$aKey]) != $selected[$aId]) {
							unset($products[$key]);
						}
			  }
			}
			
		}
		
		
		/* use detected dimension from js */
//		$imgElementWidth = 	$this->getRequest()->getParam('img_width', null);	
//		$imgElementHeight = 	$this->getRequest()->getParam('img_height', null);
	
		/* always need big image becose image need to zoom */
		$imgElementWidth = null;
		$imgElementHeight = null;
		
		$images = array();
		
		if (count($products) > 0) {
			
			foreach ($products as $simpleProduct) {
				if (count($images) == 0) {
	        $simpleProduct->load();
	        $simpleProductImages = $simpleProduct->getMediaGalleryImages();
	        if (count($simpleProductImages)) {
	          foreach ($simpleProductImages as $_image) {
	            $images[] = array(
	            	'id'=> $_image->getId(),
	            	'product_id'=> $simpleProduct->getId(),
	            	'label'=> $_image->getLabel(),
	            	'image'=> sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize($imgElementWidth, $imgElementHeight)),
	            	'thumb'=> sprintf(Mage::helper('catalog/image')->init($simpleProduct, 'image', $_image->getFile())->resize(56))
	            );
	          }
	        }
				}
			
			}
		}
		
		if (count($images) == 0) {
			foreach ($_product->getMediaGalleryImages() as $_image) {

				$images[] = array(
					'image'=> sprintf(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($imgElementWidth, $imgElementHeight)),
					'thumb'=> sprintf(Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(56)),
					'label'=> $_image->getLabel(),
					'id'=> $_image->getId(),
					'product_id'=> $productId
				);

			}
		}


		echo Mage::helper('core')->jsonEncode($images);

	}

	private function productExsist($products, $aCode, $oId) {
		foreach ($products as $key=>$product) {
			if ($product->getData($aCode) == $oId) {
				return true;
			}
		}
		return false;
		
	}
	
}