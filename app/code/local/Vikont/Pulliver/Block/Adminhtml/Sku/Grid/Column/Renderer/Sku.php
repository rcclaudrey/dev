<?php

class Vikont_Pulliver_Block_Adminhtml_Sku_Grid_Column_Renderer_Sku
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
	{
		$sku = $this->_getValue($row);
		$productId = Mage::getResourceModel('catalog/product')->getIdBySku($sku);

		return $productId
			?	'<span class="pulliver-grid-sku-found">' . $sku . '</span> <a href="'
				. Mage::getModel('adminhtml/url')->getUrl('adminhtml/catalog_product/edit', array('id' => $productId))
				. '" target="_blank" title="View product in new tab">ID: ' . $productId . '</a>'
			:	'<span class="pulliver-grid-sku-notfound" title="Product not found">' . $sku . '</span>';
	}



	/**
	 * Render column for export
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function renderExport(Varien_Object $row)
	{
		return $this->_getValue($row);
	}

}
