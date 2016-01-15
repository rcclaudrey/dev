<?php

/**

 */
class CueBlocks_SitemapEnhanced_Model_System_Config_Backend_Pathmap extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave()
    {
        $value = rtrim($this->getValue());

        $value = $this->_checkSeparator($value);

        $this->setValue($value);

        return $this;
    }

    protected function _checkSeparator($path)
    {
        if ($path != '') {
            $first = $path[0];
            $last  = $path[strlen($path) - 1];

            if ($first == '/' || $first == '\\')
                $path = substr($path, 1);
            else
                $path = $path;

            if ($last != '/' && $last != '\\')
                $path = $path . DS;
            else
                $path = $path;
        }

        return $path;
    }

}
