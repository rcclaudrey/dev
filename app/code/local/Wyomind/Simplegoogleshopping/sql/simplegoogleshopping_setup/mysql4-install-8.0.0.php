<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF   EXISTS {$this->getTable('simplegoogleshopping')};
 ");

$installer->run("

CREATE TABLE {$this->getTable('simplegoogleshopping')} (
 `simplegoogleshopping_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `simplegoogleshopping_filename` varchar(255) DEFAULT NULL,
  `simplegoogleshopping_path` varchar(255) DEFAULT NULL,
  `simplegoogleshopping_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `store_id` int(11) NOT NULL DEFAULT '1',
  `simplegoogleshopping_url` varchar(120) DEFAULT NULL,
  `simplegoogleshopping_title` text ,
  `simplegoogleshopping_description` text ,
  `simplegoogleshopping_xmlitempattern` text,
  `simplegoogleshopping_categories` longtext,
  `simplegoogleshopping_category_filter` INT(1) DEFAULT 1,
  `simplegoogleshopping_type_ids` varchar(150) DEFAULT NULL,
  `simplegoogleshopping_visibility` varchar(10) DEFAULT NULL,
  `simplegoogleshopping_attribute_sets` varchar(150) default '*',
  `simplegoogleshopping_attributes` text ,
  `cron_expr` varchar(900) NOT NULL DEFAULT '{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"04:00\"]}',
  PRIMARY KEY (`simplegoogleshopping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->run("
	
	INSERT INTO `{$this->getTable('simplegoogleshopping')}` (`simplegoogleshopping_id`, `simplegoogleshopping_filename`, `simplegoogleshopping_path`, `simplegoogleshopping_time`, `store_id`, `simplegoogleshopping_url`, `simplegoogleshopping_title`, `simplegoogleshopping_description`, `simplegoogleshopping_xmlitempattern`, `simplegoogleshopping_categories`, `simplegoogleshopping_type_ids`, `simplegoogleshopping_visibility`, `simplegoogleshopping_attributes`) VALUES


(NULL, 'GoogleShopping_datafeed.xml', '/', NULL, (SELECT store_id FROM `{$this->getTable('core_store')}` WHERE store_id>0 LIMIT 1), 'http://wwww.website.com', 'Data feed title', 'Data feed description', '<!-- Basic Product Information -->
<g:id>{sku}</g:id>
<title>{name,[substr],[70],[...]}</title>
<description>{description,[html_entity_decode],[strip_tags]}</description>
{G:GOOGLE_PRODUCT_CATEGORY}
{G:PRODUCT_TYPE,[10]}
<link>{url parent}</link>
{G:IMAGE_LINK}
<g:condition>new</g:condition>

<!-- Availability & Price -->
<g:availability>{is_in_stock?[in stock]:[out of stock]:[available for order]}</g:availability>
<g:price>{normal_price,[USD],[0]}USD</g:price>
{G:SALE_PRICE,[USD],[0]}

<!-- Unique Product Identifiers-->
<g:brand>{manufacturer}</g:brand>
<g:gtin>{upc}</g:gtin>
<g:mpn>{sku}</g:mpn>
<g:identifier_exists>TRUE</g:identifier_exists>

<!-- Apparel Products -->
<g:gender>{gender}</g:gender>
<g:age_group>{age_group}</g:age_group>
<g:color>{color}</g:color>
<g:size>{size}</g:size>

<!-- Product Variants -->
{G:ITEM_GROUP_ID}
<g:material>{material}</g:material>
<g:pattern>{pattern}</g:pattern>

<!-- Shipping -->
<g:shipping_weight>{weight,[float],[2]}kg</g:shipping_weight>

<!-- AdWords attributes -->
<g:adwords_grouping>{adwords_grouping}</g:adwords_grouping>
<g:adwords_labels>{adwords_labels}</g:adwords_labels>', '*', 'simple,configurable,bundle,virtual,downloadable', '1,2,3,4', '[{\"line\": \"0\", \"checked\": true, \"code\": \"price\", \"condition\": \"gt\", \"value\": \"0\"}, {\"line\": \"1\", \"checked\": true, \"code\": \"sku\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"2\", \"checked\": true, \"code\": \"name\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"3\", \"checked\": true, \"code\": \"description\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"4\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"5\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"6\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"7\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"8\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"9\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"10\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}]')"
);

if (strpos($_SERVER['HTTP_HOST'], "wyomind.com"))
    $installer->run("UPDATE `{$this->getTable('simplegoogleshopping')}` SET simplegoogleshopping_categories ='[{\"line\": \"1/3\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10/22\", \"checked\": false, \"mapping\": \"Furniture > Living Room Furniture\"}, {\"line\": \"1/3/10/23\", \"checked\": false, \"mapping\": \"Furniture > Bedroom Furniture\"}, {\"line\": \"1/3/13\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/12\", \"checked\": false, \"mapping\": \"Cameras & Optics\"}, {\"line\": \"1/3/13/12/25\", \"checked\": false, \"mapping\": \"Cameras & Optics > Camera & Optic Accessories\"}, {\"line\": \"1/3/13/12/26\", \"checked\": false, \"mapping\": \"Cameras & Optics > Cameras > Digital Cameras\"}, {\"line\": \"1/3/13/15\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/15/27\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/28\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/29\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/30\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/31\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/32\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/33\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/34\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/8\", \"checked\": false, \"mapping\": \"Electronics > Communications > Telephony > Mobile Phones\"}, {\"line\": \"1/3/18\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/18/19\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Activewear > Sweatshirts\"}, {\"line\": \"1/3/18/24\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Pants\"}, {\"line\": \"1/3/18/4\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Tops > Shirts\"}, {\"line\": \"1/3/18/5\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/16\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/17\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/20\", \"checked\": false, \"mapping\": \"\"}]'");

$installer->endSetup();



