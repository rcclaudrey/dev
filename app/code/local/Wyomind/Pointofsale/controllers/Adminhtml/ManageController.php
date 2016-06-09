<?php

class Wyomind_Pointofsale_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->_title($this->__('Manage'))->_title($this->__('POS / Warehouses'));
		$this->loadLayout() ->_setActiveMenu("sales/pointofsale");
		return $this;
	}


	public function indexAction() {
		$this->_initAction() ->renderLayout();
	}


	public function importCsvAction() {
		$this->loadLayout();
		$this->_setActiveMenu("sales/pointofsale");
		$this->_addBreadcrumb(Mage::helper("pointofsale")->__("POS / Warehouses"), ("POS / Warehouses"));
		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
		$this
			->_addContent($this->getLayout()->createBlock("pointofsale/adminhtml_manage_import"))
			->_addLeft($this->getLayout()->createBlock("pointofsale/adminhtml_manage_import_tabs"));
		$this->renderLayout();
	}


	public function editAction() {
		$x34 = $this->getRequest()->getParam("id");
		$x35 = Mage::getModel("pointofsale/pointofsale")->load($x34);
		if ($x35->getId() || $x34 == 0) {
			$x36 = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($x36)) {
				$x35->setData($x36);
			}
			Mage::register("pointofsale_data", $x35);
			$this->loadLayout();
			$this->_title($this->__('Manage'))->_title($this->__('POS / Warehouses'));
			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock("pointofsale/adminhtml_manage_edit")) ->_addLeft($this->getLayout()->createBlock("pointofsale/adminhtml_manage_edit_tabs"));
			$this->renderLayout();
		} else {
			Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pointofsale")->__("Item does not exist"));
			$this->_redirect("*/*/");
		}
	}


	public function newAction() {
		$this->_forward("edit");
	}


	public function saveAction() {
		$x4c="strtolower";
		$x4d="array_pop";
		$x4e="explode";
		$x4f="in_array";
		$x50="implode";
		if ($this->getRequest()->getPost()) {
		$x36 = $this->getRequest()->getPost();
		if (isset($_FILES["file"]["name"]) && $_FILES["file"]["name"] != "") {
		$x39 = 1;
		if ($x4c($x4d($x4e(".", $_FILES["file"]["name"]))) != "csv") Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pointofsale")->__("Wrong file type (" . $_FILES["file"]["type"] . ").<br>Choose a csv file."));
		else {
		$x3a = new Varien_File_Csv;
		$x3a->setDelimiter("\t");
		$x3b = $x3a->getData($_FILES["file"]["tmp_name"]);
		$x35 = Mage::getModel("pointofsale/pointofsale");
		$x3c = $x3b[0];
		while (isset($x3b[$x39])) {
		foreach ($x3b[$x39] as $x3d => $x3e) {
		$x36[$x3c[$x3d]] = $x3e;
		}
		$x35->setData($x36)->save();
		$x39++;
		}
		}
		Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("pointofsale")->__(($x39 - 1) . " places have been imported."));
		$this->_redirect("*/*/importCsv");
		return;
		}
		if (isset($x36["image"]["delete"]) && $x36["image"]["delete"] == 1) {
		$x36["image"] = "";
		}
		else {
		if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != "") {
		try {
		$x3f = new Varien_File_Uploader("image");
		$x3f->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"));
		$x3f->setAllowRenameFiles(true);
		$x3f->setFilesDispersion(false);
		$x40 = Mage::getBaseDir("media") . DS;
		$x3f->save($x40 . "stores", $_FILES["image"]["name"]);
		}
		catch (Exception $x41) {
		}
		$x36["image"] = "stores/" . $_FILES["image"]["name"];
		}
		else unset($x36["image"]);
		}
		$x35 = Mage::getModel("pointofsale/pointofsale");

		if ($x4f('-1', $x36["customer_group"])) $x36["customer_group"] = array("-1");
		$x36["customer_group"] = $x50(',', $x36["customer_group"]);
		if ($x4f('0', $x36["store_id"])) $x36["store_id"] = array("0");
		$x36["store_id"] = $x50(',', $x36["store_id"]);
		$x35->setData($x36) ->setId($this->getRequest()->getParam("place_id"));
		$x35->save();
		try {
		Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("pointofsale")->__("Item was successfully saved"));
		Mage::getSingleton("adminhtml/session")->setFormData(false);
		if ($this->getRequest()->getParam("back")) {
		$this->_redirect("*/*/edit", array("place_id" => $x35->getId()));
		return;
		}
		$this->_redirect("*/*/");
		return;
		}
		catch (Exception $x41) {
		Mage::getSingleton("adminhtml/session")->addError($x41->getMessage());
		Mage::getSingleton("adminhtml/session")->setFormData($x36);
		$this->_redirect("*/*/edit", array("place_id" => $this->getRequest()->getParam("place_id")));
		return;
		}
		}
		Mage::getSingleton("adminhtml/session")->addError(Mage::helper("pointofsale")->__("Unable to find item to save"));
		$this->_redirect("*/*/");
	}


	public function deleteAction() {
		if ($this->getRequest()->getParam("place_id") > 0) {
			try {
				$x35 = Mage::getModel("pointofsale/pointofsale");
				$x35->setId($this->getRequest()->getParam("place_id")) ->delete();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("The POS/warehouse was successfully deleted"));
				$this->_redirect("*/*/");
			} catch (Exception $x41) {
				Mage::getSingleton("adminhtml/session")->addError($x41->getMessage());
				$this->_redirect("*/*/edit", array("place_id" => $this->getRequest()->getParam("place_id")));
			}
		}
		$this->_redirect("*/*/");
	}


	public function exportCsvAction() {
		$x42 = "pointofsale.csv";
		$x43 = null;
		$x44 = Mage::getModel("pointofsale/pointofsale")->getCollection();
		$x43.="customer_group" . "\t";
		$x43.="store_id" . "\t";
		$x43.="order" . "\t";
		$x43.="store_code" . "\t";
		$x43.="name" . "\t";
		$x43.="address_line_1" . "\t";
		$x43.="address_line_2" . "\t";
		$x43.="city" . "\t";
		$x43.="state" . "\t";
		$x43.="postal_code" . "\t";
		$x43.="country_code" . "\t";
		$x43.="main_phone" . "\t";
		$x43.="email" . "\t";
		$x43.="hours" . "\t";
		$x43.="description" . "\t";
		$x43.="longitude" . "\t";
		$x43.="latitude" . "\t";
		$x43.="status" . "\t";
		$x43.="image" . "\t";
		foreach ($x44 as $x45) {
		$x3b.= $x45->getData("customer_group") . "\t";
		$x3b.= $x45->getData("store_id") . "\t";
		$x3b.= $x45->getData("order") . "\t";
		$x3b.= $x45->getData("store_code") . "\t";
		$x3b.= $x45->getData("name") . "\t";
		$x3b.= $x45->getData("address_line_1") . "\t";
		$x3b.= $x45->getData("address_line_2") . "\t";
		$x3b.= $x45->getData("city") . "\t";
		$x3b.= $x45->getData("state") . "\t";
		$x3b.= $x45->getData("postal_code") . "\t";
		$x3b.= $x45->getData("country_code") . "\t";
		$x3b.= $x45->getData("main_phone") . "\t";
		$x3b.= $x45->getData("email") . "\t";
		$x3b.= $x45->getData("hours") . "\t";
		$x3b.= $x45->getData("description") . "\t";
		$x3b.= $x45->getData("longitude") . "\t";
		$x3b.= $x45->getData("latitude") . "\t";
		$x3b.= $x45->getData("status") . "\t";
		$x3b.= $x45->getData("image") . "\t";
		$x3b.= "\x0d\x0a";
		}
		$this->_sendUploadResponse($x42, $x43 . "\x0d\x0a" . $x3b);
	}



	protected function _sendUploadResponse($x42, $x3b, $x46 = "application/octet-stream") {
		$x47 = $this->getResponse();
		$x47->setHeader("HTTP/1.1 200 OK", "");
		$x47->setHeader("Pragma", "public", true);
		$x47->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true);
		$x47->setHeader("Content-Disposition", "attachment;
		filename=" . $x42);
		$x47->setHeader("Last-Modified", date("r"));
		$x47->setHeader("Accept-Ranges", "bytes");
		$x47->setHeader("Content-Length", strlen($x3b));
		$x47->setHeader("Content-type", $x46);
		$x47->setBody($x3b);
		$x47->sendResponse();

		die;
	}


	public function stateAction() {
		$x48 = $this->getRequest()->getParam('country');
		$x49[] = "<option value=''>Please Select</option>";
		if ($x48 != '') {
			$x4a = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($x48)->load();
			foreach ($x4a as $x4b) {
				$x49[] = "<option value='" . $x4b->getCode() . "'>" . $x4b->getDefaultName() . "</option>";
			}
		}
		if (count($x49) == 1) die("<option value=''>------</option>");
		else die(implode(' ', $x49));
	}

}
