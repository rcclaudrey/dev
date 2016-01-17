<?php

class Vikont_ARIOEM_Model_Oem_Part
{
	protected $_brand = null;
	protected $_brandCode = null;
	protected $_partNumber = null;
	protected $_partInfo = null;
	protected $_models = null;

	protected static $_brands = array(
		'canam' => 'Can Am',
		'can-am' => 'Can Am',
		'honda' => 'Honda',
		'hondape' => 'Honda PE',
		'honda-pe' => 'Honda PE',
		'kawasaki' => 'Kawasaki',
		'polaris' => 'Polaris',
		'sea-doo' => 'Sea-Doo',
		'seadoo' => 'Sea-Doo',
		'suzuki' => 'Suzuki',
		'victory' => 'Victory',
		'yamaha' => 'Yamaha',
	);



	public function getBrand()
	{
		if(null === $this->_brand) {
			$brand = strtolower(Mage::registry('oem_brand'));
			if(!$brand) {
				$brand = Mage::app()->getRequest()->getParam('brand');
			}
			$this->_brand = $brand;
			$this->_brandCode = Mage::helper('arioem')->brandName2Code($this->_brand);
		}

		return $this->_brand;
	}



	public function setBrand($value)
	{
		$this->_brand = $value;
		return $this;
	}



	public function getBrandName()
	{
		$this->getBrand();

		return isset(self::$_brands[$this->_brand])
			?	self::$_brands[$this->_brand]
			:	$this->_brand;
	}



	public function getBrandCode()
	{
		$this->getBrand();
		return $this->_brandCode;
	}



	public function getPartNumber()
	{
		if(null === $this->_partNumber) {
			$partNumber = strtoupper(Mage::registry('oem_part_number'));
			if(!$partNumber) {
				$partNumber = Mage::app()->getRequest()->getParam('partNumber');
			}
			$this->_partNumber = str_replace(array(':', '/', '\\', '"', '\''), '', $partNumber);
		}

		return $this->_partNumber;
	}



	public function setPartNumber($value)
	{
		$this->_partNumber = $value;
		return $this;
	}



	public function getPartInfo()
	{
		if(null === $this->_partInfo) {
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

			$partInfo = false;

			if(isset($response['html']) && $response['html']) {
				$content = Mage::helper('arioem')->decodeHTMLResponse($response['html']);

				$dom = new DOMDocument;
				libxml_use_internal_errors(true);
				$dom->loadHTML($content);
				libxml_clear_errors();

				$searchTableBody = $dom->getElementById('ari_searchResults_GridBody');
				if(!$searchTableBody) {
					Mage::logException(new Exception(sprintf('No #ari_searchResults_GridBody element found in the response for brand=%s part=%s', $this->getBrandCode(), $this->getPartNumber())));
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

						if($cellPurpose == 'price') {
							$cellValue = trim($cellValue, '$');

							if(!isset($partInfo['msrp'])) {
								$cellPurpose = 'msrp';
							}
						}

						$partInfo[$cellPurpose] = $cellValue;
					}

					unset($partInfo['whereused']);
					unset($partInfo['addtocart']);
				}
			} else {
				// no response or error response received
			}
			$this->_partInfo = $partInfo;
		}

		return $this->_partInfo;
	}



	public function getName()
	{
		$this->getPartInfo();
		return $this->_partInfo['assembly'];
	}



	public function getPrice($formatted = false)
	{
		$this->getPartInfo();
		return $formatted
			?	Mage::helper('core')->formatPrice($this->_partInfo['price'], false)
			:	$this->_partInfo['price'];
	}



	public function getModels()
	{
		if(null === $this->_models) {
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

			$this->_models = $result;
		}

		return $this->_models;

	}

}