<?php

class Vikont_Fitment_GroupedController extends Mage_Core_Controller_Front_Action
{

	public function initAction()
	{
		$response = array(
			'errorMessage' => '',
		);

		try {
			$productId = $this->getRequest()->getParam('product');
			$product = Mage::getModel('catalog/product')->load($productId);
			Mage::register('current_product', $product);

			$categoryId = $this->getRequest()->getParam('category');
			$category = Mage::getModel('catalog/category')->load($categoryId);
			Mage::register('current_category', $category);

			$block = $this->getLayout()
					->createBlock('fitment/catalog_product_grouped_selector')
					->setProduct($product);

			$response['config'] = $block->getSelectorConfig();
			$response['html'] = $block->toHtml();
		} catch (Exception $e) {
			$response['errorMessage'] = $this->__('Fitment currently not available');
			Mage::logException($e);
		}

		echo json_encode($response);
		die;
	}



	/*
	 * Product details page fitment saver
	 */
	public function fitmentSaveAction()
	{
//Mage::register('vd', 1);
		$fitmentId = $this->getRequest()->getParam('fitment');
		$vehicleName = $this->getRequest()->getParam('vehicle');
		$tmsActivityId = $this->getRequest()->getParam('activity');
		$ariProductId = $this->getRequest()->getParam('product'); // ARI product ID
		$productId = $this->getRequest()->getParam('parent'); // ID of grouped product in Magento

		$ride = Mage::helper('fitment')->setCurrentRide($tmsActivityId, $fitmentId, $vehicleName);
		$product = Mage::getModel('catalog/product')->load($productId);

		$response = array(
			'errorMessage' => '',
		);

		if($ride) {
			$response['message'] = $this->__('Current fitment selection has been set as default');
			$response['partList'] = $this->getLayout()
				->createBlock('fitment/catalog_product_grouped_partlist')
					->setProduct($product)
					->setTmsActivityId($tmsActivityId)
					->setAriProductId($ariProductId)
					->setFitmentId($fitmentId)
					->toHtml();
		} else {
			$response['errorMessage'] = $this->__('Error saving fitment');
		}

		echo json_encode($response);
		die;
	}

}