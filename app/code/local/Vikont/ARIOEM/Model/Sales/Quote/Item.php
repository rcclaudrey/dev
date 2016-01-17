<?php

class Vikont_ARIOEM_Model_Sales_Quote_Item extends Mage_Sales_Model_Quote_Item
{
	/**
	 * Setup product for quote item
	 *
	 * @param   Mage_Catalog_Model_Product $product
	 * @return  Mage_Sales_Model_Quote_Item
	 */
	public function setProduct($product)
	{
		if ($this->getQuote()) {
			$product->setStoreId($this->getQuote()->getStoreId());
			$product->setCustomerGroupId($this->getQuote()->getCustomerGroupId());
		}

		$this->setData('product', $product)
			->setProductId($product->getId())
			->setProductType($product->getTypeId())
			->setWeight($this->getProduct()->getWeight())
			->setTaxClassId($product->getTaxClassId())
			->setBaseCost($product->getCost())
			->setIsRecurring($product->getIsRecurring())
			->setIsQtyDecimal(false);

		if( !Vikont_ARIOEM_Helper_Cart::isOemProduct($product)
		||	!$this->hasData('sku')
		) {
			$this
				->setSku($this->getProduct()->getSku())
				->setName($product->getName());
		}

		Mage::dispatchEvent('sales_quote_item_set_product', array(
			'product' => $product,
			'quote_item' => $this
		));

		return $this;
	}
}