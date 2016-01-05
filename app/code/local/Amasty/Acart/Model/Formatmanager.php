<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

    class Amasty_Acart_Model_Formatmanager extends Varien_Object
    {
        const TYPE_CUSTOMER = 'customer';
        const TYPE_CUSTOMER_GROUP = 'customer_group';
        const TYPE_CUSTOMER_LOG = 'customer_log';
        const TYPE_HISTORY = 'history';
        const TYPE_ORDER = 'order';
        const TYPE_QUOTE = 'quote';
        const TYPE_COUPON = 'coupon';

        protected $_config;
        protected $_coreHelper;
        
        function init($config){
            $this->_config = $config;
            $this->_coreHelper = Mage::helper("core");
            return $this;
        }
        
        public function formatDate($type, $field, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false)
        {
            $ret = null;
            $object = isset($this->_config[$type]) ? $this->_config[$type] : null;
            
            if ($object){
                $ret = $this->_coreHelper->formatDate($object->getData($field), $format, $showTime);
            }
            
            return $ret;
        }
        
        public function formatTime($type, $field, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showDate = false)
        {
            
            $ret = null;
            $object = isset($this->_config[$type]) ? $this->_config[$type] : null;
            if ($object){
                $ret = $this->_coreHelper->formatTime($object->getData($field), $format, $showDate);
            }
            return $ret;   
        }
        
        
        public function formatCurrency($type, $field)
        {
            $ret = null;
            $object = isset($this->_config[$type]) ? $this->_config[$type] : null;
            if ($object){
                $ret = $this->_coreHelper->formatCurrency($object->getData($field), false);
            }
            return $ret;
        }

        public function formatPrice($type, $field)
        {
            $ret = null;
            $object = isset($this->_config[$type]) ? $this->_config[$type] : null;
            if ($object){
                $ret = $this->_coreHelper->formatPrice($object->getData($field), false);
            }
            return $ret;
        }
        
        public function getOrderPaymentMethodLabel(){
            $ret = null;
            $object = isset($this->_config[self::TYPE_ORDER]) ? $this->_config[self::TYPE_ORDER] : null;
            if ($object){
                $ret = $object->getPayment()->getMethodInstance()->getTitle();
            }
            return $ret;
        }
    }
?>