<?php

/**
 * Description of Category
 * @package   CueBlocks_
 * @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_System_Config_Fieldset_Category extends Mage_Adminhtml_Block_System_Config_Form_Field
////extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $scope    = $element->getScope();
        $scope_id = $element->getScopeId();
        $inherit  = $element->getInherit();

        $factoryName = (string) $element->getFieldConfig()->source_model;
        $sourceModel = Mage::getSingleton($factoryName);
        $optionArray = $sourceModel->toOptionArray(True, $scope, $scope_id);
        $element->setValues($optionArray);

        $html = $this->getElementHtml($element);

        return $html;

//        return parent::_getElementHtml($element);
    }

    public function getElementHtml($element)
    {
        $element->addClass('select multiselect');
        $html = '';
        if ($element->getCanBeEmpty() && empty($element->_data['disabled'])) {
            $html .= '<input type="hidden" name="' . $element->getName() . '" value="" />';
        }
        $html .= '<select id="' . $element->getHtmlId() . '" name="' . $element->getName() . '" ' .
                $element->serialize($element->getHtmlAttributes()) . ' multiple="multiple">' . "\n";

        $value = $element->getValue();
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        if ($values = $element->getValues()) {
            foreach ($values as $option)
            {
                if (is_array($option['value'])) {
                    $html .= '<optgroup label="' . $option['label'] . '">' . "\n";
                    foreach ($option['value'] as $groupItem)
                    {
                        $html .= $this->_optionToHtml($element, $groupItem, $value);
                    }
                    $html .= '</optgroup>' . "\n";
                } else {
                    $html .= $this->_optionToHtml($element, $option, $value);
                }
            }
        }

        $html .= '</select>' . "\n";
        $html .= $element->getAfterElementHtml();

        return $html;
    }

    protected function _optionToHtml($element, $option, $selected)
    {
        $html = '<option value="' . htmlspecialchars($option['value'], 2) . '"';
        $html.= isset($option['title']) ? 'title="' . htmlspecialchars($option['title']) . '"' : '';
        $html.= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
        if (in_array((string) $option['value'], $selected)) {
            $html.= ' selected="selected"';
        }
        $html.= '>' . htmlspecialchars(($option['label']), 2) . '</option>' . "\n";
        return $html;
    }

//    public function render(Varien_Data_Form_Element_Abstract $element)
//    {
//        
//        return $this->toHtml();
//    }
}

