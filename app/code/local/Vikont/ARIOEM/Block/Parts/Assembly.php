<?php

class Vikont_ARIOEM_Block_Parts_Assembly extends Mage_Core_Block_Template
{
	protected $_params = null;


	protected function _construct()
	{
		$this->setTemplate('arioem/parts/assembly.phtml');
		return parent::_construct();
	}



	public function setParams($data)
	{
		$this->_params = new Varien_Object($data);
		return $this;
	}



	public function getParam($paramName)
	{
		return $this->_params->getData($paramName);
	}



	public function getAssemblyList()
	{
//http://partstream.arinet.com/Search/GetModelSearchPartModelAssemblies?
//arib=KUS
//&arisku=130BA0612
//&arim=VowrgAuVe_bap6qU3oBm0w2
//&arik=9oiOWqDlvNLyUXT4Qtun
//&ariv=http%253A%252F%252Ftmsparts.com%252Fvk%252Foem-test.html

		$response = Mage::helper('arioem/api')->request('Search/GetModelSearchPartModelAssemblies', array(), array(
			'arib' => $this->getParam('arib'),
			'arisku' => $this->getParam('arisku'),
			'arim' => $this->getParam('arim'),
			'arik' => Mage::getStoreConfig('arioem/api/app_key'),
			'ariv' => Mage::getStoreConfig('arioem/api/referer_url'),
		));

		$items = array();

		if(isset($response['html']) && $response['html']) {
			$content = Mage::helper('arioem')->decodeHTMLResponse($response['html']);

			$dom = new DOMDocument;
			libxml_use_internal_errors(true);
			$dom->loadHTML($content);
			libxml_clear_errors();
			$assemblyList = $dom->getElementsByTagName('a');

			foreach($assemblyList as $item) {
				$assemblyInfo = array();

				if(!$item->attributes) continue;
				if('ariPsSearchResultsPromptPartModelAssembly' != $item->attributes->getNamedItem('class')->value) continue;

				$assemblyInfo['slugA'] = $item->attributes->getNamedItem('sluga')->value;
				$assemblyInfo['name'] = trim($item->textContent, " \l\t\r\n");

				$items[] = $assemblyInfo;
			}
		} else {
			// no response or error response received
		}

		return $items;
	}


}