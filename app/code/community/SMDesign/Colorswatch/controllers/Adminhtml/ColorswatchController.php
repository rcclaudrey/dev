<?php
class SMDesign_Colorswatch_Adminhtml_ColorswatchController extends Mage_Adminhtml_Controller_Action {
	

	private function _initSMDesignColorSwatch() {
		$this->loadLayout()
			->_setActiveMenu('catalog')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('SMDesign ColorSwatch'), Mage::helper('adminhtml')->__('SMDesign ColorSwatch'));		
		
	}
	
	public function indexAction() {
		$this->_initSMDesignColorSwatch();
	
		$this->renderLayout();
		
	}
	
	public function attributesAction() {
		$this->_initSMDesignColorSwatch();

		$this->renderLayout();
		
	}
	
	public function imagesAction() {
		$this->_initSMDesignColorSwatch();

		
		$this->renderLayout();
		
	}
	
	
/***********************	POST ACTIONS	***********************/

	public function	saveColorSwatchesPostAction() {

	  $updated = array();
	  $updatedErrorsAttributeNames = array();
		$this->_initSMDesignColorSwatch();
		if ($data = $this->getRequest()->getPost()) {
				$data['visible_attributes'] = isset($data['visible_attributes']) ? $data['visible_attributes'] : array();
				$countOptions = 0;
				foreach (Mage::getResourceModel('catalog/product_attribute_collection')->setFrontendInputTypeFilter('select') as $model) {
					$updateErrors = 0;
					if (in_array($model->getAttributeId(), $data['visible_attributes'])) {
						try {
							if ($model->getIsConfigurable() && $model->getIsUserDefined()) {
								foreach (Mage::getResourceModel('eav/entity_attribute_option_collection')
																									->setPositionOrder('asc')
																									->setAttributeFilter($model->getId()) as $option) {
									$countOptions++;
	
	                Mage::getModel('colorswatch/attribute_settings')->setConfig($model->getId(), 'allow_attribute_to_change_main_image', (isset($data['allow_attribute_to_change_main_image'][$model->getId()]) ? $data['allow_attribute_to_change_main_image'][$model->getId()] : 0), $model->getAttributeCode());
	                Mage::getModel('colorswatch/attribute_settings')->setConfig($model->getId(), 'enable_colorswatch', (isset($data['enable_colorswatch'][$model->getId()]) ? $data['enable_colorswatch'][$model->getId()] : 0), $model->getAttributeCode());
																
									$swatchImage = Mage::getModel('colorswatch/swatch_images')->getSwatchImage($option->getAttributeId(), $option->getOptionId());				
									if ($swatchImage->getId() == 0) {
										$swatchImage = Mage::getModel('colorswatch/swatch_images');
										$swatchImage->setData('created_time', date('Y-m-d H:i:s', time())); // save UTC time
									}
									
									$swatchImage->setData('attribute_code', $model->getAttributeCode());
									$swatchImage->setData('attribute_id', $option->getAttributeId());
									$swatchImage->setData('option_id', $option->getOptionId());
									$swatchImage->setData('swatch_description', isset($_POST['option_description'][$option->getAttributeId()][$option->getOptionId()]) ? trim($_POST['option_description'][$option->getAttributeId()][$option->getOptionId()]) : '');
			
									if (isset($data['delete_image_base'][$option->getAttributeId()][$option->getOptionId()])) {
									  $swatchImage->deleteImage('image_base', $option->getAttributeId(), $option->getOptionId());
									}
									if (!$swatchImage->saveImage('image_base', $option->getAttributeId(), $option->getOptionId())) {
										$updateErrors = 1;
									}
									
									if (isset($data['delete_image_active'][$option->getAttributeId()][$option->getOptionId()])) {
									  $swatchImage->deleteImage('image_active', $option->getAttributeId(), $option->getOptionId());
									}
									if (!$swatchImage->saveImage('image_active', $option->getAttributeId(), $option->getOptionId())) {
										$updateErrors = 1;
									}
									
									if (isset($data['delete_image_hover'][$option->getAttributeId()][$option->getOptionId()])) {
									  $swatchImage->deleteImage('image_hover', $option->getAttributeId(), $option->getOptionId());
									}
									if (!$swatchImage->saveImage('image_hover', $option->getAttributeId(), $option->getOptionId())) {
										$updateErrors = 1;
									}
									
									if (isset($data['delete_image_disabled'][$option->getAttributeId()][$option->getOptionId()])) {
									  $swatchImage->deleteImage('image_disabled', $option->getAttributeId(), $option->getOptionId());
									}
									if (!$swatchImage->saveImage('image_disabled', $option->getAttributeId(), $option->getOptionId())) {
										$updateErrors = 1;
									}
									
									$swatchImage->setData('update_time', date('Y-m-d H:i:s', time()));
									$swatchImage->save();				
																										
								}
								
								if ($updateErrors == 1) {
									$updatedErrorsAttributeNames[] = $model->getData('frontend_label');	
								} else {
									$updated[] = $model->getData('frontend_label');
								}
								
							}
							
						} catch (Exception $e) {
							
						}
					}
				}

				if ((int)ini_get('max_file_uploads') > 0) {
					if (count($_FILES) >= ini_get('max_file_uploads')) {
						Mage::getSingleton('adminhtml/session')->addNotice("In your php.ini maximum number of allowed files for upload is " . ini_get('max_file_uploads') . " ( by max_file_uploads setting ), you have submitted " . count($_FILES) . " attribute options so you are trying to upload more images then it is allowed. In this case server will allow you to upload only first " . ini_get('max_file_uploads') . " images, other images will be ignored by server. Please note that this with this php setting, the server actually counts empty input fields as uploads too.Try changing the max_file_uploads in your php.ini file.");	
						$updated = array();
					}
				}
				
				if (count($updated) > 0) {
					Mage::getSingleton('adminhtml/session')->addSuccess(join(', ', $updated) . " is updated success.");	
				}
				if (count($updatedErrorsAttributeNames) > 0) {
					Mage::getSingleton('adminhtml/session')->addError(join(', ', $updatedErrorsAttributeNames) . " is not updated.");	
				}

		}

		$this->_redirect('*/*/attributes');
		
	}
	
}