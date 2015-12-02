<?php

class Vikont_Wholesale_Helper_Order extends Mage_Core_Helper_Abstract
{
	protected $_quote = null;
	protected $_order = null;
	protected $_customer = null;
    protected $_storeId = null;
	protected static $_dummyProductId = null;
	protected static $_customOptionIds = array();


	public function createOrder($data)
	{
		// clearing the cart
		Mage::getSingleton('checkout/cart')
			->truncate()
			->save();

		$this->_quote = Mage::getModel('sales/quote');
		$this->_quote->setStoreId($this->getStoreId());

		$this->_addProducts($data['rows']);

		$customer = $this->getCustomer();
		$this->_quote->assignCustomerWithAddressChange($customer);

		$shippingMethod = Mage::getStoreConfig('wholesale/order/shipping_method');
		$paymentMethod = Mage::getStoreConfig('wholesale/order/payment_method');

		$this->_quote->getShippingAddress()
				->implodeStreetAddress()
				->setCollectShippingRates(true)
				->setShippingMethod($shippingMethod)
				->setPaymentMethod($paymentMethod);

		$this->_quote->getPayment()->importData(array(
				'method' => $paymentMethod,
				'po_number' => $data['poNumber']
			));

		$this->_quote
			->collectTotals()
			->save();

		$service = Mage::getModel('sales/service_quote', $this->_quote);
		$service->submitAll();
		$this->_order = $service->getOrder();

		$this->_order
			->addStatusHistoryComment($data['notes'])
			->setIsVisibleOnFront(true)
			->setIsCustomerNotified(true)
			->save();

		// clearing the cart
		Mage::getSingleton('checkout/cart')
			->truncate()
			->save();

		return $this->_order;
	}



	protected function _addProducts($productsData)
	{
		$parts = array();

		foreach($productsData as $itemData) {
			$partNumber = $itemData[0];
			$qty = max(abs($itemData[1]), 1);

			if(isset($parts[$partNumber])) {
				$parts[$partNumber] += $qty;
			} else {
				$parts[$partNumber] = $qty;
			}
		}

		foreach($parts as $partNumber => $qty) {
			$productIsOEM = false;

			$productId = Mage::getResourceModel('catalog/product')->getIdBySku($partNumber);

			// if this is not a SKU, but a Part Number
			if(!$productId) {
				$sku = Mage::helper('wholesale/OEM')->getSkuByPartNumber($partNumber);
				$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku);
			}

			// this is [most probably] OEM product
			if(!$productId) {
				$productId = $this->getDummyProductId();
				$productIsOEM = true;
			}

			$product = Mage::getModel('catalog/product')->load($productId);
			if(!$product->getId()) {
				Mage::log('ERROR: Product not found, ID='.(int)$productId);
				Mage::throwException($this->__('Cannot load product ID=%d', (int)$productId));
				return;
			}

			if($productIsOEM) {
				$partInfo = $this->getPartInfo($partNumber);
				$productPrice = Mage::helper('wholesale')->calculateOEMPrice($partInfo['cost']);
				$brandName = Vikont_Wholesale_Helper_OEM::getSupplierName($partInfo['supplier_code']);
				$productName = $this->__('OEM %s | %s', $brandName, $partNumber);
				$customOptionId = $this->getCustomOptionId();

				$addToCartParams = new Varien_Object(array(
					'product' => $productId,
					'options' => array($customOptionId => $partNumber),
					'qty' => $qty,
				));

				$this->_quote->addProduct($product, $addToCartParams);

				$this->_quote->getItemsCollection()->getLastItem()
					->setSku($partNumber)
					->setName($productName)
//					->setRedirectUrl($oemPageURL) // TODO: make a page where this URL could point at: maybe OEM parts lookup?
					->setOriginalCustomPrice($productPrice);
			} else {
				$addToCartParams = new Varien_Object(array(
					'product' => $productId,
					'qty' => $qty,
				));

				try {
					if(Mage::getStoreConfigFlag('wholesale/order/ignore_stock_status')) {
						$product->getStockItem()
								->setManageStock(false)
								->setUseConfigManageStock(false)
								->setIsInStock(true)
								->setNotifyStockQty(false)
								->setUseConfigNotifyStockQty(false)
								->setBackorders(true)
								->setUseConfigBackorders(false)
								->setStockStatusChangedAutomatically(false);
					}

					$this->_quote->addProduct($product, $addToCartParams);
				} catch (Exception $e) {
					$message = $e->getMessage();

					if(false !== strpos($message, 'out of stock')) {
						$message = sprintf('Product %s SKU=%s cannot be ordered because it is currently out of stock.', $product->getName(), $partNumber);
					}

					Mage::throwException($message);
				}
			}
		}
	}



	public static function getDummyProductId()
	{
		if(!self::$_dummyProductId) {
			self::$_dummyProductId = Mage::getResourceModel('catalog/product')
					->getIdBySku(Mage::getStoreConfig('wholesale/order/dummy_product'));

			if(!self::$_dummyProductId) {
				Mage::throwException('Cannot find wholesale product');
			}
		}

		return self::$_dummyProductId;
	}



	public function getCustomOptionId($productId = null)
	{
		if(null === $productId) {
			$productId = $this->getDummyProductId();
		}

		if(!isset(self::$_customOptionIds[$productId])) {
			self::$_customOptionIds[$productId] = (int) Vikont_Wholesale_Helper_Db::getTableValue(
					'catalog/product_option',
					'option_id',
					'product_id=' . $productId
				);

			if(!self::$_customOptionIds[$productId]) {
				Mage::throwException('Cannot find wholesale product custom option');
			}
		}

		return self::$_customOptionIds[$productId];
	}



	public function getPartInfo($partNumber)
	{
		$partInfo = Mage::helper('wholesale/OEM')->findPart($partNumber);

		if(is_array($partInfo)) {
			return $partInfo[0];
		} else {
			return array(
				'cost' => 0,
				'part_number' => $partNumber,
				'supplier_code' => 'UNKNOWN',
			);
		}
	}



	public function getCustomer()
	{
		if(null === $this->_customer) {
			if(Mage::app()->getStore()->isAdmin()) {
				$this->_customer =  Mage::getSingleton('adminhtml/session_quote')->getCustomer();
			} else {
				$this->_customer =  Mage::getSingleton('customer/session')->getCustomer();
			}
		}
		return $this->_customer;
	}



	public function getStoreId()
	{
		if(null === $this->_storeId) {
			$this->_storeId = Mage::app()->getWebsite($this->getCustomer()->getWebsiteId())->getDefaultStore()->getId();
		}
		return $this->_storeId;
	}

}