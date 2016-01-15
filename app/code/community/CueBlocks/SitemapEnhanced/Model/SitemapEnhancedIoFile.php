<?php

/**
 * Description of SitemapEnhancedIoFile
 * @package   CueBlocks_SitemapEnhanced
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_SitemapEnhancedIoFile extends Varien_Io_File
{

    /**
     * File type: 'index' or 'sitemap'
     *
     * @var string
     */
    protected $_type;

    /**
     * File Size
     *
     * @var int
     */
    protected $_size;

    /**
     * Links counter
     *
     * @var int
     */
    protected $_links;

    /**
     * function definition
     *
     * @var string
     */
    protected $_openFunction  = 'fopen';
    protected $_writeFunction = 'fwrite';
    protected $_closeFunction = 'fclose';

    public function getLinks()
    {
        return $this->_links;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function increaseLinks($links)
    {
        $this->_links += $links;
    }

    public function increaseSize($bytes)
    {
        $this->_size += $bytes;
    }
    public function getType()
    {
        return  $this->_type;
    }

    public function streamOpen($fileName, $isCompressed = false, $type = 'sitemap', $mode = 'w+', $chmod = 0666)
    {
        $this->_links = 0;
        $this->_size = 0;
        $this->_type = $type;

        if ($type != 'index' && $isCompressed) {
            $this->_openFunction = 'gzopen';
            $this->_writeFunction = 'gzwrite';
            $this->_closeFunction = 'gzclose';
            $mode = 'w9';
        }

        $writeableMode = preg_match('#^[wax]#i', $mode);
        if ($writeableMode && !is_writeable($this->_cwd)) {
            throw new Exception('Permission denied for write to ' . $this->_cwd);
        }

        if (!ini_get('auto_detect_line_endings')) {
            ini_set('auto_detect_line_endings', 1);
        }

        @chdir($this->_cwd);
        $this->_streamHandler = call_user_func(@$this->_openFunction, $fileName, $mode);
        @chdir($this->_iwd);
        if ($this->_streamHandler === false) {
            throw new Exception('Error write to file ' . $fileName);
        }

        $this->_streamFileName = $fileName;
        $this->_streamChmod = $chmod;

        $this->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");

        if ($this->_type == 'sitemap')
            $this->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
        elseif ($this->_type == 'image')
            $this->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">');
        else
            $this->streamWrite('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        return true;
    }

    /**
     * Binary-safe file write
     *
     * @param string $str
     * @return bytes
     */
    public function streamWrite($str)
    {
        if (!$this->_streamHandler) {
            return false;
        }
        try
        {
            $bytes = call_user_func(@$this->_writeFunction, $this->_streamHandler, $str);
            $this->increaseSize($bytes);

            return $bytes;
        } catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Close an open file pointer
     * Set chmod on a file
     *
     * @return bool
     */
    public function streamClose()
    {
        if (!$this->_streamHandler) {
            return false;
        }

        if ($this->_streamLocked) {
            $this->streamUnlock();
        }

        if ($this->_type == 'sitemap' || $this->_type == 'image' )
            $this->streamWrite('</urlset>');
        else
            $this->streamWrite('</sitemapindex>');

        call_user_func(@$this->_closeFunction, $this->_streamHandler);

        $this->chmod($this->_streamFileName, $this->_streamChmod);
        $this->_streamHandler = null;

        return true;
    }

}

?>
