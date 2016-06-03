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



	public function getPartsInfo()
	{
		if(!$this->hasData(self::PARTS_INFO)) {
			$partNumber = $this->getPartNumber();

			$data = array();

			$data['partNumber'] = $partNumber;
			$data['productId'] = Mage::getResourceModel('catalog/product')->getIdBySku($partNumber);
			$data['skus'] = array();
			$data['oemData'] = Mage::helper('wholesale/OEM')->findPart($partNumber);

			$skus = Mage::helper('wholesale/OEM')->getSkusByPartNumber($partNumber);

			if(is_array($skus)) {
				foreach($skus as $sku) {
					$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku['sku']);
					if($productId) {
						$data['skus'][] = array(
							'sku' => $sku['sku'],
							'productId' => $productId,
							'product' => Mage::getModel('catalog/product')->load($productId),
						);
					}
				}
			}

			$this->setData(self::PARTS_INFO, $data);
		}
		return $this->getData(self::PARTS_INFO);
	}



	public function getProduct()
	{
		$partsInfo = $this->getPartsInfo();
		$productId = $partsInfo['productId'];

		$this->setProduct(
				$productId
				?	Mage::getModel('catalog/product')->load($productId)
				:	false
			);

		return $this->getData('product');
	}



	public function getOEMExtendedInfo()
	{
		$result = array();
		$helper = Mage::helper('wholesale');

		try {
			$partsInfo = $this->getPartsInfo();

			foreach($partsInfo['oemData'] as $item) {
				$result[] = array(
					'partNumber' => $item['part_number'],
					'price' => Mage::helper('core')->formatPrice($helper->calculateOEMPrice($item['cost']), false),
					'brand' => $this->getBrandName(Vikont_Wholesale_Helper_OEM::getARI2TMSCode($item['supplier_code'])),
				);
			}
		} catch (Exception $e) {
			// do nothing as we don't need to report any errors from here
		}
		return $result;
	}



	public function getBrandName($code)
	{
		$brandName = Mage::getModel('wholesale/source_oembrand')->getOptionText($code);
		return $brandName ? $brandName : $code;
	}

}