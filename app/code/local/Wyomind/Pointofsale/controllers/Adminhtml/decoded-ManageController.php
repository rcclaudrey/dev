<?php

class Wyomind_Pointofsale_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->_title($this->__('Manage'))->_title($this->__('POS / Warehouses'));
		$this
			->loadLayout()
			->_setActiveMenu("sales/pointofsale");
		return $this;
 	}


	public function indexAction()
	{
		$this->_initAction() ->renderLayout();
	}


	public function importCsvAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu("sales/pointofsale");
		$this->_addBreadcrumb(Mage::helper("pointofsale")->__("POS / Warehouses"), ("POS / Warehouses"));
		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock("pointofsale/adminhtml_manage_import"))
			->_addLeft($this->getLayout()->createBlock("pointofsale/adminhtml_manage_import_tabs"));
		$this->renderLayout();
	}


	public function editAction()
	{
		$modelId = $this->getRequest()->getParam("place_id");
		$placeModel = Mage::getModel("pointofsale/pointofsale")->load($modelId);

		if ($placeModel->getId() || $modelId == 0) {
			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$placeModel->setData($data);
			}
			Mage::register("pointofsale_data", $placeModel);
			$this->loadLayout();
			$this
				->_title($this->__('Manage'))
				->_title($this->__('POS / Warehouses'));

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this
				->_addContent($this->getLayout()->createBlock("pointofsale/adminhtml_manage_edit"))
				->_addLeft($this->getLayout()->createBlock("pointofsale/adminhtml_manage_edit_tabs"));

			$this->renderLayout();
		} else {
			Mage::getSingleton("adminhtml/session")
				->addError(Mage::helper("pointofsale")->__("item does not exist"));
			$this->_redirect("*/*/");
		}
	}


	public function newAction() { $this->_forward("edit"); }


	public function saveAction()
	{/*
//		$x24 = array(
//			"ac" => "activation_code",
//			"ak" => "activation_key",
//			"bu" => "base_url",
//			"md" => "md5",
//			"th" => "this",
//			"dm" => "_demo",
//			"ext" => "pos",
//			"ver" => "5.\61.\61"
//		);
//		$x25 = array(
//			"activation_key" => Mage::getStoreConfig("pointofsale/license/activation_key"),
//			"activation_code" => Mage::getStoreConfig("pointofsale/license/activation_code"),
//			"base_url" => Mage::getStoreConfig("web/secure/base_url"),
//		);
//		if ($x25[$x24['ac']] != $x24["md"]($x24["md"]($x25[$x24['ak']]) . $x24["md"]($x25[$x24['bu']]) . $x24["md"]($x24["ext"]) . $x24["md"]($x24["ver"]))) {
//			$$x24["ext"] = "valid";
//			$$x24["th"]->$x24["dm"] = true;
//		} else {
//			$$x24["th"]->$x24["dm"] = false;
//			$$x24["ext"] = "valid";
//		}

//		if (!isset($$x24["ext"]) || $$x24["th"]->$x24["dm"]) $$x24["th"]->$x24["dm"] = true;
//
//		if ($$x24["th"]->$x24["dm"]) {
//			$this->_getSession()->addError(Mage::helper("pointofsale")->__("invalid license."));
//			Mage::getConfig()->saveConfig("pointofsale/license/activation_code", "", "default", "\x30");
//			Mage::getConfig()->cleanCache();
//			$this->_redirect("* /* /");
//		}

//		if ($$x24["th"]->$x24["dm"]) return $$x24["th"];
/**/
		if ($this->getRequest()->getPost()) {
			$postData = $this->getRequest()->getPost();
			if (isset($_FILES["file"]["name"]) && $_FILES["file"]["name"] != "") {
				$counter = 1;
				if (strtolower(array_pop(explode(".", $_FILES["file"]["name"]))) != "csv") {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pointofsale")
							->__("Wrong file type (" . $_FILES["file"]["type"] . ").<br>Choose a csv file."));
				} else {
					$csvFile = new Varien_File_Csv;
					$csvFile->setDelimiter("\t");
					$csvData = $csvFile->getData($_FILES["file"]["tmp_name"]);
					$model = Mage::getModel("pointofsale/pointofsale");
					$header = $csvData[0];
					while (isset($csvData[$counter])) {
						foreach ($csvData[$counter] as $key => $value) {
							$postData[$header[$key]] = $value;
						}
						$model->setData($postData)->save();
						$counter++;
					}
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("pointofsale")
						->__(($counter - 1) . " places have been imported."));
				$this->_redirect("*/*/importCsv");  return;
			}

			if (isset($postData["image"]["delete"]) && $postData["image"]["delete"] == 1) {
				$postData["image"] = "";
			} else {
				if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != "") {
					try {
						$uploader = new Varien_File_Uploader("image");
						$uploader->setAllowedExtensions(array("\x6apg", "jpeg", "gif", "png"));
						$uploader->setAllowRenameFiles(true);
						$uploader->setFilesDispersion(false);
						$mediaPath = Mage::getBaseDir("media") . DS;
						$uploader->save($mediaPath . "stores", $_FILES["image"]["name"]);
					} catch (Exception $x2e) { }
					$postData["image"] = "stores/" . $_FILES["image"]["name"];
				} else unset($postData["image"]);
			}

			$model = Mage::getModel("pointofsale/pointofsale");

			if (in_array('-1', $postData["customer_group"])) {
				$postData["customer_group"] = array("-\61");
			}
			$postData["customer_group"] = implode(',', $postData["customer_group"]);

			if (in_array('0', $postData["store_id"])) {
				$postData["store_id"] = array("\x30");
			}

			$postData["store_id"] = implode(',', $postData["store_id"]);
			$model->setData($postData) ->setId($this->getRequest()->getParam("place_id"));
			$model->save();

			try {
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("pointofsale")->
						__("Item was successfully saved"));
				Mage::getSingleton("adminhtml/session")->setFormData(false);
				if ($this->getRequest()->getParam("back")) {
					$this->_redirect("*/*/edit", array("place_id" => $model->getId()));
					return;
				}
				$this->_redirect("*/*/");
				return;
			} catch (Exception $x2e) {
				Mage::getSingleton("adminhtml/session")->addError($x2e->getMessage());
				Mage::getSingleton("adminhtml/session")->setFormData($postData);
				$this->_redirect("*/*/edit", array("place_id" => $this->getRequest()->getParam("place_id")));
				return;
			}
		}
		Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pointofsale")->__("Unable to find item to save"));
		$this->_redirect("*/*/");
	}


	public function deleteAction()
	{
		if ($this->getRequest()->getParam("place_id") > 0) {
			try {
				$x22 = Mage::getModel("pointofsale/pointofsale");
				$x22->setId($this->getRequest()->getParam("place_id")) ->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("\x54he P\117S/warehouse was successfully deleted"));
				$this->_redirect("*/*/");
			} catch (Exception $x2e) {
				Mage::getSingleton("adminhtml/session")->addError($x2e->getMessage());
				$this->_redirect("*/*/edit", array("place_id" => $this->getRequest()->getParam("place_id")));
			}
		}
		$this->_redirect("*/*/");
	}


	public function exportCsvAction()
	{
		$fileName = "pointofsale.csv";
		$header = null;
		$collection = Mage::getModel("pointofsale/pointofsale")->getCollection();
		$header.="customer_group" . "\t";
		$header.="store_id" . "\t";
		$header.="order" . "\t";
		$header.="store_code" . "\t";
		$header.="name" . "\t";
		$header.="address_line_\61" . "\t";
		$header.="address_line_\62" . "\t";
		$header.="city" . "\t";
		$header.="state" . "\t";
		$header.="postal_code" . "\t";
		$header.="country_code" . "\t";
		$header.="main_phone" . "\t";
		$header.="email" . "\t";
		$header.="hours" . "\t";
		$header.="description" . "\t";
		$header.="longitude" . "\t";
		$header.="latitude" . "\t";
		$header.="status" . "\t";
		$header.="image" . "\t";
		foreach ($collection as $item) {
			$text.= $item->getData("customer_group") . "\t";
			$text.= $item->getData("store_id") . "\t";
			$text.= $item->getData("order") . "\t";
			$text.= $item->getData("store_code") . "\t";
			$text.= $item->getData("name") . "\t";
			$text.= $item->getData("address_line_\61") . "\t";
			$text.= $item->getData("address_line_\62") . "\t";
			$text.= $item->getData("city") . "\t";
			$text.= $item->getData("state") . "\t";
			$text.= $item->getData("postal_code") . "\t";
			$text.= $item->getData("country_code") . "\t";
			$text.= $item->getData("main_phone") . "\t";
			$text.= $item->getData("email") . "\t";
			$text.= $item->getData("hours") . "\t";
			$text.= $item->getData("description") . "\t";
			$text.= $item->getData("longitude") . "\t";
			$text.= $item->getData("latitude") . "\t";
			$text.= $item->getData("status") . "\t";
			$text.= $item->getData("image") . "\t";
			$text.= "\x0d\x0a";
		}
		$this->_sendUploadResponse($fileName, $header . "\x0d\x0a" . $text);
	}


	protected function _sendUploadResponse($filename, $content, $contentType = "application/octet-stream")
	{
		$response = $this->getResponse();
		$response->setHeader("\x48\x54\124P/\61\56\61 \620\x30 \117\x4b", "");
		$response->setHeader("Pragma", "public", true);
		$response->setHeader("Cache-Control", "must-revalidate\x2c post-check=\60\x2c pre-check=0", true);
		$response->setHeader("Content-Disposition", "attachment\73 filename=" . $filename);
		$response->setHeader("Last-Modified", date("r"));
		$response->setHeader("Accept-Ranges", "bytes");
		$response->setHeader("Content-Length", strlen($content));
		$response->setHeader("Content-type", $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}


	public function stateAction()
	{
		$x35 = $this->getRequest()->getParam('country');
		$x36[] = "<option value=''>Please Select</option>";
		if ($x35 != '') {
			$x37 = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($x35)->load();
			foreach ($x37 as $x38) {
				$x36[] = "<option value='" . $x38->getCode() . "'>" . $x38->getDefaultName() . "</option>";
			}
		}

		if (count($x36) == 1) die("<option value=''>------</option>");
		else die(implode(' ', $x36));
	}

};