<?php

/**
 * Description of Ping
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_Pings extends Mage_Core_Model_Abstract
{

    /**
     * helper
     *
     * @var CueBlocks_SitemapEnhanced_Helper_Data
     */
    protected $_helper;

    public function getHelper()
    {
        if (!$this->_helper)
            $this->_helper = Mage::helper('sitemapEnhanced');

        return $this->_helper;
    }

    public function ping($sitemap)
    {
        $subConf = $this->getHelper()->getPingConf($sitemap->getStoreId());
        $searchEngine = explode(',', $subConf->getSearchEngine());

//        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $item->getSitemapFileFilename());
        $fileUrl = $sitemap->getLinkForRobots();

        $path = $sitemap->getPath() . $fileUrl['filename'];
        $url = $fileUrl['url'];

        if (file_exists($path)) {

            $msg = "";

// check yahoo key
            if (!$subConf->getYahooKey() && ($key = array_search("Yahoo", $searchEngine))) {
                unset($searchEngine[$key]);
                $msg = $msg . '- Skipping Yahoo. (You need to provide an API key to submit your sitemap to Yahoo)<br/>';
            }

            foreach ($searchEngine as $engineName) {
                $methodName = '_ping' . $engineName;

//                if (method_exists($methodName)) {

                if ($code = call_user_func(array($this, $methodName), $url)) {
                    $msg = $msg . '- Sent to ' . $engineName . ' (CODE: ' . $code . ') - OK <br/>';
                } else {
                    $msg = $msg . '- Failed to sent to ' . $engineName . '- check system.log for detail. <br/>';
                }
//                }
            }
            return 'url: ' . $url . "<br/>" . $msg;

        } else {
            throw new Mage_Core_Exception("Sitemap could not be found in required location: " . $path);
        }
    }

    protected function _pingGoogle($url)
    {
        $ping = "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . urlencode($url);
        return $this->_makeRequest($ping);
    }

    protected function _pingBing($url)
    {
        $ping = "http://www.bing.com/webmaster/ping.aspx?siteMap=" . urlencode($url);
        return $this->_makeRequest($ping);
    }

    protected function _pingAsk($url)
    {
        $ping = "http://submissions.ask.com/ping?sitemap=" . urlencode($url);
        return $this->_makeRequest($ping);
    }

    protected function _pingMoreOver($url)
    {
        $ping = "http://api.moreover.com/ping?u=sitemap=" . urlencode($url);
        return $this->_makeRequest($ping);
    }

    protected function _makeRequest($ping)
    {

        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout' => 20
        ));

        $curl->write(Zend_Http_Client::GET, $ping, '1.1');
        $data = $curl->read();

        if ($data === false) {
            return false;
        }

        $code = $curl->getInfo(CURLINFO_HTTP_CODE);

        if ($code == 200) {
            return $code;
        } else {
            Mage::log("Submission to: " . $ping . " failed, HTTP response code was not 200");
            Mage::log("Response error: " . $data); // uncomment to debug raw submission response
            return false;
        }

//TODO: handle timeout?
    }
}
