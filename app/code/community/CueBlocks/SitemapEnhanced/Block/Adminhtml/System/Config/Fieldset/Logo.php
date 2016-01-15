<?php

/**
 * Description of Logo
 * @package   CueBlocks_SitemapEnhanced
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */

/**
 * Renderer for CueBlocks banner in System Configuration
 * 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_System_Config_Fieldset_Logo
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'sitemapenhanced/system/config/fieldset/logo.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }
}

?>
