<?php
class SMDesign_Colorswatch_Helper_Image extends Mage_Core_Helper_Abstract {

	private $_colorswatch;
	private $_imageType;
	
	
	public function setColorSwatch($swatch) {
		$this->_colorswatch = $swatch;
		return $this;
	}
	public function getColorswatch() {
		return $this->_colorswatch;
	}
	public function setCurrentType($imageType) {
		$this->_imageType = $imageType;
		return $this;
	}
	public function getCurrentImageType() {
		return $this->_imageType;
	}
	
	public function resizeImage($imageName, $width=NULL, $height=NULL, $imagePath='colorswatch/image') {
	    $imagePath = str_replace("/", DS, $imagePath);
	    $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $this->getCurrentImageType() . DS . $this->getColorSwatch()->getAttributeId() . DS . $this->getColorSwatch()->getOptionId() . DS . $imageName;

	    if($width == NULL && $height == NULL) {
	        $width = 100;
	        $height = 100;
	    }
	    $resizePath = $width . 'x' . $height;
	    $resizePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $this->getCurrentImageType() . DS . $this->getColorSwatch()->getAttributeId() . DS . $this->getColorSwatch()->getOptionId() . DS . $resizePath . DS . $imageName;
	 
	    if (file_exists($imagePathFull) && is_file($imagePathFull) && !file_exists($resizePathFull)) {

	        $imageObj = new Varien_Image($imagePathFull);
	        $imageObj->constrainOnly(TRUE);
	        $imageObj->keepAspectRatio(TRUE);
	        $imageObj->keepFrame(FALSE);
	        $imageObj->keepTransparency(TRUE);

	        $imageObj->resize($width, $height);
	        $imageObj->save($resizePathFull);

	    }
	 
	    $imagePath=str_replace(DS, "/", $imagePath);
	    return Mage::getBaseUrl("media") . $imagePath . "/"  . $this->getCurrentImageType() . '/' . $this->getColorSwatch()->getAttributeId() . '/' . $this->getColorSwatch()->getOptionId() . '/' . $resizePath . "/" . $imageName;
	}

}