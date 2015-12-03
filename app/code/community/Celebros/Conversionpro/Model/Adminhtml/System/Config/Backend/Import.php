<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Conversionpro
 * @author		Shay Acrich (email: me@shayacrich.com)
 *
 */
class Celebros_Conversionpro_Model_Adminhtml_System_Config_Backend_Import extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    /**
	 * This responds to a settings import request from the advanced section of the admin panel configurations.
	 * The code here should get the imported file, run through it, and update every value that's included in it.
     */
    protected function _beforeSave()
    {
		parent::_beforeSave();

		//List of settings fields that we want to import or export.
		$fields = array(
			'general_settings' => array(
				'host',
				'port',
				'sitekey'
			),
			'export_settings' => array(
				'zipname',
				'datahistoryname',
				'delimiter',
				'enclosed_values',
				'min_tier_price',
				'type',
				'path',
				'ftp_host',
				'ftp_port',
				'ftp_user',
				'ftp_password',
				'passive',
				'cron_expr',
				'extra_tables'
			),
			'display_settings' => array(
				'campaigns_enabled',
				'alt_message',
				'price_selector',
				'enable_multiselect',
				'go_to_product_on_one_result'
			),
			'nav_to_search_settings' => array(
				'nav_to_search',
				'nav_to_search_search_by',
				'nav_to_search_use_full_category_path',
				'nav_to_search_enable_blacklist',
				'nav_to_search_blacklist'
			),
			'anlx_settings' => array(
				'host',
				'cid'
			),
			'autocomplete_settings' => array(
				'autocomplete_enabled',
				'ac_customer_name',
				'ac_frontend_address',
				'ac_scriptserver_address'
			),
			'livesight_settings' => array(
				'livesight_enabled'
			),
			'crosssell_settings' => array(
				'crosssell_enabled',
				'crosssell_limit',
				'upsell_enabled',
				'upsell_limit',
				'crosssell_customer_name',
				'crosssell_request_handle',
				'crosssell_address'
			),
			'advanced' => array(
				'export_chunk_size',
				'export_process_limit',
				'enable_monitoring',
				'enable_connectivity',
				'connectivity_attempts',
				'connectivity_failures'
			)
		);
		
		//@todo clean up all the issets and array_key_exists - they'll probably cause unexpected behavior in case something doesn't run correctly.
		//Fetching the imported settings from the file.
        $imported_settings = json_decode(file_get_contents(Mage::getBaseDir('base') . '/' . $this->_getUploadDir() . '/' . $this->getValue()));
		
		$stores = array(0);
		foreach (Mage::app()->getStores() as $store) {
			$stores[$store->getId()] = $store->getId();
		}
		
		foreach ($stores as $store) {
			$store_settings = Mage::getStoreConfig('conversionpro', $store);
			$isOverrideEnabled = Mage::getStoreConfig('conversionpro/advanced/import_override', $store);
			$scope = ($store == 0) ? 'default' : 'stores';

			foreach ($fields as $group => $values) {

				if (array_key_exists($group, $store_settings) && !is_array($store_settings[$group])) {
					$store_settings[$group] = array();
				}
				
				foreach ($values as $key) {
					$value = '';
					if (array_key_exists($group, $store_settings) && array_key_exists($key, $store_settings[$group])) {
						$value = $store_settings[$group][$key];
					}
					if (!isset($value) || $value == '' || $isOverrideEnabled) {
						if (isset($imported_settings)) {
							if (isset($imported_settings[$store]->$group) && isset($imported_settings[$store]->$group->$key)) {
								
								//Override the existing value in the database.
								Mage::getConfig()->saveConfig('conversionpro/' . $group . '/' . $key, $imported_settings[$store]->$group->$key, $scope, $store);
							} else {
								//Delete the row from the database, so that it'll check the 'Use Default' option.
								Mage::getConfig()->deleteConfig('conversionpro/' . $group . '/' . $key, $scope, $store);
							}
						}
					}
				}
			}
		}
		
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
		
        return $this;
    }
}