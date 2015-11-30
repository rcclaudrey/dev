<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Notification_Toolbar {

    protected $_notAssigned = 0;

    function __construct() {


        $date_config = Mage::app()->getLocale()->date(strtotime(Mage::getStoreConfig("advancedinventory/setting/order_notification_from_date")), null, null, false)->toString('yyyy-MM-dd');
        $order = Mage::getModel("sales/order")->getCollection();
        $statuses = explode(',', Mage::getStoreConfig("advancedinventory/setting/disallow_assignation_status"));
        $where=null;
        foreach ($statuses as $status) {
            $where="AND status<>'$status'";
        }
        $order->getselect()->from('', 'COUNT(DISTINCT entity_id) AS count')->where("FIND_IN_SET(0,assignation_warehouse) AND created_at >='" . $date_config . " 00:00:00'  " . $where);

        if ($order->getFirstItem())
            $this->_notAssigned = $order->getFirstItem()->getCount();



        if (version_compare(Mage::getVersion(), '1.4.0', '<')) {
            if ($this->_toHtml(null) != null)
                Mage::getSingleton("core/session")->addNotice($this->_toHtml(null));
        }
    }

    function _toHtml($className = 'notification-global') {
        $html = null;

        $filter = base64_encode("assignation_warehouse=0");


        if ($this->_notAssigned > 0 && Mage::getStoreConfig("advancedinventory/setting/order_notification")) {
            ($this->_notAssigned > 1) ? $s = "s" : $s = "";
            ($this->_notAssigned > 1) ? $be = "are" : $be = "is";
            $html.= "<div class='$className'>
                <span class='f-right'>
                   
                </span>"
                    . $this->_notAssigned . " " . Mage::helper('advancedinventory')->__("of your order$s require your attention.")
                    . " <a href='" . $this->getUrl('adminhtml/sales_order/index', array("filter" => $filter)) . "'>" . Mage::helper('advancedinventory')->__('Manage your orders') . "</a>
                  
            </div>";
        }
        return $html;
    }

}
