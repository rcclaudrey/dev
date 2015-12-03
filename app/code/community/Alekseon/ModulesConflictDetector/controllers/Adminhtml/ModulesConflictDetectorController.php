<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Adminhtml_ModulesConflictDetectorController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }    
    
        $this->loadLayout();
        $this->_setActiveMenu('system/modules_conflict_detector');  
        $this->_addContent(
            $this->getLayout()->createBlock('alekseon_modulesConflictDetector/adminhtml_rewrites', 'modules_rewrites')
        );
        $this->_addContent(
            $this->getLayout()->createBlock('alekseon_modulesConflictDetector/adminhtml_explanations', 'explanations')
        );    
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('alekseon_modulesConflictDetector/adminhtml_rewrites_grid')->toHtml());
    }
}