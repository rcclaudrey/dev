<?php

class Vikont_ARIOEM_IndexController extends Mage_Core_Controller_Front_Action
{

	public function assemblyAction()
	{
		$responseAjax = new Varien_Object(Mage::helper('arioem')->getAssemblyData());
		$this->getResponse()->setBody($responseAjax->toJson());
	}



	public function testAction()
	{
vd($this);
die;
		$id = $this->getRequest()->getParam('id');
vd($id);

		$customerGroup = Mage::getModel('customer/group');
		$customerGroup->load($id);
vd($customerGroup->getData());

		$customerGroup
			->addData(array(
					'tax_class_id' => 1,
					'cost_percent' => (float)$this->getRequest()->getParam('cp'),
				))
			->save();

vd(Mage::getModel('customer/group')->load($id)->getData());
die;
	}

}