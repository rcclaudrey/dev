<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */ 
$templateCode = 'amrma_status';

$locale = 'en_US';
    
$template = Mage::getModel('adminhtml/email_template');

$template->loadDefault($templateCode, $locale);
$template->setData('orig_template_code', $templateCode);
$template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));

$template->setData('template_code', 'Amasty: RMA');

$template->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);

$template->setId(NULL);

$template->save();

$this->startSetup();

$this->run("
    SET @nStatusId = (select status_id from `{$this->getTable('amrma/status')}` where status_key = 'pending');
    INSERT INTO `{$this->getTable('amrma/template')}` (status_id, store_id, template) VALUES
    (@nStatusId, 0, ".$template->getId().");
");

$this->endSetup(); 
?>