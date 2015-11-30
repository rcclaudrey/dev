<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */

    class Amasty_Rma_Block_Items extends Mage_Core_Block_Template
    {

        function getItems(){


            if (!$this->getData("items")){

                $collection = Mage::getResourceModel('amrma/item_collection')
                            ->addFilter('request_id', $this->getData("request")->getId());

                $this->setData("items", $collection);

            }

            return $this->getData("items");

        }
    }
?>

