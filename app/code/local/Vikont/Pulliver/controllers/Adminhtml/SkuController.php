<?php

class Vikont_Pulliver_Adminhtml_SkuController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	{
		if ($this->getRequest()->getQuery('ajax')) {
			$this->_forward('grid');
			return;
		}

		$this->loadLayout();
		$this->_title($this->__('Pulliver'))->_title($this->__('View SKU Import Table'));
		$this->_setActiveMenu('catalog/import/pulliver/viewsku');
        $this->_addBreadcrumb(Mage::helper('wholesale')->__('Pulliver'), Mage::helper('wholesale')->__('Pulliver'));
		$this->renderLayout();
	}



	public function gridAction()
	{
		$this->loadLayout()->renderLayout();
	}



	public function massDeleteAction()
	{
		$skuIds = $this->getRequest()->getParam('skus');

		if($skuIds && !is_array($skuIds)) {
			 Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pulliver')->__('Please select SKU(s).'));
		} else {
			try {
				Mage::helper('pulliver/sku')->delete('oemdb/sku', 'sku', $skuIds);

				Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('pulliver')->__('Total of %d SKU(s) were deleted.', count($skuIds))
					);
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}

		$this->_redirect('*/*/index');
	}



	/**
	 * Export customer grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName = 'sku.csv';
		$content = $this->getLayout()->createBlock('pulliver/adminhtml_sku_grid')->getCsvFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}




}