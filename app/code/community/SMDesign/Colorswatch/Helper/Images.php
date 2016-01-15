<?php
class SMDesign_Colorswatch_Helper_Images extends Mage_Core_Helper_Abstract {

	protected $swatch = null;
	protected $swatchBaseImageName = null;

	protected $baseImageURL = null;
	protected $baseImagePath = null;

	protected $imageUrl = null;
	protected $imagePath = null;
	protected $type = null;

	protected $placeholderImage = '/images/catalog/product/placeholder/image.jpg';

	protected $constrainOnly = true;
	protected $keepAspectRatio = false;
	protected $keepFrame = false;
	protected $keepTransparency = true;

	const BASE_IMAGE		= 'image_base';
	const ACTIVE_IMAGE		= 'image_active';
	const HOVER_IMAGE		= 'image_hover';
	const DISABLED_IMAGE	= 'image_disabled';
	
	public function init($swatch) {
		
		$this->swatch = $swatch;
		
		$defaultImages = array(
			'image_base'	=> $swatch->getImageBase(),
			'image_active'	=> $swatch->getImageActive(),
			'image_hover'	=> $swatch->getImageHover(),
			'image_disabled'=> $swatch->getImageDisabled(),
		);

		$this->prepareImage();

		return $this;
	}
	
	function getSwatch() {
		return $this->swatch;
	}
	
	function prepareImage($type = self::BASE_IMAGE) {
		$this->type = $type;
		$this->baseImagePath = Mage::getBaseDir('media') . DS . 'colorswatch' . DS . 'image' . DS . $this->type . DS . $this->getSwatch()->getAttributeId() . DS . $this->getSwatch()->getOptionId() . DS . $this->getSwatch()->getData($type);
		$this->baseImageURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "colorswatch/image/{$this->type}/{$this->getSwatch()->getAttributeId()}/{$this->getSwatch()->getOptionId()}/{$this->getSwatch()->getData($type)}";
		$this->imageUrl = null;
		$this->imagePath = null;
		return $this;
	}

	function getClassName() {
		return str_replace('_', '-', $this->type);
	}
	
	public function isImageExsist() {
		return (is_file($this->baseImagePath) && file_exists($this->baseImagePath) ? true : false);
	}

	public function __toString() {
		return ($this->imageUrl) ? $this->imageUrl : $this->baseImageURL;
	}

	function constrainOnly($data = true) {
		$this->constrainOnly = $data;
		return $this;
	}

	function keepAspectRatio($data = true) {
		$this->keepAspectRatio = $data;
		return $this;
	}

	function keepFrame($data = true) {
		$this->keepFrame = $data;
		return $this;
	}

	function keepTransparency($data = true) {
		$this->keepTransparency = $data;
		return $this;
	}

	public function resize($width = null, $height = null) {

		if($width == NULL && $height == NULL) {
			$width = Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_width');
			$height = Mage::getStoreConfig('smdesign_colorswatch/general/swatch_image_size_height');
		}

		$resizePath = $width . 'x' . $height;
		$resizePathFull = Mage::getBaseDir('media') . DS . 'colorswatch' . DS . 'image' . DS . $this->type . DS . $this->getSwatch()->getAttributeId() . DS . $this->getSwatch()->getOptionId() . DS .  $resizePath . DS . $this->getSwatch()->getData($this->type);

		if (!file_exists($resizePathFull)) {
			if (file_exists($this->baseImagePath) && is_file($this->baseImagePath)) {

				$imageObj = new Varien_Image($this->baseImagePath);
				$imageObj->constrainOnly($this->constrainOnly);
				$imageObj->keepAspectRatio($this->keepAspectRatio);
				$imageObj->keepFrame($this->keepFrame);
				$imageObj->keepTransparency($this->keepTransparency);

				$imageObj->resize($width, $height);
				$imageObj->save($resizePathFull);

			}
		}
		
		$this->imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "colorswatch/image/{$this->type}/{$this->getSwatch()->getAttributeId()}/{$this->getSwatch()->getOptionId()}/{$resizePath}/{$this->getSwatch()->getData($this->type)}";
		$this->imagePath = $resizePathFull;

		return $this;
	}

}