<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Edit_Tab_Renderer_Button extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        //You can write html for your button here
        $html = "<script type='text/javascript'>"
                . "function apply(){"
                . "$('edit_form').action='" . $this->getUrl('advancedinventory/adminhtml_pos/apply', array("place_id" => $placeId)) . "';"
                . "$('edit_form').submit();"
                . "}"
                . "</script>";
        $html .= '<input type="button" class="save btn" onclick=\'apply()\' value="Update all multistock products with these settings"/>';
        $html.="<style>"
                . ".btn {
        background: url('/skin/adminhtml/default/default/images/btn_bg.gif') repeat-x scroll 0 100% #ffac47;
        border-color: #ed6502 #a04300 #a04300 #ed6502;
        border-style: solid;
        border-width: 1px;
        color: #fff!important;
        cursor: pointer;
        font: bold 12px arial,helvetica,sans-serif;
        padding: 1px 7px 2px;
        text-align: center !important;
        white-space: nowrap;
        margin:20px 0 20px 210px;
        text-decoration:none!important;
        display:block;
        width:300px
      }"
                . "</style>";
        if ($this->getRequest()->getParam("place_id") > 0)
            return $html;
    }

}
