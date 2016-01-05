<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Adminhtml_Amacart_HistoryController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout(); 

        $this->_setActiveMenu('promo/amacart/history');
            
        $this->_addContent($this->getLayout()->createBlock('amacart/adminhtml_history')); 
            $this->renderLayout();

            }
        
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'history.csv';
        $grid       = $this->getLayout()->createBlock('amacart/adminhtml_history_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'history.xml';
        $grid       = $this->getLayout()->createBlock('amacart/adminhtml_history_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/amacart');
    }
}
?>