<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Model_AlekseonAdminNotification_Observer
{
    public function preDispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $feedModel  = Mage::getModel('alekseon_modulesConflictDetector/alekseonAdminNotification_feed');
            $feedModel->checkUpdate();
        }

    }
}