<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Adminhtml_System_Config_Backend_Image_Noimg extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
    /**
     * The tail part of directory path for uploading
     * 
     */
    const UPLOAD_DIR = 'amconf/noimg';

    /**
     * Token for the root part of directory path for uploading
     * 
     */
    const UPLOAD_ROOT = 'system/filesystem/media';

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw Mage_Core_Exception 
     */
    protected function _getUploadDir()
    {
        $uploadDir = $this->_appendScopeInfo(self::UPLOAD_DIR);
        $uploadRoot = $this->_getUploadRoot(self::UPLOAD_ROOT);
        $uploadDir = $uploadRoot . '/' . $uploadDir;
        return $uploadDir;
    }

    /**
     * Makes a decision about whether to add info about the scope.
     * 
     * @return boolean 
     */
    protected function _addWhetherScopeInfo()
    {
        return true;
    }
}
