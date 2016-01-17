<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Library extends Mage_Adminhtml_Block_Template {

    public function _ToHtml() {
        ;
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $tableEet = $resource->getTableName('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
        $data = $read->fetchAll($select);
        $typeId = $data[0]['entity_type_id'];

        function cmp($a, $b) {

            return ($a['frontend_label'] < $b['frontend_label']) ? -1 : 1;
        }

        /*  Liste des  attributs disponible dans la bdd */

        $attributesList = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($typeId)
                ->addSetInfo()
                ->getData();
        $selectOutput = null;
        $attributesList[] = array("attribute_code" => "qty", "frontend_label" => "Quantity");
        $attributesList[] = array("attribute_code" => "is_in_stock", "frontend_label" => "Is in stock");
        $attributesList[] = array("attribute_code" => "entity_id", "frontend_label" => "Product ID");
        usort($attributesList, "cmp");

        $tabOutput = '<div id="dfm-library"><ul> ';
        $contentOutput = '<table >';








        $contentOutput .="<tr><td><b>References</b></td></tr>";
        foreach ($attributesList as $attribute) {


            if (!empty($attribute['frontend_label']))
                $contentOutput.= "<tr><td>" . $attribute['frontend_label'] . "</td><td><span class='pink'>{" . $attribute['attribute_code'] . "}</span></td></tr>";
        }
        foreach ($attributesList as $attribute) {


            if (!empty($attribute['attribute_code']) && empty($attribute['frontend_label']))
                $contentOutput.= "<tr><td>" . $attribute['frontend_label'] . "</td><td><span class='pink'>{" . $attribute['attribute_code'] . "}</span></td></tr>";
        }





        $tabOutput .=" <h3>Documentation</h3><ul>";
        $tabOutput .=" <li><a class='external_link' target='_blank' href='http://wyomind.com/google-shopping-magento.html?src=sgs-library&directlink=documentation#Special_attributes'>Special Attributes</a></li>";
        $tabOutput .=" <li><a class='external_link' target='_blank' href='http://wyomind.com/google-shopping-magento.html?src=sgs-library&directlink=documentation#Basic_attributes_&_basic_options'>Attribute options</a></li>";
        $tabOutput .=" <li><a class='external_link' target='_blank' href='http://wyomind.com/google-shopping-magento.html?src=sgs-library&directlink=documentation#Simple_Google_Shopping_tutorial'>Tutorial</a></li>";
        $tabOutput .="</ul>";



        $contentOutput .="</table></div>";
        $tabOutput .= '</ul>';
        return($tabOutput . $contentOutput);
    }

}
