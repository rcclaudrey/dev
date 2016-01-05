<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Adminhtml_AmconfController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction() 
	{
        $page  = (int) $this->getRequest()->getParam('page');
        $attr  = (int) $this->getRequest()->getParam('attribute_id');

        $html = Mage::app()->getLayout()->createBlock('amconf/adminhtml_catalog_product_attribute_edit_tab_images')
            ->setPage($page)
            ->setAttribute($attr)
            ->toHtml();

        $this->getResponse()->setBody($html);
	}

    protected function _isAllowed()
    {
        return true;
    }
}