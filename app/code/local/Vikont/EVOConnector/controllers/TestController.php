<?php
ini_set('display_errors', '1');
class Vikont_EVOConnector_TestController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		// Welcome to the test controller!
		// It is available at http://tmsparts.com/evoc/test/ or http://tmsparts.com/evoc/test/index/ , whichever looks better for you.
		// You're inside of the standard Magento environment with all the configuration initialized and applied, as it is regular Magento controller.
		// Below is a standard code retrieving certain information from an additional database.
		// As far as you'll see in the output, $_resource->getConnection('oemdb_read') returns false that's not correct.
		//
		// vd() function widely used here is just an analogue of famous var_dump(), but more sophisticated one
		try {

//			$collection = Mage::getModel('catalog/product')->getCollection()->setPageSize(10)->load();
//			vd($collection);

			$dbh = new PDO('mysql:dbname=tms_oem;host=127.0.0.1', 'tms_magento', 'skey2Coll!#');
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = "SELECT * FROM sku WHERE sku='4473167'";
			sql($sql);

			foreach ($dbh->query($sql) as $row) {
				vd($row);
			}


		/*
				try {
		//			vd($_resource = Mage::getSingleton('core/resource'));
		//			vd($_connection = $_resource->getConnection('oemdb_read'));

		//			vd(Vikont_ARIOEM_Helper_OEM::getPartNumbers('4473167')); // you can use value '4473167' as it's from a real DB record

				} catch (PDOException $e) {
					echo 'OMG, it is PDO exception!';
					vd($e->getMessage());
				} catch(Exception $e) {
					vd($e->getMessage());
				}
				/**/

		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
	}




	public function testAction()
	{
		Vikont_EVOConnector_Helper_Data::sendResponse(
				Vikont_EVOConnector_Helper_Data::array2xml(array(
						'root' => array(
							'a' => 'b',
							'spec' => '<&">',
							'rus' => 'ёпрст',
						),
					))
			);
	}


	public function test2Action()
	{
	}

}
