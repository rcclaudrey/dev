<?php

/**
 * Description of Data
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_config = null;

    const PATH_TO_CONFIG = 'sitemap_enhanced';

    /**
     * return an object contaning the 'sitemapEnhanced' configuration value for $storeId
     * optional param $force is usefull to force the data reload ( and skip singleton patern data cache problem )
     *
     * @param int $storeId
     * @param bool $force
     * @return Varien_Object
     */
    public function getConfig($storeId, $force = false)
    {
        if (!$this->_config || $force) {
            $this->_config = new Varien_Object;

            $general = new Varien_Object;
            $category = new Varien_Object;
            $product = new Varien_Object;
            $prod_out = new Varien_Object;
            $prod_tag = new Varien_Object;
            $prod_review = new Varien_Object;
//            $prod_media    = new Varien_Object;
            $cms = new Varien_Object;
            $custom_pages = new Varien_Object;
            $generate = new Varien_Object;
            $ping = new Varien_Object;
            $advanced = new Varien_Object;

            $general->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/general', $storeId));
            $category->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/category', $storeId));
            $product->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/product', $storeId));
            $prod_out->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/prod_out', $storeId));
            $prod_tag->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/prod_tag', $storeId));
            $prod_review->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/prod_review', $storeId));
//            $prod_media->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/prod_media', $storeId));
            $cms->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/page', $storeId));
            $custom_pages->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/custom_page', $storeId));
            $generate->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/generate', $storeId));
            $ping->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/ping', $storeId));
            $advanced->setData(Mage::getStoreConfig(self::PATH_TO_CONFIG . '/advanced', $storeId));

            $this->_config->setGeneral($general);
            $this->_config->setCategory($category);
            $this->_config->setProduct($product);
            $this->_config->setProdOut($prod_out);
            $this->_config->setProdTag($prod_tag);
            $this->_config->setProdReview($prod_review);
//            $this->_config->setMedia($prod_media);
            $this->_config->setCms($cms);
            $this->_config->setCustomPages($custom_pages);
            $this->_config->setGenerate($generate);
            $this->_config->setPing($ping);
            $this->_config->setAdvanced($advanced);
        }
        return $this->_config;
    }

    // General Option group
    public function getGeneralConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getGeneral();
    }

    // Category Option group
    public function getCategoryConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getCategory();
    }

    // Product Option group
    public function getProductConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getProduct();
    }

    // Product Option group
    public function getProdOutConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getProdOut();
    }

    // Product Option group
    public function getProdTagConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getProdTag();
    }

    // Product Option group
    public function getProdReviewConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getProdReview();
    }

    // Product Option group
//    public function getMediaConf($storeId,$force=false)
//    {
//        return $this->getConfig($storeId,$force)->getProdMedia();
//    }
    // CMS Option group
    public function getCmsConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getCms();
    }

    // CMS Option group
    public function getCustomPagesConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getCustomPages();
    }

    // Generate Option group
    public function getGenerateConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getGenerate();
    }

    // Submit Option group
    public function getPingConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getPing();
    }

    // Advanced Option group
    public function getAdvancedConf($storeId, $force = false)
    {
        return $this->getConfig($storeId, $force)->getAdvanced();
    }

    /**
     * Send corresponding email template
     *
     * @param int $storeId
     * @param array $templateParams
     * @return Mage_Customer_Model_Customer
     */
    public function sendEmailTemplate($storeId, $templateParams = array())
    {
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');

        // workarround for 1.4 compatibility
        if (!($mailer && $emailInfo))
            return $this->sendEmailTemplateOld($storeId, $templateParams);

        $genConf = $this->getGeneralConf($storeId, true);
        $template = $genConf->getReportEmailTemplate();
        $sender = $genConf->getReportEmailIdentity();
        $recipientAddress = $genConf->getReportEmail();

        if (!trim($recipientAddress))
            $recipientAddress = Mage::getStoreConfig('trans_email/ident_general/email', $storeId);

        $arrRecipientAddress = explode(';', $recipientAddress);

        foreach ($arrRecipientAddress as $address) {
            $emailInfo->addTo($address);
        }

        $templateParams['recipiants'] = $recipientAddress;

        $sName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name', $storeId);
        $sAddress = Mage::getStoreConfig('trans_email/ident_' . $sender . '/email', $storeId);
        $templateParams['sender'] = $sAddress;

        $mailer->addEmailInfo($emailInfo);

// Set all required params and send emails
        $mailer->setSender($sender);
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($template);
        $mailer->setTemplateParams($templateParams);
        $mailer->send();
    }

    public function sendEmailTemplateOld($storeId, $templateParams = array())
    {

        $genConf = $this->getGeneralConf($storeId, true);
        $template = $genConf->getReportEmailTemplate();
        $sender = $genConf->getReportEmailIdentity();
        $recipientAddress = $genConf->getReportEmail();

        if (!trim($recipientAddress))
            $recipientAddress = Mage::getStoreConfig('trans_email/ident_general/email', $storeId);

        $arrRecipientAddress = explode(';', $recipientAddress);

        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area' => 'adminhtml', 'store' => $storeId))
            ->sendTransactional(
                $template, $sender, $recipientAddress, null, $templateParams);
    }

    public function isUnique($sitemap)
    {
        $id = $sitemap->getId();
        $path = str_replace("\\","/",$sitemap->getSitemapPath());
        if(substr($path,-1) !== '/') {
            $path.='/';
        }
        $filename = $this->clearExtension($sitemap->getSitemapFilename());

        $collection = $sitemap->getCollection()
            ->addFieldToFilter('sitemap_path', array('eq' => $path));

        if ($id != null)
            $collection->addFieldToFilter('sitemap_id', array('neq' => $id));

        foreach ($collection as $site) {
            if ($filename == $this->clearExtension($site->getSitemapFilename()))
                return false;
        }

        return true;
    }

    /**
     * Base Filename without extension
     *
     * @return string
     */
    public function clearExtension($filename)
    {
        $filename = str_replace(array('.xml.gz', '.xml'), '', $filename);

        return $filename;
    }

    /**
     * Return all product assigned to more than a category
     *
     * @param unknown_type $storeId
     * @return array
     */
    public static function getDoubleProduct($filterInvisible = false)
    {

        $show = true;
        $prodColl = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);

        foreach ($prodColl as $prod) {
            $v = $prod->getVisibility();
            $name = $prod->getName();
            $catColl = $prod->getCategoryIds();

            if ($filterInvisible)
                $show = ($v == 4) ? true : false;

            if (count($catColl) > 1 & $show)
                echo $name . ' ' . implode(',', $catColl) . ' visibility: ' . $v . '<br>';
        }
    }

    /**
     * Return the absolute path
     *
     * @param $relPath the relative path
     * @return string
     */
    public function fixRelative($relPath)
    {

//        $fixPath = preg_replace('/\w+\/\.\.\//', 'OO', $relPath);

        $path = str_replace(array('/', '\\'), DS, $relPath);
        $parts = array_filter(explode(DS, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DS . implode(DS, $absolutes) . DS;


//        return $fixPath;
    }

    /** Check if Magento is > CE 1.7 or EE 1.12
     *
     * @param $relPath the relative path
     * @return string
     */
    public function isMageAbove18()
    {
        $mage = new Mage;

        if (method_exists($mage, 'getEdition')) {
            $edition = Mage::getEdition();
        } else {
            // if 'getEdition' is not defined we are above CE 1.7
            return false;
        }
        $versionInfo = Mage::getVersionInfo();

        if (isset($versionInfo['minor']))

            if (($edition == 'Community' && $versionInfo['minor'] > 7)
                || ($edition == 'Enterprise' && $versionInfo['minor'] > 12)
            ) {
                return true;
            }

        return false;
    }
}
