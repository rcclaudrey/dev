<?php

class Vikont_ARIOEM_Block_Adminhtml_System_Config_Form_Field_Heading
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

		$html = sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4>%s</td></tr>',
            $element->getHtmlId(),
			$element->getHtmlId(),
			$element->getLabel(),
			isset($origData['note']) ? '<div style="margin-top:10px">'.$origData['note'].'</div>' : ''
        );

		if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }

		return $html;
	}

}
