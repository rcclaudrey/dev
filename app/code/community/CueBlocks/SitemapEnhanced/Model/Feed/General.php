<?php

/**
 * CueBlocks
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * Description of Feed
 * @category    CueBlocks
 * @package     CueBlocks_
 * @developer   Francesco Magazzu' <francesco.magazzu at cueblocks.com>
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * */
class CueBlocks_SitemapEnhanced_Model_Feed_General extends CueBlocks_SitemapEnhanced_Model_Feed_Abstract {

    const XML_FEED_URL = 'store.cueblocks.com/magento_notifications/';
    const XML_FEED_FILENAME = 'feed_general.rss';
    const XML_LASTCHECK = 'cb_general_notifications_lastcheck';

    /**
     * Feed url
     *
     * @var string
     */
    protected $_feedUrl;

    public function getFeedUrl() {
        if (is_null($this->_feedUrl)) {

//            $this->_feedUrl = (Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://');
            $this->_feedUrl = 'http://';
            $this->_feedUrl .= self::XML_FEED_URL . self::XML_FEED_FILENAME;
        }
        return $this->_feedUrl;
    }

    public function feedFetch() {
        $this->checkUpdate();
    }

    public function setLastUpdate() {
        Mage::app()->saveCache(time(), self::XML_LASTCHECK);
        return $this;
    }

    public function getLastUpdate() {
        return Mage::app()->loadCache(self::XML_LASTCHECK);
    }

}
