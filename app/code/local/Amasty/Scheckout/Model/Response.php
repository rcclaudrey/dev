<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

class Amasty_Scheckout_Model_Response extends Mage_Core_Controller_Response_Http
{
    protected $_body = array();
    protected $_errors = array();
    protected $_redirect = NULL;
    
    function setBody($part, $name = null){
        $res = Mage::helper('core')->jsonDecode($part);
        $this->_body[] = $res;
        if (isset($res['error']) && $res['error'] == 1){
            $m = '';
            
            if (isset($res['message']))
                $m = $res['message'];
            else if (isset($res['error_messages']))
                $m = array($res['error_messages']);
            
            
            if (!is_array($m))
                $m = array($m);
            
            $this->_errors = array_merge($this->_errors, $m);
        }
        
        if (isset($res['redirect'])){
            $this->_redirect = $res['redirect'];
        }
    }
    
    function getBody($spec = false){
        return $this->_body;
    }
    
    function getErrors(){
        return $this->_errors;
    }
    
    function setError($e){
        return $this->_errors[] = $e;
    }
    
    function getErrorsCount(){
        return count($this->_errors);
    }
    
    function getRedirect(){
        return $this->_redirect;
    }
}
?>