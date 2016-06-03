<?php

class Vikont_ARIOEM_PartcenterController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this
			->loadLayout()
			->renderLayout();
	}



	public function searchAction()
	{
		$this
			->loadLayout()
			->renderLayout();
	}



	public function addAction()
	{
		$data = $this->getRequest()->getPost();
		$response = array();

		try {
			$report = Mage::helper('arioem/cart')->addToCart($data);

			if($report->hasWarnings()) {
				$response['warnings'] = $report->getWarnings();
			}
			if($report->getError()) {
				$response['errorMessage'] = $this->__('An error has occurred while adding to Cart');
			} else {
				$message = (1 == $report->getItemsAddedTotal())
					? $this->__('The item was added to your shopping cart')
					: $this->__('%d items were added to your shopping cart', $report->getItemsAddedTotal());
				$response['message'] = $message;

				$response['skusAdded'] = array(
					$data['brand'] => $report->getSkusAdded(),
				);
			}
		} catch (Exception $e) {
			Mage::logException($e);
			$response['errorMessage'] = $this->__('Cannot add the item to shopping cart, reason: %s', $e->getMessage());
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	public function printDiagramAction()
	{
		echo $this->getLayout()->createBlock('arioem/part_selector_print')
			->setParams($this->getRequest()->getParams())
			->toHtml();
		die;
	}



	public function cartItemsAction()
	{
		$response = array();

		try {
			$response['cartItems'] = Vikont_ARIOEM_Helper_OEM::getSortedCartOEMItems();
		} catch (Exception $e) {
			Mage::logException($e);
			$response['errorMessage'] = $this->__('Cannot get shopping cart items, reason: %s', $e->getMessage());
		}

		$this->getResponse()->setBody(json_encode($response));
	}

}