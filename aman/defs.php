<?php

// = = = = = = = = = = = = = = = = = = = =  vvv below is a compulsory part, please do not change that:

// attribute tabs for grouping them together
// while creating a new attribute set, we need to consequently add: 
// a) base group of attrs - attributes within default tabs that are absolutely required for all products, but they will not be added automatically
// b) common attrs at their tabs - attrs that are not mandatory, but are common to all custom attr sets we're going to add
// c) custom attrs at their tabs - custom for each attr set

$baseTabs= array(
	'General' => array(
		'name',
		'description',
		'short_description',
		'sku',
		'weight',
		'news_from_date',
		'news_to_date',
		'status',
		'url_key',
		'visibility',
		'is_imported',
	),
	'Price' => array(
		'price',
		'special_price',
		'special_from_date',
		'special_to_date',
		'cost',
		'tier_price',
		'tax_class_id',
		'enable_googlecheckout',
		'price_view',
	),
	'Meta information' => array(
		'meta_title',
		'meta_keyword',
		'meta_description',
	),
	'Images' => array(
		'image',
		'small_image',
		'thumbnail',
		'media_gallery',
		'gallery',
	),
	'Recurring Profile' => array(
		'is_recurring',
		'recurring_profile',
	),
	'Design' => array(
		'custom_design',
		'custom_design_from',
		'custom_design_to',
		'custom_layout_update',
		'page_layout',
		'options_container',
	),
	'Gift Options' => array(
		'gift_message_available',
	),
);

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ^^^ end of compulsory part



// = = = = = = = = = = = = = = = = = = = = = = = = = from this line, you are free to change the stuff:


//Available attribute data types are: datetime, decimal, int, text, varchar
//Available front-end input types are: boolean, date, gallery, hidden, image, media_image, multiline, multiselect, price, select, text, textarea


// attributes as:
// code => array( label, type, input, default, source, required, visible_on_front, option_values )
$attributes = array(
	// custom for base produts
	'os_sex'						=> array('Sex', 'int', 'select', 1, 'moduleName/source_sex', false, false),
	'os_age'						=> array('Age', 'varchar', 'select', '', null, false, false, array(
			'Baby',
			'Child',
			'Junior',
			'Adult',
			'0-3 years',
			'3-7 years',
			'10-18 years',
			'18+ years',
		)),
);

$commonTabs = array(
	'Brand' => array(
		'manufacturer',
	),
	'Product Information' => array(
		'os_product_reference',
		'os_product_ean_code',
		'os_product_range',
		'os_product_duty_code',
		'os_product_source_country',
		'os_product_warranty',
		'os_product_extra_information',
		'os_product_associated_product_recommended',
		'os_product_pdf',
		'os_product_flash_file',
	)
);


$customTabs1 = array(
	'Options' => array(
		'optical_glass_color',
		'optical_glass_color_custom',
		'optical_glass_lens_length',
		'optical_glass_lens_height',
		'optical_glass_bridge_length',
		'optical_glass_temple_length',
		'optical_glass_color_and_lens',
		'optical_glass_size',
	),
	'Glass Model' => array(
		'optical_glass_model_name',
		'optical_glass_collection',
		'optical_glass_out_of_collection',
		'optical_glass_montage_type',
		'optical_glass_shape',
		'optical_glass_material',
		'optical_glass_base',
		'optical_glass_sphere_min',
		'optical_glass_sphere_max',
		'optical_glass_cylinder_max',
		'optical_glass_other_features',
		'os_sex',
		'os_age',
		'os_brand_program',
	),
);


