<?php

class Vikont_EVOConnector_Model_Source_Taxrules
{
	const TAXABLE = 1;
	const NON_TAXABLE = 2;
	const TAX_EXEMPT = 3;


	protected static $_taxRules = array(
		self::TAXABLE		=> 'Customer - Taxable',
		self::NON_TAXABLE	=> 'Customer - Non Taxable',
		self::TAX_EXEMPT	=> 'Customer - Tax Exempt',
	);


	public static function getTaxRules()
	{
		return self::$_taxRules;
	}


	public static function getTaxRuleById($id)
	{
		return isset(self::$_taxRules[$id])
			? self::$_taxRules[$id]
			: null;
	}


	public static function detectTaxRule($address, $customerIsWholesale)
	{
		if( (strtolower($address->getData('region')) == 'california')
		||	($address->getData('region_id') == 12)
		||	(	($postcode = (int)$address->getData('region'))
				&& $postcode >= 90001
				&& $postcode <= 96162
		)) { // welcome to California!
			$key = $customerIsWholesale
				?	self::TAX_EXEMPT
				:	self::TAXABLE;
		} else {
			$key = self::NON_TAXABLE;
		}

		return array(
			'id' => $key,
			'name' => self::$_taxRules[$key],
		);
	}

}