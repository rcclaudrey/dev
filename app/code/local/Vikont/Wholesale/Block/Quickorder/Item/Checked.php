<?php

class Vikont_Wholesale_Block_Quickorder_Item_Checked extends Mage_Core_Block_Template
{
	const PARTNUMBER_DATA_NAME = 'part_number';
	const PARTS_INFO = 'parts_info';


	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_wholesale/quickorder/item/checked.phtml');
	}



	public function getPartNumber()
	{
		return $this->getData(self::PARTNUMBER_DATA_NAME);
	}



	public function getOutputData()
	{
		$result = false;
		$partNumber = $this->getPartNumber();

		$oemPart = Mage::helper('wholesale/OEM')->findPart($partNumber);
		if($oemPart) {
			$result = array(
				'type' => 'oem',
				'sku' => $partNumber,
				'brand' => Vikont_Wholesale_Helper_OEM::getBrandNameByCode(Vikont_Wholesale_Helper_OEM::getARI2TMSCode(
						trim($oemPart['supplier_code']))),
				'name' => $oemPart['part_name'],
				'msrp' => Vikont_Format::formatPrice($oemPart['msrp']),
				'price' => Vikont_Format::formatPrice(Mage::helper('wholesale')->calculateOEMPrice($oemPart['cost'])),
			);
		} else {
			$productId = Mage::helper('wholesale/product')->findProductIdByAttributeValue('ari_part_number', $partNumber);
			if($productId) {
				$product = Mage::getModel('catalog/product')->load($productId);

				$result = array(
					'type' => 'regular',
					'sku' => $product->getSku(),
					'brand' => $product->getData('ari_manufacturer'),
					'name' => $product->getName(),
					'msrp' => Vikont_Format::formatPrice($product->getMsrp()),
					'price' => Vikont_Format::formatPrice(Mage::helper('wholesale')->calculateWholesalePrice($product->getPrice())),
				);
			} else {
				$sku = Mage::helper('wholesale/OEM')->getSkuByPartNumber($partNumber);
				if($sku) {
					$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku);
					if($productId) {
						$product = Mage::getModel('catalog/product')->load($productId);

						$result = array(
							'type' => 'sku',
							'sku' => $sku,
							'brand' => $product->getData('ari_manufacturer'),
							'name' => $product->getName(),
							'msrp' => Vikont_Format::formatPrice($product->getMsrp()),
							'price' => Vikont_Format::formatPrice(Mage::helper('wholesale')->calculateWholesalePrice($product->getPrice())),
						);
					}
				}
			}
		} 

		return $result;
	}

}