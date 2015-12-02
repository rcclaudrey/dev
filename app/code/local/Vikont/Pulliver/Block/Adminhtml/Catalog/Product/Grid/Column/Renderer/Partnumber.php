<?php

/**
 * Extended text grid column filter
 */
class Vikont_Pulliver_Block_Adminhtml_Catalog_Product_Grid_Column_Renderer_Partnumber
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	protected $_filteredValue = null;



	public function getFilteredValue($paramName)
	{
		if(null === $this->_filteredValue) {
			$this->_filteredValue = $this->getColumn()->getFilter()->getValue($paramName);
		}

		return $this->_filteredValue;
	}



    /**
     * Render the grid cell value
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
		$html = $row->getData($this->getColumn()->getIndex());

		if($partNumber = $this->getFilteredValue('part_number')) {
			$distribs = array();
			foreach(Vikont_Pulliver_Helper_Sku::getDistributorFieldNames() as $dCode => $dField) {
				$dNumbers = explode(',', $row->getData('gd_'.$dField));
				foreach($dNumbers as $dNumber) {
					if($partNumber == $dNumber) {
						$distribs[] = '<span class="pulliver-distribs-part-number">'.htmlspecialchars($dCode).'</span>';
						break;
					}
				}
			}
			$html .= '<div class="pulliver-distribs">'.implode(',', $distribs).'</div>';
		}

		return $html;

    }

}
