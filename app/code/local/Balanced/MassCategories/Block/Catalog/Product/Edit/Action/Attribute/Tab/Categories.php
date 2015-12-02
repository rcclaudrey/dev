<?php
/**
 * Magento
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product mass attribute update websites tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
 
class Balanced_MassCategories_Block_Catalog_Product_Edit_Action_Attribute_Tab_Categories
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	//Balanced Functions
	public function getShowAmount()
    {
        return 20;
    }
	
	public function getTree() {
		$store = (int) $this->getRequest()->getParam('store');
		
		if ($store) {
			$Mystore = Mage::app()->getStore($store);
			$rootId = $Mystore->getRootCategoryId();
		}
		else {
			$rootId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
		}	
		
		$tree = Mage::getResourceSingleton('catalog/category_tree')->load();
		
		$root = $tree->getNodeById($rootId);
		
		if($root && $root->getId() == 1) {
			$root->setName(Mage::helper('catalog')->__('Root'));
		}
		
		$collection = Mage::getModel('catalog/category')->getCollection()
		->setStoreId($store)
		->addAttributeToSelect('name')
		->addAttributeToSelect('is_active');
		
		$tree->addCollectionData($collection, true);
		
		return "<ul>" . $this->BuildBranch($root) . "</ul>";
	}	
		
	public function BuildBranch(Varien_Data_Tree_Node $node) {
		$buildString = '<li style="padding-left: 16px;">';
		$buildString .= '<div class="tree-level-' . $node->getLevel() . '">';
		
		if($node->getChildrenCount()!=0) {
			$buildString .= '<div class="opener" id="opener' . $node->getId() .'"  OnClick="OpenMe(this)"></div>';
		}
		else {
			$buildString .= '<div class="child"></div>';
		}
		
		$buildString .= '
			<input type="checkbox" class="inputcb" id="inputcb' . $node->getId() .'" OnClick="Decide(this)" enabled="false"/>
			<div class="folder"></div>
			<a tabindex="1" href="#" hidefocus="on" id="linkItem"><span unselectable="on" id="extdd-' . $node->getLevel() . '">' . $node->getName() . '</a>
		';
		
		$buildString .= '</div>';
		
		if($node->getChildrenCount()!=0) {
			$buildString .= '<ul id="ToOpen' . $node->getId() .'">';
				
			foreach ($node->getChildren() as $child) {
				$buildString .=  $this->BuildBranch($child);
			}
			
			$buildString .= '</ul>';
		
		}
		
		$buildString .= '</li>';

		return $buildString;
	}

	//DO NOT TOUCH MAGENTO CRAP  *****************************************************************
    public function getWebsiteCollection()
    {
        return Mage::app()->getWebsites();
    }

    public function getGroupCollection(Mage_Core_Model_Website $website)
    {
        return $website->getGroups();
    }

    public function getStoreCollection(Mage_Core_Model_Store_Group $group)
    {
        return $group->getStores();
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('catalog')->__('Categories');
    }

    public function getTabTitle()
    {
        return Mage::helper('catalog')->__('Categories');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
	
	public function getProducts()
    {
        return $this->helper('adminhtml/catalog_product_edit_action_attribute')->getProducts();
    }
	
}
