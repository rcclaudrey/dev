<?php

class Vikont_Fitment_Block_Catalog_Product_Grouped_Partlist extends Mage_Catalog_Block_Product_View_Type_Grouped
{

	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('vk_fitment/catalog/product/grouped/partlist.phtml');
	}



	public function getProducts()
	{
		$result = array();

		try {
			$tmsActivityId = $this->getTmsActivityId();
			$ariActivityId = Vikont_Fitment_Helper_Data::getTmsActivity($tmsActivityId, 'ari_activity');
			$ariProductId = $this->getAriProductId();
			$fitmentId = $this->getFitmentId();

			$fitmentNotes = Mage::helper('fitment/api')
					->preventErrorReporting()
					->request('fitmentnotes',
							array(
								'productID' => $ariProductId,
								'fitmentID' => $fitmentId,
							)
						);

			if(!$fitmentNotes) {
				Mage::log(sprintf('Unable to get fitment notes from ARI API: ARI product ID = %d, ARI fitment ID = %d', $ariProductId, $fitmentId));
			}

			$skuInfo = Mage::helper('fitment/api')->request(
				'skuinfo',
				array(
					'activityID' => $ariActivityId,
					'productID' => $ariProductId
				), array(
					'fitmentID' => $fitmentId,
			));

			if(!$skuInfo) {
				throw new Exception(sprintf('Unable to get SKU info from ARI API: ARI activity ID = %d, ARI product ID = %d, ARI fitment ID = %d', $ariActivityId, $ariProductId, $fitmentId));
			}

			$fitmentItems = array();

			foreach($skuInfo as $item) {
				$sku = (string)$item['Id'];
				$fitmentItems[$sku] = $item;
			}

			foreach($fitmentNotes as $item) {
				$sku = (string)$item['SkuId'];

				if(isset($fitmentItems[$sku])) {
					$fitmentItems[$sku]['Applications'] = $item['Applications'];
					$fitmentItems[$sku]['Note'] = $item['Note'];
				}
			}

			foreach($fitmentItems as &$item) {
				if(!isset($item['Applications'])) {
					$item['Applications'] = array();
					$item['Note'] = '';
				}
			}
			unset($item);

			$collection = Mage::getResourceModel('catalog/product_collection')
					->addAttributeToSelect('name')
					->addFinalPrice()
					->addFieldToFilter('sku', array('in' => array_keys($fitmentItems)));

			// put the check in here
			// so if an SKU hasn't been found in the Mage DB, then report about that

			$missingItems = $fitmentItems;

			foreach($collection as $product) {
				$sku = $product->getSku();
				if(isset($fitmentItems[$sku])) {
					$result[$sku] = array(
						'item' => $fitmentItems[$sku],
						'product' => $product,
					);
					unset($missingItems[$sku]);
				}
			}

			if(count($missingItems)) {
				Mage::log('The following SKUs do not exist in Magento, although they were gathered from ARI API: '
						. implode(', ', array_keys($missingItems)));
			}
			// borrow a code from Vikont_Wholesale_Helper_Email::notifyCustomer to send an email on this
			// or make a separate log file

		} catch (Exception $e) {
			Mage::logException($e);
		}
		return $result;
	}

}