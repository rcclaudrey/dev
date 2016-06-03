<?php

class Vikont_OEMGrid_Block_Adminhtml_Widget_Grid_Column_Renderer_Action
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{

	/**
	 * Render single action as link html
	 *
	 * @param array $action
	 * @param Varien_Object $row
	 * @return string
	 */
	protected function _toLinkHtml($action, Varien_Object $row)
	{
		$actionAttributes = new Varien_Object();

		$actionCaption = '';
		$this->_transformActionData($action, $actionCaption, $row);

		if(isset($action['confirm'])) {
			$action['onclick'] = 'if(window.confirm(\''
				   . addslashes($this->escapeHtml($action['confirm']))
				   . '\')) location=this.href';
			unset($action['confirm']);
		}

		$actionAttributes->setData($action);
		return '<a ' . $actionAttributes->serialize() . '>' . $actionCaption . '</a>';
	}

}
