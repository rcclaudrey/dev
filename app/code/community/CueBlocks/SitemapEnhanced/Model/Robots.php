<?php

/**
 * Description of Robots
 * @package   CueBlocks_SitemapEnhanced
 * @company    CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Robots
{

    protected $disallowRules;
    protected $sitemaps;
    protected $parsed;

    Const ADDROBOTS_FAILD   = 0;
    Const ADDROBOTS_ADDED   = 1;
    Const ADDROBOTS_ALREADY = 2;

//    function fileType($theString, $theType)
//    {
//        $type = "";
//
//        if (substr($theString, -1) == "/") {
//            $type = "D";
//        }
//
//        if ($type == "") {
//            $parts = pathinfo($theString);
//            if (( isset($parts["extension"])) && ( $parts["extension"] != "" )) {
//                $type = "F";
//            } else {
//                $type = "B";
//            }
//        }
//
//        switch ($type)
//        {
//            case "B":
//                return TRUE;
//                break;
//
//            case "F":
//            case "D":
//                if ($type == $theType) {
//                    return TRUE;
//                } else {
//                    return FALSE;
//                }
//                break;
//        }
//    }

    protected function _readFile()
    {
        $path     = BP . DS;
        $filename = 'robots.txt';
        $filepath = $path . $filename;

        if (file_exists($filepath)) {
            $fh          = fopen($filepath, "r");
            $fileContent = file_get_contents($filepath);
            fclose($fh);
        }
        else
            $fileContent = '';

        return $fileContent;
    }

    protected function _parseContent()
    {
        $robot = str_replace("\r", "", $this->_readFile());
        $robot = explode("\n", $robot);

        $userAgent     = "";
        $disallowArray = array();
        $sitemapArray = array();

        $this->disallowRules = '';
        $this->sitemaps = '';

        while (list( $key, $line ) = each($robot))
        {
            if (preg_match('/^user-agent:\\s*([^\\r\\n#]+)/i', $line, $match)) {
                $userAgent = $match[1];
            }

            if (preg_match('/^disallow:\\s*([^\\r\\n#]+)/im', $line, $match)) {
                $Disallow = trim($match[1]);

                if (( $Disallow != "" ) && ( $userAgent != "" )) {
//                    if ($this->fileType($Disallow, "F")) {
//                        $disallowArray[$userAgent]["files"][] = preg_quote(trim($Disallow), '/');
//                    }
//                    if ($this->fileType($Disallow, "D")) {
//                        $disallowArray[$userAgent]["dirs"][] = preg_quote(trim($Disallow), '/');
//                    }

                    $disallowArray[$userAgent][] = preg_quote(trim($Disallow), '/');
                }
            }
            if (preg_match('/^sitemap:\\s*([^\\r\\n#]+)/im', $line, $match)) {
                $Sitemap = trim($match[1]);
                if ($Sitemap != "") {
                    $sitemapArray[] = trim($Sitemap);
                }
            }
        }

        $this->disallowRules = $disallowArray;
        $this->sitemaps = $sitemapArray;
        $this->parsed = true;

        return TRUE;
    }

    public function getDisallow($robotsName = null)
    {

        if (!$this->parsed) {
            $this->_parseContent();
        }

        if ($robotsName) {
            if (isset($this->disallowRules[$robotsName])) {
                return $this->disallowRules[$robotsName];
            }
        } else if (isset($this->disallowRules['*'])) {
            return $this->disallowRules['*'];
        }
        return false;
    }

    public function isAllowed($url)
    {
        $rules = $this->getDisallow();

        if ($rules && count($rules)) {

            $parsed = parse_url($url);

            foreach ($rules as $rule)
            {
                if (preg_match("/^$rule/", $parsed['path']))
                    return false;
            }
        }

        return true;
    }

    public function getSitemaps()
    {

        if (!$this->parsed) {
            $this->_parseContent();
        }

        return $this->sitemaps;
    }

    public function isSitemap($url)
    {
        foreach ($this->getSitemaps() as $site)
        {
            if ($url == $site)
                return true;
        }
        return false;
    }

    public function addSitemap($url)
    {
        if ($this->isSitemap($url))
            return self::ADDROBOTS_ALREADY;
        else {
            $path     = BP . DS;
            $filename = 'robots.txt';
            $filepath = $path . $filename;

            $folderwrite = is_writable($path);
            $write       = file_exists($filepath) ? is_writable($filepath) : true;
            if ($folderwrite) {
                if ($write) {

                    $fh = fopen($filepath, "a");
                    fwrite($fh, 'Sitemap: ' . $url . "\n");
                    fclose($fh);
                    return self::ADDROBOTS_ADDED;
                }
            }
        }
        return self::ADDROBOTS_FAILD;
    }

    public function hasPermission()
    {

        return ($this->hasReadPermission() & $this->hasWritePermission());
    }

    public function hasReadPermission()
    {
        $path     = BP . DS;
        $filename = 'robots.txt';
        $filepath = $path . $filename;


        $folderread = is_readable($path);
        $read       = file_exists($filepath) ? is_readable($filepath) : true;

        return ($folderread & $read );
    }

    public function hasWritePermission()
    {
        $path     = BP . DS;
        $filename = 'robots.txt';
        $filepath = $path . $filename;

        $folderwrite = is_writable($path);
        $write = file_exists($filepath) ? is_writable($filepath) : true;

        return ($folderwrite & $write );
    }

}