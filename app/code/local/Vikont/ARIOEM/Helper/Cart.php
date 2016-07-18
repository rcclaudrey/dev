<?php

class Vikont_ARIOEM_Helper_Cart extends Mage_Core_Helper_Abstract
{
	protected static $_oemProductId = null;



	public static function getOemProductId()
	{
		if(!self::$_oemProductId) {
			self::$_oemProductId = Mage::getResourceModel('catalog/product')
					->getIdBySku(Mage::getStoreConfig('arioem/add_to_cart/dummy_product'));
		}

		return self::$_oemProductId;
	}



	public static function isOemProduct($product)
	{
		$productId = is_object($product) ? $product->getId() : (int) $product;
		return ($productId == self::getOemProductId());
	}



	public function addToCart($data)
	{
//		$oemPageURL = $data['pageURL'];
		$brandCode = $data['brand'];
		$vehicleName = @$data['vehicle'];
		$yearName = @$data['year'];
		$modelName = @$data['model'];
		$assemblyName = @$data['assembly'];

		$response = new Varien_Object();

		$oemBrands = Mage::getModel('arioem/source_oembrand');
		if ('SLN' == $brandCode) {
			$brandCode = 'POL';
		}
		$brandName = $oemBrands->getOptionText($brandCode);
		if(!$brandName) {
			$brandName = Vikont_ARIOEM_Model_Oem_Part::getBrandNameByShortname($brandCode);
		}
		$oemHelper = Mage::helper('arioem/OEM');
		$commonHelper = Mage::helper('arioem');
		$gainPercent = $commonHelper->getCustomerCostPercent();
		$itemsAddedTotal = 0;
		$skusAdded = array();
		$items = array();
		$warnings = array();

		foreach($data['parts'] as $part) {
			$partData = $oemHelper->getOEMData($brandCode, $part['sku']);

			if($partData) {
				if($gainPercent) {
					$price = $partData['cost'] * (100 + $gainPercent) / 100;
				} else {
					$origPrice = floatval($partData['price']);
					$price = $origPrice
						?	$origPrice
						:	$price = (float) trim(str_replace(',', '', $part['price']), ' $');
				}
			} else {
				$warnings[] = $this->__('No record for %s, part #%s was found in DB. Please contact our support.', $part['name'], $part['sku']);

				Vikont_ARIOEM_Model_Log::logError(sprintf('No record for OEM product found: Brand=%s PartNumber=%s Name=%s Price=%s Qty=%d IP=%s CustomerId=%d URL=%s',
						$brandCode,
						$part['sku'],
						$part['name'],
						$part['price'],
						$part['qty'],
						$_SERVER['REMOTE_ADDR'],
						Mage::getSingleton('customer/session')->getCustomerId(),
						$oemPageURL
					));

				continue;
			}

			$qty = ((int)$part['qty'])
				?	(int)$part['qty']
				:	1;

			$items[] = array(
				'sku' => $part['sku'],
				'name' => $partData['part_name'], //$part['name'], // just to make sure
				'qty' => $qty,
				'price' => $price,
				'pageURL' => @$part['pageURL'],
			);
		}

		$checkoutSession = Mage::getSingleton('checkout/session');
		$cart = Mage::getSingleton('checkout/cart');

		$cart->getQuote()->setIsSuperMode(true); // TODO

		$productId = Vikont_ARIOEM_Helper_Cart::getOemProductId();
		if(!$productId) {
			Mage::throwException($this->__('Cannot find OEM parent product'));
		}

		$brandOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_brand_option_id');
		$yearOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_year_option_id');
		$modelOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_model_option_id');
		$partNumberOptionId  = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_partNo_option_id');

		$productNameTemplate = Mage::getStoreConfig('arioem/add_to_cart/dummy_product_name_template');

		foreach($items as $itemData) {
			$product = Mage::getModel('catalog/product')->load($productId);
			if(!$product->getId()) {
				Mage::log('ERROR: ARIOEM: No dummy OEM product found');
				Mage::throwException($this->__('Cannot add product(s) to Cart'));
				break;
			}

			$addToCartOptions = array(
				$brandOptionId => $brandName,
			);
			if ($yearName) $addToCartOptions[$yearOptionId] = $yearName;
			if ($modelName) $addToCartOptions[$modelOptionId] = $modelName;
			$addToCartOptions[$partNumberOptionId] = $itemData['sku'];

			$addToCartParams = array(
				'product' => $productId,
				'options' => $addToCartOptions,
				'qty' => $itemData['qty'],
			);

			$itemCountBefore = count($cart->getItems());

			$cart->addProduct($product, $addToCartParams);

			if(!count($cart->getItems())) {
				$cart->save();
			}

			$productName = str_replace('%BRAND%', $brandName, $productNameTemplate);
			$productName = str_replace('%VEHICLE%', $vehicleName, $productName);
			$productName = str_replace('%YEAR%', $yearName, $productName);
			$productName = str_replace('%MODEL%', $modelName, $productName);
			$productName = str_replace('%ASSEMBLY%', $assemblyName, $productName);
			$productName = str_replace('%PART_NUMBER%', $itemData['sku'], $productName);
			$productName = str_replace('%PART_NAME%', $itemData['name'], $productName);

			// if the item was really added, but not just increased the qty of its copy that's been added before
			if($itemCountBefore < count($cart->getItems())) {
				$cart->getItems()->getLastItem() // TODO: check if same item has been added before
					->setSku($itemData['sku'])
					->setName($productName)
					->setOriginalCustomPrice($itemData['price'])
					->setRedirectUrl($itemData['pageURL'])
					->save();
			}

			$itemsAddedTotal += $itemData['qty'];
			$skusAdded[] = $itemData['sku'];
		}

		$cart->getQuote()->setTotalsCollectedFlag(false);
		$cart->save();

		$checkoutSession->setCartWasUpdated(true);

		Mage::dispatchEvent('checkout_cart_add_product_complete',
			array('product' => $product, 'request' => Mage::app()->getRequest(), 'response' => Mage::app()->getResponse())
		);

		if(count($warnings)) {
			$response->setWarnings($warnings);
		}

		$response
			->setCartSummaryQty((int)($cart->getSummaryQty()))
			->setItemsAddedTotal($itemsAddedTotal)
			->setSkusAdded($skusAdded)
//			->setItems($items)
			->setError($cart->getQuote()->getHasError());

		return $response;
	}

}