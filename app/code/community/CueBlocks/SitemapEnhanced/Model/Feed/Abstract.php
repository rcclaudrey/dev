<?php

class CueBlocks_SitemapEnhanced_Model_Feed_Abstract extends Mage_AdminNotification_Model_Feed {

    /**
     * Check feed for modification
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function checkUpdate() {

        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();
        $feedXml = $this->getFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity' => (int) $item->severity,
                    'date_added' => $this->getDate((string) $item->pubDate),
                    'title' => (string) $item->title,
                    'description' => (string) $item->description,
                    'url' => (string) $item->link,
                );
            }

            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

}
