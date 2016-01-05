<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('historyGrid');
      
      $this->setDefaultSort('history_id');
      $this->setDefaultDir('DESC');
  }

  protected function _prepareCollection()
  {
      $resource = Mage::getSingleton('core/resource');
      
      $collection = Mage::getModel('amacart/history')->getCollection();
      
      $collection->getSelect()->joinLeft( 
                array('schedule' => $resource->getTableName('amacart/schedule')), 
                'main_table.schedule_id = schedule.schedule_id',
                array('schedule.delayed_start')
      );
      
      $collection->getSelect()->join( 
                array('rule' => $resource->getTableName('amacart/rule')), 
                'main_table.rule_id = rule.rule_id',
                array('rule.name as rulename')
      );
//
//      $collection->getSelect()->joinLeft(
//                array('coupon' => $resource->getTableName('salesrule/coupon')),
//                'main_table.sales_rule_id = coupon.rule_id',
//                array('ifnull(coupon_code, coupon.code) as coupon_code', 'coupon.times_used')
//      );
//
      $collection->getSelect()->joinLeft( 
                array('canceled' => $resource->getTableName('amacart/canceled')), 
                'main_table.canceled_id = canceled.canceled_id',
                array('canceled.reason', 'canceled.created_at as last_action_at')
      );
//      
//      $collection->getSelect()->joinLeft( 
//                array('bought' => $resource->getTableName('amacart/canceled')), 
//                'main_table.canceled_id = bought.canceled_id and bought.reason = "bought"',
//                array('bought.created_at as bought_at')
//      );
//      
      $collection->addFieldToFilter('status', array('neq' => Amasty_Acart_Model_History::STATUS_PENDING));
      
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

    protected function _prepareColumns()
    {

        $hlp =  Mage::helper('amacart'); 
        $this->addColumn('history_id', array(
            'header'    => $hlp->__('ID'),
            'index'     => 'history_id',
            'width'     => 40,
        ));

        $this->addColumn('rulename', array(
            'header'    => $hlp->__('Rule'),
            'index'     => 'rulename',

            'filter_index' => 'rule.name'
        ));
        
        $this->addColumn('customer_name', array(
            'header'    => $hlp->__('Customer Name'),
            'index'     => 'customer_name',
        ));

        $this->addColumn('email', array(
            'header'    => $hlp->__('Customer Email'),
            'index'     => 'email',
        ));

        $this->addColumn('coupon_code', array(
            'header'    => $hlp->__('Coupon'),
            'index'     => 'coupon_code',
            
            'filter_index' => 'coupon_code'
        ));
        
//        $this->addColumn('times_used', array(
//            'header'    => $hlp->__('Coupon Used'),
//            'index'     => 'times_used',
//            'filter' => FALSE,
//            'width' => 20,
//            'align' => 'center',
//            'renderer'  => 'amacart/adminhtml_history_grid_renderer_used',
//        ));

        $this->addColumn('finished_at', array(
            'header'    => $hlp->__('Sent At'),
            'index'     => 'finished_at',
            'type' => 'datetime',
            'width' => '160'
        ));
        
        $this->addColumn('last_action_at', array(
            'header'    => $hlp->__('Last Action At'),
            'index'     => 'last_action_at',
            'filter_index' => 'canceled.created_at',
            'type' => 'datetime',
            'width' => '160'
        ));

        $this->addColumn('reason', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'reason',
            'renderer'  => 'amacart/adminhtml_history_grid_renderer_reason',
            'type'      => 'options',
            'options'   => $hlp->getReasonsTypes(),
        ));
        
        $this->addExportType('*/*/exportCsv', $hlp->__('CSV'));
        $this->addExportType('*/*/exportExcel', $hlp->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
}