<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Block_Adminhtml_Explanations extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('alekseon/modulesConflictDetector/explanations.phtml');
    }
    
    public function getConflictColor()
    {
        return Alekseon_ModulesConflictDetector_Model_Rewrites::CONFLICT_COLOR;
    }
    
    public function getNoConflictColor()
    {
        return Alekseon_ModulesConflictDetector_Model_Rewrites::NO_CONFLICT_COLOR;
    }

    public function getConflictResolvedColor()
    {
        return Alekseon_ModulesConflictDetector_Model_Rewrites::RESOLVED_CONFLICT_COLOR;
    }    
}