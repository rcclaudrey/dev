<?php

class Vikont_OEMGrid_Helper_Import extends Mage_Core_Helper_Abstract
{

	public function getUploadPath()
	{
		return MAGENTO_ROOT . DS . trim(Mage::getStoreConfig('oemgrid/import/upload_path'), DS);
	}



	public function getFileList()
	{
		$res = array();

		$dirName = $this->getUploadPath();

		if (file_exists($dirName)) {
			$files = scandir($dirName);
			if ($files) {
				foreach($files as $file) {
					if ('.' == $file || '..' == $file) continue;

					$fileName = $dirName . DS . $file;

					$res[] = array(
						'name' => $file,
						'size' => filesize($fileName),
						'created' => date('Y-m-d H:i', filemtime($fileName)),
					);
				}
			}
		}

		return $res;
	}



	public function findUploadedFileName($fileName)
	{
		$baseName = $this->getUploadPath() . DS . $fileName; 
		$newName = $baseName;
		$suffix = 1;

		while (file_exists($newName)) {
			$newName = $baseName . '_' . $suffix;
			$suffix++;
		}

		return $newName;
	}

}