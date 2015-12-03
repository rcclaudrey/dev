<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author		Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Block_Js_Head extends Mage_Adminhtml_Block_Template
{
    
    protected $_jsurlz = array();
   
    public function addJs($jsurl)
    {
        $this->_jsurlz[] = $jsurl;
    }
    
    public function getJsUrlz()
    {
        return $this->_jsurlz;
    }
    
}