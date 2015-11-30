<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
class Amasty_Rma_Model_Request extends Mage_Core_Model_Abstract
{
    const EXCEPTION_INVALID_EMAIL_OR_ORDER = 1;
    const EXCEPTION_INVALID_PERMISSIONS = 2;
    
    public function _construct()
    {
        $this->_init('amrma/request');
    }
    
    function authenticate($username, $order){
        $salesOrder = Mage::getModel('sales/order')->load($order, 'increment_id');
        $hlr = Mage::helper('amrma');

        if (!$salesOrder->getId() ||
                strtolower($salesOrder->getCustomerEmail()) != strtolower($username)) {
            throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Invalid email or order.'),
                self::EXCEPTION_INVALID_EMAIL_OR_ORDER
            );
            
        }  else if (!$hlr->canCreateRma($salesOrder->getId())){
//            throw Mage::exception('Mage_Core', Mage::helper('amrma')->getFailReason($salesOrder->getId()),
//                self::EXCEPTION_INVALID_PERMISSIONS
//            );
        }
        else if($salesOrder->getId()) {

            if (!Mage::helper("amrma")->getIsGuestEnabled() && !$salesOrder->getCustomerId()){
                throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Guest RMA not allowed.'),
                    self::EXCEPTION_INVALID_PERMISSIONS
                );
            }
        } 

        return $salesOrder;
    }
    
    public function saveRmaItems(){
        $hlr = Mage::helper('amrma');
        $allItems = array();
        
        if ($hlr->getIsEnablePerItem()){
            $allItems = $this->getItems();
        } else {
            $request = Mage::app()->getRequest();
            
            $orders = $hlr->getOrderItems($this->getOrderId());
            
            foreach($orders as $order){
                if ($order->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE){
                    $allItems[] = array(
                        'order_item_id' => $order->getId(),
                        'qty_requested' => $order->getQtyOrdered(),
                        'resolution' => $request->getParam('resolution'),
                        'condition' => $request->getParam('condition'),
                        'reason' => $request->getParam('reason'),
                    );
                }
                
            }
        }
        
        $this->_saveItems($allItems);
    }
    
    protected function _saveItems($allItems){
        foreach ($allItems as $parentId => $data_item) {
        
            if (is_numeric($parentId)){
                $items = array($parentId => $data_item['qty_requested']);

                if (array_key_exists('items', $data_item)){
                    $items = $data_item['items'];
                }
                
                foreach($items as $item_id => $qty){

                    $orderItem = Mage::getModel("sales/order_item")->load($item_id);
                    if ($orderItem->getId()){
                        $item = Mage::getModel("amrma/item");
                        $item->setData(array(
                            'request_id' => $this->getId(),
                            'sales_item_id' => $orderItem->getId(),
                            'product_id' => $orderItem->getProductId(),
                            'sku' => $orderItem->getSku(),
                            'name' => $orderItem->getName(),
                            'qty' => $orderItem->getQtyOrdered() < $qty ? $orderItem->getQtyOrdered() : $qty,
                            'reason' => $data_item['reason'],
                            'condition' => $data_item['condition'],
                            'resolution' => $data_item['resolution']
                        ));
                        $item->save();
                    }
                }
            }
        }
    }
        
    public function getStatus()
    {
        if (!$this->hasStatus()) {
            $status = $this->_getResource()->getStatus($this->getStatusId());
            $this->setStatus($status);
        }

        return $this->_getData("status");
    }
    
    public function getCustomer()
    {
        if ($this->getCustomerId() && !$this->hasCustomer()) {
            $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            $this->setCustomer($customer);
        }
        return $this->_getData("customer");
        
    }
    
    function updateItemsQty(){
        $items = $this->getItems();
        if (is_array($items)){
            foreach($items as $id => $data){
                $item = Mage::getModel('amrma/item')->load($id);
                if ($item->getId() && $item->getQty() != $data['qty']){
                    $item->setQty($data['qty']);
                    $item->save();
                }
            }
        }
    }
    
    function submitComment($isAdmin, $tmpFile = array()){
        
        $ret = null;
        $commentText = trim(strip_tags($this->getComment()));
        
        if (empty($commentText)){
            $hlr = Mage::helper("amrma");
            $commentText = $hlr->__("Status has been changed to %s", $this->getStatusLabel());
        } else {
            $this->setAllowSentNotification(true);
        }
        
        $submitComment = !empty($commentText) || $tmpFile['error'] != 0;
        
        if ($submitComment){
            $maxSize = Mage::helper('amrma')->getMaxAttachmentSize();
            
            if ($maxSize && $maxSize < $tmpFile['size'] / 1024 / 1024){
                throw Mage::exception('Mage_Core', Mage::helper('amrma')->__('Max attachment size %s Mb', $maxSize));
            }

            $comment = Mage::getModel('amrma/comment');
            $comment->setData(array(
               'request_id' => $this->getId(),
               'comment_value' => $commentText,
               'is_admin' => $isAdmin,
               'unique_key' => uniqid($this->getId()),
               'created' => Mage::getSingleton('core/date')->gmtDate()
            ));
        
            $comment->save();
        
            if ($tmpFile['error'] == 0){
                $name = $tmpFile['name'];
                $key = uniqid($comment->getId()).".".pathinfo($name, PATHINFO_EXTENSION);;

                $file = Mage::getModel('amrma/file');

                if (is_writable($file->getUploadPath(""))){

                    $file->setData(array(
                        'comment_id' => $comment->getId(),
                        'file' => $key,
                        'name' => $name
                    ));

                    $file->save();

                    $comment->addFile($file);

                    $data = file_get_contents($tmpFile['tmp_name']);
                    file_put_contents($file->getUploadPath($key), $data);
                } else {
                    Mage::throwException('Unable upload file');
                }

            }
            
            $ret = $comment;
        }
        
        return $ret;
    }
    
    public function getStatusLabel(){
        $store = Mage::app()->getStore();
        $status = Mage::getModel('amrma/status')->load($this->getStatusId());
        return $status->getStoreLabel($store->getId());
    }
    
    public function sendNotificaition($comment, $statusChanged = TRUE){  
        $ret = FALSE;
        $status = $this->getStatus();
        $storeId = $this->getStoreId();
        $commentValue = $comment->getCommentValue();
        
        
        $templateId = $statusChanged ? 
            $status->getStoreTemplate($storeId) : 
            "amrma_comment";
        
        if ($statusChanged && !$templateId && !empty($commentValue)){
            $templateId = "amrma_comment";
        }
        
        if ($templateId && ($statusChanged || $this->getAllowSentNotification())){
            $sender = array('name' => Mage::getStoreConfig('amrma/email/name'),
                    'email' => Mage::getStoreConfig('amrma/email/email'));

            $recepientEmail = $this->getEmail();     

            $vars = array(
                'request' => $this,
                'store' => Mage::app()->getStore($storeId),
                'comment' => $comment,
            );

            $translate  = Mage::getSingleton('core/translate');

            Mage::getModel('core/email_template')
                ->sendTransactional($templateId, $sender, $recepientEmail, "", $vars, $storeId);

            $translate->setTranslateInline(true); 
            $ret = TRUE;
        }
        return $ret;
    }
    
    protected function _sendAdminNotification($templateId, $comment){
        $ret = FALSE;
        $storeId = $this->getStoreId();
        
        if ($templateId && Mage::helper("amrma")->getIsNotifyAdmin()){
            $sender = array('name' => $this->getCustomerName(),
                    'email' => $this->getEmail());
            
            $vars = array(
                'request' => $this,
                'store' => Mage::app()->getStore($storeId),
                'comment' => $comment,
            );

            $translate  = Mage::getSingleton('core/translate');

            Mage::getModel('core/email_template')
                ->sendTransactional($templateId, $sender, Mage::getStoreConfig('amrma/email/email'), "", $vars, $storeId);

            $translate->setTranslateInline(true); 
        
            $ret = TRUE;
        }
        return $ret;
    }
    
    public function sendNotificaition2admin($comment){
        $templateId = "amrma_admin";
        return $this->_sendAdminNotification($templateId, $comment);
    }
    
    public function sendNotificaitionRmaCreated($comment){
        $templateId = "amrma_created";
        return $this->_sendAdminNotification($templateId, $comment);
    }
    
    function getCustomerName(){
        return $this->getCustomerFirstname() . " " . $this->getCustomerLastname();
    }
    
    function getShippedLabel(){
        $hlr = Mage::helper("amrma");
        $options = $hlr->getBooleanOptions();
        return isset($options[$this->getIsShipped()]) ? $options[$this->getIsShipped()] : "";
    }
    
    function allowPrintLabel(){
        return $this->getAllowCreateLabel() && !$this->getIsShipped();
    }
}
?>