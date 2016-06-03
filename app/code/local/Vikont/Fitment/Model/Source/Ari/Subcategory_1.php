<?php

class Vikont_Fitment_Model_Source_Ari_Subcategory extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	const CACHE_FILENAME = 'vk/subcategories-all.json';


	public function getErrorMessageOptions()
	{
		return array(array(
					'value' => 0,
					'label' => Mage::helper('core')->__('-- Cannot get category list --'),
				)
			);
	}



	public function getAllOptions($withEmpty = true)
	{
		if(!$this->_options) {
			$cachedValue = $this->_readCacheFromFile();

			if($cachedValue) {
				$this->_options = json_decode($cachedValue, true);
			} else {
				$this->_options = array();

				$activities = Mage::helper('fitment/api')->request('activities');
				if(!is_array($activities)) {	return $this->getErrorMessageOptions();	}

				foreach($activities as $activity) {
					$categories = Mage::helper('fitment/api')->request('categories', array($activity['Id']));
					if(!$categories) {	return $this->getErrorMessageOptions();	}

					foreach($categories as $category) {
						$subCategories = Mage::helper('fitment/api')->request('subcategories', array($activity['Id'], $category['Id']));
						if(!$subCategories) {	return $this->getErrorMessageOptions();	}

						foreach($subCategories as $subCategory) {
							$this->_options[] = array(
								'value' => $subCategory['Id'],
								'label' => sprintf('%s: %s / %s', $activity['Name'], $category['Name'], $subCategory['Name']),
							);
						}
						$this->_saveCacheToFile(json_encode($this->_options));
					}
				}
			}
		}

		if ($withEmpty) {
			$options = $this->_options;

			if(is_array($options)) {
				array_unshift($options, array(
					'value' => 0,
					'label' => Mage::helper('core')->__('-- Please Select --'),
				));
			} else {
				$options = $this->getErrorMessageOptions();
			}

			return $options;
        }

		return $this->_options;
	}



	public function toOptionArray()
	{
		return $this->getAllOptions();
	}



	protected function _getFileName()
	{
        return Mage::getBaseDir('cache') . DS . self::CACHE_FILENAME;
	}



	protected function _saveCacheToFile($contents)
	{
		$fileName = $this->_getFileName();

		if(!file_exists($dirName = dirname($fileName))) {
			mkdir($dirName, 0777, true);
		}

		if ($f = fopen($fileName, 'w')) {
			fwrite($f, $contents);
			fclose($f);
		}
	}



	protected function _readCacheFromFile()
	{
		$fileName = $this->_getFileName();

		if(file_exists($fileName)) {
			if ($f = fopen($fileName, 'r')) {
				$res = fgets($f);
				fclose($f);
				return $res;
			}
		}

		return null;
	}

}