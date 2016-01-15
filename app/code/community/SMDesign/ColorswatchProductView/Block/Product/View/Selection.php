<?php
class SMDesign_ColorswatchProductView_Block_Product_View_Selection extends Mage_Catalog_Block_Product_View_Type_Configurable {

	private $_colorSwatch;
	private $_attributes;
	private $_colorSwatchArray;
	private $_isEnabled = true;
        
	function __construct() {
                $this->_isEnabled = Mage::getStoreConfig('smdesign_colorswatch/general/enabled_colorswatch');
		if (Mage::getModel('colorswatch/swatch_images')->getCollection()->getSize() == 0) {
			Mage::app()->getStore()->setConfig('smdesign_colorswatch/general/enabled_colorswatch', 0);
		}
	}
	
        protected function _prepareLayout() {
            if ($this->getColorSwatch()->getColorSwatchCollection() == null || (Mage::getStoreConfig('smdesign_colorswatch/general/enabled_colorswatch') && !Mage::registry('product')->getUseSmdColorswatch())) { 
                Mage::app()->getStore()->setConfig('smdesign_colorswatch/general/enabled_colorswatch', 0);
            }
            if ((Mage::getStoreConfig('smdesign_colorswatch/general/enabled_colorswatch') && !Mage::getStoreConfig('smdesign_colorswatch/general/show_options_configurable_block'))) {
                $this->getLayout()->getBlock('product.info.options.configurable')->setTemplate('');
            }

            return parent::_prepareLayout();

        }
	
	protected function _toHtml() {
            if ($this->_isEnabled && $this->getColorSwatch()->getColorSwatchCollection() == null) {
                return $this->getColorSwatch()->getData('swatch_error');
            }
            return ((Mage::getStoreConfig('smdesign_colorswatch/general/enabled_colorswatch'))  ? parent::_toHtml() : '');
	}
    
	public function getColorSwatch() {
            if (empty($this->_colorSwatch)) {
                $this->_colorSwatch = Mage::getModel('colorswatch/product_swatch')->setProduct($this->getProduct());
            }
            return $this->_colorSwatch;
	}

  public function getProduct() {
      $product = $this->_getData('product');
      if (!$product) {
          $product = Mage::registry('product');
      }
      return $product;
  }
  
  public function getAttributes() {
  	if (empty($this->_attributes)) {
			$this->_attributes = $this->getColorSwatch()->getAttributes();
  	}
  	return $this->_attributes;
  }
  
  public function getColorSwatchArray() {
  	if (empty($this->_colorSwatchArray)) {
			$this->_colorSwatchArray = $this->getColorSwatch()->getColorSwatchArray();
  	}
  	return $this->_colorSwatchArray;
  }
  
  
  public function getSwatchesHtml($_attribute) {
  	$html = '';
  	if ($this->getColorSwatch()->getColorSwatchCollection() != null) {
			$block = $this->getLayout()->createBlock('colorswatchproductview/product_view_selection_swatch', 'colorswatch-prduct-view-attribute-row-' . $_attribute->getId())
				->setTemplate('colorswatch/product/view/selection/swatch.phtml')
				->setAttributeObject($_attribute)
				->setColorswatch($this->getColorSwatch()->getColorSwatchCollection())
				->setColorswatchArray($this->getColorSwatchArray())
				->setWrapperContent($this)
				->setAllowProducts($this->getColorSwatch()->getAllowProducts())
				->setPreviewSwatchSelected($this->getLastSwatchHtmlSelectionExsist());
				
			$html = $block->toHtml();
			$this->setLastSwatchHtmlSelectionExsist($block->getSelectonExsist());
  	}
		return $html;
  	
  }
  
  public function getJsonConfig() {
  	return $this->getColorSwatch()->getJsonConfig();
  }

  
  public function getSwatchesCss() {
    echo "<style type=\"text/css\">\n";
    echo $this->_getData('swatches_css');
    echo "</style>\n";
  }
  
}