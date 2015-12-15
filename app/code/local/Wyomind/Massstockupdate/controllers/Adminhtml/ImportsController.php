<?php

class Wyomind_Massstockupdate_Adminhtml_ImportsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {

        $this->loadLayout()
                ->_setActiveMenu("system/convert/import");
        return $this;
    }

    public function indexAction()
    {

        $this->_initAction()
                ->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/convert/massstockupdate');
    }

    public function editAction()
    {


        $id = $this->getRequest()->getParam('profile_id');
        $model = Mage::getModel('massstockupdate/import')->load($id);

        if ($model->getProfileId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('massstockupdate_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('massstockupdate/import')->_addBreadcrumb(Mage::helper('massstockupdate')->__('Stock Updater'), ('Stock Updater'));
            $this->_addBreadcrumb(Mage::helper('massstockupdate')->__('Stock Updater'), ('Stock Updater'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()
                            ->createBlock('massstockupdate/adminhtml_import_edit'))
                    ->_addLeft($this->getLayout()
                            ->createBlock('massstockupdate/adminhtml_import_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('massstockupdate')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {

        $this->_forward('edit');
    }

    public function saveAction()
    {

        // check if data sent
        if ($data = $this->getRequest()->getPost()) {


            // init model and set data
            $model = Mage::getModel('massstockupdate/import');

            if ($this->getRequest()->getParam('profile_id')) {
                $model->load($this->getRequest()->getParam('profile_id'));
            }

            $model->setData($data);


            // try to save it
            try {

                // save the data
                $model->save();

                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('massstockupdate')->__('The profile has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // go to grid or forward to run action
                if ($this->getRequest()->getParam('run')) {
                    $this->getRequest()->setParam('profile_id', $model->getProfileId());
                    $this->_forward('run');
                    return;
                }

                $this->getRequest()->setParam('profile_id', $model->getProfileId());
                $this->_forward('edit');
                return;
            } catch (Exception $e) {

                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('profile_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {

        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('profile_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('massstockupdate/import');
                $model->setId($id);
                // init and load ordersexporttool model


                $model->load($id);

                $model->delete();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('massstockupdate')->__('The profil has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());

                $this->_redirect('*/*/');
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('massstockupdate')->__('Unable to find the profile to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    public function ftpAction()
    {
        $ftpHost = $this->getRequest()->getParam('ftp_host');
        $ftpLogin = $this->getRequest()->getParam('ftp_login');
        $ftpPassword = $this->getRequest()->getParam('ftp_password');
        $ftpDir = $this->getRequest()->getParam('ftp_dir');
        $useSftp = $this->getRequest()->getParam('use_sftp');
        $ftpActive = $this->getRequest()->getParam('ftp_active');

        if ($useSftp)
            $ftp = new Varien_Io_Sftp();
        else
            $ftp = new Varien_Io_Ftp();

        try {
            $ftp->open(
                    array(
                        'host' => $ftpHost,
                        'user' => $ftpLogin, //ftp
                        'username' => $ftpLogin, //sftp
                        'password' => $ftpPassword,
                        'timeout' => '120',
                        'path' => $ftpDir,
                        'passive' => !($ftpActive)
                    )
            );



            $ftp->write(null, null);
            $ftp->close();



            die("Connection succeeded");
        } catch (Exception $e) {
            die(Mage::helper("massstockupdate")->__("Ftp error : ") . $e->getMessage());
        }
    }

    private function downloadFile()
    {
        $ftpHost = $this->getRequest()->getParam('ftp_host');
        $ftpLogin = $this->getRequest()->getParam('ftp_login');
        $ftpPassword = $this->getRequest()->getParam('ftp_password');
        $ftpDir = $this->getRequest()->getParam('ftp_dir');
        $useSftp = $this->getRequest()->getParam('use_sftp');
        $ftpActive = $this->getRequest()->getParam('ftp_active');

        if ($useSftp)
            $ftp = new Varien_Io_Sftp();
        else
            $ftp = new Varien_Io_Ftp();
        try {
            $ftp->open(
                    array(
                        'host' => $ftpHost,
                        'user' => $ftpLogin, //ftp
                        'username' => $ftpLogin, //sftp
                        'password' => $ftpPassword,
                        'timeout' => '120',
                        'path' => $ftpDir,
                        'passive' => !($ftpActive)
                    )
            );
            if ($ftp->cd($ftpDir)) {
                $filename = 'var/tmp/stock_import_' . time() . '.csv';

                $io = new Varien_Io_File();
                $realPath = $io->getCleanPath(Mage::getBaseDir() . '/var/tmp/');
                if (is_readable($realPath)) {
                    $content = $ftp->read($this->getRequest()->getParam('file_path'), $filename);
                    $ftp->close();
                    if (!$content) {
                        return array("The file '" . $this->getRequest()->getParam('file_path') . "' cannot be fetched.");
                    }
                    return $filename;
                } else {
                    $ftp->close();
                    return array("Please make sure that var/tmp is writable.");
                }
            } else {
                $ftp->close();
                return array("Cannot access '$ftpDir' on this server.");
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function transformXmlToCsv($file, $xpath, $limit = -1)
    {
        $xml = new SimpleXMLElement(file_get_contents($file));


        $xml = $xml->xpath($xpath);

        $columns = array();
        $products = array();

        foreach ($xml as $product) {
            $tmp = array();
            foreach ($product as $key => $value) {
                if (!in_array($key, $columns)) {
                    $columns[] = $key;
                }
                $tmp[$key] = (string) $value;
            }
            $products[] = $tmp;
        }

        $csv = implode(";", $columns) . "\n";
        $counter = 0;
        foreach ($products as $product) {
            if ($limit != -1 && $counter > $limit) {
                break;
            }
            $tmp = array();
            foreach ($columns as $column) {
                if (array_key_exists($column, $product)) {
                    $tmp[] = trim(str_replace("\n", " ", $product[$column]));
                } else {
                    $tmp[] = "";
                }
            }
            $csv .= implode(";", $tmp) . "\n";
            $counter++;
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $realPath = $io->getCleanPath(Mage::getBaseDir() . '/var/tmp/');
        $io->open(array('path' => $realPath));
        $csv_file = "msu_" . time() . ".csv";
        if ($io->isWriteable($realPath)) {
            //var_dump($realPath.$csv_file); return;
            $io->streamOpen($realPath . $csv_file, 'w');
            $io->streamWrite($csv);
            $io->streamClose();
            return "var/tmp/" . $csv_file;
        } else {
            return array("Cannot access '$realPath' on this server.");
        }
    }

    public function loadfileAction()
    {

        $tmp_file = $this->getRequest()->getParam('file');
        if ($this->getRequest()->getParam('file_system_type') === "1") {

            $content = $this->downloadFile();

            if (is_array($content)) {
                $rtn['status'] = 'error';
                $rtn['body'] = $content[0];
                die(json_encode($rtn));
            } else {
                $tmp_file = $content;
            }
        }

        $io = new Varien_Io_File();
        $realPath = $io->getCleanPath(Mage::getBaseDir() . '/' . $tmp_file);
        if ($tmp_file == '') {
            $rtn['status'] = 'error';
            $rtn['body'] = (Mage::helper('massstockupdate')->__('File path can\'t be empty.'));
        } elseif (stripos($tmp_file, 'csv') < 1 && stripos($tmp_file, 'xml') < 1) {
            $rtn['status'] = 'error';
            $rtn['body'] = (Mage::helper('massstockupdate')->__('Wrong file type. "%s" must be a csv or xml file.', $realPath));
        } elseif (!$io->fileExists($realPath, false)) {
            $rtn['status'] = 'error';
            $rtn['body'] = (Mage::helper('massstockupdate')->__('Wrong file path. "%s" is not a file.', $realPath));
        } elseif (!is_readable($realPath)) {
            $rtn['status'] = 'error';
            $rtn['body'] = (Mage::helper('massstockupdate')->__('Please make sure that "%s" is readable by web-server.', $realPath));
        } else {
            $rtn['status'] = 'valid';

            if ($this->getRequest()->getParam('file_type') == 1) { // xml
                $csv_file = $this->transformXmlToCsv($realPath, $this->getRequest()->getParam('xpath'), 1000);
                if (is_array($csv_file)) {
                    $rtn['status'] = 'error';
                    $rtn['body'] = $csv_file[0];
                    die(json_encode($rtn));
                }
                $realPath = $io->getCleanPath(Mage::getBaseDir() . '/' . $csv_file);
            }

            if ($this->getRequest()->getParam('file_type') === "1") {
                $fileSeparator = ";";
                $fileEnclosure = "none";
            } else {
                $fileSeparator = $this->getRequest()->getParam('separator');
                $fileEnclosure = $this->getRequest()->getParam('enclosure');
            }


            $io->streamOpen($realPath, 'r');
            $rtn = array();
            $i = 0;

            if (Mage::helper('core')->isModuleEnabled('Wyomind_Advancedinventory')) {
                $places = Mage::getModel('pointofsale/pointofsale')->getPlaces();
                foreach ($places as $p) {
                    $rtn["places"][$i]['label'] = $p->getName();
                    $rtn["places"][$i]['value'] = $p->getPlaceId();
                    $rtn["places"][$i]['id'] = $p->getPlaceId();
                    $rtn["places"][$i]['style'] = "store " . $p->getPlaceId();
                    $i++;
                }
                $rtn["places"][$i]['label'] = "Manage Local Stock";
                $rtn["places"][$i]['value'] = "manage_local_stock";
                $rtn["places"][$i]['id'] = 'manage_local_stock';
                $rtn["places"][$i]['style'] = "manage_local_stock";
                $i++;
            }


            if ($this->getRequest()->getParam('autoSetInStock') == "0") {
                $rtn["places"][$i]['label'] = "Stock status";
                $rtn["places"][$i]['value'] = "is_in_stock";
                $rtn["places"][$i]['id'] = 'is_in_stock';
                $rtn["places"][$i]['style'] = "is_in_stock";
                $i++;
            }
            /* if ($this->getRequest()->getParam('autoSetManageStock') == "0") {
              $rtn["places"][$i]['label'] = "Manage stock";
              $rtn["places"][$i]['value'] = "manage_stock";
              $rtn["places"][$i]['id'] = 'manage_stock';
              $rtn["places"][$i]['style'] = "manage_stock";
              $i++;
              } */
            // if total stock are not sync with local stocks or  if total stock are sync with local stocks but sync is done by user
            if ($this->getRequest()->getParam('autoSetTotal') == "0") {
                $rtn["places"][$i]['label'] = "Total Stock";
                $rtn["places"][$i]['value'] = "total";
                $rtn["places"][$i]['id'] = 'total';
                $rtn["places"][$i]['style'] = "total";
                $i++;
            } else {
                $rtn["places"][$i]['label'] = "used";
                $rtn["places"][$i]['value'] = "used";
                $rtn["places"][$i]['id'] = 'used';
                $rtn["places"][$i]['style'] = "used";
                $i++;
            }


            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $tableEet = $resource->getTableName('eav_entity_type');
            $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
            $data = $read->fetchAll($select);
            $typeId = $data[0]['entity_type_id'];

            function cmp($a, $b)
            {

                return ($a['attribute_code'] < $b['attribute_code']) ? -1 : 1;
            }

            /*  Liste des  attributs disponible dans la bdd */

            $attributesList = Mage::getResourceModel('eav/entity_attribute_collection')
                    ->setEntityTypeFilter($typeId)
                    ->addSetInfo()
                    ->getData();
            usort($attributesList, "cmp");


            foreach ($attributesList as $attribute) {
                if (!empty($attribute['frontend_label'])) {
                    $rtn["places"][$i]['label'] = $attribute['attribute_code'];
                    $rtn["places"][$i]['value'] = $attribute['attribute_code'];
                    $rtn["places"][$i]['id'] = "attribute-" . $attribute['attribute_code'] . "-" . $attribute['attribute_id'] . "-" . $attribute['backend_type'];
                    $rtn["places"][$i]['style'] = "attribute";
                    $i++;
                }
            }



            $rtn["places"][$i]['label'] = "not used";
            $rtn["places"][$i]['value'] = "not-used";
            $rtn["places"][$i]['id'] = 'not-used';
            $rtn["places"][$i]['style'] = "not-used";
            $i++;

            $sku_offset = $this->getRequest()->getParam('skuOffset');
            $offset = $sku_offset - 1;
            $l = 0;
            if ($fileEnclosure != "none") {
                while (false !== ($csvLine = $io->streamReadCsv($fileSeparator, $fileEnclosure)) && $l < 1000) {
                    $skus = array_splice($csvLine, $offset, 1);
                    array_unshift($csvLine, $skus[0]);

                    if (strlen($csvLine[0]) > 50)
                        $csvLine[0] = '<span title="' . $csvLine[0] . '">' . substr($csvLine[0], 0, 50) . '...' . '</span>';
                    $rtn['body'][$l] = $csvLine;
                    $rtn['body'][$l][] = 0;
                    $l++;
                }
            }
            else {
                while (false !== ($csvLine = $io->streamReadCsv($fileSeparator)) && $l < 1000) {
                    $skus = array_splice($csvLine, $offset, 1);
                    array_unshift($csvLine, $skus[0]);

                    if (strlen($csvLine[0]) > 50)
                        $csvLine[0] = '<span title="' . $csvLine[0] . '">' . substr($csvLine[0], 0, 50) . '...' . '</span>';
                    $rtn['body'][$l] = $csvLine;
                    $rtn['body'][$l][] = 0;
                    $l++;
                }
            }
            $io->streamClose();
            if ($this->getRequest()->getParam('useCustomRules')) {
                $rules = $this->getRequest()->getParam('customRules');
                foreach ($rtn['body'] as $i => $line) {
                    eval(str_replace('$C[', '$line[', $rules));
                    $rtn['body'][$i] = $line;
                }
            }
        }
        die(json_encode($rtn));
    }

    public function runAction()
    {


        $id = $this->getRequest()->getParam('profile_id');

        $import = Mage::getModel('massstockupdate/import');
        $import->setId($id);
        if (Mage::getStoreConfig("massstockupdate/import/backup_enabled"))
            $import->backup();

        if ($import->load($id)) {



            try {
                if ($data = $import->importProcess()) {

                    if ($import->_demo) {
                        $this->_getSession()->addError(Mage::helper('massstockupdate')->__("Invalid license."));
                        Mage::getConfig()->saveConfig('massstockupdate/license/activation_code', '', 'default', '0');
                        Mage::getConfig()->cleanCache();
                    } else {
                        $this->_getSession()->addSuccess(Mage::helper('massstockupdate')->__('The profile  "%s" has been executed.', $import->getProfileName()));
                        if (Mage::getStoreConfig("massstockupdate/import/report_debug")) {
                            $this->_getSession()->addSuccess(Mage::helper('massstockupdate')->__('No data updated (debug mode)'));
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addException($e, Mage::helper('massstockupdate')->__('Unable to run the profile.'));
            }
        } else {
            $this->_getSession()->addError(Mage::helper('massstockupdate')->__('Unable to find a profile to run.'));
        }

        // go to grid
        $this->_redirect('*/*/');
    }

    public function backupAction()
    {


        $import = Mage::getModel('massstockupdate/import');
        $import->backup();


        $this->_redirect('*/*/');
    }

}
