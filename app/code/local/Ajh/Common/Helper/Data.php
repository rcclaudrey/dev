<?php
class Ajh_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isNew($product)
    {
        $news_from_date = $product->getNewsFromDate();
        $news_to_date = $product->getNewsToDate();
        $current_date = Mage::getModel('core/date')->date('Y-m-d');
        $new = ($current_date >= $news_from_date && $current_date <= $news_to_date) || ($news_from_date == '' && $current_date <= $news_to_date && $news_to_date != '') || ($news_from_date != '' && $current_date >= $news_from_date && $news_to_date == '');
        if ($new == 1)
        {
            return true;
        }
        return false;
    }

    public function isSale($product)
    {
        $current_date = Mage::getModel('core/date')->date('Y-m-d');
        $special_price = $product->getSpecialPrice();
        // Get the Special Price FROM date
        $special_from_date = $product->getSpecialFromDate();
        // Get the Special Price TO date
        $special_to_date = $product->getSpecialToDate();
        $today =  time();

        if ($special_price) {
            if($today >= strtotime( $special_from_date) && $today <= strtotime($special_to_date) || $today >= strtotime( $special_from_date) && is_null($special_to_date)) {
                return true;
            }
        }

        return false;
    }



	public function addInStockSortingToProductCollection($collection)
	{
		$selectOrderPart = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
		$stockOrderingFound = false;

		foreach($selectOrderPart as $orderNode) {
			if($orderNode[0] == 'stock_status.stock_status') {
				$stockOrderingFound = true;
				break;
			}
		}

		if(!$stockOrderingFound) {
			$collection->getSelect()->reset(Zend_Db_Select::ORDER);

			$fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);
			if(!isset($fromPart['stock_status'])) {
				Mage::getResourceModel('cataloginventory/stock_status')
					->addStockStatusToSelect($collection->getSelect(), Mage::app()->getWebsite());
			}

			$collection->getSelect()->order('stock_status.stock_status DESC');

			foreach($selectOrderPart as $orderNode) {
				$collection->getSelect()->order(implode(' ', $orderNode));
			}
		}
	}

}
