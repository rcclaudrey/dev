<?php

class Wyomind_Advancedinventory_Helper_Permissions extends Mage_Core_Helper_Abstract {

    public $_userPermissions = array();

    public function getUserPermissions() {
        $user_id = Mage::getSingleton('admin/session')->getUser()->getUserId();
        $json = Mage::getStoreConfig("advancedinventory/setting/pos_permissions");
        $this->_userPermissions = array('isAdmin' => false, "permissions" => array());
        if ($json != '*') {
            $data = Mage::helper('core')->jsonDecode($json, Zend_Json::TYPE_OBJECT);

            if (isset($data->$user_id)) {
                $perm = $data->$user_id;
                foreach ($perm as $p) {
                    if ("all" == $p) {
                        $this->_userPermissions["isAdmin"] = true;
                        break;
                    } else if ("na" == $p) {
                        $this->_userPermissions["permissions"][] = 0;
                    } else {
                        $this->_userPermissions["permissions"][] = $p;
                    }
                }
            }
        } else {
            $this->_userPermissions["isAdmin"] = true;
        }
        return $this;
    }

    public function isAdmin() {
        return $this->_userPermissions["isAdmin"];
    }

    public function getPos() {
        return $this->_userPermissions["permissions"];
    }

}
