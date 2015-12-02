<?php

class Vikont_ARIOEM_PartsController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this->loadLayout()->renderLayout();
	}



	public function partInfoAction()
	{
		$responseData = array(
			'errorMessage' => '',
		);

		try {
			$responseData['html'] = $this->getLayout()->createBlock('arioem/parts_part')->toHtml();
		} catch (Exception $e) {
			$response['errorMessage'] = Vikont_ARIOEM_Helper_Data::reportError($e->getMessage());
		}

		echo json_encode($responseData);
		die;
	}



	public function barcodeAction()
	{
//		$text = $_SERVER['QUERY_STRING']

	}



	public function testAction()
	{
		Mage::helper('arioem')->getBarcodeImageFile('abc-def\'"4.12\\mnk-/..\\');
//		Mage::helper('arioem'))->getBarcode('13101-MEN-A70');
	}

}