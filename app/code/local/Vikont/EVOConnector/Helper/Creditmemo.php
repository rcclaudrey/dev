<?php

class Vikont_EVOConnector_Helper_Creditmemo extends Mage_Core_Helper_Abstract
{

	protected function _initCreditmemo($order, $qtys = array())
	{
		$creditmemo = false;
		$invoice = $order->getInvoiceCollection()->getFirstItem();

		if (!$order->canCreditmemo()) {
			throw new Exception($this->__('Cannot create credit memo for the order'));
			return false;
		}

		$service = Mage::getModel('sales/service_order', $order);
		if ($invoice) {
			$creditmemo = $service->prepareInvoiceCreditmemo($invoice, $qtys);
		} else {
			$creditmemo = $service->prepareCreditmemo($qtys);
		}

		$isAutoReturnEnabled = Mage::helper('cataloginventory')->isAutoReturnEnabled();
		foreach ($creditmemo->getAllItems() as $creditmemoItem) {
			$creditmemoItem->setBackToStock($isAutoReturnEnabled);
		}

		Mage::register('current_creditmemo', $creditmemo);
		return $creditmemo;
	}



	public function creditMemo($order, $comment, $qtys = array())
	{
		$creditmemo = $this->_initCreditmemo($order, $qtys);

		if ($creditmemo) {
			$creditmemo->addComment($comment, true, true);
			$creditmemo->setRefundRequested(true);
			$creditmemo->setOfflineRequested(true);
			$creditmemo->register();
			$creditmemo->setEmailSent(true);
			$creditmemo->getOrder()->setCustomerNoteNotify(true);

			// saving credit memo
			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($creditmemo)
				->addObject($creditmemo->getOrder());
			if ($creditmemo->getInvoice()) {
				$transactionSave->addObject($creditmemo->getInvoice());
			}
			$transactionSave->save();

			$creditmemo->sendEmail(true, $comment);
//				($this->__('The credit memo has been created.'));
			return true;
		}
		return false;
	}

}