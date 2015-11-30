<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Block_Adminhtml_Sales_Order_View_Tab_Rma
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('order_amrma');
        $this->setUseAjax(true);
    }

    protected function _getCollectionClass()
    {
        return 'amrma/request_collection';
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect('*')
            ->setOrder('created', 'desc')
            ->addStatusLabel();
        ;
        
        $collection->addFilter('order_id', $this->getOrder()->getEntityId());
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    public function getOrder(){
        return Mage::registry('current_order');
    }

    protected function _prepareColumns()
    {
        $hlr = Mage::helper('amrma');
        
        $this->addColumn('request_id', array(
            'header'    => $hlr->__('ID'),
            'index'     => 'request_id',
            'width' => 50
        ));
        
        $this->addColumn('increment_id', array(
            'header'    => $hlr->__('Order #'),
            'index'     => 'increment_id'
        ));
        
        $this->addColumn('created', array(
            'header'    => $hlr->__('Date'),
            'index'     => 'created',
            'type'      => 'date',
            'width' => 100
        ));
        
        $this->addColumn('label', array(
            'header'    => $hlr->__('Order Status'),
            'index'     => 'label',
            'width' => 100
        ));
        
        $this->addColumn('view',
            array(
                'header'    => $hlr->__('View'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $hlr->__('View'),
                        'url'     => array(
                            'base'=>'amrma/adminhtml_request/edit',
                            'params'=>array()
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
    
    public function getRowUrl($item)
    {
        return $this->getUrl('adminhtml/amrma_request/edit', array('id' => $item->getId()));
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/amrma_sales_order/rma', array('_current' => true));
    }
    
    public function getTabLabel()
    {
        return Mage::helper('amrma')->__('RMA');
    }

    public function getTabTitle()
    {
        return Mage::helper('amrma')->__('RMA');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
    
    protected function _prepareLayout()
    {
        $hlr = Mage::helper('amrma');
        
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $hlr->__('Create RMA'),
                    'onclick'   => 'setLocation(\'' . Mage::getUrl("amrmafront/guest/loginPost", array(
                        "username" => $this->getOrder()->getCustomerEmail(),
                        "order" => $this->getOrder()->getIncrementId(),
                    )) .'\')',
                    'class'   => 'add'
                ))
        );
        
        return parent::_prepareLayout();
    }
    
    public function getMainButtonsHtml()
    {
        $hlr = Mage::helper('amrma');
        
        $html = parent::getMainButtonsHtml();
        
        if ($hlr->canCreateRma($this->getOrder()->getEntityId())){
            $html .= $this->getChildHtml('add_button');
        }
        return $html;
    }
}
?>