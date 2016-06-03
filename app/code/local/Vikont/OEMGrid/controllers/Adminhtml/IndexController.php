<?php

class Vikont_OEMGrid_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		$this
			->loadLayout()
			->_title($this->__('OEM Parts Manager'))->_title($this->__('Manage OEM Parts'))
			->_addBreadcrumb($this->__('OEM Parts Manager'), $this->__('OEM Parts Manager'))
			->_setActiveMenu('catalog/oemgrid')
			->renderLayout();
	}



	public function gridAction()
	{
		$this->loadLayout()->renderLayout();
	}



	public function saveAction()
	{
		$rowId = $this->getRequest()->getParam('rowId');
		$colName = $this->getRequest()->getParam('colName');
		$value = $this->getRequest()->getParam('value');

		$response = array(
			'id' => $rowId . '-' . $colName,
			'html' => '',
		);

		try {
			$gridBlock = $this->getLayout()->createBlock('oemgrid/adminhtml_part_grid');
			$gridBlock->prepareColumns(); // making the grid to add columns

			$column = $gridBlock->getColumn($colName);
			$fieldName = $column->getIndex();

			Mage::getModel('oemgrid/part')
				->load($rowId)
				->setData($fieldName, $value)
				->save();

			$model = Mage::getModel('oemgrid/part')
				->load($rowId);

			$response['html'] = $column->getRowField($model);
		} catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = true;
			$response['message'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	public function deleteAction()
	{
		$session = Mage::getSingleton('adminhtml/session');
		$id = $this->getRequest()->getParam('id');

		if ($id) {
			try {
				Mage::getModel('oemgrid/part')
					->setId($id)
					->delete();

				$session->addSuccess($this->__('OEM part was successfully deleted'));
			} catch (Exception $e) {
				Mage::logException($e);
				$session->addError($e->getMessage());
			}
		}

		if ($this->getRequest()->isAjax()) {
			$this->_redirect('*/*/grid');
		} else {
			$this->_redirect('*/*/');
		}
	}

}