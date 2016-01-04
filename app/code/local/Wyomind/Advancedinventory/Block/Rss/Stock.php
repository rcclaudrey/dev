<?php

class Wyomind_Advancedinventory_Block_Rss_Stock extends Mage_Rss_Block_Abstract {

    /**
     * Cache tag constant for feed notify stock
     *
     * @var string
     */
    const CACHE_TAG = 'block_html_rss_catalog_notifystock';

    /**
     * Constructor
     *
     * @return null
     */
    protected function _construct() {
        $wh_id = $this->getRequest()->getParam('wh');
        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();
        $pos = $permissions->getPos();

        if (in_array($wh_id, $pos) || $all) {
            $this->setCacheTags(array(self::CACHE_TAG));

            $this->setCacheKey('advancedinventory_rss_stock' . $wh_id);
            $this->setCacheLifetime(600);
        } else {
            die('Access denied');
        }
    }

    /**
     * Render RSS
     *
     * @return string
     */
    protected function _toHtml() {
        $wh_id = $this->getRequest()->getParam('wh');
        $newUrl = Mage::getUrl('advancedinventory/rss/stock', array("wh" => $wh_id));
        $name = Mage::getModel("pointofsale/pointofsale")->load($wh_id)->getName();
        $title = Mage::helper('rss')->__('Low Stock Products for ' . $name);

        $rssObj = Mage::getModel('rss/rss');
        $data = array(
            'title' => $title,
            'description' => $title,
            'link' => $newUrl,
            'charset' => 'UTF-8',
        );
        $rssObj->_addHeader($data);


        $globalNotifyStockQty = (float) Mage::getStoreConfig(
                        Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY);

        Mage::helper('rss')->disableFlat();

        $product = Mage::getModel('catalog/product');
        $collection = $product->getCollection();
        $stockItemTable = $collection->getTable('cataloginventory/stock_item');


        $collection
                ->addAttributeToSelect('name', true)
                ->joinTable('advancedinventory/stock', 'product_id=entity_id', array(
                    'qty' => 'quantity_in_stock'
                        ), "place_id='.$wh_id.' AND manage_stock=1 AND quantity_in_stock<$globalNotifyStockQty", 'inner')
                ->setOrder('qty');

        $collection->addAttributeToFilter('status', array('in' => Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));


        Mage::getSingleton('core/resource_iterator')->walk(
                $collection->getSelect(), array(array($this, 'addNotifyItemXmlCallback')), array('rssObj' => $rssObj, 'product' => $product, 'globalQty' => $globalNotifyStockQty)
        );

        return $rssObj->createRssXml();
    }

    /**
     * Adds single product to feed
     *
     * @param array $args
     * @return void
     */
    public function addNotifyItemXmlCallback($args) {
        $product = $args['product'];
        $product->setData($args['row']);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product/edit/', array('id' => $product->getId(), '_secure' => true, '_nosecret' => true));
        $qty = 1 * $product->getQty();
        $description = Mage::helper('rss')->__('%s has reached a quantity of %s.', $product->getName(), $qty);
        $rssObj = $args['rssObj'];
        $data = array(
            'title' => $product->getName(),
            'link' => $url,
            'description' => $description,
        );
        $rssObj->_addEntry($data);
    }

}
