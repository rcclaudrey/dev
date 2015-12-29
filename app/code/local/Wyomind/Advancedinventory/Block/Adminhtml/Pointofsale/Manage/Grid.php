<?php

class Wyomind_Advancedinventory_Block_Adminhtml_Pointofsale_Manage_Grid extends Wyomind_Pointofsale_Block_Adminhtml_Manage_Grid {

    protected function _prepareCollection() {

        parent::_prepareCollection();


        $collection = Mage::getModel('pointofsale/pointofsale')->getCollection();
        $this->setCollection($collection);

        $filter = $this->getParam($this->getVarNameFilter(), null);
        parse_str(urldecode(base64_decode($filter)), $data);
        if (isset($data['store']))
            $collection->addFieldToFilter('name', array('like' => "%" . $data['store'] . "%"));

        $permissions = Mage::helper('advancedinventory/permissions')->getUserPermissions();
        $all = $permissions->isAdmin();
        $pos = $permissions->getPos();

        if ($all)
            return $this;

        foreach ($pos as $p) {
            $filters[] = array('eq' => $p);
        }
        if (!count($pos))
            $filters[] = array('eq' => "No permissions!");

        if (count($filters)) {
            $collection->addFieldToFilter('place_id', $filters);
        }

        return $this;
    }

}
?>
