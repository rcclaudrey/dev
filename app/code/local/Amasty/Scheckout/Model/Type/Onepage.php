<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Scheckout
 */

    class Amasty_Scheckout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
    {
        public function saveBilling($data, $customerAddressId)
        {
            if (empty($data)) {
                return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
            }
            
            if (isset($data['amcustomerattr'])
                && Mage::helper('core')->isModuleEnabled('Amasty_Customerattr')) { // BEGIN: `Amasty: Customer Attributes`
                // checking unique attributes
                $checkUnique = array();
                $nameGroupAttribute = '';
                $idGroupSelect = '';
                $collection = Mage::getModel('eav/entity_attribute')->getCollection();
                
                $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
                $collection->addFieldToFilter($alias . 'is_user_defined', 1);
                $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
                
                foreach ($collection as $attribute) {
                    if ($attribute->getIsUnique()) {
                        $translations = $attribute->getStoreLabels();
                        if (isset($translations[Mage::app()->getStore()->getId()])) {
                            $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                        } else {
                            $attributeLabel = $attribute->getFrontend()->getLabel();
                        }
                        $checkUnique[$attribute->getAttributeCode()] = $attributeLabel;
                    }
                }
                
                $collection = Mage::getModel('customer/attribute')->getCollection();
                $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
                $collection->addFieldToFilter($alias . 'is_user_defined', 1);
                $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
                foreach ($collection as $attribute) {
                    if ('selectgroup' == $attribute->getTypeInternal()) {
                        $nameGroupAttribute = $attribute->getAttributeCode();
                    }
                }
                foreach ($data['amcustomerattr'] as $attributeCode => $attributeValue) {
                    if ($attributeCode == $nameGroupAttribute) {
                        $idGroupSelect = $attributeValue;
                    }
                }
                if ($idGroupSelect) {
                    $option = Mage::getModel('eav/entity_attribute_option')->load($idGroupSelect);
                    if ($option && $option->getGroupId()) {
                        $customer = Mage::getModel('customer/customer');
                        $customer->setGroupId($option->getGroupId());
                    }
                }
                
                if ($checkUnique) {
                    foreach ($checkUnique as $attributeCode => $attributeLabel) {
                        //skip empty values
                        if (!$data['amcustomerattr'][$attributeCode]) {
                            continue;
                        }
                        $customerCollection = Mage::getResourceModel('customer/customer_collection');
                        $customerCollection->addAttributeToFilter($attributeCode, array('eq' => $data['amcustomerattr'][$attributeCode]));
                        if ($customerId = Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                            $mainAlias = ( false !== strpos($customerCollection->getSelect()->__toString(), 'AS `e') ) ? 'e' : 'main_table';
                            $customerCollection->getSelect()->where($mainAlias . '.entity_id != ?', $customerId);
                        }
                        if ($customerCollection->getSize() > 0) {
                            $result = array(
                                'error'     => 1,
                                'message'   => Mage::helper('amcustomerattr')->__('Please specify different value for "%s" attribute. Customer with such value already exists.', $attributeLabel),
                            );
                            return $result;
                        }
                    }
                }
                Mage::getSingleton('checkout/session')->setAmcustomerattr($data['amcustomerattr']);
            } // END: `Amasty: Customer Attributes`

            $address = $this->getQuote()->getBillingAddress();
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntityType('customer_address')
                ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());

            if (!empty($customerAddressId)) {
                $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                        return array('error' => 1,
                            'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                        );
                    }

                    $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                    $addressForm->setEntity($address);
                    $addressErrors  = $addressForm->validateData($address->getData());
                    if ($addressErrors !== true) {
                        return array('error' => 1, 'message' => $addressErrors);
                    }
                }
            } else {
                $addressForm->setEntity($address);
                // emulate request object
                $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => array_values($addressErrors));
                }
                $addressForm->compactData($addressData);
                //unset billing address attributes which were not shown in form
                foreach ($addressForm->getAttributes() as $attribute) {
                    if (!isset($data[$attribute->getAttributeCode()])) {
                        $address->setData($attribute->getAttributeCode(), NULL);
                    }
                }
                $address->setCustomerAddressId(null);
                // Additional form data, not fetched by extractData (as it fetches only attributes)
                $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
            }

            // set email for newly created user
            if (!$address->getEmail() && $this->getQuote()->getCustomerEmail()) {
                $address->setEmail($this->getQuote()->getCustomerEmail());
            }

            // validate billing address
            if (($validateRes = $address->validate()) !== true) {
                return array('error' => 1, 'message' => $validateRes);
            }

            $address->implodeStreetAddress();

            if (true !== ($result = $this->_validateCustomerData($data))) {
                return $result;
            }

            if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
                if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                    return array('error' => 1, 'message' => Mage::helper('checkout')->__('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.'));
                }
            }

            if (!$this->getQuote()->isVirtual()) {
                /**
                 * Billing address using otions
                 */
                $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

                switch ($usingCase) {
                    case 0:
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shipping->setSameAsBilling(0);
                        break;
                    case 1:
                        $billing = clone $address;
                        $billing->unsAddressId()->unsAddressType();
                        $shipping = $this->getQuote()->getShippingAddress();
                        $shippingMethod = $shipping->getShippingMethod();

                        // Billing address properties that must be always copied to shipping address
                        $requiredBillingAttributes = array('customer_address_id');

                        // don't reset original shipping data, if it was not changed by customer
                        foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                            if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                                && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                            ) {
                                $billing->unsetData($shippingKey);
                            }
                        }
                        $shipping->addData($billing->getData())
                            ->setSameAsBilling(1)
                            ->setSaveInAddressBook(0)
                            ->setShippingMethod($shippingMethod)
                            ->setCollectShippingRates(true);
                        $this->getCheckout()->setStepData('shipping', 'complete', true);
                        break;
                }
            }

            $this->getQuote()->collectTotals();
            $this->getQuote()->save();

            if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
                //Recollect Shipping rates for shipping methods
                $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            }

            $this->getCheckout()
                ->setStepData('billing', 'allow', true)
                ->setStepData('billing', 'complete', true)
                ->setStepData('shipping', 'allow', true);

            return array();
        }
        
        protected function _prepareNewCustomerQuote()
        {
            parent::_prepareNewCustomerQuote();
            
            $isSubscribed = Mage::app()->getRequest()->getParam('is_subscribed', false) || Mage::getSingleton('checkout/session')->getAmscheckoutIsSubscribed();
            if ($isSubscribed) {
                $quote      = $this->getQuote();
                $customer = $quote->getCustomer();
                $customer->setIsSubscribed(1);
                Mage::getSingleton('checkout/session')->setAmscheckoutIsSubscribed(true);
            } 
        }
        
        protected function _prepareGuestQuote()
        {
            parent::_prepareGuestQuote();
            
            $isSubscribed = Mage::app()->getRequest()->getParam('is_subscribed', false) || Mage::getSingleton('checkout/session')->getAmscheckoutIsSubscribed();
            if ($isSubscribed) {
                $quote = $this->getQuote();
                
                Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());

                Mage::getSingleton('checkout/session')->setAmscheckoutIsSubscribed(true);
            } 
        }

        function customerEmailExists()
        {
            $address = $this->getQuote()->getBillingAddress();

            return $this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId());
        }
    }
?>