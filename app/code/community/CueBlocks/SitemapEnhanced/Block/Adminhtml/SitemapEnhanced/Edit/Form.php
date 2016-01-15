<?php

/**
 * Description of 
 * @package   CueBlocks_SitemapEnhanced
 * ** @company   CueBlocks - http://www.cueblocks.com/
 
 */
class CueBlocks_SitemapEnhanced_Block_Adminhtml_SitemapEnhanced_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Init form
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemapEnhanced_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Sitemap Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('sitemapEnhanced_sitemap');

        $form = new Varien_Data_Form(array(
                    'id'     => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ));

        $fieldset = $form->addFieldset('add_sitemap_form', array('legend' => Mage::helper('sitemapEnhanced')->__('Sitemap')));

        if ($model->getId()) {
            $fieldset->addField('sitemap_id', 'hidden', array(
                'name' => 'sitemap_id',
            ));
        }

        $fieldset->addField('sitemap_filename', 'text', array(
            'label'    => Mage::helper('sitemapEnhanced')->__('File name of your XML Sitemap:'),
            'name'     => 'sitemap_filename',
            'required' => true,
//            'after_element_html' => '<div id="row_sitemapEnhanced_general_useindex_comment" class="system-tooltip-box" style="height: 166px; display: none; ">fsfsdfsdfsd</div>',
            'note'     => Mage::helper('adminhtml')->__('This will be the name of your XML Sitemap \'Index\' file. </br>
                                                         Make sure that you declare the exact same sitemap file name in your robots.txt file.</br>
                                                         The extension will append add \'_index\' to your name. </br> 
                                                         For eg. if you give the name as \'abc\', then the Sitemap index file that will be generated will be \'/abc_index.xml\'. </br> 
                                                         All sub-sitemaps, will start with the name \'abc\'. </br>  
                                                         <b>Warning:</b> If there is a pre-existing XML sitemap file with this exact name, at the same location, it will be over-written and it can\'t be restored. </br>
                                                         Please double check or speak with your SEO team if you are in doubt.'),
            'value'    => $model->getSitemapFilename()
        ));

        $fieldset->addField('sitemap_path', 'text', array(
            'label'    => Mage::helper('sitemapEnhanced')->__('Root location:'),
            'name'     => 'sitemap_path',
            'required' => true,
            'note'     => Mage::helper('adminhtml')->__('This is the location where your \'Index\' Sitemap file will be generated. </br>
                                                         Enter directory name followed by / to generate the sitemap at the specified directory, or simply enter / to generate the sitemap at the root location. </br>
                                                         Please follow <a target="_blank" href="http://www.sitemaps.org/protocol.html">Sitemap protocol specification</a> regarding file location.</br>
                                                         Please ensure that a directory by that name is existing at the specified location and it has \'write\' permission.</br>
                                                         For the XML sitemap Index file to be easily picked up by Search Engines, please ensure that this path is specified in your robots.txt file a well.'),
            'value'    => $model->getSitemapPath()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'label'    => Mage::helper('sitemapEnhanced')->__('Store View:'),
                'title'    => Mage::helper('sitemapEnhanced')->__('Store View'),
                'name'     => 'store_id',
                'required' => true,
                'value'    => $model->getStoreId(),
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name'  => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }
        
        $fieldset->addField('legend', 'note', array(
            'name'  => 'legend',
            'label'    => Mage::helper('sitemapEnhanced')->__('Legend:'),
            'note'     => Mage::helper('adminhtml')->__('<span class="required">*</span> required fields.'),
           
        ));

        $fieldset->addField('generate', 'hidden', array(
            'name'  => 'generate',
            'value' => ''
        ));

        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
