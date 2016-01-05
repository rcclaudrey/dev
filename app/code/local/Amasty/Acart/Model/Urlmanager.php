<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

    class Amasty_Acart_Model_Urlmanager extends Varien_Object
    {
        protected $_history;
        protected $_rule;
        
        protected $_googleAnalyticsParams = array(
            'utm_source', 'utm_medium', 'utm_term', 
            'utm_content', 'utm_campaign'
        );
        
        function init($history){
            $this->_history = $history;
            $this->_rule = Mage::getModel("amacart/rule")->load($history->getRuleId());
            return $this;
        }
        
        protected function getParams($params = array()){
            $params["id"] = $this->_history->getId();
            $params["key"] = $this->_history->getPublicKey();
            
            foreach($this->_googleAnalyticsParams as $param){
                $val = $this->_rule->getData($param);
                if (!empty($val)){
                    $params[$param] = $val;
                }
            }
            return $params;
        }
        public function unsubscribe(){
            return $this->get(Mage::getUrl('amacartfront/main/unsubscribe',
                $this->getParams()
            ));
        }
        
        public function get($target){
            return Mage::getUrl('amacartfront/main/url', $this->getParams(array(
                'target' => base64_encode($target),
            )));
        }
        
        public function mageUrl($url){
            return $this->get(Mage::getUrl($url));
        }
        
        public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, $showTime = false){
            return Mage::helper("core")->formatDate($date, $format, $showTime);
        }
    }
?>