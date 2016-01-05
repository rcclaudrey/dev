<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */
class Amasty_Acart_Block_Adminhtml_Queue_Grid_Renderer_Delay extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $hlp =  Mage::helper('amacart'); 
        
        $schedule  = Mage::getModel('amacart/schedule');
        
        $schedule->setDelayedStart($row->getDelayedStart());
        
        $days = $schedule->getDays();
        $hours = $schedule->getHours();
        $minutes = $schedule->getMinutes();
        
        
        $ret = array();
        
        if ($days > 0){
            $ret[] = $days . ' ' . $hlp->__('Days');
        }
        
        if ($hours > 0){
            $ret[] = $hours . ' ' . $hlp->__('Hours');
        }
        
        if ($minutes > 0){
            $ret[] = $minutes . ' ' . $hlp->__('Minutes');
        }
        
        
                
        return implode(' ', $ret) . '';

    }
}