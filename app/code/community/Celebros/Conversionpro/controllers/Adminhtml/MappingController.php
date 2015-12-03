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
class Celebros_Conversionpro_Adminhtml_MappingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

		$this->_addContent($this->getLayout()->createBlock('conversionpro/adminhtml_settings_edit'))
                ->_addLeft($this->getLayout()->createBlock('conversionpro/adminhtml_settings_edit_tabs'));

		$this->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            /* here's your form processing */
            
            $mappingModel = Mage::getSingleton("conversionpro/mapping");
			
            if (!key_exists('mapping',$post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            foreach ($post['mapping'] as $key => $value) {
            	$mappingModel->load($key);
            	$mappingModel->setXmlField($value);
            	$mappingModel->save();
            }
            
            $message = $this->__('The mapping has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
	
	/**
	 * This is an ajax action for the configuration's advanced settings area cache reset button.
	 * We're deleting all the items under conversionpro_cache and then returning a redirect to the originating address.
	 */
	public function resetCacheAction()
	{
		$col = Mage::getModel('conversionpro/cache')
			->getCollection()
			->load();
		foreach ($col as $item) {
			$item->delete();
		}
		$this->_redirect('*/*');
	}
	
	/**
	 * Ajax action for responding to the export settings button on the admin panel configuration menu, under advanced.
	 * This should return an file that holds a json encoded array of all of Conversion Pro's settings for all store views.
	 */
	public function exportSettingsAction()
    {
        //List of settings fields that we don't want to import or export.
		$excluded_fields = array(
			'conversionpro_enabled',
			'export_enabled',
			'global_export',
			'cron_enabled',
			'reset_cache',
			'export_settings',
			'import_settings',
			'import_override'
		);
		
		$fileName   = 'settings.txt';
        $content = array();
		
		//First, get the settings for the default store view.
		Mage::app()->setCurrentStore(0);
		$default_settings = Mage::getStoreConfig('conversionpro', 0);
		foreach ($default_settings as $group => $values) {
			$content[0][$group] = array();
			
			foreach ($values as $key => $value) {
				//We'll skip this field if it's included in the list of fields that we don't want to import.
				if (in_array($key, $excluded_fields)) {
					continue;
				}
				
				$content[0][$group][$key] = $value;
			}
			
		}
		
		//Now, iterate over all store views, and extract only the delta from the default values.
		//Variables that take their value from the default store view won't appear in the other store view's arrays, 
		// and so they won't get saved again. This is to prevent a scenario where we update the same value for each 
		// store view (because that's what we got from the default store view), and now each store view has that value 
		// with the 'Use Default' checkbox unchecked.
		foreach (Mage::app()->getStores() as $store) {
			Mage::app()->setCurrentStore($store->getId());
			$store_settings = Mage::getStoreConfig('conversionpro', $store->getId());
			foreach ($store_settings as $group => $values) {
				$content[$store->getId()][$group] = array();
			
				foreach ($values as $key => $value) {
					//We'll skip this field if it's included in the list of fields that we don't want to import.
					if (in_array($key, $excluded_fields)) {
						continue;
					}
					
					//We'll only add this key to the array if it's not identical to the value on the global store view.
					if ($content[0][$group][$key] != $value) {
						$content[$store->getId()][$group][$key] = $value;
					}
				}
			}
		}
		
		//Send the file with the results of everything we just did, json encoded.
		$this->_sendUploadResponse($fileName, json_encode($content));
    }

	/**
	 * Prepares files for download, for use in the settings export under exportSettingsAction().
	 */
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
