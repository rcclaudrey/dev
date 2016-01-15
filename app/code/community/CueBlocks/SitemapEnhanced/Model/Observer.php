<?php

/**
 * Description of Observer
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */

/**
 * SitemapEnhanced module observer
 *
 * @category   Mage
 * @package    Mage_Sitemap
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class CueBlocks_SitemapEnhanced_Model_Observer
{
    /**
     * Cronjob expression configuration
     */

    const XML_PATH_CRON_EXPR = 'crontab/jobs/generate_sitemapEnhanced/schedule/cron_expr';

    /**
     * Generate sitemaps
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function scheduledGenerateSitemaps($schedule)
    {

        $errors = array();
        $helper = Mage::helper('sitemapEnhanced');
        /* @var $helper CueBlocks_SitemapEnhanced_Helper_Data */

        $collection = Mage::getModel('sitemapEnhanced/sitemapEnhanced')->getCollection();
        /* @var $collection Mage_Sitemap_Model_Mysql4_Sitemap_Collection */
        foreach ($collection as $sitemap)
        {
            /* @var $sitemap Mage_Sitemap_Model_Sitemap */

            $storeId = $sitemap->getStoreId();
            $genConf = $helper->getGenerateConf($storeId, true);

// check if scheduled generation enabled
            if (!$genConf->getEnabled()) {
                return;
            }

            $domain = Mage::app()
                    ->getStore($storeId)
                    ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

            try
            {
                $sitemap->generateXml(true);
            } catch (Exception $e)
            {
                $errors[] = $e->getMessage();
                Mage::log('ERR:' . $e->getMessage());
            }

//            if (Mage::getStoreConfigFlag(self::XML_PATH_SUBMIT_ENABLED)) {

            try
            {
                $retSub = explode('<br/>', $sitemap->ping());
                $retSub = array_merge(array('Files Submitted: '), $retSub);
            } catch (Exception $e)
            {
                $errors[] = $e->getMessage();
                Mage::log('ERR:' . $e->getMessage());
            }

            $freq = '';
            switch ($genConf->getFrequency())
            {
                case 'D':
                    $freq = 'Daily';
                    break;
                case 'W':
                    $freq = 'Weekly';
                    break;
                case 'M':
                    $freq = 'Monthly';
                    break;
            }
            //send Email Report
            $helper->sendEmailTemplate($storeId, array('sitemap'   => $sitemap, 'frequency' => $freq));
        }
    }

}
