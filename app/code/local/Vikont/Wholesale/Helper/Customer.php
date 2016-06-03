<?php

class Vikont_Wholesale_Helper_Customer extends Mage_Core_Helper_Data
{

	protected $_customerFieldNames = array(
		'group_id' => 'Customer group',
		'created_at' => 'Created at',
		'updated_at' => 'Updated at',
		'is_active' => 'Active',
		'firstname' => 'First name',
		'middlename' => 'Middle name',
		'lastname' => 'Last name',
		'default_billing' => 'Default billing address',
		'default_shipping' => 'Default shipping address',
		'dealer_status' => 'Dealer status',
		'dealer_cost' => 'Dealer percent',
//		'application' => 'Dealer application',
//		'' => '',
	);



	public function explainCustomerFields($data)
	{
		$res = array();

		foreach($data as $key => $value) {
			if (!isset($this->_customerFieldNames[$key])) continue;

			switch ($key) {
				case 'is_active':
					$tValue = ((int)$value) ? 'Yes' : 'No';
					break;

				case 'default_billing':
				case 'default_shipping':
					$tValue = Mage::getModel('customer/address')->load($value)->format('text');
					break;

				case 'group_id':
					$tValue = Mage::getModel('customer/group')->load($value)->getCustomerGroupCode();
					break;

				case 'application':
					$tValue = '';
//					$tValue = Mage::getLayout()->createBlock('core/template')
//						->setTemplate('')
					break;

				case 'dealer_status':
					$tValue = Mage::getModel('wholesale/source_dealer_status')->getOptionText($value);
					break;

				case 'dealer_cost':
					$tValue = '';
					break;

				default:
					$tValue = $value;
			}

			$res[$key] = array(
				'label' => $this->_customerFieldNames[$key],
				'value' => $tValue,
			);
		}

		return $res;
	}



	public function compareCustomerData($before, $after)
	{
		$diff = array_diff_assoc($before, $after);
		unset($diff['store_id']);
		unset($diff['created_at']);
		unset($diff['updated_at']);
		unset($diff['password']);
		unset($diff['password_hash']);
		unset($diff['password_confirmation']);
		unset($diff['parent_id']);
		unset($diff['confirmation']);
//		unset($diff['']);

		return $diff;
	}


}

