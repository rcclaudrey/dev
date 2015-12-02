<?php

class Vikont_EVOConnector_Helper_Activity extends Mage_Core_Helper_Abstract
{
	protected static $_customerIsWholesale = false;



	public function getOustandingActivity()
	{
		$globalXML = Vikont_EVOConnector_Helper_Data::array2xml(array(
			'outstandingActivityResponse' => array(
				'orders' => '%order_nodes%',
				'cancellations' => '%cancellation_nodes%'
			)
		));

		// filling up the <orders> node
		$orderNodesXML = '';

		$ordersCollection = Mage::getModel('sales/order')->getCollection();
		$orderId = (string)Mage::app()->getRequest()->getParam('order');

		if($orderId) {
			$ordersCollection->addFieldToFilter('increment_id', array('eq' => $orderId));
		} else {
			$ordersCollection->addFieldToFilter(
					Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD,
					array('eq' => Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_NEW)
				);
		}

		$commentsXMLTemplate = <<<ORDER_COMMENT
%skus%
Customer Comment: %customer_comment%
Warehouse: %warehouse%
Customer Type: %customer_type%
ORDER_COMMENT;

		foreach($ordersCollection as $order) {
			$customerIsWholesale = $this->_isCustomerWholesale($order); // initializing global variable; dirty but cheap

			list($carrierCode) = explode('_', $order->getData('shipping_method'));
			$shippingDescription = explode(' - ', $order->getData('shipping_description'), 2);
			$carrierMethod = isset($shippingDescription[1]) ? $shippingDescription[1] : '';

			$shippingCost = $order->getData('shipping_amount');
			$shippingVendor = strtoupper($carrierCode);
			$shippingMethod = strtoupper($carrierMethod);

			if('PICKUPATSTORE' == $shippingVendor) {
				$shippingVendor = 'WC';
				$shippingMethod = 'WC';
			} else if($customerIsWholesale) {
				$shippingCost = 0;
				$shippingVendor = 'UPS';
				$shippingMethod = 'GROUND';
			}

			$taxRule = Vikont_EVOConnector_Model_Source_Taxrules::detectTaxRule($order->getShippingAddress(), $customerIsWholesale);

			$orderNode = array('webOrder' => array(
				'orderID' => $order->getData('increment_id'),
				'date' => Vikont_EVOConnector_Helper_Data::getDateFormatted($order->getData('created_at')) . '.000',
				'shippingCost' => $shippingCost,
				'shippingVendor' => $shippingVendor,
				'shippingMethod' => $shippingMethod,
				'comment' => '%comment%',
				'taxRuleID' => $taxRule['id'],
				'taxAmount' => sprintf('%.2f', $order->getData('tax_amount')),
				'customers' => '%customers%',
				'payments' => '%payments%',
				'orderItems' => '%order_items%',
			));

			$orderNodeXML = Vikont_EVOConnector_Helper_Data::array2xml($orderNode);

			// billing address
			$billingAddress = $order->getBillingAddress();
			$billingAddress['address_type'] = 'BILLING';

			// shipping address
			$shippingAddress = $order->getShippingAddress();
			$shippingAddress['address_type'] = 'SHIPPING';

			// fixing Paypal caused issue
			if(!$shippingAddress->getLastname()) {
				$shippingAddress->setLastname($billingAddress->getLastname());
				$shippingAddress->setFirstname($billingAddress->getFirstname());
			}

			$billingAddressXML = Vikont_EVOConnector_Helper_Data::array2xml($this->_getCustomerNode($billingAddress, 'billing'));
			$shippingAddressXML = Vikont_EVOConnector_Helper_Data::array2xml($this->_getCustomerNode($shippingAddress, 'shipping'));

			$orderNodeXML = str_replace('%customers%',
					CR . $billingAddressXML . $shippingAddressXML,
					$orderNodeXML
				);

			// order items
			$orderItemNodesXML = '';
			$orderCommentInfo = array(); // to be put to the order's <comment> node
			$orderCommentInfoItem['warehouse'] = ''; // TODO !!!

			foreach($order->getAllVisibleItems() as $itemNumber => $orderItem) {
				$orderItem->setData('item_number', $itemNumber);
				$orderItemNode = $this->_getOrderItemNode($orderItem);

				if($orderItemNode['orderItem']['comment']) {
//					$orderCommentInfoItem['warehouse'] = ''; // TODO !!!

					if(in_array($orderItemNode['orderItem']['manufacturerID'], array('TR', 'WP', 'PU'))) {
						$orderCommentInfo[] = sprintf("Part numbers for %s \n SKU = %s: %s",
							$orderItem->getData('name'),
							$orderItem->getData('sku'),
							$orderItemNode['orderItem']['comment']
						);
					} else {
						$orderCommentInfo[] = sprintf('Part numbers for SKU = %s: %s',
								$orderItem->getData('sku'),
								$orderItemNode['orderItem']['comment']
							);
					}

				}
				unset($orderItemNode['orderItem']['comment']);
				$orderItemNodesXML .= Vikont_EVOConnector_Helper_Data::array2xml($orderItemNode);
			}
			$orderNodeXML = str_replace('%order_items%', CR . $orderItemNodesXML, $orderNodeXML);

			// payments section
			$paymentsXML = '';

			foreach($order->getPaymentsCollection() as $payment) {
				@list($paymentType) = explode('_', $payment->getMethod());
				$paymentType = strtoupper($paymentType);

				switch($paymentType) {
					case 'PURCHASEORDER':
						$paymentType = 'OTHER'; break;

					case 'CCSAVE':
					case 'HPS':
						$paymentType = 'VISA'; break;
				}

				$paymentsXML .= Vikont_EVOConnector_Helper_Data::array2xml(
					array(
						'payment' => array(
							'type' => $paymentType,
							'amount' => sprintf('%.2f', $payment->getData('amount_ordered')),
				)));
			}

			$orderNodeXML = str_replace('%payments%', CR . $paymentsXML, $orderNodeXML);

			// order comment
//Related Skus: PU: 1112332 WP: 222552
//Customer Comment:  some comment here
//Warehouse: Tucker Rocky - Warehouse 5
//Customer Type: Retail
			$commentsXML = $commentsXMLTemplate;

			$commentsXML = str_replace('%skus%', implode("\n", $orderCommentInfo), $commentsXML);
			$commentsXML = str_replace('%customer_comment%',
//					implode(','.CR, array_map('htmlspecialchars', $this->_getOrderComments($order))),
					implode(','.CR, $this->_getOrderComments($order)),
					$commentsXML
				);
			$commentsXML = str_replace('%warehouse%', $orderCommentInfoItem['warehouse'], $commentsXML);
			$commentsXML = str_replace('%customer_type%', Mage::helper('evoc/customer')->getCustomerType(), $commentsXML);

			$payment = $order->getPaymentsCollection()->getFirstItem();
			if('purchaseorder' == $payment->getMethod()) {
				$commentsXML .= "\nPO Number: " . $payment->getPoNumber();
			}

			$orderNodeXML = str_replace('%comment%', htmlspecialchars($commentsXML), $orderNodeXML);

			// and finally adding order node XML to the nodes list
			$orderNodesXML .= CR . $orderNodeXML;
		}

		$globalXML = str_replace('%order_nodes%', $orderNodesXML, $globalXML);

		// filling up the <cancellations> node
		$cancellationNodesXML = '';

		$globalXML = str_replace('%cancellation_nodes%', $cancellationNodesXML, $globalXML);

		return $globalXML;
	}



	protected function _getCustomerNode($address, $addressType = 'billing')
	{
		$customerId = $address->getData('customer_id');
		$companyName = strtoupper($address->getData('company'));

		if($this->_isCustomerWholesale()) {
			if($addressType == 'billing') {
				$companyName = 'DC-' . $companyName;
			}
		} else {
			$customerId = Mage::getStoreConfig('evoc/misc/dummy_user_prefix') . (string)crc32($address->getData('email'));
		}

		$street = explode("\n", $address->getData('street'), 2);

		return array('customer' => array(
			'type' => $address->getData('address_type'),
			'customerID' => $customerId,
			'prefixName' => strtoupper($address->getData('prefix')),
			'firstName' => strtoupper($address->getData('firstname')),
			'middleName' => strtoupper($address->getData('middlename')),
			'lastName' => strtoupper($address->getData('lastname')),
			'suffixName' => strtoupper($address->getData('suffix')),
			'companyName' => $companyName,
			'address1' => strtoupper($street[0]),
			'address2' => isset($street[1]) ? strtoupper($street[1]) : '',
			'city' => strtoupper($address->getData('city')),
			'state' => strtoupper($address->getData('region')), // Mage::helper('directory')->__($address->getRegion())
			'county' => '',
			'country' => strtoupper($address->getData('country_id')),
			'zipCode' => $address->getData('postcode'),
			'phone' => strtoupper($address->getData('telephone')),
			'workPhone' => strtoupper($address->getData('fax')),
			'email' => strtoupper($address->getData('email')),
		));
	}



	protected function _getOrderItemNode($item)
	{
/**
<orderItem>
     <itemID>166914</itemID>
     <manufacturerID>TR</manufacturerID>
     <manufacturerName>Tucker Rocky</manufacturerName>
     <itemNumber>12345s</itemNumber>
     <description>Patchwork T-Shirt</description>
     <quantity>1</quantity>
     <amount>5.00</amount>
</orderItem>
/**/

		$sku = $item->getData('sku');
		$distributorInfo = $this->_getDistributorInfo($sku);

		if(!$distributorInfo['manufacturerID']) { // this is probably OEM product
			// let's try to find this in the OEM Price table
			$oemPriceData = Vikont_EVOConnector_Helper_OEM::getOEMCostData($sku);
			if($oemPriceData && is_array($oemPriceData) && count($oemPriceData)) {
				$row = reset($oemPriceData);
				$supplierCode = $row['supplier_code'];
				$distributorInfo['manufacturerID'] = $supplierCode;

				$fields = Vikont_EVOConnector_Helper_Data::getDistributors();
				if(isset($fields[$supplierCode])) {
					$distributorInfo['manufacturerName'] = $fields[$supplierCode][1];
				}
			}

			if(!$distributorInfo['manufacturerName']) {
				list($brandName) = explode('|', $item->getData('name'), 2);
				$brandParts = explode(' ', trim($brandName), 2);
				$manufacturerName = (count($brandParts) > 1) ? $brandParts[1] : 'UNKNOWN';
				$distributorInfo['manufacturerName'] = $manufacturerName;

				if(!$distributorInfo['manufacturerID']) {
					$manufacturerId = Vikont_EVOConnector_Helper_OEMBrand::getOEMBrandCodeByName(strtolower($manufacturerName));
					$distributorInfo['manufacturerID'] = $manufacturerId ? $manufacturerId : $manufacturerName;
				}
			}
		}

		$rowTotal = floatval($item->getData('row_total')) - floatval($item->getData('discount_amount'));
		$itemQty = (int) $item->getData('qty_ordered');
		$itemPrice = $rowTotal / $itemQty;

		return array('orderItem' => array(
			'itemID' => $item->getData('item_id'),
			'manufacturerID' => $distributorInfo['manufacturerID'],
			'manufacturerName' => $distributorInfo['manufacturerName'],
			'itemNumber' => $distributorInfo['itemNumber'], // ? $distributorInfo['itemNumber'] : $sku,
			'description' => $item->getData('name'),
			'quantity' => $itemQty,
			'amount' => sprintf('%.2f', $itemPrice),
			'comment' => $distributorInfo['comment'], // to place to upper order's <comment> section
		));
	}



	protected function _getOrderComments($order)
	{
		$comments = array();

		// originally status history is sorted descending by 'created_at' field
		// to change this, change to:
		// Mage::getResourceModel('sales/order_status_history_collection')
		//		->setOrderFilter($order)
		//		->setOrder('created_at', 'desc') // change appropriately
		//		->setOrder('entity_id', 'desc');
		foreach ($order->getStatusHistoryCollection() as $status) {
			if (!$status->isDeleted()
			&& $status->getComment()
//			&& $status->getIsVisibleOnFront()
			) {
				$comments[] =  $status->getComment();
			}
		}

		return $comments;
	}



	protected function _getDistributorInfo($sku)
	{
		$result = array(
			'itemNumber' => $sku, // for OEM and missing products
			'manufacturerID' => null,
			'manufacturerName' => null,
			'comment' => null,
		);

		$data = Vikont_EVOConnector_Helper_OEM::getPartNumbers($sku);

		if($data && is_array($data) && count($data)) {
			$data = reset($data);
		} else {
			return $result;
		}

		if($data['d_trocky']) {
			$result['itemNumber'] = $data['d_trocky'];
			$result['manufacturerID'] = 'TR';
			$result['manufacturerName'] = 'Tucker Rocky';
			$result['comment'] = Vikont_EVOConnector_Helper_Data::combineArray(array('TR' => $data['d_trocky'], 'WP' => $data['d_wpower'], 'PU' => $data['d_punlim']));
		} else if($data['d_wpower']) {
			$result['itemNumber'] = $data['d_wpower'];
			$result['manufacturerID'] = 'WP';
			$result['manufacturerName'] = 'Western Power Sports';
			$result['comment'] = Vikont_EVOConnector_Helper_Data::combineArray(array('TR' => $data['d_trocky'], 'WP' => $data['d_wpower'], 'PU' => $data['d_punlim']));
		} else if($data['d_punlim']) {
			$result['itemNumber'] = $data['d_punlim'];
			$result['manufacturerID'] = 'PU';
			$result['manufacturerName'] = 'Parts Unlimited';
			$result['comment'] = Vikont_EVOConnector_Helper_Data::combineArray(array('TR' => $data['d_trocky'], 'WP' => $data['d_wpower'], 'PU' => $data['d_punlim']));
		} else {
			$fields = Vikont_EVOConnector_Helper_Data::getDistributors();
			unset($fields['TR']);
			unset($fields['WP']);
			unset($fields['PU']);

			foreach($fields as $tmsCode => $field) {
				if($data[$field[0]]) {
					$result['itemNumber'] = $data[$field[0]];
					$result['manufacturerID'] = $tmsCode;
					$result['manufacturerName'] = $field[1];
					break;
				}
			}
		}

		return $result;
	}



	protected function _isCustomerWholesale($order = null)
	{
		if($order) {
			$customerId = $order->getBillingAddress()->getCustomerId();

			if($customerId) {
				self::$_customerIsWholesale = Mage::helper('evoc/customer')->isCustomerWholesale($customerId);
			} else {
				self::$_customerIsWholesale = false;
			}
		}

		return self::$_customerIsWholesale;
	}

}