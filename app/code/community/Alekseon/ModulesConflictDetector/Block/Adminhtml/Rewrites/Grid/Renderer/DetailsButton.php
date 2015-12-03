<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Block_Adminhtml_Rewrites_Grid_Renderer_DetailsButton extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $url = $this->getGridUrl($row->getClass());
        $label = Mage::helper('alekseon_modulesConflictDetector')->__('Show Details');
        return '<a href="' . $url . '">' . $label . '</a>';
    }
    
    public function getGridUrl($class = null)
    {
        return $this->getUrl('*/*/details', array('class' => $class));
    }    
}