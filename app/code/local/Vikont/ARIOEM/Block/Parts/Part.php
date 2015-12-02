<?php

class Vikont_ARIOEM_Block_Parts_Part extends Mage_Core_Block_Template
{
	protected $_brand = null;
	protected $_brandCode = null;
	protected $_partNumber = null;


	protected function _construct()
	{
		$this->setTemplate('arioem/parts/part.phtml');
		$this->parseUrl();

		return parent::_construct();
	}



	public function getQuery()
	{
		return $this->hasData('query')
			?	$this->getData('query')
			:	$_SERVER['QUERY_STRING'];
	}



	public function parseUrl()
	{
		//	staging.tmsparts.com/arioem/parts?honda/13101-MEN-A70
		$parts = explode('/', $this->getQuery());
		$this->_brand = reset($parts);
		$this->_brandCode = Mage::helper('arioem')->brandName2Code($this->_brand);
		$this->_partNumber = end($parts); //count($parts) ? end($parts) : false;
		$this->_partNumber = str_replace(array(':', '/', '\\', '"', '\''), '', $this->_partNumber);

		return $this;
	}



	public function getBrand()
	{
		return $this->_brand;
	}



	public function getBrandCode()
	{
		return $this->_brandCode;
	}



	public function getPartNumber()
	{
		return $this->_partNumber;
	}



	public function getPartInfo()
	{
//	http://partstream.arinet.com/Search?
//	cb=jsonp1448652343912
//	&arib=HOM
//	&part=13101-MEN-A70
//	&page=1
//	&arik=9oiOWqDlvNLyUXT4Qtun
//	&aril=en-US
//	&ariv=http%253A%252F%252Ftmsparts.com%252Fvk%252Foem-test.html


		$response = Mage::helper('arioem/api')->request('Search', array(), array(
			'arib' => $this->getBrandCode(),
			'part' => $this->getPartNumber(),
			'arik' => Mage::getStoreConfig('arioem/api/app_key'),
			'ariv' => Mage::getStoreConfig('arioem/api/referer_url'),
		));
//vd($response);

		$partInfo = false;

		if(isset($response['html']) && $response['html']) {
			$content = Mage::helper('arioem')->decodeHTMLResponse($response['html']);

			$dom = new DOMDocument;
			$dom->loadHTML($content);

			$searchTableBody = $dom->getElementById('ari_searchResults_GridBody');
			if(!$searchTableBody) {
				throw new Exception('No #ari_searchResults_GridBody element found in the response');
			}

			foreach($searchTableBody->childNodes as $tableRow) {
				$partInfo = array();

				foreach($tableRow->childNodes as $rowCell) {
//	ari_searchResults_Column_Content_PartNum
//	ari_searchResults_Column_Content_IsSuperceeded
//	ari_searchResults_Column_Content_Assembly
//	ari_searchResults_Column_Content_WhereUsed
//	ari_searchResults_Column_Content_Price x 2 MSRP / Online Price
//	ari_searchResults_Column_Content_AddToCart name="...?...&ariprice=107.70..."
					if(!$rowCell->attributes) continue;
					$className = $rowCell->attributes->getNamedItem('class')->value;
					$cellPurpose = strtolower(substr(stristr($className, 'ari_searchresults_column_content_'), strlen('ari_searchresults_column_content_')));
					$cellValue = trim($rowCell->textContent, "\n\l\r ");
					$partInfo[$cellPurpose] = $cellValue;
				}

				unset($partInfo['whereused']);
				unset($partInfo['addtocart']);
//				unset($partInfo['']);
			}
		} else {
			// no response or error response received
		}

		return $partInfo;
	}



	/*
	 * This is reserved for grabbing the model name from the URL, once provided
	 */
	public function getModel()
	{
	}



	public function getModels()
	{
// http://partstream.arinet.com/
// Search/GetModelSearchModelsForPrompt
// ?cb=jsonp1448918621924
// &arib=HOM
// &arisku=13101-MEN-A70
// &modelName=
// &arik=9oiOWqDlvNLyUXT4Qtun
// &aril=en-US
// &ariv=http%253A%252F%252Ftmsparts.com%252Fvk%252Foem-test.html
// ariPsSearchResultsByPartPromptContentItem
		$response = Mage::helper('arioem/api')->request('Search/GetModelSearchModelsForPrompt', array(), array(
			'arib' => $this->getBrandCode(),
			'arisku' => $this->getPartNumber(),
			'arik' => Mage::getStoreConfig('arioem/api/app_key'),
			'ariv' => Mage::getStoreConfig('arioem/api/referer_url'),
		));

		$result = array();

		if(isset($response['html']) && $response['html']) {
			$content = Mage::helper('arioem')->decodeHTMLResponse($response['html']);

			$dom = new DOMDocument;
			$dom->loadHTML($content);

			$modelsContent = $dom->getElementById('ariPSSearchResultsPromptDataContainer');
			if(!$modelsContent) {
				throw new Exception('No #ariPSSearchResultsPromptDataContainer element found in the response');
			}

			foreach($modelsContent->getElementsByTagName('a') as $anchor) {
				if('ariPsSearchResultsByPartPromptContentItem' != $anchor->attributes->getNamedItem('class')->value) {
					continue;
				}

				$modelId = $anchor->attributes->getNamedItem('modelid')->value;
				$slug = $anchor->attributes->getNamedItem('slug')->value;
				$modeluTag = $anchor->attributes->getNamedItem('modelutag')->value;
				$modelName = '';

				$childrenSpan = $anchor->getElementsByTagName('span');
				if($childrenSpan->length) {
					$span = $childrenSpan->item(0);
					$modelName = $span->textContent;
				}

				$result[$modelId] = array(
					'tag' => $modeluTag,
					'slug' => $slug,
					'name' => $modelName,
				);
			}
		} else {
			// no response received
		}

		return $result;

	}



	public function getPartsAssemblies()
	{
//	http://partstream.arinet.com/Search/GetModelSearchPartModelAssemblies
//	?cb=jsonp1448959106400
//	&arib=HOM
//	&arisku=13101-MEN-A70
//	&arim=yFdXLJ6Qe0P2z9iCo64rJA2
//	&arik=9oiOWqDlvNLyUXT4Qtun
//	&aril=en-US
//	&ariv=http%253A%252F%252Ftmsparts.com%252Fvk%252Foem-test.html


	}



	public function getBarcode()
	{

	}

}