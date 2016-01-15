<?php
class SMDesign_Colorswatch_Model_Swatch_Images extends Mage_Core_Model_Abstract {
	
		protected $imageBase;
	
    protected function _construct() {
        $this->_init('colorswatch/swatch_images');
    }
    
    function getSwatchImage($_attributeId, $_optionId) {
    	$collection = $this->getCollection()
	    	->addFieldToFilter('attribute_id', $_attributeId)
	    	->addFieldToFilter('option_id', $_optionId);

	    if ($collection->getSize() > 0) {
	    	foreach ($collection as $swatchImage) {
	    		return $this->load($swatchImage->getData('entity_id'));
	    	}
	    }
    	return $this;
    }
    
    function getSwatchImageId($_attributeId, $_optionId) {
    	return $this->getSelect()->from(array('main_table' => $this->getMainTable()))
    		->where("main_table.attribute_id=$_attributeId AND main_table.attribute_id=?", $_optionId)->getId();
    }
    
    
    function saveImage($imageType, $_attributeId, $_optionId) {
    	
    	$imagekey = "$imageType-$_attributeId-$_optionId";
    	if (isset($_FILES[$imagekey]['error']) && $_FILES[$imagekey]['error'] == 0) {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader($imagekey);
					
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir('media') . DS . 'colorswatch' . DS . 'image' . DS . $imageType . DS . $_attributeId . DS . $_optionId . DS;

					if (!is_writable(Mage::getBaseDir('media'))) {
						throw new Exception('Magento Color Swatch extension is not able to write images in your ' . Mage::getBaseDir('media') . " directory.");
					}
					if ($uploader->save($path, $_FILES[$imagekey]['name'] )) {
						
		      	/* start clear cache */
		      	foreach (glob($path . '*') as $cachePath) {
		      		if (is_dir($cachePath) && is_file($cachePath . DS . $this->getData($imageType))) {
		      			unlink($cachePath . DS . $this->getData($imageType));
		      		}
		      	}
		      	/* end clear cache */
		      	
						$this->setData($imageType, $_FILES[$imagekey]['name']);
					} else {
						throw new Exception("Varien_File_Uploader class not upload image correct, please check your GD setting");
					}
					
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError(" {$e->getMessage()}");
					return false;
				}
    	}
    	
    	return $this;
    }
    
    function deleteImage($imageType, $_attributeId, $_optionId) {
      
      try {
      	$imagePath = Mage::getBaseDir('media') . DS . 'colorswatch' . DS . 'image' . DS . $imageType . DS . $_attributeId . DS . $_optionId . DS;
      	/* start clear cache */
      	foreach (glob($imagePath . '*') as $cachePath) {
      		if (is_dir($cachePath) && is_file($cachePath . DS . $this->getData($imageType))) {
      			@unlink($cachePath . $this->getData($imageType));
      		}
      	}
      	/* end clear cache */
        @unlink($imagePath . $this->getData($imageType));
        $this->setData($imageType, '');
      } catch (Exception $e) {
				
			}
      
    	return $this;
    }
    
    function getImage() {
    	return Mage::getModel('colorswatch/swatch_image')
    			->setBaseImage($this->getData('image_base'))
    			->setActiveImage($this->getData('image_active'))
    			->setHoverImage($this->getData('image_hover'))
    			->setDisabledImage($this->getData('image_disabled'))
    			->setAttributeId($this->getData('attribute_id'))
    			->setOptionId($this->getData('option_id'))
    			->setDescription($this->getData('swatch_description'))
    			->setColorSwatch($this)
    			;
    }
}
?>