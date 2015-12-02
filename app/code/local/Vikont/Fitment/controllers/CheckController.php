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



	public function listCategoriesAction()
	{
		$collection = Mage::getModel('catalog/category')
			->getCollection()
				->addAttributeToSelect('name')
				->addAttributeToSort('path');

		$mode = $this->getRequest()->getParam('mode');
		$mode = $mode ? $mode : 'html';

//		if('html' == $mode)
			echo '<code style="white-space:pre">';

		foreach($collection as $category) {
			if('html' == $mode) {
				printf('%7s %s%s<br/>',
					$category->getId(),
					str_repeat('.   ', $category->getLevel()),
					$category->getName()
				);
			} elseif('csv' == $mode) {
				printf('%d,%s"%s"<br/>',
					$category->getId(),
					str_repeat(',', $category->getLevel()),
					$category->getName()
				);
			}
		}

//		if('html' == $mode)
			echo '</code>';
	}



	public function testController()
	{
		echo 123;
		$a=1;
		$b=0;
		echo $a / $b;
		die;
//		Mage::throwException(new Exception('test error'));
	}

}