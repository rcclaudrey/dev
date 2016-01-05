<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Block_Adminhtml_Rule_Edit_Tab_Test extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         $this->setId('gridAbandoned');
         $this->setUseAjax(true);
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/testGrid', array('_current'=>true));
    }

    protected function _prepareCollection()
    {
        $resource = Mage::getSingleton('core/resource');
        
      /** @var $collection Mage_Reports_Model_Resource_Quote_Collection */
        $collection = Mage::getResourceModel('amacart/report_quote_collection');

        $collection->addCustomerData();

        $filter = $this->getParam($this->getVarNameFilter(), array());

        if ($filter) {
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
        }

        if (!empty($data)) {
            $collection->prepareForAbandonedReport(NULL, $data);
        } else {
            $collection->prepareForAbandonedReport(NULL);
        }
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column)
    {
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        $skip = array('subtotal', 'customer_name', 'email'/*, 'created_at', 'updated_at'*/);

        if (in_array($field, $skip)) {
            return $this;
        }

        parent::_addColumnFilterToCollection($column);
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('run', array(
            'header'    => '',//Mage::helper('amacart')->__('Run'),
            'index'     =>'customer_name',
            'sortable'  =>false,
            'filter'    => false,
            'renderer'  => 'amacart/adminhtml_rule_edit_tab_test_renderer_test',
            'align'     => 'center',
        ));
        
        $this->addColumn('customer_name', array(
            'header'    =>Mage::helper('amacart')->__('Customer Name'),
            'index'     =>'customer_name',
            'sortable'  =>false
        ));

        $this->addColumn('customer_email', array(
            'header'    =>Mage::helper('amacart')->__('Email'),
            'index'     =>'target_email',
            'filter_index' => 'ifnull(main_table.customer_email, quote2email.email)',
            'sortable'  =>false
        ));

        $this->addColumn('items_count', array(
            'header'    =>Mage::helper('amacart')->__('Number of Items'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'items_count',
            'sortable'  =>false,
            'type'      =>'number'
        ));

        $this->addColumn('items_qty', array(
            'header'    =>Mage::helper('amacart')->__('Quantity of Items'),
            'width'     =>'80px',
            'align'     =>'right',
            'index'     =>'items_qty',
            'sortable'  =>false,
            'type'      =>'number'
        ));

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('subtotal', array(
            'header'        => Mage::helper('amacart')->__('Subtotal'),
            'width'         => '80px',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'subtotal',
            'sortable'      => false,
            'renderer'      => 'adminhtml/report_grid_column_renderer_currency',
            'rate'          => $this->getRate($currencyCode),
        ));

        $this->addColumn('coupon_code', array(
            'header'    =>Mage::helper('amacart')->__('Applied Coupon'),
            'width'     =>'80px',
            'index'     =>'coupon_code',
            'sortable'  =>false
        ));

        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('amacart')->__('Created At'),
            'width'     =>'170px',
            'type'      =>'datetime',
            'index'     =>'created_at',
            'filter_index'=>'main_table.created_at',
            'sortable'  =>false
        ));

        $this->addColumn('updated_at', array(
            'header'    =>Mage::helper('amacart')->__('Updated At'),
            'width'     =>'170px',
            'type'      =>'datetime',
            'index'     =>'updated_at',
            'filter_index'=>'main_table.updated_at',
            'sortable'  =>false
        ));

        $this->addColumn('remote_ip', array(
            'header'    =>Mage::helper('amacart')->__('IP Address'),
            'width'     =>'80px',
            'index'     =>'remote_ip',
            'sortable'  =>false
        ));

        return parent::_prepareColumns();
    }
    
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        
        
        $model = $this->getModel();
        
        if ($model){
            $js = array();
            
            $js [] = "
                function runRuleTesting(btn, id, email){
                    var r=confirm('Email will be sent to ' + email + ', are you sure?!')
                    if (r==true)
                    {
                    btn.setAttribute('disabled', true);
                    new Ajax.Request('" . Mage::helper("adminhtml")->getUrl('*/*/testRule')  . " ', {
                        parameters: {
                            quote_id: id,
                            rule_id: " . $model->getId() . "
                        },
                        onSuccess: function(response) {
                            btn.removeAttribute('disabled');
                        }
                      });
                }
                }
            ";

            $html .= '<script> ' . implode('', $js) . ' </script>';
        }
        return $html;
    }
    
    public function getRowUrl($row)
    {
        return "#";
    }
}