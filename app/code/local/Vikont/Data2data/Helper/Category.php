<?php

class Vikont_Data2data_Helper_Category extends Mage_Core_Helper_Abstract
{
	protected static $_entityTypeId = null;
	protected static $_attributes = null;



	public static function getEntityTypeId()
	{
		if(!self::$_entityTypeId) {
			self::$_entityTypeId = Vikont_Data2data_Helper_Db::getEntityTypeId(Mage_Catalog_Model_Category::ENTITY);
		}
		return self::$_entityTypeId;
	}



	public function getAttributeList()
	{
		if(!self::$_attributes) {
			$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

			$attrs = Vikont_Data2data_Helper_Db::getTableValues('eav/attribute', array('attribute_id', 'attribute_code'), array(
					'entity_type_id='.self::getEntityTypeId(),
				));

			foreach($attrs as $attr) {
				$data = $setup->getAttribute(self::getEntityTypeId(), $attr['attribute_id']);

				self::$_attributes[$attr['attribute_code']] = array(
					'backend' => $data['backend_model'],
					'type' => $data['backend_type'],
					'table' => $data['backend_table'],
					'frontend' => $data['frontend_model'],
					'input' => $data['frontend_input'],
					'label' => $data['frontend_label'],
					'frontend_class' => $data['frontend_class'],
					'source' => $data['source_model'],
					'required' => $data['is_required'],
					'user_defined' => $data['is_user_defined'],
					'default' => $data['default_value'],
					'unique' => $data['is_unique'],
					'note' => $data['note'],
					'global' => $data['is_global'],
				);
			}
		}
		return self::$_attributes;
	}



	public function getCategoryIds()
	{
		return Vikont_Data2data_Helper_Db::getTableValues('catalog/category', 'entity_id');
	}


	public function getCategory($categoryId)
	{
		return Mage::getModel('catalog/category')->load($categoryId);
	}
}