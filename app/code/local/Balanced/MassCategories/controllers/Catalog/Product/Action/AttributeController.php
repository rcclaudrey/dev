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
 * Catalog product attribute controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

include_once("Mage/Adminhtml/controllers/Catalog/Product/Action/AttributeController.php");

class Balanced_MassCategories_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Catalog_Product_Action_AttributeController
{ 
    public function saveAction()
    {
		//did they choose to change these products cats?
		$changeCats = $this->getRequest()->getParam('changeCats');
		
		if($changeCats) {
			if($changeCats == 'on') {
				$productIDs = $this->getRequest()->getParam('productToChange');

				//get an array of products
				$productIDs = explode(",", $productIDs); 
					
				$catIDs = $this->getRequest()->getParam('category_ids');
					
				if($catIDs){
					//turn this into an array of categories
					$catIDs = explode(", ", $catIDs); 
				}
				else
				{
					//if we unselected all of the cats then we need to just pass an array
					$catIDs = array();	
				}
				
				foreach($productIDs as $productID) {
					$product = Mage::getModel('catalog/product')->load($productID);
					$product->setCategoryIds($catIDs);
					try {
						$product->save();
					}
					catch (Exception $ex) {
					
					}

				}
			}
		}
		

         parent::saveAction();
    }
	
    // This is to fix an assumption in Magento that breaks translations
    protected function _getRealModuleName()
    {
        return "Balanced_MassCategories";
    }
}
