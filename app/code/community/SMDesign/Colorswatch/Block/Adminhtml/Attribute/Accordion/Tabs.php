<?php
class SMDesign_Colorswatch_Block_Adminhtml_Attribute_Accordion_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
  
  protected $_model;
  
    public function __construct() {
        parent::__construct();
        $this->setTemplate('colorswatch/attribute/accordion/tabs.phtml');
    }

    function setModel($model) {
      $this->_model = $model;
      $this->setId('grid_tab_' . $model->getAttributeId());
      $this->setDestElementId('grid_tab_content_' . $model->getAttributeId());
     	
      return $this;
    }
    
    function getModel() {
    	return $this->_model;
    }
    
    protected function _prepareLayout() {
      $random = rand(1000, 9999);
      
				$_currentModel = Mage::getModel('colorswatch/session')->getModel();
				$_optionCollection = Mage::getModel('colorswatch/attribute')->setModel($_currentModel)->getOptions()->setStoreFilter();
      
      
        $this->addTab('tab_grid_active_image_' . $random, array(
            'label'     => $this->__('Active Image'),
            'content'   => $this->getLayout()->createBlock('colorswatch/adminhtml_attribute_accordion_tabs_content', 'attribute_accordion_tabs_content_active_image')
            								->setOptionCollection($_optionCollection)
            								->setModel($_currentModel)
            								->setTemplate('colorswatch/attribute/accordion/tabs/image-active.phtml')->toHtml(),
            'active'    => true
        ));

        $this->addTab('tab_grid_hover_image_' . $random, array(
            'label'     => $this->__('Hover Image'),
            'content'   => $this->getLayout()->createBlock('colorswatch/adminhtml_attribute_accordion_tabs_content', 'attribute_accordion_tabs_content_hover_image')
            								->setOptionCollection($_optionCollection)
            								->setModel($_currentModel)
            								->setTemplate('colorswatch/attribute/accordion/tabs/image-hover.phtml')->toHtml(),
        ));
        
        $this->addTab('tab_grid_disabled_image_' . $random, array(
            'label'     => $this->__('Disabled Image'),
            'content'   => $this->getLayout()->createBlock('colorswatch/adminhtml_attribute_accordion_tabs_content', 'attribute_accordion_tabs_content_disabled_image')
            								->setOptionCollection($_optionCollection)
            								->setModel($_currentModel)
            								->setTemplate('colorswatch/attribute/accordion/tabs/image-disabled.phtml')->toHtml(),
        ));
        return parent::_prepareLayout();
    }
}