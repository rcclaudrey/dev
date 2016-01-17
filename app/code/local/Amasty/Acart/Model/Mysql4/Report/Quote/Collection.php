<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */ 
class Amasty_Acart_Model_Mysql4_Report_Quote_Collection extends Mage_Reports_Model_Mysql4_Quote_Collection
{
    static $_addCustomerData = false;
    public function addCustomerData($filter = null)
    {
        if (!self::$_addCustomerData){
            self::$_addCustomerData = true;

            $customerEntity         = Mage::getResourceSingleton('customer/customer');
            $attrFirstname          = $customerEntity->getAttribute('firstname');
            $attrFirstnameId        = (int) $attrFirstname->getAttributeId();
            $attrFirstnameTableName = $attrFirstname->getBackend()->getTable();

            $attrLastname           = $customerEntity->getAttribute('lastname');
            $attrLastnameId         = (int) $attrLastname->getAttributeId();
            $attrLastnameTableName  = $attrLastname->getBackend()->getTable();

            $attrEmail       = $customerEntity->getAttribute('email');
            $attrEmailTableName = $attrEmail->getBackend()->getTable();

            $this->getSelect()
                ->joinLeft(
                    array('cust_email' => $attrEmailTableName),
                    'cust_email.entity_id = main_table.customer_id',
                    array('email' => 'cust_email.email')
                )
                ->joinLeft(
                    array('cust_fname' => $attrFirstnameTableName),
                    'cust_fname.entity_id=main_table.customer_id and cust_fname.attribute_id=' . $attrFirstnameId,
                    array('firstname' => 'cust_fname.value')
                )
                ->joinLeft(
                    array('cust_lname' => $attrLastnameTableName),
                    'cust_lname.entity_id=main_table.customer_id and cust_lname.attribute_id=' . $attrLastnameId,
                    array(
                        'lastname'      => 'cust_lname.value',
                        'customer_name' => new Zend_Db_Expr('CONCAT(cust_fname.value, " ", cust_lname.value)')
                    )
                )
                ->joinLeft(
                    array('quote2email' => $this->getTable('amacart/quote2email')),
                    'main_table.entity_id = quote2email.quote_id',
                    array('ifnull(main_table.customer_email, quote2email.email) as target_email')
                );

            $this->_joinedFields['customer_name'] = 'CONCAT(cust_fname.value, " ", cust_lname.value)';
            $this->_joinedFields['email']         = 'cust_email.email';

            if ($filter) {
                if (isset($filter['customer_name'])) {
                    $this->getSelect()->where(
                        $this->_joinedFields['customer_name'] . ' LIKE ?',
                        "%{$filter['customer_name']}%"
                    );
                }
                if (isset($filter['email'])) {
                    $this->getSelect()->where(
                        $this->_joinedFields['email'] . ' LIKE ?',
                        "%{$filter['email']}%"
                    );
                }
            }

            $this->getSelect()->where("ifnull(main_table.customer_email, quote2email.email) is not null");
        }
        return $this;
    }
}
?>