<?php
class SMDesign_ColorswatchProductView_Block_Product_View_Selection_Swatch extends Mage_Core_Block_Template {
	
	protected $_attributeOptions;
	protected $imageWidth;
	protected $imageHeight;
	
	
	function __construct() {
  	$this->imageWidth = ((int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_width') > 0) ? (int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_width') : 30;
  	$this->imageHeight = ((int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_height') > 0) ? (int)Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_height') : 30;
		$this->setSelectonExsist(false);
	}
	
	
	function getSwatchWidth() {
		return $this->imageWidth;
	}
	
	function getSwatchHeight() {
		return $this->imageHeight;
	}
	
	function getColorSwatch($attributeId, $optionId) {
		$_colorswatch = $this->getColorswatchArray();
		if (isset($_colorswatch[$attributeId][$optionId])) {
			$this->_prepareSwatchCss($attributeId, $optionId, $_colorswatch[$attributeId][$optionId]);
			return $_colorswatch[$attributeId][$optionId];
		}
		return false;
	}
	
	function getAttributeOptions() {
		if (empty($this->_attributeOptions)) {
			$this->_attributeOptions = Mage::getModel('colorswatch/attribute')->setModel($this->getAttributeObject()->getProductAttribute())->getOptions()->setStoreFilter();
		}
		return $this->_attributeOptions;
	}
	
	protected function _prepareSwatchCss($_attributeId, $_optionId, $_colorswatch) {
//		$swatchCss = $this->getWrapperContent()->getSwatchesCss();
		$swatchCss = $this->getWrapperContent()->_getData('swatches_css');
		
		if ($_colorswatch->getImage()->getSwatchImage()->isImageExsist()) {
			$swatchCss .= ".colorswatch-$_attributeId-$_optionId span.swatch { background: url('{$_colorswatch->getImage()->getSwatchImage()->resize($this->getSwatchWidth(), $this->getSwatchHeight())}') no-repeat 0 0; text-indent: -9999px; }\n";
		}
		if ($_colorswatch->getImage()->getHoverImage()->isImageExsist()) {
			$swatchCss .= ".colorswatch-$_attributeId-$_optionId span.swatch:hover { background: url('{$_colorswatch->getImage()->getHoverImage()->resize($this->getSwatchWidth(), $this->getSwatchHeight())}') no-repeat 0 0; text-indent: -9999px; }\n";
		}
		if ($_colorswatch->getImage()->getActiveImage()->isImageExsist()) {
			$swatchCss .= ".colorswatch-$_attributeId-$_optionId.active span.swatch { background: url('{$_colorswatch->getImage()->getActiveImage()->resize($this->getSwatchWidth(), $this->getSwatchHeight())}') no-repeat 0 0; text-indent: -9999px; }\n";
		}
		if ($_colorswatch->getImage()->getDisabledImage()->isImageExsist()) {
			$swatchCss .= ".colorswatch-$_attributeId-$_optionId.not_allowed span { background: url('{$_colorswatch->getImage()->getDisabledImage()->resize($this->getSwatchWidth(), $this->getSwatchHeight())}') no-repeat 0 0; text-indent: -9999px; }\n";
			$swatchCss .= ".colorswatch-$_attributeId-$_optionId.not_clickable span { background: url('{$_colorswatch->getImage()->getDisabledImage()->resize($this->getSwatchWidth(), $this->getSwatchHeight())}') no-repeat 0 0; text-indent: -9999px; }\n";
		}
		$this->getWrapperContent()->setSwatchesCss($swatchCss);
	}
	
	function isSelected(SMDesign_Colorswatch_Model_Swatch_Images $swatch) {
		if ($swatch->getSortPosition() == 0 || ($swatch->getSortPosition() > 0 && is_object($this->getPreviewSwatchSelected()) && $this->getPreviewSwatchSelected()->getSortPosition()+1 == $swatch->getSortPosition()))
		if (in_array($swatch->getAttributeCode(), array_keys($_GET))) {
			if ($_GET[$swatch->getAttributeCode()] == $swatch->getOptionId()) {
				$this->setSelectonExsist($swatch);
				return true;
			}
		}
		return false;
	}
	
	function isSelectionDisabled(SMDesign_Colorswatch_Model_Swatch_Images $swatch) {
		if (is_object($this->getPreviewSwatchSelected())) {
			$allowedOption = $this->getPreviewSwatchSelected()->getAllowedOptions();
			if (isset($allowedOption[$this->getAttributeObject()->getAttributeId()])) {
				if (in_array($swatch->getOptionId(), array_keys($allowedOption[$this->getAttributeObject()->getAttributeId()]))) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	
}