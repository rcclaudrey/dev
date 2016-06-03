<?php

class Vikont_ARIOEM_Block_Adminhtml_System_Config_Form_Field_Note
	extends Mage_Adminhtml_Block_Abstract
	implements Varien_Data_Form_Element_Renderer_Interface
{
	/**
	 * Render element html
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$origData = $element->getOriginalData();
		return sprintf('<tr class="system-fieldset-sub-head"><td colspan="5"><div style="%s">%s</div></td></tr>',
			isset($origData['style']) ? $origData['style'] : '', isset($origData['note']) ? $origData['note'] : ''
		);
	}
}
