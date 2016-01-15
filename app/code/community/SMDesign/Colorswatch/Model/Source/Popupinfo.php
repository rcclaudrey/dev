<?php
class SMDesign_Colorswatch_Model_Source_Popupinfo {
	
	public static function toOptionArray() {
		$list = array(
					"1" => Mage::Helper('colorswatch')->__('Show swatch description'),
					"2" => Mage::Helper('colorswatch')->__('Use "Info text" from config.')
					);

		return ($list);
	}
}