<?php

class Vikont_Wholesale_Block_Quickorder_Item_Uploaded extends Mage_Core_Block_Template
{
	const ROWS_DATA_NAME = 'rows';


	protected $_partNumbers = null;


	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_wholesale/quickorder/item/uploaded.phtml');
	}



	protected function _toHtml()
	{
		if($this->getPartNumbers()) return parent::_toHtml();
		else return '';
	}



	public function getPartNumbers()
	{
		if(null === $this->_partNumbers) {
			// this is to be set from parent block template that, in turn, is to get the actual (non-empty) data from orderPostAction()
			$items = $this->getData(self::ROWS_DATA_NAME);

			if(!$items) {
				// this is to be set from checkPartNumberAction()
				$session = Mage::getSingleton('customer/session');
				$items = Mage::getSingleton('customer/session')->getFormData();

				if($items && is_array($items)) {
					$session->unsFormData();
				}
			}
			$this->_partNumbers = $items ? $items : false;
		}
		return $this->_partNumbers;
	}



	public function getOutputData()
	{
		$result = array();
		$partNumbers = array();

		if(!is_array($this->getPartNumbers())) {	// just for a case
			return $result;
		}

		foreach($this->getPartNumbers() as $line) {
			$partNumber = @$line[0];
			if (!$partNumber) continue;

			if(count($line) > 0) {
				if(count($line) > 1) {
					$qty = (int)$line[1];
					$qty = $qty ? $qty : 1;
				} else {
					$qty = 1;
				}

				if (isset($result[$partNumber])) {
					$result[$partNumber]['qty'] += $qty;
				} else {
					$partNumbers[$partNumber] = true;

					$result[$partNumber] = array(
						'partNumber' => $partNumber,
						'qty' => $qty,
						'type' => 'na',
						'name' => 'Part not found',
					);
				}
			}
		}

		$oemData = Mage::helper('wholesale/OEM')->findParts(array_keys($partNumbers));
		if ($oemData) {
			foreach ($oemData as $item) {
				$partNumber = $item['part_number'];

				unset($partNumbers[$partNumber]);

				$result[$partNumber]['type'] = 'oem';
				$result[$partNumber]['brand'] = Vikont_Wholesale_Helper_OEM::getBrandNameByCode(
						Vikont_Wholesale_Helper_OEM::getARI2TMSCode($item['supplier_code']));
				$result[$partNumber]['name'] = $item['part_name'];
				$result[$partNumber]['msrp'] = Vikont_Format::formatPrice($item['msrp']);
				$result[$partNumber]['price'] = Vikont_Format::formatPrice(
						Mage::helper('wholesale')->calculateOEMPrice(
								$item['cost'],
								$item['price'],
								$item['msrp']
					));
			}
		}

		// now checking for regular products
		if (count($partNumbers)) {
			$productIds = Mage::helper('wholesale/product')->findRegularProducts(array_keys($partNumbers));
			if ($productIds) {
				foreach($productIds as $productId => $partNumber) {
					unset($partNumbers[$partNumber]);

					$product = Mage::getModel('catalog/product')->load($productId);

					$result[$partNumber]['type'] = 'regular';
					$result[$partNumber]['brand'] = $product->getData('ari_manufacturer');
					$result[$partNumber]['name'] = $product->getName();
					$result[$partNumber]['msrp'] = Vikont_Format::formatPrice($product->getMsrp());
					$result[$partNumber]['price'] = Vikont_Format::formatPrice(calculateWholesalePrice($product->getPrice()));
				}
			}
		}

		// now checking for regular products found by part number
		if (count($partNumbers)) {
			foreach($partNumbers as $partNumber => $value) { // yes, we don't need $value here
				$sku = Mage::helper('wholesale/OEM')->getSkuByPartNumber($partNumber);
				if ($sku) {
					$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku);
					if($productId) {
						$product = Mage::getModel('catalog/product')->load($productId);

						$result[$partNumber]['type'] = 'sku';
						$result[$partNumber]['brand'] = $product->getData('ari_manufacturer');
						$result[$partNumber]['name'] = $product->getName();
						$result[$partNumber]['msrp'] = Vikont_Format::formatPrice($product->getMsrp());
						$result[$partNumber]['price'] = Vikont_Format::formatPrice(calculateWholesalePrice($product->getPrice()));
					}
				}
			}
		}

		return array_values($result);
	}

}