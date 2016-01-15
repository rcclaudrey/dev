<?php
class SMDesign_Colorswatch_Model_Swatch_Image extends Mage_Core_Model_Abstract {
	
	protected $_url;
	protected $_currentImage;
	protected $_currentType = 'image_base';
	
	function __toString() {
		return $this->getImageUrl();
	}
	
	public function resize($width = null, $height = null) {
		$this->_url = Mage::helper('colorswatch/image')
										->setColorSwatch($this->getColorSwatch())
										->setCurrentType($this->_currentType)
										->resizeImage($this->getImage(), $width, $height);
		return $this;
	}
	
	protected function getImage() {
		if (!$this->isImageExsist()) {
			$this->_currentType = 'image_base';
			$this->_currentImage = $this->getBaseImage();
		}
		return $this->_currentImage;
	}

	public function getSwatchImage() {
		$this->_currentType = 'image_base';
		$this->_currentImage = $this->getColorSwatch()->getIsDisabled() ? $this->_data['disabled_image'] : $this->_data['base_image'];
		return $this;
	}
	
	public function getActiveImage() {
		$this->_currentType = 'image_active';
		$this->_currentImage = $this->_data['active_image'];
		return $this;
	}
	
	public function getHoverImage() {
		$this->_currentType = 'image_hover';
		$this->_currentImage = $this->_data['hover_image'];
		return $this;
	}
	
	public function getDisabledImage() {
		$this->_currentType = 'image_disabled';
		$this->_currentImage = $this->_data['disabled_image'];
		return $this;
	}
	
	public function getImageUrl() {
		if (empty($this->_url)) {
			$this->_url = Mage::getBaseUrl("media") . "/colorswatch/image/" . DS . $this->_currentType . DS . $this->getColorSwatch()->getAttributeId() . DS . $this->getColorSwatch()->getOptionId() . DS . $this->getImage();
		}
		return $this->_url;
	}
	
	public function isImageExsist() {
		if (is_file(Mage::getBaseDir('media') . DS  . 'colorswatch/image' . DS . $this->_currentType . DS . $this->getColorSwatch()->getAttributeId() . DS . $this->getColorSwatch()->getOptionId() . DS .  $this->_currentImage)) {
		  return true;
		}
		return false;
	}
}