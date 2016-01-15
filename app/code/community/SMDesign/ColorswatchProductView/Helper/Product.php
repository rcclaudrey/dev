<?php
class SMDesign_Colorswatchproductview_Helper_Product extends Mage_Catalog_Helper_Product {
	
	function canShow($product, $where = 'catalog') {

		$isVisible = parent::canShow($product, $where);
		if (!$isVisible && Mage::app()->getRequest()->getParam('pid') > 0) {
			$product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('pid'));
			foreach ($product->getTypeInstance(true)->getUsedProducts(null, $product) as $childProduct) {
				$childIds[] = $childProduct->getId();
			}
			if (in_array(Mage::app()->getRequest()->getParam('id'), $childIds)) {
				$isVisible = true;
			}
		}

		return $isVisible;
	}
}