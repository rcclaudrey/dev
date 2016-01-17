<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


class Amasty_Fpc_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amfpc';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = $this->__('Pages to Index');
        parent::__construct();
        $this->_removeButton('add');

        $script = "
            if (confirm('".$this->__('Are you sure?')."'))
                window.location.href='".$this->getUrl('adminhtml/amfpclog/clear')."';
        ";

        $this->addButton('clear', array(
            'label' => $this->__('Clear'),
            'onclick' => $script,
            'class' => 'delete',
        ));
    }
}