<?php

class Vikont_ARIOEM_CartController extends Mage_Core_Controller_Front_Action
{

	public function addAction()
	{
		$coreSession = Mage::getSingleton('core/session');
		$data = $this->getRequest()->getPost();
//vd($data);
		$oemPageURL = $data['pageURL'];
		$oemBrands = Mage::getModel('arioem/source_oembrand');
		$oemHelper = Mage::helper('arioem/OEM');
		$commonHelper = Mage::helper('arioem');
		$gainPercent = $commonHelper->getCustomerCostPercent();
		$response = new Varien_Object();
		$itemsAddedTotal = 0;
		$skusAdded = array();
		$items = array();

		foreach($data['parts'] as $part) {
			$price = (float) trim(str_replace(',', '', $part['price']), ' $');

			if($gainPercent) {
				$cost = (float) $oemHelper->getOEMCost($part['brand'], $part['sku']);

				if($cost) {
					$price = $cost * (100 + $gainPercent) / 100;
				} else {
					$coreSession->addWarning($this->__('No cost to apply custom price for Part #%s %s has been found, retail price used instead. Please confirm this with our customer support.', $part['sku'], $part['name']));

					Vikont_ARIOEM_Model_Log::logError(sprintf('No cost for OEM product found: Brand=%s PartNumber=%s Name=%s Price=%s Qty=%d IP=%s CustomerId=%d URL=%s. Retail price used instead',
							$part['brand'],
							$part['sku'],
							$part['name'],
							$part['price'],
							$part['qty'],
							$_SERVER['REMOTE_ADDR'],
							Mage::getSingleton('customer/session')->getCustomerId(),
							$oemPageURL
						));
//					continue;
				}
			}

			$items[] = array(
				'brand' => $part['brand'],
				'sku' => $part['sku'],
				'name' => $part['name'],
				'qty' => $part['qty'],
				'price' => $price,
			);
		}

		$checkoutSession = Mage::getSingleton('checkout/session');
		$cart = Mage::getSingleton('checkout/cart');

		$cart->getQuote()->setIsSuperMode(true); // TODO

		try {
			$productId = Vikont_ARIOEM_Helper_Cart::getOemProductId();
			if(!$productId) {
				Mage::throwException($this->__('Cannot find OEM parent product'));
			}

			$customOptionId  = Vikont_ARIOEM_Helper_Db::getTableValue(
					'catalog/product_option',
					'option_id',
					'product_id=' . $productId
				);

			foreach($items as $itemData) {
				$product = Mage::getModel('catalog/product')->load($productId);
				if(!$product->getId()) {
					Mage::log('ERROR: ARIOEM: No dummy OEM product found');
					Mage::throwException($this->__('Cannot add product(s) to Cart'));
					break;
				}

				$addToCartParams = array(
					'product' => $productId,
					'options' => array($customOptionId => $itemData['sku']),
					'qty' => $itemData['qty'],
				);

				$itemCountBefore = count($cart->getItems());

				$cart->addProduct($product, $addToCartParams);

				$brandName = $oemBrands->getOptionText($itemData['brand']);

				if(!count($cart->getItems())) {
					$cart->save();
				}

				// if the item was really added, but not just increased the qty of its copy that's been added before
				if($itemCountBefore < count($cart->getItems())) {
					$cart->getItems()->getLastItem() // TODO: check if same item has been added before
						->setSku($itemData['sku'])
						->setName($this->__('OEM %s | %s', $brandName, $itemData['name']))
						->setOriginalCustomPrice($itemData['price'])
						->setRedirectUrl($oemPageURL)
						->save();
				}

				$itemsAddedTotal += $itemData['qty'];
				$skusAdded[] = $itemData['sku'];
			}

			$cart->getQuote()->setTotalsCollectedFlag(false);
			$cart->save();

			$checkoutSession->setCartWasUpdated(true);

			Mage::dispatchEvent('checkout_cart_add_product_complete',
				array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
			);

			if(!$cart->getQuote()->getHasError()) {
				$response->setError(0);
				$response->setProductsAdded($skusAdded);
				$response->setQty((string)(int)($cart->getSummaryQty()));

				$message = (count($items) == 1)
					? $this->__('Item %s was added to your shopping cart', $items[0]['name'])
					: $this->__('%d items were added to your shopping cart', $itemsAddedTotal);
				$response->setMessage($message);

				$cartTopBlock = $this->getLayout()->createBlock('checkout/cart_sidebar');
				$topCartHtml = $cartTopBlock
						?	$cartTopBlock
								->setTemplate('checkout/cart/cartheader.phtml')
								->setIsAjax(true)
								->toHtml()
						:	'';
				$response->setCartTop($topCartHtml);
			} else {
				$response->setError(1);
				$response->setMessage($this->__('Some error has occurred while adding to Cart'));
			}
		} catch (Exception $e) {
			Mage::logException($e);
			$response->setError(1);
			$response->setMessage($this->__('Cannot add the item to shopping cart, reason: %s', $e->getMessage()));
		}

		$this->getResponse()->setBody($response->toJson());
	}

}