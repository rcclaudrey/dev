<?php

class Vikont_Wholesale_Model_Price
{
	/**
     * Store calculated catalog rules prices for products
     * Prices collected per website, customer group, date and product
     *
     * @var array
     */
    protected $_dealerPrices = array();



	public function calculateFinalPrice($product)
	{
		$costPlus = Vikont_Wholesale_Helper_Data::getCustomerDealerCostPercent();

		if(!$costPlus) {
			return 0;
		}

		$finalPrice = (float)$product->getCost() * (100 + $costPlus) / 100;

		return $finalPrice;
	}



	public function processFinalPrice($observer)
	{
		$product = $observer->getEvent()->getProduct();
		$customPrice = $this->calculateFinalPrice($product);
		if (	$customPrice
			&&	$customPrice < $product->getData('final_price')
		) {
//			$product->setFinalPrice($customPrice);
			$product->setIsSuperMode(true);
			$product->setData('price', $customPrice);
			$product->setData('special_price', $customPrice);
			$product->setData('final_price', $customPrice);
		}
	}



	public function prepareCatalogProductCollectionPrices(Varien_Event_Observer $observer)
	{
		// @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
		$collection = $observer->getEvent()->getCollection();
		$productIds = array();
		$costPlus = (int)(100 * Vikont_Wholesale_Helper_Data::getCustomerDealerCostPercent());

		foreach ($collection as $product) {
			$key = implode('|', array($costPlus, $product->getId()));
			if (!isset($this->_dealerPrices[$key])) {
				$productIds[] = $product->getId();
			}
		}

		if ($productIds) {
			$dealerPrices = array();

			foreach ($productIds as $productId) {
				$key = implode('|', array($costPlus, $productId));
				$this->_dealerPrices[$key] = isset($dealerPrices[$productId]) ? $dealerPrices[$productId] : false;
			}
		}
	}



	/**
	 * Calculate price using dealer price for configurable product
	 * @param Varien_Event_Observer $observer
	 * @return Mage_CatalogRule_Model_Observer
	 */
	public function catalogProductTypeConfigurablePrice(Varien_Event_Observer $observer)
	{
		$product = $observer->getEvent()->getProduct();
		if(	$product instanceof Mage_Catalog_Model_Product
		&&	$product->getConfigurablePrice() !== null
		) {
			$customPrice = $this->calculateFinalPrice($product);

			if (	$customPrice
				&&	$customPrice < $product->getConfigurablePrice()
			) {
				$product->setConfigurablePrice($customPrice);
			}
		}
	}



	/**
	 * Apply dealer price for specific product for specific customer
	 * @param   Varien_Event_Observer $observer
	 * @return  Mage_CatalogRule_Model_Observer
	 */
	public function applyAllRulesOnProduct($observer)
	{/**
		$product = $observer->getEvent()->getProduct();
		if ($product->getIsMassupdate()) {
			return;
		}
		Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($product);
/**/
	}

}