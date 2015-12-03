<?php

require_once(Mage::getModuleDir('controllers', 'Mage_Catalog') . DS . 'CategoryController.php');

class Ajh_Tires_TiresController extends Mage_Catalog_CategoryController {

    public function viewAction() {
        $url = Mage::getUrl('tireshop.html');
        $this->_redirect($url);

        exit;
    }

}
