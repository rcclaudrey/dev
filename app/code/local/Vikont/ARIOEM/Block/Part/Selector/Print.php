<?php

class Vikont_ARIOEM_Block_Part_Selector_Print extends Mage_Core_Block_Template
{
	const ASSEMBLY_INFO_TEMPLATE = '%BRAND% %VEHICLE% %MODEL% (%YEAR%) - %ASSEMBLY%';

	protected $_info = array(
		'brand' => array(
			'code' => '',
			'name' => '',
			'hash' => '',
		),
		'vehicle' => array(
			'code' => '',
			'name' => '',
			'hash' => '',
		),
		'year' => array(
			'code' => '',
			'name' => '',
			'hash' => '',
		),
		'model' => array(
			'code' => '',
			'name' => '',
			'hash' => '',
		),
		'assembly' => array(
			'code' => '',
			'name' => '',
			'hash' => '',
		),
	);


	protected function _construct()
	{
		$this->setTemplate('arioem/part/selector/print.phtml');
		return parent::_construct();
	}



	public function setParams($params)
	{
		$this->setData('params', $params);
		$this->requestAssemblyInfo();
		return $this;
	}



	public function requestAssemblyInfo()
	{
		$params = $this->getParams();
		$params['action'] = 'state';

		$arioemConfig = array();
		require MAGENTO_ROOT . '/arioem/creds.php';

		$oem = new Vikont_ARIOEMAPI($arioemConfig);

		try {
			$this->_info = $oem->dispatch($params);
		} catch (Exception $e) {
			Mage::logException($e);
		}
		return $this;
	}



	public function getAssemblyImageUrl()
	{
		// index.php?action=image&brandCode=HOM&parentId=2773&assemblyId=69305&width=0&resizeBy=small
		$params = array(
			'action' => 'image',
			'brandCode' => $this->_info['brand']['code'],
			'parentId' => $this->_info['model']['code'],
			'assemblyId' => $this->_info['assembly']['code'],
			'width' => 0,
		);
		$url = $this->getUrl('', array('_direct' => 'arioem/index.php?' . http_build_query($params)));
		return $url;
	}



	public function getAssemblyInfo()
	{
		return $this->_info;
	}



	public function getAssemblyInfoFormatted($template = self::ASSEMBLY_INFO_TEMPLATE)
	{
		$res = str_replace('%BRAND%', $this->_info['brand']['name'], $template);
		$res = str_replace('%VEHICLE%', $this->_info['vehicle']['name'], $res);
		$res = str_replace('%YEAR%', $this->_info['year']['name'], $res);
		$res = str_replace('%MODEL%', $this->_info['model']['name'], $res);
		$res = str_replace('%ASSEMBLY%', $this->_info['assembly']['name'], $res);
		return $res;
	}



	public function getAssemblyUrl()
	{
		return $this->getUrl('arioem/partcenter') . '#' . sprintf('brand=%s&vehicle=%s&year=%s&model=%s&assembly=%s',
				urlencode($this->_info['brand']['hash']),
				urlencode($this->_info['vehicle']['hash']),
				urlencode($this->_info['year']['hash']),
				urlencode($this->_info['model']['hash']),
				urlencode($this->_info['assembly']['hash'])
			);
	}

}