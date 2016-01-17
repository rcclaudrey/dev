<?php

class Vikont_ARIOEM_CheckController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		echo $this->getLayout()->createBlock('core/template')->setTemplate('arioem/check.phtml')->toHtml();
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

		$data = Mage::helper('arioem/api')
					->setApiMode('check')
					->request($requestType, $params, $options);

		$time = round(microtime(true) - $time, 3);

		if(isset($data['responseType']) && $data['responseType'] == 'image') {
			$fileName = 'arioem-images/' . implode('-', $params) . '.gif';
			$filePath = Mage::getModel('core/config')->getBaseDir('media') . '/' . $fileName;
			$imageDirPath = dirname($filePath);

			if(!file_exists($imageDirPath)) {
				mkdir($imageDirPath, 0777, true);
			}

			if ($f = fopen($filePath, 'w')) {
				fwrite($f, $data['image']);
				fclose($f);
			} else {
				Mage::log("Cannot open file '$filePath' for writing");
			}

			$responseData = array(
				'requestType' => $requestType,
				'responseType' => 'image',
				'time' => $time,
				'imageURL' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $fileName,
			);
		} else{
			$responseData = array(
				'requestType' => $requestType,
				'responseType' => 'data',
				'time' => $time,
				'dump' => vd($data, true),
			);
		}

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