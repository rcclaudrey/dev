<?php

class Vikont_EVOConnector_Helper_Order extends Mage_Core_Helper_Abstract
{

	public function sendAcknowledgeResponse($status)
	{
		$response = <<<XML
<acknowledgeResponse>
	<responseStatus>{$status}</responseStatus>
</acknowledgeResponse>
XML;
		Vikont_EVOConnector_Helper_Data::sendResponse($response);
	}



	public function changeOrderState($order, $state, $status, $comment, $setEVOGrabbedStatus = true)
	{
		if(!is_object($order)) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($order);
		}

		if(in_array($order->getState(), array(
			Mage_Sales_Model_Order::STATE_COMPLETE,
			// what else?
		))) {
			return; // do nothing
		}

		$isCustomerNotified = false;

		if(		Mage_Sales_Model_Order::STATE_HOLDED == $state
			||	Mage_Sales_Model_Order::STATE_HOLDED == $status
		) {
			$order
				->setHoldBeforeState($order->getState())
				->setHoldBeforeStatus($order->getStatus());
		}

		$order->setState($state, $status, $comment, $isCustomerNotified);

		if($setEVOGrabbedStatus) {
			$order->setData(Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD, Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_READY);
		}

		$order->save();
	}



	public function orderItem($orderId, $itemId, $requestXML)
	{
		if('item' != strtolower((string)$requestXML->getName())) {
			$this->sendAcknowledgeResponse('FAILURE');
			return;
		}

//		$itemId = $itemId ? $itemId : (int)$requestXML->itemNumber;
		$action = strtolower((string)$requestXML->action);

		switch($action) {
			case 'cancel':
/**
<item>
	<action>CANCEL</action>
	<itemNumber> 390-93248-324324</itemNumber>
	<quantity>1</quantity>
	<date>2013-06-06 14:50:42.856</date>
</item>
/**/
				try {
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

					if (!$order->getId()) {
						Vikont_EVOConnector_Model_Log::log(sprintf('ERROR: order does not exist at %s', $message));
						$this->sendAcknowledgeResponse('FAILURE');
						return;
					}

					$orderItem = $order->getItemById($itemId);

					if ($orderItem->getStatusId() == Mage_Sales_Model_Order_Item::STATUS_CANCELED) { // OMG, it's already canceled! who did this?
						$this->sendAcknowledgeResponse('SUCCESS');
						return;
					}

					$qtyRequested = (int)$requestXML->quantity;
					$qtyToRefund = min($qtyRequested, $orderItem->getQtyToRefund());
					$qtyToCancel = min($qtyRequested - $qtyToRefund, $orderItem->getQtyToCancel());

					if($qtyRequested > $qtyToRefund + $qtyToCancel) {
						Vikont_EVOConnector_Model_Log::log(sprintf('ERROR: requested qty to cancel (%d) is greater than the sum of qtys available to cancel (%d) and to refund (%d)',
								$qtyRequested,
								$orderItem->getQtyToCancel(),
								$orderItem->getQtyToRefund()
							));
						$this->sendAcknowledgeResponse('FAILURE');
						return;
					}

					if($qtyToRefund) {
						$message = sprintf('EVO Cancel request at %s', (int)$requestXML->date);
						$qtys = array('qtys' => array($orderItem->getId() => $qtyToRefund));
						$result = Mage::helper('evoc/creditmemo')->creditMemo($order, $message, $qtys);

						Vikont_EVOConnector_Model_Log::log(sprintf('SUCCESS credit memo on order ID=%s item ID=%d for qty=%d', $orderId, $itemId, $qtyToRefund));
					}

					if ($qtyToCancel) {
						// so we cancel the available items first
						Mage::dispatchEvent('sales_order_item_cancel', array('item' => $orderItem));
						$orderItem->setQtyCanceled($orderItem->getQtyCanceled() + $qtyToCancel);
						$orderItem->setTaxCanceled(
							$orderItem->getTaxCanceled() +
							$orderItem->getBaseTaxAmount() * $qtyToCancel / $orderItem->getQtyOrdered()
						);
						$orderItem->setHiddenTaxCanceled(
							$orderItem->getHiddenTaxCanceled() +
							$orderItem->getHiddenTaxAmount() * $qtyToCancel / $orderItem->getQtyOrdered()
						);
						$orderItem->save();

						Vikont_EVOConnector_Model_Log::log(sprintf('SUCCESS cancel item on order ID=%s item ID=%d qty=%d', $orderId, $itemId, $qtyToRefund));
					}

					$this->sendAcknowledgeResponse('SUCCESS');
					return;
				} catch (Exception $e) {
					Mage::logException($e);
					Vikont_EVOConnector_Model_Log::log(sprintf('Error canceling order item for order #%s, item ID %d, error message is: %s',
							$orderId,
							$itemId,
							$e->getMessage()
					));
					$this->sendAcknowledgeResponse('FAILURE');
					return;
				}
				break;

			case 'sell':
/**
<item>
	<action>SELL</action>
	<itemNumber>424101</itemNumber>
	<quantity>1</quantity>
	<date>2013-06-06 14:50:42.856</date>
</item>
/**/
				try {
/*
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
					so we need to get all invoices and check whether the item is mentioned there
					$order->getInvoiceCollection()
					and if it's not, then to create it
					$invoicedItem = array(
						(int)$requestXML->itemNumber => (int)$requestXML->quantity,
					);
//vd($invoicedItem);
//die;
					$invoice = Mage::getModel('sales/service_order', $order)->prepareShipment($invoicedItem);
					but now we just need to say "OK EVO, we know there's some order item, this is great news you've sold it"
/**/
					$this->sendAcknowledgeResponse('SUCCESS');

					return;
				} catch (Exception $e) {
					Mage::logException($e);
					Vikont_EVOConnector_Model_Log::log(sprintf('Error on sell response for order #%s, item ID %d, error message is: %s',
							$orderId,
							$itemId,
							$e->getMessage()
					));
					$this->sendAcknowledgeResponse('FAILURE');
					return;
				}
				break;

			default:
				$this->sendAcknowledgeResponse('FAILURE');
		}
	}



	public function orderShipment($orderId, $requestXML)
	{
/**
<shipment>
	<date>2013-06-05 12:37:52.811</date>
	<shipmentCarrier>UPS</shipmentCarrier>
	<shipmentMethod>GROUND</shipmentMethod>
	<trackingNumber>123123123123</trackingNumber>
	<items>
		<item>
			<itemID>093890700</itemID>
			<quantity>1</quantity>
		</item>
	</items>
</shipment>
/**/

		try {
			if('shipment' != strtolower($requestXML->getName())) {
				throw new Exception(sprintf('<shipment> tag is missing', $orderId));
			}

			$shippedItems = array();

			foreach($requestXML->items->item as $itemNode) {
				$shippedItems[(int)$itemNode->itemID] = (float)$itemNode->quantity;
			}

			if(!count($shippedItems)) {
				throw new Exception('no shipment items');
			}

			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($shippedItems);
			$track = Mage::getModel('sales/order_shipment_track')->addData(array(
				'carrier_code' => (string)$requestXML->shipmentCarrier,
				'title' => (string)$requestXML->shipmentMethod,
				'number' => (string)$requestXML->trackingNumber,
			));
			$shipment->addTrack($track);
			$shipment->register();

			// saving shipment and order in one transaction
			$order->setIsInProcess(true);
			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($order)
				->save();

			$shipment->sendEmail(true, '');

			$this->sendAcknowledgeResponse('SUCCESS');

			Vikont_EVOConnector_Model_Log::log(sprintf('An acknowledge response SUCCESS has been sent for shipment on order #%s', $orderId));

			return;
		} catch (Exception $e) {
			Mage::logException($e);
			Vikont_EVOConnector_Model_Log::log(sprintf('Error creating shipment for order #%s, error message is: %s',
					$orderId,
					$e->getMessage()
			));
//			$this->sendAcknowledgeResponse('FAILURE');
			$this->sendAcknowledgeResponse('SUCCESS');

//			Vikont_EVOConnector_Model_Log::log(sprintf('An acknowledge response FAILURE has been sent for shipment on order #%s', $orderId));
			Vikont_EVOConnector_Model_Log::log(sprintf('An acknowledge response FAILURE should have been sent for shipment on order #%s', $orderId));

			return;
		}
	}



	public function orderVoid($orderId, $requestXML)
	{
/**
<order>
	<action>VOID</action>
	<date>2013-06-05 13:39:34.406</date>
	<reason>Phone void</reason>
</order>
/**/
		if(	'order' != strtolower((string)$requestXML->getName())
		||	'void' != strtolower((string)$requestXML->action)
		) {
			$this->sendAcknowledgeResponse('FAILURE');
			return;
		}

		$message = sprintf('EVO VOID request to order #%s at %s, reason: %s',
				$orderId,
				(string)$requestXML->date,
				(string)$requestXML->reason
			);

		try {
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

			if (!$order->getId()) {
				Vikont_EVOConnector_Model_Log::log(sprintf('ERROR: order does not exist at %s', $message));
				$this->sendAcknowledgeResponse('FAILURE');
				return;
			}

			if ($order->canCancel()) {
				$order->getPayment()->cancel();
				$order->registerCancellation($message, false); // let's raise an exception in case anything's wrong
				Mage::dispatchEvent('order_cancel_after', array('order' => $this));
				$order->save();

				Vikont_EVOConnector_Model_Log::log(sprintf('SUCCESS cancel on %s', $message));
				$this->sendAcknowledgeResponse('SUCCESS');
				return;
			} else if($order->canCreditmemo()) {
				$result = Mage::helper('evoc/creditmemo')->creditMemo($order, $message);

				if($result) {
					Vikont_EVOConnector_Model_Log::log(sprintf('SUCCESS credit memo on %s', $message));
					$this->sendAcknowledgeResponse('SUCCESS');
				} else {
					Vikont_EVOConnector_Model_Log::log(sprintf('ERROR credit memo on %s', $message));
					$this->sendAcknowledgeResponse('FAILURE');
				}

				return;
			}

			$this->changeOrderState($order, Mage_Sales_Model_Order::STATE_CANCELED, 'canceled', $message);
			$this->sendAcknowledgeResponse('SUCCESS');

		} catch (Exception $e) {
			Mage::logException($e);
			Vikont_EVOConnector_Model_Log::log(sprintf('%s ERROR: %s',
					$message,
					$e->getMessage()
				));
		}
	}



	public function addOrderComment($orderId, $comment, $visibleOnFront = false, $notifyCustomer = false)
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

		$order->addStatusHistoryComment($comment, $order->getStatus())
                    ->setIsVisibleOnFront($visibleOnFront)
                    ->setIsCustomerNotified($notifyCustomer);

        $order->save();
	}


}