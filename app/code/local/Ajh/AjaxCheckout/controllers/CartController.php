<?php

/**
 * Created by PhpStorm.
 * User: Camille
 * Date: 2/19/15
 * Time: 10:40 PM
 */
class Ajh_AjaxCheckout_CartController extends Mage_Core_Controller_Front_Action {

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct() {
        $productId = (int) $this->getRequest()->getParam('pid');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction() {
        $cart = $this->_getCart();
        $params = $this->getRequest()->getParams();
        $response = new Varien_Object();
        $response->setError(0);
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            /**
             * Check product availability
             */
            if (!$product) {
//                $this->_goBack();
//                return;
                $response->setError(1);
                $response->setErrorMessage('Unable to find Product.');
            }
            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$cart->getQuote()->getHasError()) {
                $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                $this->loadLayout();
                $content = $this->getLayout()->getBlock('floatingcart')->toHtml();
                $header = $this->getLayout()->getBlock('topCart')->setIsAjax()->toHtml();
                $response->setContent($content);
                $response->setHeader($header);
                $response->setQty($this->_getCart()->getSummaryQty());
                $response->setSuccessMessage($message);
            } else {
                $response->setError(1);
                $response->setErrorMessage('An error occurred while adding product to cart.');
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $response->setError(2);
                $response->setErrorMessage(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {                
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $msg .= Mage::helper('core')->escapeHtml($message);
                }
                $response->setError(1);
                $response->setErrorMessage(Mage::helper('core')->escapeHtml($msg));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $response->setError(1);
            $response->setErrorMessage($e, $this->__('Cannot add the item to shopping cart.'));
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction() {
//        if (!$this->_validateFormKey()) {
//            Mage::throwException('Invalid form key');
//        }
        $id = (int) $this->getRequest()->getParam('id');
        $response = new Varien_Object();
        $response->setError(0);
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();
                $response->setQty($this->_getCart()->getSummaryQty());
                $this->loadLayout();
                $content = $this->getLayout()->getBlock('cart_sidebar')->toHtml();
                $response->setContent($content);
                $response->setSuccessMessage($this->__('<div class="removed-successfully">This item was removed from the cart</div>'));
            } catch (Exception $e) {
                $response->setError(1);
                $response->setErrorMessage($this->__('<div class="removed-failed">Can not remove the item.</div>'));
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function updateCategoryAction() {
        Mage::register('isSecureArea', 1);
        Mage::app()->setCurrentStore(0);
        $categoryId = 11359;
        $_catArray = array(
            "Body" => "3",
            "Brake" => "4",
            "Casual Wear" => "30",
            "Chemical" => "31",
            "Control" => "5",
            "Displays" => "28",
            "Drive" => "7",
            "DVDs" => "49",
            "Electrical" => "8",
            "Engine" => "9",
            "Exhaust" => "10",
            "Eyewear" => "32",
            "Footwear" => "33",
            "Fuel & Air" => "11",
            "Graphics" => "12",
            "Helmets" => "34",
            "Implements" => "2",
            "Luggage" => "16",
            "Miscellaneous" => "42",
            "Plow" => "20",
            "Propulsion" => "39",
            "Protection" => "35",
            "Riding Apparel" => "29",
            "Seats" => "21",
            "Security & Covers" => "22",
            "Shop" => "36",
            "Skis" => "38",
            "Snow Accessories" => "44",
            "Stands" => "23",
            "Suspension" => "24",
            "Tires & Wheels" => "25",
            "Tools" => "46",
            "Toys" => "48",
            "Track Systems" => "41",
            "Trailers & Ramps" => "37",
            "WaterCraft Accessories" => "40",
            "Winch" => "26",
            "Windshield" => "27",
        );
        $_subcatArray = array(
            "2 - Stroke Oil" => "484",
            "A-Arms & Components" => "6",
            "Accessories" => "8",
            "Accessory Mounts" => "60",
            "Air Boxes" => "11",
            "Air Filters" => "13",
            "Air Tools" => "700",
            "Air Vents" => "651",
            "Alarms" => "674",
            "Anchors" => "743",
            "Ankle" => "474",
            "Antennas" => "711",
            "Armrests" => "16",
            "Audio Components" => "18",
            "Auto Accessories" => "469",
            "Axle Blocks" => "19",
            "Axles" => "23",
            "Backrests" => "702",
            "Bags/Pouches" => "25",
            "Batteries" => "27",
            "Bearings" => "28",
            "Bed Accessories" => "709",
            "Belt Guards" => "728",
            "Bike Covers" => "572",
            "Bike Stands" => "764",
            "Bilge" => "737",
            "Bodywork" => "34",
            "Bolts" => "707",
            "Boots" => "395",
            "Bracing" => "37",
            "Brackets" => "706",
            "Brake / Clutch Fluid" => "396",
            "Brake Calipers" => "40",
            "Brake Clevises" => "41",
            "Brake Line Clamps" => "44",
            "Brake Lines" => "45",
            "Brake Pads/Shoes" => "46",
            "Brake Reservoir Covers" => "51",
            "Brake Rotors" => "52",
            "Bumpers" => "57",
            "Cables" => "59",
            "Cabs & Accessories" => "58",
            "Camera Systems" => "493",
            "Canopies" => "505",
            "Carburetion-Fuel" => "62",
            "Carburetors & Accessories" => "64",
            "Cargo Containers" => "570",
            "Cargo Nets" => "599",
            "Casual Accessories" => "397",
            "Casual Footwear" => "398",
            "Center Caps" => "571",
            "Chain & Sprocket Kits" => "66",
            "Chain Guards" => "729",
            "Chain Lube" => "404",
            "Chains" => "70",
            "Chest & Back" => "440",
            "Chocks" => "714",
            "Chrome Covers & Trim" => "724",
            "Cleaners" => "406",
            "Cleaning" => "601",
            "Clutch & Components" => "73",
            "Clutch" => "72",
            "Communicators" => "407",
            "Complete Seats" => "75",
            "Computers & Meters" => "76",
            "Connectors" => "594",
            "Coolant" => "408",
            "Crankcase Components" => "82",
            "Cruise / Throttle Controls" => "84",
            "Cushions" => "520",
            "Cycle Country Plow System" => "87",
            "Cylinder Heads" => "629",
            "Cylinder Kits" => "90",
            "Dealer Items" => "754",
            "Dock Accessories" => "744",
            "Dollies" => "757",
            "Drive Belts" => "97",
            "Drive Shaft Covers" => "731",
            "Driveline" => "735",
            "EFI Programmers" => "100",
            "Elbow" => "475",
            "Electrical Components" => "102",
            "Electrical Covers" => "751",
            "Electrical" => "101",
            "End Caps" => "105",
            "Engine Covers" => "108",
            "Engine Oil" => "410",
            "Engine" => "106",
            "Exhaust Brackets / Hangers" => "111",
            "Eyewear Accessories" => "411",
            "Fairing Accessories" => "725",
            "Fairing Brackets" => "116",
            "Fairings" => "598",
            "Fender Wells" => "561",
            "Filter Cleaner & Oil" => "413",
            "Flags" => "693",
            "Fog Blocker" => "497",
            "Foot Controls" => "121",
            "Fork Bleeders" => "639",
            "Fork Covers" => "657",
            "Fork Damping Rods" => "640",
            "Fork Kits" => "641",
            "Fork Preload Adjuster" => "653",
            "Fork Seals" => "124",
            "Fork Tube Cap" => "646",
            "Frame" => "126",
            "Front Grills" => "133",
            "Fuel Injection" => "458",
            "Fuel Products" => "488",
            "Full System Exhaust" => "140",
            "Full-face Helmets" => "415",
            "Garage Door Openers" => "492",
            "Gas Cans & Accessories" => "416",
            "Gas Tanks & Accessories" => "144",
            "Gaskets & Seals" => "146",
            "Gauges & Components" => "710",
            "General Tools & Kits" => "511",
            "Gloves" => "446",
            "Glue / Thread Lock" => "487",
            "Goggles" => "417",
            "Grab Bars" => "149",
            "Graphic Kits" => "150",
            "Grease" => "418",
            "Grip Tape" => "576",
            "Guards & Accessories" => "155",
            "Half Helmets" => "419",
            "Handguards" => "569",
            "Handlebars & Accessories" => "157",
            "Handling" => "736",
            "Hardware" => "159",
            "Headers" => "161",
            "Headlight Rings" => "163",
            "Headrest" => "164",
            "Headwear" => "447",
            "Heated Apparel Accessories" => "514",
            "Heated Seats" => "610",
            "Heaters" => "612",
            "Heatshields" => "167",
            "Helmet Accessories & Repla" => "420",
            "Helmet Accessories & Replacement Parts" => "420",
            "Helmet locks" => "727",
            "Hip" => "516",
            "Hole Shot Buttons" => "169",
            "Home & School Accessories" => "753",
            "Hood Bras" => "692",
            "Horn" => "170",
            "Hubs" => "171",
            "Hydration Systems" => "421",
            "Hydraulic Clutches" => "172",
            "Ice Scratchers" => "781",
            "Idler Wheel" => "717",
            "Intake Kits" => "181",
            "Jackets & Hoodies" => "719",
            "Jackets" => "448",
            "Jerseys" => "449",
            "Jet Kits" => "184",
            "Kick Starters" => "187",
            "Kickstands" => "188",
            "Kidney Belt" => "483",
            "Knee" => "422",
            "Leather Accessories" => "423",
            "Levers & Perches" => "190",
            "License Plate Frames" => "695",
            "Life Vests" => "459",
            "Lights & Accessories" => "199",
            "Locks" => "504",
            "Lower Deflectors" => "201",
            "Lowering Kits" => "202",
            "Lubrication" => "485",
            "Luggage Accessories" => "204",
            "Master Cylinders" => "207",
            "Mirror Block - Offs" => "652",
            "Mirrors" => "209",
            "Miscellaneous" => "749",
            "Mixing Cups" => "513",
            "Modular Helmets" => "430",
            "Mounting Hardware" => "216",
            "Mud Flaps" => "726",
            "Muffler Repack Kits" => "218",
            "Neck" => "465",
            "Nerf Bars & Heel Guards" => "219",
            "Number Plate Accessories" => "221",
            "Off-road Helmets" => "431",
            "Oil Filters" => "223",
            "Oil Pumps" => "228",
            "Open-face Helmets" => "432",
            "Padding Kits" => "671",
            "Pajamas" => "524",
            "Pants" => "450",
            "Parts & Accessories" => "237",
            "Passenger Seats" => "238",
            "Personal Accessories" => "741",
            "Pipes" => "241",
            "Pistons" => "243",
            "Pit Mats" => "713",
            "Playwear" => "525",
            "Plows" => "545",
            "Plugs, Caps & Dipsticks" => "247",
            "Polish" => "490",
            "Power Up Kits" => "248",
            "Primary Drive" => "461",
            "Pull-Start Kits" => "252",
            "Pulleys" => "253",
            "Quiet Cores" => "592",
            "Rack Trunks" => "596",
            "Racks" => "254",
            "Radiators & Components" => "257",
            "Radio" => "258",
            "Raingear" => "437",
            "Ramps" => "438",
            "Reeds & Reed Valves" => "263",
            "Reflectors" => "626",
            "Registration Kits" => "579",
            "Replacement Parts" => "473",
            "Ride Mats" => "765",
            "Riding Apparel Accessories" => "439",
            "Rims" => "267",
            "Roll Cage" => "691",
            "Rollers, Sliders & Guides" => "270",
            "Runners" => "745",
            "Saddlebags & Accessories" => "271",
            "Safety Harnesses" => "286",
            "Safety Vest" => "515",
            "Safety Wire" => "509",
            "Seat Accessories" => "708",
            "Seat Covers" => "272",
            "Seat Kits" => "273",
            "Shift Levers" => "279",
            "Shift Linkages" => "280",
            "Shirts" => "720",
            "Shocks, Struts & Components" => "285",
            "Shop Apparel" => "441",
            "Shop Other" => "442",
            "Shop Supplies" => "443",
            "Shop" => "512",
            "Shorts" => "470",
            "Shoulder" => "478",
            "Shovels" => "778",
            "Ski Bottoms" => "747",
            "Ski Loops" => "746",
            "Skis" => "748",
            "Sled Dollies" => "712",
            "Slip-On / Silencers" => "290",
            "Snorkel Kits" => "292",
            "Socks" => "444",
            "Spark Plugs" => "293",
            "Sprayers" => "518",
            "Spreaders" => "553",
            "Spring Tool" => "577",
            "Springs" => "463",
            "Sprocket Guards" => "300",
            "Sprockets" => "302",
            "Stand kits" => "303",
            "Stands & pins" => "305",
            "Stands" => "304",
            "Starters" => "307",
            "Steering Column Covers" => "604",
            "Steering Damper / Stabilizers" => "309",
            "Steering Stem Nuts" => "502",
            "Steering Wheels" => "698",
            "Steps" => "758",
            "Stickers" => "533",
            "Strut Bar" => "645",
            "Suits" => "451",
            "Sunglasses" => "445",
            "Suspension Fluids" => "314",
            "Suspension Linkage" => "644",
            "Suspension" => "508",
            "Swingarms & Components" => "319",
            "Switch" => "320",
            "Throttle Kits" => "326",
            "Throttle Tubes" => "327",
            "Tiedown Systems" => "620",
            "Tiedowns & Bungee Straps" => "503",
            "Tire & Wheel Kits" => "329",
            "Tire & Wheel" => "506",
            "Tire Chains" => "330",
            "Tire Sealant" => "452",
            "Tires" => "331",
            "Tow Straps" => "718",
            "Track Accessories" => "716",
            "Track Systems" => "335",
            "Tracks" => "715",
            "Traction Plates" => "774",
            "Trailer Accessories" => "454",
            "Trailer Hitches" => "336",
            "Trailer Wheels & Tires" => "455",
            "Trailers" => "338",
            "Transmission / Gear Oil" => "456",
            "Transmissions" => "366",
            "Triple Tree & Clamps" => "341",
            "Tubes / Valve Stems" => "342",
            "Tunnels" => "679",
            "Turn Signals & Components" => "344",
            "Under Garments" => "457",
            "Under Seat Storage" => "606",
            "Valve Train" => "348",
            "Vest" => "429",
            "Warn Plow System" => "349",
            "Water Pump & Components" => "350",
            "Water Repellent" => "517",
            "Watersports" => "742",
            "Wetsuit" => "649",
            "Wheel Covers" => "522",
            "Wheel Spacers" => "354",
            "Wheels" => "355",
            "Winch Mount Kits" => "357",
            "Winch Parts and Accessorie" => "358",
            "Winch Parts and Accessories" => "358",
            "Winches" => "359",
            "Windshield Accessories" => "361",
            "Windshields" => "362",
            "Wiring" => "364",
            "Wrist" => "476"
        );
        $children = Mage::getModel('catalog/category')->getCategories($categoryId);
        foreach ($children as $category) {
            $grandchildren = Mage::getModel('catalog/category')->getCategories($category->getId());
            if (count($grandchildren) > 0) {
                foreach ($grandchildren as $grandCategory) {
//                    if (isset($_catArray[$grandCategory->getName()])) {
//                        echo $grandCategory->getName() . '<br/>';
//                        $_category = Mage::getModel('catalog/category')->load($grandCategory->getId()); // Load category (loads fine)
//                        echo $_catArray[$grandCategory->getName()] . '-' . $_category->getId() . '<br/>';
//                        $_category->setAriCategoryId($_catArray[$grandCategory->getName()]); // <-- set Attribute
////                        $grandCategory->setAriActivityId($_catArray[$grandCategory->getName()]); // <-- set Attribute
//                        $_category->save(); // <-- save the category
//                    }

                    $_grandchildren = Mage::getModel('catalog/category')->getCategories($grandCategory->getId());
                    if (count($_grandchildren) > 0) {
                        foreach ($_grandchildren as $_grandCategory) {
                            if (isset($_subcatArray[$_grandCategory->getName()])) {
                                $__category = Mage::getModel('catalog/category')->load($_grandCategory->getId()); // Load category (loads fine)
                                $__category->setAriSubcategoryId($_subcatArray[$_grandCategory->getName()]); // <-- set Attribute
                                //                        $grandCategory->setAriActivityId($_catArray[$grandCategory->getName()]); // <-- set Attribute

                                if (isset($_catArray[$grandCategory->getName()])) {
                                    $__category->setAriCategoryId($_catArray[$grandCategory->getName()]); // <-- set Attribute
                                }

                                $__category->save(); // <-- save the category
                            }
                        }
                    }
                }
            } else {/*
              //                if (isset($_catArray[$category->getName()])) {
              //                    $_category = Mage::getModel('catalog/category')->load($category->getId()); // Load category (loads fine)
              //                    $_category->setAriSubcategoryId($_catArray[$category->getName()]); // <-- set Attribute
              ////                        $grandCategory->setAriActivityId($_catArray[$grandCategory->getName()]); // <-- set Attribute
              //                    $_category->save(); // <-- save the category
              //                }/* */
            }
        }
        /*
          // start emulation
          $appEmulation = Mage::getSingleton('core/app_emulation');
          //        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(0);
          $category = Mage::getModel('catalog/category')->load($categoryId); // Load category (loads fine)
          $category->setAriActivityId(1); // <-- set Attribute
          $category->save(); // <-- save the category
          stop, return to origional env.
          $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
          echo $category->getAriActivityId();/* */
        echo 'done';
    }

//    public function updateCategoryFitmentAction() {
//        Mage::register('isSecureArea', 1);
//        Mage::app()->setCurrentStore(0);
//        $file = fopen(getcwd() . "/var/import/fitment/Category-Fitment-Guide.csv", "r");
//
//        while (!feof($file)) {
//            $row = fgetcsv($file);
//
//            if ($row[0] > 1) {
//                echo $row[0] . ' -- ' . 1 . '<br/>';
//
//                $_category = Mage::getModel('catalog/category')->load($row[0]); // Load category (loads fine)                
//                $_category->setHasFitment(1); // <-- set Attribute
//                $_category->save(); // <-- save the category                                
//            }
//        }
//
//        fclose($file);
//
//        echo 'done';
//        die;
//    }

    public function updateAriActivtyAction() {
        Mage::register('isSecureArea', 1);
        Mage::app()->setCurrentStore(0);

        $_category = Mage::getModel('catalog/category')->load(11363); // Load category (loads fine)                        
        $_category->setAriActivityId(5); // <-- set Attribute
        $_category->save(); // <-- save the category   
        
        $_category = Mage::getModel('catalog/category')->load(11364); // Load category (loads fine)                        
        $_category->setAriActivityId(5); // <-- set Attribute
        $_category->save(); // <-- save the category                    

        echo 'done';
        die;
    }

    public function topCartAction() {
        echo $this->getLayout()->createBlock('checkout/cart_sidebar')
                ->setTemplate('checkout/cart/cartheader.phtml')
                ->setIsAjax(true)
                ->toHtml();
        die;
    }

}
