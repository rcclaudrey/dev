<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


$installer = new Mage_Eav_Model_Entity_Setup($this->_resourceName);
$installer->startSetup();
$installer->updateAttribute('catalog_product', 'amconf_simple_price', 'apply_to', 'configurable');
$installer->endSetup();
