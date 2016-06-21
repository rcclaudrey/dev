<?php

/**
 * Extended text grid column filter
 */
class Vikont_Pulliver_Block_Adminhtml_Catalog_Product_Grid_Column_Filter_Partnumber
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
	protected $_filter = null;



	public function getValue($column = null)
	{
		if(!$this->_filter) {
			$grid = $this->getColumn()->getGrid();
			$filter = $grid->getParam($grid->getVarNameFilter(), null);
			$this->_filter = $grid->helper('adminhtml')->prepareFilterString($filter);
		}

		$columnIndex = $column ? $column : $this->getColumn()->getIndex();

		return isset($this->_filter[$columnIndex]) ? $this->_filter[$columnIndex] : false;
	}



    public function getHtml()
    {
		$partNumber = htmlspecialchars($this->getValue('part_number'));

		$html = <<<HTML
<div class="pulliver-grid-sku">
	<input type="text" name="{$this->_getHtmlName()}" id="{$this->_getHtmlId()}" value="{$this->getEscapedValue()}" class="input-text no-changes"/>
	<span class="part-number">{$this->__('Part Number')}</span>
	<input type="text" name="part_number" id="productGrid_product_filter_part_number" value="$partNumber" class="input-text no-changes" title="{$this->__("Part number must be complete.\nPartial match search will not work!\n\nIn the grid rows, distributor codes in red show for which of them given Part Number exists")}"/>
</div>
HTML;

		return $html;
    }


}
