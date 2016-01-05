<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
//      $this->setId('queueAcartGrid');
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
      
//      $collection->getSelect()->joinLeft( 
//                array('canceled' => $resource->getTableName('amacart/canceled')), 
//                'main_table.canceled_id = canceled.canceled_id',
//                array('canceled.reason', 'canceled.created_at as bought_at')
//      );
      
//      $collection->getSelect()->joinLeft(
//                array('coupon' => $resource->getTableName('salesrule/coupon')),
//                'main_table.sales_rule_id = coupon.rule_id',
//                array('coupon.code as coupon_code')
//      );
      
      
      $collection->addFieldToFilter('status', array('eq' => Amasty_Acart_Model_History::STATUS_PENDING));

//      echo $collection->getSelect();
//      exit;
              
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
            'filter_index' => 'rulename',
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
        
        
        $this->addColumn('delayed_start', array(
            'header'    => $hlp->__('Delay'),
            'index'     => 'delayed_start',
            'filter' => FALSE,
            'renderer'  => 'amacart/adminhtml_queue_grid_renderer_delay',
        ));
        
        $this->addColumn('coupon_code', array(
            'header'    => $hlp->__('Coupon'),
            'index'     => 'coupon_code',
            
            'filter_index' => 'coupon_code'
        ));

        $this->addColumn('scheduled_at', array(
            'header'    => $hlp->__('Scheduled At'),
            'index'     => 'scheduled_at',
            'type' => 'datetime',
            'width' => '160'
        ));

//        $this->addColumn('status', array(
//            'header'    => $hlp->__('Status'),
//            'index'     => 'status',
//            'width'     => 180,
//        ));

//        $this->addColumn('reason', array(
//            'header'    => $hlp->__('Reason'),
//            'index'     => 'reason',
//            'renderer'  => 'amacart/adminhtml_queue_grid_renderer_reason',
//            'width'     => 200,
//        ));
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('amacart')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('amacart')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
//                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));
        
        return parent::_prepareColumns();
    }
        
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('queue_id');
        $this->getMassactionBlock()->setFormFieldName('queue');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amacart')->__('Cancel'),
             'url'      => $this->getUrl('*/*/massCancel'),
             'confirm'  => Mage::helper('amacart')->__('Are you sure?')
        ));
        
        return $this; 
    }
    
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }
    
}