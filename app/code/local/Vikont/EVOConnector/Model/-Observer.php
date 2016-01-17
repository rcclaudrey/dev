<?php

class Vikont_EVOConnector_Model_Observer
{

	public function sales_order_place_after($event)
	{
		$order = $event->getOrder();
		$doTheStuff = true; //in_array($order->getPayment()->getMethodInstance()->getCode(), Mage::getStoreConfig('evoc/order/auto_invoicing_payment_methods')); // Mage::getModel('payment/config')->getAllMethods();

		if($doTheStuff) {
			if ($order->canInvoice()) {
				$invoice = $order->prepareInvoice();
				$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
				$invoice->register();

				$invoice->getOrder()->setCustomerNoteNotify(false);
				$invoice->getOrder()->setIsInProcess(true);
				$order->addStatusHistoryComment('Automatically invoiced by EVO Connector', false);

				Mage::getModel('core/resource_transaction')
				   ->addObject($invoice)
				   ->addObject($invoice->getOrder())
				   ->save();

				$invoice->sendEmail(true, '');

				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
				$order->save();
			} else {
				Vikont_EVOConnector_Model_Log::logWarning(sprintf('Order #%s cannot be invoiced', $order->getIncrementId()));
			}
		}

		return $this;
	}

}
