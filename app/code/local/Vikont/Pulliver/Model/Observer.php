<?php

class Vikont_Pulliver_Model_Observer
{

	public function catalog_product_collection_load_before($info)
	{
//echo 'catalog_product_collection_load_before';
//vd($info['collection']);
	}


	public function eav_collection_abstract_load_before($info)
	{
//echo 'eav_collection_abstract_load_before';
//vd($info['collection']);
	}

}