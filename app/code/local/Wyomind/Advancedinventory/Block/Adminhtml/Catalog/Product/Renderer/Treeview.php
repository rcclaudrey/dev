<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Catalog_Product_Renderer_Treeview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected function _getStore() {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function render(Varien_Object $row) {

        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();
        $pos = $permissions->getPos();


        if (in_array($row->getTypeId(), array('simple', 'virtual', 'downloadable'))) {
            if (!$all) {
                $html = array();
                foreach ($pos as $p) {
                    $stock = Mage::getModel("advancedinventory/stock")->getStockByProductIdAndPlaceId($row->getId(), $p);
                    if ($stock->getManageLocalStock())
                        $html[] = "<span style='font-size:11px;'>" . Mage::getModel("pointofsale/pointofsale")->load($p)->getName() . " (" . $stock->getQuantityInStock() . ")</span> ";
                }
                if (!count($html))
                    return "-";
                return "<div style='text-align:left'>" . implode('<br>', $html) . "</div>";
            }

            $stock = Mage::getModel("advancedinventory/stock")->getMultiStockEnabledByProductId($row->getId());
            if ($stock) {
                echo "<script type='text/javascript'>"
                . " function myOpenPopulate() {
                        return true;
                    }"
                . "var struct= new Array;";
                if (Mage::app()->getRequest()->getParam('store') == Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $websites = Mage::app()->getWebsites();
                    foreach ($websites as $website) {
                        $w[$website->getId()] = array();
                        foreach ($website->getGroups() as $group) {
                            $g[$group->getId()] = array();
                            $stores = $group->getStores();
                            foreach ($stores as $store) {
                                $w[$website->getId()][] = $store->getId();
                                $g[$group->getId()][] = $store->getId();
                                $s[$store->getId()] = Mage::getModel("advancedinventory/stock")->getStockByProductIdAndStoresId($row->getId(), $store->getId())->getQty();
                            }
                        }
                    }




                    foreach ($websites as $website) {
                        $qty = (int) Mage::getModel("advancedinventory/stock")->getStockByProductIdAndStoresId($row->getId(), $w[$website->getId()])->getQty();
                        echo "struct.push(
                                {
                                    'id': 'w" . $website->getId() . "-p" . $row->getId() . "',
                                    'txt': '<b><u>" . $website->getName() . " (" . $qty . ")</u></b>',
                                    'onopenpopulate' : myOpenPopulate,
                                    'openlink' : '" . Mage::getUrl('advancedinventory/adminhtml_stocks/treeview', array("type" => "storegroup", 'instanceid' => $website->getId(), "productid" => $row->getId())) . "',
                                    'canhavechildren' : true
                                }
                            );";
                    }
                } else {


                    $storeId = Mage::app()->getRequest()->getParam('store');
                    $qty = Mage::getModel("advancedinventory/stock")->getStockByProductIdAndStoresId($row->getId(), $storeId)->getQty();

                    echo "struct.push(
                         {
                                    'id': 's" . $storeId . "-p" . $row->getId() . "',
                                    'txt': '" . Mage::app()->getStore($storeId)->getName() . " (" . (int) $qty . ")',
                                    'onopenpopulate' : myOpenPopulate,
                                    'openlink' : '" . Mage::getUrl('advancedinventory/adminhtml_stocks/treeview', array("type" => "pos", 'instanceid' => $storeId, "productid" => $row->getId())) . "',
                                    'canhavechildren' : true
                                });";
                }
                echo "</script>";
                echo '<div id="myTree_' . $row->getId() . '"></div>';
                echo "
                        <script type='text/javascript'>
                        
                            trees.push(new TafelTree('myTree_" . $row->getId() . "', struct, {
                                    'generate': true,
                                    'imgBase': '" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . "/adminhtml/default/default/advancedinventory/images/',
                                    'openAtLoad': false,
                                    'cookies': false
                                }));
                           
                        </script>
                        ";
            } else
                return "-";
        } else
            return "-";
    }

}
