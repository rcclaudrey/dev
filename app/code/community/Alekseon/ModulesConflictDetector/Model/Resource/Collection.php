<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_ModulesConflictDetector_Model_Resource_Collection extends Alekseon_ModulesConflictDetector_Model_Resource_NonDbCollection
{
    protected function _getColumnsValue($item, $column)
    {
        if ($column == 'rewrites') {
            $data = $item->getData($column);
            $result = false;
            
            if (!isset($data['classes'])) {
                return $result;
            }
            
            $classes = $data['classes'];
            
            foreach($classes as $class) {
                if (!$result || $class['conflict'] == Alekseon_ModulesConflictDetector_Model_Rewrites::NO_CONFLICT_TYPE) {
                    $result = $class['class'];
                }
            }

            return $result;
        } else {
            return parent::_getColumnsValue($item, $column);
        }
    }
}