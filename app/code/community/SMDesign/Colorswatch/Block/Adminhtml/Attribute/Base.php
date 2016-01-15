<?php
class SMDesign_Colorswatch_Block_Adminhtml_Attribute_Base extends SMDesign_Colorswatch_Block_Adminhtml_Abstract {

    var $attributePerPage = 2;
    var $currentPage = 1;
    var $attributeCounter = 0;  
    var $searchTream = '';  
  
    function _prepareLayout() {
    	
    	if (Mage::getModel('colorswatch/swatch_images')->getCollection()->getSize() == 0) {
    		Mage::getSingleton('adminhtml/session')->addNotice("Please save the ColorSwatch Attribute Settings to enable color swatches on your products");
    	}
    	
    	$this->setHeaderText(Mage::helper('colorswatch')->__('SMDesign ColorSwatch Attribute Settings'));
    	
    	$this->attributePerPage = (int)Mage::app()->getStore()->getConfig('smdesign_colorswatch/general/adminhtml_attribute_per_page');
    	$this->attributePerPage = ($this->attributePerPage > 0 ? $this->attributePerPage : 20);
    	
    	$this->currentPage = max($this->getRequest()->getParam('page_id', 1), 1);
    	$this->searchTream = strtolower($this->getRequest()->getParam('search', ''));
    	
			$block = $this->getLayout()->createBlock('adminhtml/widget_accordion', 'colorswatch_attribute_accordion');
			

			
			$resetToFirstPage = true;
			$isDead = false;
			while ($resetToFirstPage) {
  			foreach (Mage::getResourceModel('catalog/product_attribute_collection')->setFrontendInputTypeFilter('select') as $model) {

  				if ($model->getIsConfigurable() && $model->getIsUserDefined() && ($this->searchTream == '' || ($this->searchTream != '' && (preg_match("@{$this->searchTream}@", strtolower($model->getFrontendLabel())) || preg_match("@{$this->searchTream}@", strtolower($model->getAttributeCode())))    ))) {

  				  if ( ($this->attributePerPage*$this->currentPage - $this->attributePerPage)  <= $this->attributeCounter && $this->attributeCounter < $this->attributePerPage*$this->currentPage) {
  				     
    				  $block->addItem($model->getAttributeCode(), array(
                  'title'     => $model->getData('frontend_label') . " ({$model->getAttributeCode()})" ,
                  'content'   => $this->getLayout()->createBlock('colorswatch/adminhtml_attribute_accordion_content', 'colorswatch_attribute_accordion')
                  	->setModel($model)
                  	->toHtml(),
                  'open'      => false
              ));
  				  }
            $this->attributeCounter++;
  				}
  				
  			}
  			
  			if ($isDead || $this->attributePerPage*$this->currentPage <= $this->attributeCounter || $this->attributeCounter == 0 || $this->currentPage == 1) {
  			  $resetToFirstPage = false;
  			} else {
  			  $this->attributeCounter = 0;
  			  $this->currentPage = 1;
  			  $isDead = true;
  			}
			}
			
			
			$this->setChild('colorswatch_attribute_accordion', $block);


			$this->addButton('save', array(
			        'label'     => 'save',
			        'onclick'   => "
colorswatchForm = $('colorswatch-attribute-form');
for (k in colorswatchForm) {
  if (colorswatchForm[k]) {
    var currentNodeName = colorswatchForm[k].nodeName;
    if (currentNodeName && currentNodeName.toLowerCase() == 'input') {
      if (colorswatchForm[k].type.toLowerCase() == 'file') {
        if (colorswatchForm[k].value == '') {
          
		      for (childIndex=0; childIndex < colorswatchForm[k].parentNode.childNodes.length; childIndex++) {
		      	if (colorswatchForm[k].parentNode.childNodes[childIndex].className == 'fakefile' ) {
        			colorswatchForm[k].parentNode.style.position = 'relative';
        			colorswatchForm[k].parentNode.childNodes[childIndex].style.position = 'static';
		      	}
		      }
		      colorswatchForm[k].parentNode.removeChild(colorswatchForm[k]);
        }
      }
    }
  }
}
$('colorswatch-attribute-form').submit();
			        ",
			        'class'     => 'save',
			    ));
			
    	return parent::_prepareLayout();
    }
		
		public function getFormAction() {
			return $this->getUrl('*/*/saveColorSwatchesPost', array('_current' => true));
		}
}