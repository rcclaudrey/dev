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



	public function getProcessedData()
	{
		$result = array();
		$partNumbers = array();

		if(is_array($this->getPartNumbers())) {	// just for a case
			foreach($this->getPartNumbers() as $line) {
				$item = array();
				if(count($line) > 0) {
					$partNumbers[] = $line[0];

					$item = array();

					if(count($line) > 1) {
						$qty = (int)$line[1];
						$qty = $qty ? $qty : 1;
					} else {
						$qty = 1;
					}

					$item['partNumber'] = $line[0];
					$item['qty'] = $qty;
					$item['productId'] = false; // for the product found by its SKU
					$item['skus'] = array(); // for the products found by their part numbers at SKU table
					$item['oemData'] = array(); // just to not to leave this empty

					$result[] = $item;
				}
			}
		}

		// checking for regular products
		$productIds = Mage::helper('wholesale/product')->findRegularProducts($partNumbers);
		if($productIds) {
			foreach($result as &$value) {
				foreach($productIds as $productId => $sku) {
					if($sku == $value['partNumber']) {
						$value['productId'] = $productId;
					}
				}
			}
			unset($value);
		}

		foreach($result as &$value) {
			$skus = Mage::helper('wholesale/OEM')->getSkusByPartNumber($value['partNumber']);
			if(is_array($skus)) {
				foreach($skus as $sku) {
					$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku['sku']);
					if($productId) {
						$value['skus'][] = array(
							'sku' => $sku,
							'productId' => $productId,
							'product' => Mage::getModel('catalog/product')->load($productId),
						);
					}
				}
			}
		}
		unset($value);

		// checking for OEM products
		$checkedData = Mage::helper('wholesale/OEM')->findParts($partNumbers);
		if($checkedData) {
			foreach($result as &$value) {
				foreach($checkedData as $item) {
					if($item['part_number'] == $value['partNumber']) {
						$value['oemData'][] = $item;
					}
				}
			}
			unset($value);
		}
		return $result;
	}



	public function getItemBlock($itemInfo)
	{
		return $this->getLayout()->createBlock('wholesale/quickorder_item_checked')
				->setData(Vikont_Wholesale_Block_Quickorder_Item_Checked::PARTS_INFO, $itemInfo);
	}

}