<?php

define('CPET', 'catalog_product'); // catalog_product entity type
define('APPLY_TO', 'simple,grouped,configurable,bundle');

class AttributeManager
{
	/* 
	 * @var Mage_Eav_Model_Entity_Setup
	 */
	protected $_setup = null;
	
	/*
	 * @var array
	 */
	protected $_existingAttributes = array();


	public function AttributeManager()
	{
		$this->_setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
	}



	/*
	 * Adds several groups to attribute set at once; then adds attributes to that groups
	 *
	 * @param string $setName Attribute set name
	 * @param array $groups Attribute groups to be added having format:
	 * array(
			'group1' => array(
				'attr1',
	 *			...
	 *		),
	 *		...
	 * )
	 *
	 * @see Mage_Eav_Model_Entity_Setup::addAttributeGroup
	 * @see Mage_Eav_Model_Entity_Setup::addAttributeToGroup
	 */
	public function addAttributeGroupToSet($setName, $groups)
	{
		foreach($groups as $groupName => $groupAttrs) {
			$this->_setup->addAttributeGroup(CPET, $setName, $groupName);

			foreach($groupAttrs as $attrCode) {
				$this->_setup->addAttributeToGroup(CPET, $setName, $groupName, $attrCode);
			}
		}

		return $this;
	}



	/*
	 * Creates attributes, several at once
	 *
	 * @param array $attrs Attributes having format as:
	 * code => label, type, input, default, source, required, visible_on_front
	 *
	 * @see Mage_Eav_Model_Entity_Setup::addAttribute
	*/
	public function createAttributes($attrs)
	{
		foreach($attrs as $attrCode => $attrData) {

			if($this->_setup->getAttributeId(CPET, $attrCode)) {
				$this->_existingAttributes[$attrCode] = true;
			}

			$data = array(
					'group'				=> null,
					'label'				=> $attrData[0],
					'type'				=> $attrData[1],
					'input'				=> $attrData[2],
					'default'			=> $attrData[3],
					'source'			=> $attrData[4],
					'required'			=> $attrData[5],
					'visible_on_front'	=> $attrData[6],
					'global'			=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
					'apply_to'			=> APPLY_TO,
					'is_configurable'	=> 1,
					'is_visible'		=> 1,
					'user_defined'		=> 1,
				);

	//		$this->_setup->removeAttribute(CPET, $attrCode);
			$this->_setup->addAttribute(CPET, $attrCode, $data);

			if(isset($attrData[7])) {
				addAttributeOptions($this->_setup, $attrCode, $attrData[7]);
			}
		}

		return $this;
	}



	/*
	 * Adds several options to attributes at once; wrapper for Mage_Eav_Model_Entity_Setup::addAttributeOption
	 *
	 * @param string $attributeCode Attribute code
	 * @param array $options Option values to be added
	 *
	 * @see Mage_Eav_Model_Entity_Setup::addAttributeOption
	 */
	public function addAttributeOptions($attributeCode, $options)
	{
		if(isset($this->_existingAttributes[$attributeCode])) {
			return;
		}

		$option = array(
			'attribute_id' => $this->_setup->getAttributeId(CPET, $attributeCode),
			'value' => array(array())
		);

		foreach($options as $value) {
			$option['value'][0][0] = $value;
			$this->_setup->addAttributeOption($option);
		}

		return $this;
	}



	/*
	 * Creates an attribute set.
	 * A wrapper for Mage_Catalog_Model_Resource_Eav_Mysql4_Setup::addAttributeSet
	 * 
	 * @parram string $entityTypeName
	 * @param string $attributeSetName
	 * 
	 * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Setup
	 */
	public function addAttributeSet($entityTypeName, $attributeSetName)
	{
		$this->_setup->addAttributeSet($entityTypeName, $attributeSetName);

		return $this;
	}

}