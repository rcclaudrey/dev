<?php

class Vikont_ARIOEM_ShoppinglistController extends Mage_Core_Controller_Front_Action
{

	protected function _getCart()
	{
		return Mage::getSingleton('checkout/cart');
	}



	protected function _getSession()
	{
		return Mage::getSingleton('checkout/session');
	}



	protected function _getQuote()
	{
		return $this->_getCart()->getQuote();
	}



	public function indexAction()
	{
		$this->loadLayout()->renderLayout();
	}



	public function updateAction()
	{
		$data = $this->getRequest()->getPost();

		$response = array(
			'action' => $data['action'],
			'errorMessage' => '',
		);

		try {
			switch ($data['action']) {
				case 'update':
					$updateInfo = array();

					foreach($data['items'] as $itemId => $qty) {
						$updateInfo[$itemId] = array('qty' => max((int)$qty, 1));
					}

					$this->_getCart()
						->updateItems($updateInfo)
						->save();
		            $this->_getSession()->setCartWasUpdated(true); /*

					$quoteItem = $cart->getQuote()->getItemById($id);
					if (!$quoteItem) {
						Mage::throwException($this->__('Quote item is not found.'));
					}
					if ($qty == 0) {
						$cart->removeItem($id);
					} else {
						$quoteItem->setQty($qty)->save();
					}
					$this->_getCart()->save();/**/
					
					break;

				case 'delete':
					$this->_getCart()->removeItem($data['itemId'])->save();
					break;

				case 'clear':
					$oemAttrSetId = Mage::getStoreConfig('arioem/add_to_cart/oem_product_attr_set_id');
					$items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();

					foreach($items as $item) {
						if($oemAttrSetId == $item->getProduct()->getAttributeSetId()) {
							$this->_getCart()->removeItem($item->getId());
						}
					}

					$this->_getCart()->save();
					break;

				case 'refresh':
				default:
					// do nothing here
			}

			Mage::register('isAJAX', true);

			$response['html'] = $this->getLayout()->createBlock('arioem/shoppinglist')->toHtml();
		} catch (Exception $e) {
			$response['errorMessage'] = Vikont_ARIOEM_Helper_Data::reportError($e->getMessage());
		}

		$responseAjax = new Varien_Object($response);
		$this->getResponse()->setBody($responseAjax->toJson());
	}

}