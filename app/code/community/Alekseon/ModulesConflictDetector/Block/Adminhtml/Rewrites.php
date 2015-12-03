<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Block_Adminhtml_Rewrites extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'alekseon_modulesConflictDetector';
        $this->_controller = 'adminhtml_rewrites';
        $this->_headerText = Mage::helper('alekseon_modulesConflictDetector')->__('Modules Conflict Detector');
        parent::__construct();
        $this->removeButton('add');
    }

}