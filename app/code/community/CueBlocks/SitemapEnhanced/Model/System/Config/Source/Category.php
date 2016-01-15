<?php

/**
 * Description of Category
 * @package   CueBlocks_SitemapEnhanced
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Model_System_Config_Source_Category
{

    public function toOptionArray($fieldType, $scope = null, $scope_id = null)
    {
        /*
         * @see Mage_Catalog_Model_Resource_Category_Tree 
         */
        $tree = Mage::getResourceModel('catalog/category_tree');

        $collection = $tree->getCollection('path');
        $collection->addAttributeToFilter('level', array('nin' => array(0, 1)));

        if ($scope == 'stores') {
            $rootCatId   = Mage::getModel('core/store')->load($scope_id)->getRootCategoryId();
            $rootCatPath = Mage::getModel('catalog/category')->load($rootCatId)->getPath();
            $collection->addAttributeToFilter('path', array('like' => $rootCatPath . '/%'));
        }

        $collection->load();
        $options = array();
        
        foreach ($collection as $category)
        {
            $level     = $category->getLevel();
            $strLevel  = str_repeat(' - ', $level) . '> ';
            $options[] = array(
                'label' => $strLevel . $category->getName(),
                'value' => $category->getId()
            );
        }

        return $options;
    }

}
