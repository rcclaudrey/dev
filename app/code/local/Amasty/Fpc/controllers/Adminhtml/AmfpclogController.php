<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


class Amasty_Fpc_Adminhtml_AmfpclogController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/amfpccrawler/amfpc_log');

        $this->_title($this->__('Reports'))
            ->_title($this->__('Amasty FPC Crawler'))
            ->_title($this->__('Pages to Index'))
        ;

        $this->_addContent(
            $this->getLayout()->createBlock('amfpc/adminhtml_log')
        );

        $this->renderLayout();
    }

    public function clearAction()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        $connection->truncate(
            $resource->getTableName('amfpc/url')
        );

        Mage::getSingleton('adminhtml/session')->addSuccess(
            $this->__('Statistics table has been truncated')
        );

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            'report/amfpccrawler/amfpc_log'
        );
    }
}
