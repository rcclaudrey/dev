<?php

/**
 * Description of SitemapEnhancedFiles
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_SitemapEnhancedFiles extends Mage_Core_Model_Abstract
{

    /**
     * Io File Model
     *
     * @var CueBlocks_SitemapEnhanced_Model_SitemapEnhancedIoFile
     */
    protected $_io;

    public function _construct()
    {
        $this->_init('sitemapEnhanced/sitemapEnhancedFiles');
    }

    public function initIo($_isCompressed)
    {
        if ($this->_io == null) {
            $this->_io = Mage::getModel('sitemapEnhanced/sitemapEnhancedIoFile');
            $this->_io->setAllowCreateFolders(true);
            $this->_io->open(array('path' => $this->getSitemapFilePath()));

            if ($this->_io->fileExists($this->getSitemapFileFilename()) && !$this->_io->isWriteable($this->getSitemapFileFilename())) {
                Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $filename, $this->getPath()));
            }

            $this->_io->streamOpen($this->getSitemapFileFilename(), $_isCompressed, $this->getSitemapFileType());
        }
    }

    public function getIo()
    {
        if ($this->_io != null)
            return $this->_io;
    }

}

