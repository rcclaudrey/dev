<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    public function getShowTemplateHints()
    {
        if (is_null(self::$_showTemplateHints)) {
            self::$_showTemplateHints = Mage::getStoreConfig(self::XML_PATH_DEBUG_TEMPLATE_HINTS)
                && Mage::helper('core')->isDevAllowed();
            self::$_showTemplateHintsBlocks = true;
        }
        return true;
    }
}