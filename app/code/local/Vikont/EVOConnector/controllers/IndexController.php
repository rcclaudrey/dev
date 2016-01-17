<?php

define('CR', chr(13));


class Vikont_EVOConnector_IndexController extends Mage_Core_Controller_Front_Action
{

	protected function _authenticate()
	{
		// this dirty hack was performed by professional stunt programmers! Don't try this at home!!!
		// return true;

		if(!Vikont_EVOConnector_Helper_Data::isModuleAllowed()) {
			$this->getResponse()
				->setHeader('HTTP/1.1', '404 Not Found')
				->setHeader('Status', '404 File not found')
				->setBody('Page not found')
				->sendResponse();
			die();
		}

		if(	isset($_SERVER['PHP_AUTH_USER'])
		&&	isset($_SERVER['PHP_AUTH_PW'])
		) {
			$userName = Mage::getStoreConfig('evoc/auth/username', Mage_Core_Model_App::ADMIN_STORE_ID);
			$userPass = Mage::getStoreConfig('evoc/auth/password', Mage_Core_Model_App::ADMIN_STORE_ID);

			if(	($userName != $_SERVER['PHP_AUTH_USER'])
			||	($userPass != $_SERVER['PHP_AUTH_PW'])
			) {
				$this->getResponse()
					->setHeader('HTTP/1.1', '401 Unauthorized')
					->setHeader('WWW-Authenticate', 'Basic realm="EVO Connector"')
					->setBody('Wrong username or password')
					->sendResponse();
				die();
			}

			return true;
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION']) {
			$properUserName = Mage::getStoreConfig('evoc/auth/username', Mage_Core_Model_App::ADMIN_STORE_ID);
			$properUserPass = Mage::getStoreConfig('evoc/auth/password', Mage_Core_Model_App::ADMIN_STORE_ID);

			$auth = explode(' ', ''.@$_SERVER['HTTP_AUTHORIZATION']);

			if(	(count($auth) > 1)
			&&	('basic' == strtolower($auth[0]))
			) {
				$userPassPair = base64_decode($auth[1]);

				if( $userPassPair
				&&	($properUserName.':'.$properUserPass === $userPassPair)
				) {
					return true;
				}
			}

			$this->getResponse()
					->setHeader('HTTP/1.1', '401 Unauthorized')
					->setHeader('WWW-Authenticate', 'Basic realm="EVO Connector"')
					->setBody('Wrong username or password')
					->sendResponse();
			die();
		} else {
			$this->getResponse()
				->setHeader('HTTP/1.1', '401 Unauthorized')
				->setHeader('WWW-Authenticate', 'Basic realm="EVO Connector"')
				->setBody('Authorization required')
				->sendResponse();
			die();
		}
	}



	public function indexAction()
	{
		$this->_forward('version');
	}



	public function versionAction()
	{
		$this->_authenticate();

		$result = array(
			'versions' => array(
				'version' => '1.0',
			)
		);

		Vikont_EVOConnector_Helper_Data::sendResponse($result);
	}



	public function taxrulesAction()
	{
		$this->_authenticate();

		$ruleNodes = '';
/*
		$collection = Mage::getModel('tax/calculation_rule')->getCollection();

		foreach($collection as $item) {
			$node = array('taxRule' => array(
				'taxRuleID' => $item->getData('tax_calculation_rule_id'),
				'description' => $item->getData('code'),
			));

			$ruleNodes .= Vikont_EVOConnector_Helper_Data::array2xml($node);
		}
 /**/

		foreach(Vikont_EVOConnector_Model_Source_Taxrules::getTaxRules() as $ruleId => $ruleName) {
			$node = array('taxRule' => array(
				'taxRuleID' => $ruleId,
				'description' => $ruleName,
			));
			$ruleNodes .= Vikont_EVOConnector_Helper_Data::array2xml($node);
		}

		$result = str_replace('%tax_nodes%', CR.$ruleNodes, Vikont_EVOConnector_Helper_Data::array2xml(array(
			'taxRules' => '%tax_nodes%'
		)));

		Vikont_EVOConnector_Helper_Data::sendResponse($result);
	}



	public function outstandingactivityAction()
	{
		$this->_authenticate();

		$xml = Mage::helper('evoc/activity')->getOustandingActivity();

		Vikont_EVOConnector_Helper_Data::sendResponse($xml);
	}




	public function acknowledgerequestAction()
	{
		$this->_authenticate();

		$requestBody = file_get_contents('php://input');
/**
$requestBody = <<<XML
<acknowledgeRequest>
  <id>100000167</id>
  <responseType>WEB_ORDER</responseType>
  <date>2015-07-03 13:56:17.669</date>
  <responseIssues>
    <responseIssue>
      <code>1301</code>
      <message>Invalid payment type for payment CCSAVE</message>
      <responseStatus>FAILURE</responseStatus>
    </responseIssue>
  </responseIssues>
</acknowledgeRequest>
XML;
/**/
/**
		$requestBody = <<<XML
<acknowledgeRequest>
	<id>1111111111</id>
	<responseType>WEB_ORDER</responseType>
	<date>2014-11-12 10:01:00.662</date>
	<responseIssues>
		<responseIssue>
			<code>1010</code>
			<message>A web order with id 1111111111 is already imported.</message>
			<responseStatus>FAILURE</responseStatus>
		</responseIssue>
		<responseIssue>
			<code>222</code>
			<message>some another message</message>
			<responseStatus>FAILURE</responseStatus>
		</responseIssue>
	</responseIssues>
</acknowledgeRequest>
XML;
/**/
		$requestXML = Vikont_EVOConnector_Helper_Data::parseXML($requestBody);
		if(!$requestXML) {
			Mage::helper('evoc/order')->sendAcknowledgeResponse('FAILURE');
			return;
		}

		if('acknowledgeRequest' !== $requestXML->getName()) {
			Vikont_EVOConnector_Model_Log::log('unknown request type, should be "acknowledgeRequest", got "$requestXML->getName()" instead');
			Mage::helper('evoc/order')->sendAcknowledgeResponse('FAILURE');
			return;
		}

		$orderId = (string)$requestXML->id;
		$responseType = (string)$requestXML->responseType;
		$responseDate = (string)$requestXML->date;

		switch($responseType) {
			case 'WEB_ORDER':
				$responseIssues = (array)$requestXML->responseIssues;

				if(		isset($responseIssues['responseIssue'])
					&&	count($responseIssues['responseIssue'])
				) {
					$reportIssues = array();

					foreach($requestXML->responseIssues->responseIssue as $issue) {
						$issueCode = (int)$issue->code;

						if(1010 == $issueCode) {
							$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

							// if an order has been dealt with by EVOC, any non-zero value should be at its evo_status field
							if((int)$order->getData(Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD)) {
								$errorMessage = sprintf(
										'WARNING: unexpected acknowledgeRequest WEB_ORDER issue for order %s at %s:, code: %d, message: %s',
										$orderId,
										$responseDate,
										$issueCode,
										(string)$issue->message
									);

								Vikont_EVOConnector_Model_Log::log($errorMessage);

								continue;
							} else {
								// if the order hasn't been marked as accepted, then let's do that!
								$order
									->setData(	Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD,
												Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_APPROVED )
									->save();

								Vikont_EVOConnector_Model_Log::log(
										'Marked order #%s as already imported, code: %d, message: %s',
										$orderId,
										$issueCode,
										(string)$issue->message
									);

								continue;
							}
						}

						$reportIssues[] = sprintf('code: %d, message: %s',
								$issueCode,
								(string)$issue->message
							);
					}

					if(count($reportIssues)) {
						Vikont_EVOConnector_Model_Log::log(sprintf('acknowledgeRequest WEB_ORDER issue(s) for order %s at %s:',
								$orderId,
								$responseDate
							));

						$reportIssues =  implode(", \n", $reportIssues);
						Vikont_EVOConnector_Model_Log::log($reportIssues);
						$comment = 'Rejected by EVO at ' . $responseDate . " with issues:\n" . $reportIssues; // life is pain
						Mage::helper('evoc/order')->changeOrderState($orderId, 'holded', 'holded', $comment);
					}
					Mage::helper('evoc/order')->sendAcknowledgeResponse('SUCCESS');
					return;
				} else {
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

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
//						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
//						$order->save(); // this is to be called later when changing order state
					} else {
						Vikont_EVOConnector_Model_Log::logWarning(sprintf('Order #%s cannot be invoiced', $order->getIncrementId()));
					}

					$comment = 'Accepted by EVO at ' . $responseDate;
					Mage::helper('evoc/order')->changeOrderState($order, Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_PROCESSING, $comment);
					Mage::helper('evoc/order')->sendAcknowledgeResponse('SUCCESS');
					return;
				}
				break;

			case 'WEB_ORDER_CANCELLATION':
				// it seems we don't do this here
				break;

			default:
				Vikont_EVOConnector_Model_Log::log(sprintf('unknown responseType: "%s" for orderId="%s" at %s',
						$responseType,
						$orderId,
						$responseDate
					));
				Mage::helper('evoc/order')->sendAcknowledgeResponse('FAILURE');
		}
	}



	// Item cancel		url/Order/{id}/Item/{id}
	// Item Sell			url/Order/{id}/Item/{id}
	// Shipment			url/Order/{id}/Shipment
	// Void				url/Order/{id}
	public function orderAction()
	{
		$this->_authenticate();

		$requestBody = file_get_contents('php://input');
		Vikont_EVOConnector_Model_Log::log(sprintf('order action, request is: %s, body is: %s', $_SERVER['REQUEST_URI'], $requestBody));

		$requestXML = Vikont_EVOConnector_Helper_Data::parseXML($requestBody);
		if(!$requestXML) {
			Mage::helper('evoc/order')->sendAcknowledgeResponse('FAILURE');
			return;
		}

		$marker = 'evoc/index/order/';
		$uri = strtolower($_SERVER['REQUEST_URI']);
		$paramURIPart = trim(substr($uri, strlen($marker) + stripos($uri, $marker)), '/');

		if(false !== strpos($paramURIPart, '/item/')) {
			$itemId = null;
			@list($orderId, $itemId) = explode('/item/', $paramURIPart);
			Mage::helper('evoc/order')->orderItem($orderId, $itemId, $requestXML);
			return;
		} elseif(false !== strpos($paramURIPart, '/shipment')) { // this is for order items shipment
			list($orderId) = explode('/shipment', $paramURIPart);
			Mage::helper('evoc/order')->orderShipment($orderId, $requestXML);
			return;
		} else {
			$orderId = $paramURIPart;
			Mage::helper('evoc/order')->orderVoid($orderId, $requestXML);
			return;
		}
	}



	// Post Authorization (url/PostAuth/{order id}/{payment id}/{payment amount} ) [POST]
	// index.php/evoc/index/PostAuth/1111111111/VISA/13.72
	public function postauthAction()
	{
		$this->_authenticate();

		$orderHelper = Mage::helper('evoc/order');

		Vikont_EVOConnector_Model_Log::log(sprintf('postAuth action, request is: %s', $_SERVER['REQUEST_URI']));

		$marker = 'evoc/index/postauth/';
		$paramURIPart = trim(substr($_SERVER['REQUEST_URI'], strlen($marker) + stripos($_SERVER['REQUEST_URI'], $marker)), '/');
		$parts = explode('/', $paramURIPart);

		if(count($parts) >= 3) {
			list($orderId, $paymentId, $amount) = $parts;
			$comment = sprintf('Post Authorization for %s %s %s', $orderId, $paymentId, $amount);
			$orderHelper->addOrderComment($orderId, $comment);
			$orderHelper->sendAcknowledgeResponse('SUCCESS');
			return;
		} else {
			Vikont_EVOConnector_Model_Log::log(sprintf('not enough parameters, %d of required 3 passed: %s', count($parts), $_SERVER['REQUEST_URI']));
			$orderHelper->sendAcknowledgeResponse('FAILURE');
		}
	}



	public function dumpAction()
	{
		$this->_authenticate();

		$order = Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('order'));
		if(!$order->getId()) {
			echo 'No such order';
			die;
		}

		vd($order->getData());

		foreach($order->getItemsCollection() as $item) {
			vd($item->getData());
		}
	}



	public function stateProcessingAction()
	{
		$this->_authenticate();

		$orderId = $this->getRequest()->getParam('order');
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$comment = 'Accepted by EVO at ' . date('Y-m-d H:i');
		Mage::helper('evoc/order')->changeOrderState($order, Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_PROCESSING, $comment);
		die;
	}



	public function stateCompleteAction()
	{
		$this->_authenticate();

		$orderId = $this->getRequest()->getParam('order');
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$comment = 'Manually set as completed at ' . date('Y-m-d H:i');
		$state = Mage_Sales_Model_Order::STATE_COMPLETE;
		$status = Mage_Sales_Model_Order::STATE_COMPLETE;
		$isCustomerNotified = false;

		$order
			->setHoldBeforeState($order->getState())
			->setHoldBeforeStatus($order->getStatus());

		$order->setData('state', $state);
//		$order->setData(Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD, 1);

        if ($status) {
            $order->setStatus($status);
            $history = $order->addStatusHistoryComment($comment, false); // no sense to set $status again
            $history->setIsCustomerNotified($isCustomerNotified);
        }

		$order->save();

		die;
	}



	public function avoidGrabbingAction()
	{
		$this->_authenticate();

		$orderId = $this->getRequest()->getParam('order');
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
		$order->setData(
				Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_FIELD,
				Vikont_EVOConnector_Helper_Data::ORDER_EVO_STATUS_SENT
			);
		$order->save();

		die;
	}

}