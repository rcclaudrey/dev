<?php

class Vikont_ARIOEM_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Controller_Action
{

	public function saveAction()
	{
		try {
			$customerGroup = Mage::getModel('customer/group');
			$id = $this->getRequest()->getParam('id');

			if(null !== $id) {
				$id = (int)$id;
				$customerGroup->load($id);
			}

			if(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID !== $id) {
				$customerGroup->setCustomerGroupCode((string)$this->getRequest()->getParam('code'));
			}

			$customerGroup
				->addData(array(
						'tax_class_id' => (int)$this->getRequest()->getParam('tax_class'),
						'cost_percent' => (float)$this->getRequest()->getParam('cost_percent'),
					))
				->save();

			Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('customer')->__('Customer group has been saved.')
				);

			$this->_redirect('adminhtml/customer_group');

			return;

		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirectReferer($this->getUrl('adminhtml/customer_group/edit', array('id' => (int)$id)));
//			$this->getResponse()->setRedirect($this->getUrl('adminhtml/customer_group/edit', array('id' => @$data['id'])));
			return;
		}

		$this->_redirect('adminhtml/customer_group'); // not sure what this is for here?
	}

}