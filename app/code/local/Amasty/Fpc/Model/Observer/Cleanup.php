<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


class Amasty_Fpc_Model_Observer_Cleanup
{
    public function clean()
    {
        if (!Mage::getStoreConfigFlag('amfpc/improvements/cleanup_reports'))
            return;

        $days = +Mage::getStoreConfig('amfpc/improvements/reports_lifetime');

        $lifetime = $days * 24 * 3600;
        $now = time();

        $directories = array(
            Mage::getBaseDir('session'),
            Mage::getBaseDir('var') . DS . 'report'
        );

        foreach ($directories as $dir) {
            if (!is_dir($dir) || !is_readable($dir) || !is_writeable($dir))
                continue;

            $files = scandir($dir);

            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file[0] == '.')
                        continue;

                    $fullPath = $dir . DS . $file;

                    $time = filemtime($fullPath);
                    if ($time && $time + $lifetime < $now) {
                        unlink($fullPath);
                    }
                }
            }
        }
    }
}
