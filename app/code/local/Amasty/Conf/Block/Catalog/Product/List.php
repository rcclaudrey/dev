<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
		$html = parent::getPriceHtml($product, $displayMinimalPrice, $idSuffix);
		if(Mage::getStoreConfig('amconf/list/enable_list') == 1 && $product->isConfigurable()){
			$html .= Mage::helper('amconf')->getHtmlBlock($product, '');
		}
		
		return $html;
	}
}