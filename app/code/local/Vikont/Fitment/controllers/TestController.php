<?php

class Vikont_Fitment_CheckController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		echo $this->getLayout()->createBlock('core/template')->setTemplate('vk_fitment/check.phtml')->toHtml();
		die;
	}



	/*
	 * ARI API Checker AJAX request receiver/responder
	 */
	public function postAction()
	{
		$requestType = $this->getRequest()->getParam('requestType');

		if($params = $this->getRequest()->getParam('params')) {
			foreach($params as $key => $value) {
				if(!$value) unset($params[$key]);
			}
		} else {
			$params = array();
		}

		if($options = $this->getRequest()->getParam('options')) {
			foreach($options as $key => $value) {
				if(!$value) unset($options[$key]);
			}
		} else {
			$options = array();
		}

		$time = microtime(true);
Mage::register('vd', 1);
		$html = vd(Mage::helper('fitment/api')->request($requestType, $params, $options), true);
		$time = round(microtime(true) - $time, 3);

		$responseData = array(
			'dump' => $html,
			'time' => $time,
		);

		echo json_encode($responseData);
		die;
	}

}