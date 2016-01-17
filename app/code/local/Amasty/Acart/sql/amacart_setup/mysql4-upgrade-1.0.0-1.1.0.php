<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

$templateCode = 'amacart_template_main_template';

$locale = 'en_US';
    
$template = Mage::getModel('adminhtml/email_template');

$template->loadDefault($templateCode, $locale);
$template->setData('orig_template_code', $templateCode);
$template->setData('template_variables', Zend_Json::encode($template->getVariablesOptionArray(true)));

$template->setData('template_code', Amasty_Acart_Model_Schedule::DEFAULT_TEMPLATE_CODE);

$template->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);

$template->setId(NULL);

$template->save();

?>