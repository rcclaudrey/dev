<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author      Shay Acrich (email: me@shayacrich.com)
 *
 */
if (Mage::helper('conversionpro')->isConversionproEnabled()) {
    class Celebros_Conversionpro_Catalogsearch_ResultController extends Mage_Core_Controller_Front_Action
    {
        public function indexAction()
        {
            $this->loadLayout();
            $this->renderLayout();
        }
        
    }
} else {
    require_once 'Mage/CatalogSearch/controllers/ResultController.php';
    class Celebros_Conversionpro_Catalogsearch_ResultController extends Mage_CatalogSearch_ResultController {}
}