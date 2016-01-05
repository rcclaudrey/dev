<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('ruleGrid');
      $this->setDefaultSort('rule_id');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amacart/rule')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
   
    $hlp =  Mage::helper('amacart'); 
    $this->addColumn('rule_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'rule_id',
    ));
//    
    $this->addColumn('is_active', array(
        'header'    => Mage::helper('salesrule')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'is_active',
        'type'      => 'options',
        'options'   => $hlp->getStatuses()
    ));    
//    
    $this->addColumn('name', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'name',
    ));
//    
//    $this->addColumn('methods', array(
//        'header'    => $hlp->__('Methods'),
//        'index'     => 'methods',
//    	'filter' 	=> false,
//    	'sortable'	=> false,        
//        'renderer'  => 'ampayrestriction/adminhtml_rule_grid_renderer_methods', 
//    ));
//    
//    $this->addColumn('cust_groups', array(
//        'header'    => $hlp->__('Customer Groups'),
//        'index'     => 'cust_groups',
//        'renderer'  => 'ampayrestriction/adminhtml_rule_grid_renderer_groups', 
//    	'filter' 	=> false,
//    	'sortable'	=> false,        
//    ));
//    
//    $this->addColumn('stores', array(
//        'header'    => $hlp->__('Store Views'),
//        'index'     => 'stores',
//        'renderer'  => 'ampayrestriction/adminhtml_rule_grid_renderer_stores', 
//    	'filter' 	=> false,
//    	'sortable'	=> false,        
//    ));    
//    
//    $this->addColumn('action',array(
//            'header'    => Mage::helper('catalog')->__('Action'), 
//            'width'     => '50px',
//            'type'      => 'action',
//            'actions'   => array(
//                array(
//                    'caption' => Mage::helper('catalog')->__('Duplicate'),
//                    'url'     => array('base' => 'ampayrestriction/adminhtml_rule/duplicate'),
//                    'field'   => 'rule_id'
//                )
//            ),
//            'filter'    => false,
//            'sortable'  => false,
//            'index'     => 'rule_id',
//            'is_system' => true,
//        ));     

    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
}