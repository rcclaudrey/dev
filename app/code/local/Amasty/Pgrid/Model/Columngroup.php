<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Columngroup extends Mage_Core_Model_Abstract
{

    public function __construct()
    {
        $this->_init('ampgrid/columngroup', 'entity_id');
    }

    /**
     * @return bool
     */
    public function isSelected() {
        $selectedGroupId = Mage::helper('ampgrid')->getSelectedGroupId();
        return $this->getId() == $selectedGroupId;
    }

    /**
     * Flag for check categories
     * @return string
     */
    public function getCategoriesKey()
    {
        return 'category';
    }

    /**
     * @return bool
     */
    public function categoryColumnEnabled()
    {
        $additionalColumns = $this->getAdditionalColumns();
        $additionalColumns = explode(',', $additionalColumns);

        return in_array($this->getCategoriesKey(), $additionalColumns);
    }
}
