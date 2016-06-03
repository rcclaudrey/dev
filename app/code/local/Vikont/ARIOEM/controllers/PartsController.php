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

}