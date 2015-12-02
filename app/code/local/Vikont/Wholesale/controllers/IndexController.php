<?php

class Vikont_Wholesale_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		Mage::helper('wholesale')->requireLogin();
		$this->loadLayout()->renderLayout();
	}

}