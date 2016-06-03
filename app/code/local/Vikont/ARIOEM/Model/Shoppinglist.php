<?php

class Vikont_ARIOEM_Model_Shoppinglist
{
	protected static $_quote = null;

	public function Vikont_ARIOEM_Model_Shoppinglist()
	{
		self::$_quote = Mage::getSingleton('checkout/session')->getQuote();
	}



	/*
	 * Removes an item from a shopping list
	 * @param array|string Item array or item ID to be removed
	 * @return $this
	 */
	public function update($qtys)
	{
		return $this;
	}



	public function clear()
	{
		return $this;
	}

}
