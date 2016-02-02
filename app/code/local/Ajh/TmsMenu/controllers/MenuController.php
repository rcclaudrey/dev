<?php

class Ajh_TmsMenu_MenuController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
//		<block type="page/html_topmenu" name="catalog.topnav" template="page/html/topmenu.phtml"/>
		try {
			$res = Mage::app()->getLayout()
					->createBlock('page/html_topmenu')
						->setTemplate('page/html/topmenu.phtml')
						->toHtml();
		} catch (Exception $e) {
			Mage::logException($e);
			$res = '';
		}

		$expires =  date('D, j M Y H:i:s', time() + 432000) . ' GMT';
		header_remove('Pragma:');
		header('Expires: ' . $expires);
//		header('Cache-Control: max-age=432000, public, must-revalidate');
		header('Cache-Control: max-age=432000, public');
//		setcookie('expires', $expires);

		echo $res;
		die;
	}

}