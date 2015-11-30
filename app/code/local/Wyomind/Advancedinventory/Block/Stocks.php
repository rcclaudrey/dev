<?php

class Wyomind_Advancedinventory_Block_Stocks extends Mage_Core_Block_Template {
    /* Code snippet to use in  app/code/design/frontend/default/default/catalog/product/view.php
     * <?php echo $this->getLayout()->createBlock('advancedinventory/stocks')->output($_product) ?> 
     */

    private $_product;
    private $_cmsMapPage = 'pointofsales';

    function _json() {

        $attributes = array();
        $_attributes = $this->_product->getTypeInstance(true)->getConfigurableAttributes($this->_product);
        foreach ($_attributes as $_attribute) {
            $attributes[] = Mage::getModel('eav/config')->getAttribute('catalog_product', $_attribute->getAttributeId());
        }



        $AssociatedProduct = $this->_product->getTypeInstance()->getUsedProducts();
        $children = array();
        $i = 0;
        $places = Mage::getModel('advancedinventory/stock')->getStocksByProductIdAndStoreId($this->_product->getId(), Mage::app()->getStore()->getStoreId());
        foreach ($AssociatedProduct as $child) {
            foreach ($attributes as $attr) {
                $children[$i]["attribute" . $attr->getAttributeId()] = $child->getData($attr->getAttributeCode());
            }
            foreach ($places as $p) {

                $data = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($child->getId(), $p->getPlaceId());
                $children[$i]['stock'][] = array("store" => $p->getPlaceId(), "qty" => $data->getQuantityInStock(), "status" => Mage::helper('advancedinventory')->getStockStatus($data));
            }


            $i++;
        };

        echo '<script>
        stock = ' . json_encode($children) . ';

        document.observe("dom:loaded", function() {
            $$(".super-attribute-select").each(function(sa) {

                sa.observe("change", function() {
                    attr = [];
                    selection = true;
                    $$(".super-attribute-select").each(function(s) {
                        if (s.value === "")
                            selection = false;
                        attr.push({\'id\': s.id, \'value\': s.value});
                    });

                    if (selection) {

                        stock.each(function(e) {
                            found = true;
                            attr.each(function(a) {
                           
                                if (eval("e." + a.id) !== a.value)
                                    found = false;
                            });
                            if (found)
                               e.stock.each(function(s){
                                   if(typeof $$("#store_"+s.store+" SPAN.units")[0]!="undefined")
                                     $$("#store_"+s.store+" SPAN.units")[0].update(s.qty)
                                     if(s.qty<1){
                                        $$("#store_"+s.store+" SPAN.details")[0].hide();
                                     }
                                     else{
                                        $$("#store_"+s.store+" SPAN.details")[0].show();
                                     }
                                     $$("#store_"+s.store+" SPAN.status")[0].writeAttribute("class","status "+s.status).update(eval(s.status))
                                   
                               });
                        })
                    }
                    else {
                       $$(".ai_store").each(function(s){
                     
                         s.select("SPAN.units")[0].update(s.select("SPAN.units")[0].readAttribute("default"))
                       })
                    }
                })
            })
        })
        in_stock="' . Mage::helper('advancedinventory')->__("In stock") . '";
        out_of_stock="' . Mage::helper('advancedinventory')->__("Out of stock") . '";
        backorder="' . Mage::helper('advancedinventory')->__("Backorders") . '";
        not_managed="' . Mage::helper('advancedinventory')->__("Out of stock") . '";


    </script>';
    }

    function _toHtml() {
        $rtn = null;
        if ($this->_product->isConfigurable()) {
            $rtn.=$this->_json();
        }
        $rtn .= "<table class='data-table'>
                 <thead>
                    <tr>
                        <th>" . Mage::helper('advancedinventory')->__('Store') . "</th><th>" . Mage::helper('advancedinventory')->__('Quantity in stock') . "
                        </th>
                 </thead>
                 <tbody>";
        $places = Mage::getModel('advancedinventory/stock')->getStocksByProductIdAndStoreId($this->_product->getId(), Mage::app()->getStore()->getStoreId());
        $c = 0;
        foreach ($places as $p) {
            if ($this->_product->getTypeId() == 'configurable') {

                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $this->_product);
                $stock = 0;
                $managelocalstock = false;
                foreach ($childProducts as $child) {
                    //echo $child->getId()."<br>";

                    $data = Mage::getModel('advancedinventory/stock')->getStockByProductIdAndPlaceId($child->getId(), $p->getPlaceId());
                    if ($data->getManageLocalStock())
                        $managelocalstock = true;
                    if ($data->getManageLocalStock())
                        $stock+=(int) $data->getQuantityInStock();
                }
                //echo "stock : ".$stock."<br>";
            } else {
                $stock = $p->getQty();
                $managelocalstock = $p->getManageLocalStock();
            }
            $customer_id = Mage::getSingleton('customer/session')->getCustomerGroupId();

            if ($p->getStatus() != 1 || !in_array(Mage::app()->getStore()->getStoreId(), explode(',', $p->getStoreId())) || !in_array($customer_id, explode(',', $p->getCustomerGroup())) || !$managelocalstock)
                continue;
            $c++;

            $defaultBackOrder = Mage::getStoreConfig("cataloginventory/item_options/backorders");
            $backOrder = ($p->getUseConfigSettingForBackorders()) ? $defaultBackOrder : $p->getBackorderAllowed();
            if ($stock > 0) {
                $display = "<span class='status in_stock'>" . Mage::helper('advancedinventory')->__('In stock') . "</span> <span class='details'> (<span class='units' default='$stock'>" . $stock . "</span> " . Mage::helper('advancedinventory')->__('units') . ")</span></span>";
            } else if (!$p->getManageStock()) {
                $display = "<span class='status out_of_stock'>" . Mage::helper('advancedinventory')->__('Out of stock') . "</span> <span class='details' style='display:none'> (<span class='units' default='$stock'>" . $stock . "</span> " . Mage::helper('advancedinventory')->__('units') . ")</span></span>";
            } else {
                if ($backOrder == 0)
                    $display = "<span class='status out_of_stock'>" . Mage::helper('advancedinventory')->__('Out of stock') . "</span> <span class='details' style='display:none'> (<span class='units' default='$stock'>" . $stock . "</span> " . Mage::helper('advancedinventory')->__('units') . ")</span></span>";
                if ($backOrder == 1)
                    $display = "<span class='status in_stock'>" . Mage::helper('advancedinventory')->__('In stock') . "</span> <span class='details' style='display:none'> (<span class='units' default='$stock'>" . $stock . "</span> " . Mage::helper('advancedinventory')->__('units') . ")</span></span>";
                if ($backOrder == 2)
                    $display = "<span class='status backorder'>" . Mage::helper('advancedinventory')->__('Backorders') . "</span> <span class='details' style='display:none'> (<span class='units' default='$stock'>" . $stock . "</span> " . Mage::helper('advancedinventory')->__('units') . ")</span></span>";
            }
            if ($p->getManageStock()) {
                $rtn.="<tr class='ai_store' id='store_" . $p->getId() . "'>";
                $rtn.= "<td>" . $p->getName() . "</td>";
                $rtn.="<td>" . $display . "</td>";
                $rtn.="</tr>";
            }
        }
        $rtn.="
                </tbody>
            </table><a target='_blank' href='" . Mage::getUrl($this->_cmsMapPage) . "'>" . Mage::helper('advancedinventory')->__('Find the nearest store') . "</a>";

        if ($c)
            return $rtn;
        else
            return null;
    }

    function output($product) {
        $this->_product = $product;



        return $this->_toHtml();
    }

}
