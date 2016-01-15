<?php

class SMDesign_ColorswatchProductView_Model_Observer {
	
	public function initSelection(Varien_Event_Observer $observer) {
		$product = $observer->getEvent()->getProduct();
		$controllerAction = $observer->getEvent()->getControllerAction();
		
		if (Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $product->getTypeId()) {
			$usedAttributes = $product->getTypeInstance(true)->getUsedProductAttributes($product);

			$productData = array();
			foreach ($usedAttributes as $attribute) {
				$optionValue = $controllerAction->getRequest()->getParam($attribute->getAttributeCode(), -1);
				if (-1 == $optionValue) {
					break;
				}
				$productData[$attribute->getId()] = $optionValue;
			}
			$product->setData('preconfigured_values', new Varien_Object(array('super_attribute'=>$productData)));
		}
	}
}

