<?php

class Vikont_ARIOEM_PartsController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this->loadLayout();

		$part = Mage::getSingleton('arioem/oem_part');

		$this->getLayout()->getBlock('head')
				->setTitle($this->__('%s %s %s, %s - OEM parts TMSParts.com',
						$part->getPartNumber(),
						$part->getName(),
						$part->getBrandName(),
						$part->getPrice(true)
					))
				->setDescription($this->__('Get your %s %s part at tmsparts.com. Guaranteed lowest price!',
						$part->getBrandName(),
						$part->getPartNumber()
					))
				->setKeywords($this->__('%s %s %s',
						$part->getBrandName(),
						$part->getPartNumber(),
						$part->getName()
					));

		$this->renderLayout();
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



	public function alistAction()
	{
		$responseData = array(
			'errorMessage' => '',
		);

		try {
			$responseData['html'] = $this->getLayout()->createBlock('arioem/parts_assembly')
					->setParams($this->getRequest()->getParams())
					->toHtml();
		} catch (Exception $e) {
			$response['errorMessage'] = Vikont_ARIOEM_Helper_Data::reportError($e->getMessage());
		}

		echo json_encode($responseData);
		die;
	}

}