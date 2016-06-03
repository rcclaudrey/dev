<?php

class Vikont_OEMGrid_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		$this
			->loadLayout()
			->_title($this->__('OEM Data Import'))->_title($this->__('OEM Data Import'))
			->_addBreadcrumb($this->__('OEM Data Import'), $this->__('OEM Data Import'))
			->_setActiveMenu('catalog/oemimport')
			->renderLayout();
	}



	public function fileListAction()
	{
		$response = array();

		try {
			$response['files'] = Mage::helper('oemgrid/import')->getFileList();
		} catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = true;
			$response['errorMessage'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	public function fileUploadAction()
	{
		$helper = Mage::helper('oemgrid/import');
		$response = array();

		try {
			$newFileName = $helper->findUploadedFileName($_FILES['file']['name']);

			$dirName = dirname($newFileName);
			if (!file_exists($dirName)) {
				mkdir($dirName, 0700, true);
			}

			move_uploaded_file($_FILES['file']['tmp_name'], $newFileName);

			$response['files'] = $helper->getFileList();
		} catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = true;
			$response['errorMessage'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	public function fileDeleteAction()
	{
		$response = array();

		try {
			$requestedFileName = $this->getRequest()->getParam('file');
			$dirName = realpath(Mage::helper('oemgrid/import')->getUploadPath());
			$fileName = realpath($dirName . DS . $requestedFileName);
			if (false === strpos($fileName, $dirName)) {
				throw new Exception($this->__('Illegal filename'));
			}

			if (!file_exists($fileName)) {
				throw new Exception($this->__('The file does not exist'));
			}

			chmod ($fileName, 0777);
			unlink($fileName);

			$response['files'] = Mage::helper('oemgrid/import')->getFileList();
			$response['message'] = $this->__('The file %s was removed', $requestedFileName);
		} catch (Exception $e) {
			Mage::logException($e);
			$response['error'] = true;
			$response['errorMessage'] = $e->getMessage();
		}

		$this->getResponse()->setBody(json_encode($response));
	}



	public function importAction()
	{
		
	}



	public function testAction() 
	{
	}

}