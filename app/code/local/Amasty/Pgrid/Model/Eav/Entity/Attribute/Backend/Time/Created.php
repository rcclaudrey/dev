<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Model_Eav_Entity_Attribute_Backend_Time_Created extends Mage_Eav_Model_Entity_Attribute_Backend_Time_Created
{
    /**
     * Before delete method
     *
     * @param Varien_Object $object
     *
     * @return self
     */
    public function beforeSave($object)
    {
        if (version_compare(Mage::getVersion(), '1.9.0.0', '>=')) {
            return $this->_beforeSave($object);
        } else {
            return $this->_beforeSaveOld($object);
        }
    }

    /**
     * Before delete method
     *
     * @param Varien_Object $object
     *
     * @return self
     */
    protected function _beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $date = $object->getData($attributeCode);
        if (is_null($date)) {
            if ($object->isObjectNew()) {
                $object->setData($attributeCode, Varien_Date::now());
            }
        } else {

            // ADD THIS
            $date = strtotime($date);

            // convert to UTC
            $zendDate = Mage::app()->getLocale()->utcDate(null, $date, true, $this->_getFormat($date));
            $object->setData($attributeCode, $zendDate->getIso());
        }

        return $this;
    }

    /**
     * Before save method for magento version less than 1.7 or equal
     *
     * @param Varien_Object $object
     *
     * @return self
     */
    protected function _beforeSaveOld($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        if ($object->isObjectNew() && is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, Varien_Date::now());
        }

        return $this;
    }

}