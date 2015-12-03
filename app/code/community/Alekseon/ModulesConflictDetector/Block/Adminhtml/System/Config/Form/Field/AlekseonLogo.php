<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Block_Adminhtml_System_Config_Form_Field_AlekseonLogo extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $alekseonUrl = Mage::helper('alekseon_modulesConflictDetector')->getAlekseonUrl();
        $createdAtImageUrl = $alekseonUrl . '/images/created_by_alekseon.png';
        return '<div><a href="' . $alekseonUrl . '" target="_blank"><img src="' . $createdAtImageUrl . '" alt="Created by Alekseon" title="Created by Alekseon" /></a></div>';
    }
}
