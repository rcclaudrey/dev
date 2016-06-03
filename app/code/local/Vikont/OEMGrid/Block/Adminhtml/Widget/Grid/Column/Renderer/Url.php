<?php

class Vikont_OEMGrid_Block_Adminhtml_Widget_Grid_Column_Renderer_Url
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	protected $_compiledAttributes = null;


	protected function _getValue(Varien_Object $row)
	{
		$url = $row->getData($this->getColumn()->getIndex());
		$caption = $this->getColumn()->getCaption();

		if (null === $this->_compiledAttributes) {
			$htmlAttributes = array();

			foreach($this->getColumn()->getHtmlAttributes() as $key => $value) {
				$htmlAttributes[] = $key . '="' . htmlspecialchars($value) . '"';
			}

			$this->_compiledAttributes = (implode(' ', $htmlAttributes));
		}

		return $url
			?	'<a href="' . $url . '" ' . $this->_compiledAttributes . '>'
				. htmlspecialchars($caption ? $caption : $url) . '</a>'
			:	$this->getColumn()->getEmptyText();
	}

}
