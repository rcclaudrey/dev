<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */ 
class Amasty_Shiprules_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('ruleGrid');
      $this->setDefaultSort('pos');
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('amshiprules/rule')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
    $hlp =  Mage::helper('amshiprules'); 
    $this->addColumn('rule_id', array(
      'header'    => $hlp->__('ID'),
      'align'     => 'right',
      'width'     => '50px',
      'index'     => 'rule_id',
    ));
    
    $this->addColumn('pos', array(
        'header'    => $hlp->__('Priority'),
        'index'     => 'pos',
    ));    
    
    $this->addColumn('is_active', array(
        'header'    => Mage::helper('salesrule')->__('Status'),
        'align'     => 'left',
        'width'     => '80px',
        'index'     => 'is_active',
        'type'      => 'options',
        'options'   => $hlp->getStatuses(),
    ));    
    
    $this->addColumn('name', array(
        'header'    => $hlp->__('Name'),
        'index'     => 'name',
    ));
    
    $this->addColumn('methods', array(
        'header'    => $hlp->__('Methods'),
        'index'     => 'methods',
        'renderer'  => 'amshiprules/adminhtml_rule_grid_renderer_methods', 
    ));
    
    $this->addColumn('calc', array(
        'header'    => $hlp->__('Calculation'),
        'index'     => 'calc',
        'type'      => 'options',
        'options'   => $hlp->getCalculations(),        
    ));
    
    $this->addColumn('rate_base', array(
        'header'    => $hlp->__('Base Rate'),
        'index'     => 'rate_base',
    ));
    
    $this->addColumn('rate_fixed', array(
        'header'    => $hlp->__('Fixed Rate'),
        'index'     => 'rate_fixed',
    ));

    $this->addColumn('rate_percent', array(
        'header'    => $hlp->__('Percentage'),
        'index'     => 'rate_percent',
    ));
    
    $this->addColumn('handling', array(
        'header'    => $hlp->__('Handling'),
        'index'     => 'handling',
    ));    
    
    $this->addColumn('action',array(
        'header'    => Mage::helper('catalog')->__('Action'), 
        'width'     => '50px',
        'type'      => 'action',
        'actions'   => array(
            array(
                'caption' => Mage::helper('catalog')->__('Duplicate'),
                'url'     => array('base' => 'amshiprules/adminhtml_rule/duplicate'),
                'field'   => 'rule_id'
            )
        ),
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'rule_id',
        'is_system' => true,
    ));     

    return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  protected function _prepareMassaction()
  {
    $this->setMassactionIdField('rule_id');
    $this->getMassactionBlock()->setFormFieldName('rules');
    
    $actions = array(
        'massActivate'   => 'Activate',
        'massInactivate' => 'Inactivate',
        'massDelete'     => 'Delete',
    );
    foreach ($actions as $code => $label){
        $this->getMassactionBlock()->addItem($code, array(
             'label'    => Mage::helper('amshiprules')->__($label),
             'url'      => $this->getUrl('*/*/' . $code),
             'confirm'  => ($code == 'massDelete' ? Mage::helper('amshiprules')->__('Are you sure?') : null),
        ));        
    }
    return $this; 
  }
}