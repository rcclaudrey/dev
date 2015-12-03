<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Model_AlekseonAdminNotification_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_ENABLED_PATH = 'alekseon_adminNotification/general/enabled';
    const XML_FREQUENCY_PATH = 'alekseon_adminNotification/general/frequency';
    const NOTIFICANTION_LASTCHECK_CACHE_KEY = 'alekseon_notifications_lastcheck';

    protected $_alekseonInstalledModules;
    
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = Mage::helper('alekseon_modulesConflictDetector')->getAlekseonUrl() . '/rss/magento_rss.xml';
            $query = '?utm_source=' . urlencode(Mage::getStoreConfig('web/unsecure/base_url'));
            $query .= '&utm_medium=' . urlencode('Magento Connect');
            if (method_exists('Mage', 'getEdition')) {
                $query .= '&utm_content=' . urlencode(Mage::getEdition() . ' ' . Mage::getVersion());
            } else {
                $query .= '&utm_content=' . urlencode(Mage::getVersion());
            }
            $query .= '&utm_term=' . urlencode(implode(',', $this->_getAlekseonInstalledModules()));
            
            $this->_feedUrl .= $query;
        }
      
        return $this->_feedUrl;
    }
    
    public function checkUpdate()
    {
        if (!Mage::getStoreConfig(self::XML_ENABLED_PATH)) {
            return $this;
        }
    
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();
        $feedXml = $this->getFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
            
                $module = (string)$item->module;
                if ($module && !in_array($module, $this->_getAlekseonInstalledModules())) {
                    continue;
                }
            
                $feedData[] = array(
                    'severity'      => (int)$item->severity,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }

            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }

        }
        $this->setLastUpdate();

        return $this;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache(self::NOTIFICANTION_LASTCHECK_CACHE_KEY);
    }
    
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), self::NOTIFICANTION_LASTCHECK_CACHE_KEY);
        return $this;
    }
    
    protected function _getAlekseonInstalledModules()
    {
        if (is_null($this->_alekseonInstalledModules)) {
            $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
            $this->_alekseonInstalledModules = array();
            foreach ($modules as $moduleName) {
                if (substr($moduleName, 0, 9) == 'Alekseon_'){
                    $this->_alekseonInstalledModules[] = $moduleName;
                }
            }
        }
        return $this->_alekseonInstalledModules;
    }
}
