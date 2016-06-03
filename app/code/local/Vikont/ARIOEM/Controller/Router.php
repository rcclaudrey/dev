<?php

class Vikont_ARIOEM_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard
{

	/**
     * Match the request
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
		if (!$this->_beforeModuleMatch()) {
			return false;
		}

		$front = $this->getFront();
        $path = trim($request->getPathInfo(), '/');
		$pathParts = explode('/', $path, 4);

		if(!isset($pathParts[0])) {
			return parent::match($request);
		}

		switch ($pathParts[0]) {
			case Mage::getStoreConfig('arioem/parts/shortname'):
				$request->setRouteName('arioem');
				$request->setModuleName('Vikont_ARIOEM');
				$request->setControllerModule('Vikont_ARIOEM');
				$controllerName = 'parts'; // empty($pathParts[1]) ? 'index' : $pathParts[1];
				$request->setControllerName($controllerName);
				$actionName = 'index'; // empty($pathParts[2]) ? 'index' : $pathParts[2];
				$request->setActionName($actionName);
				$request->setDispatched(true);

				$brand = empty($pathParts[1]) ? false : $pathParts[1];
				Mage::register('oem_brand', $brand);
				$request->setParam('brand', $brand);

				$partNumber = empty($pathParts[2]) ? false : $pathParts[2];
				Mage::register('oem_part_number', $partNumber);
				$request->setParam('partNumber', $partNumber);

				$controllerClassName = $this->_validateControllerClassName('Vikont_ARIOEM', $controllerName);
				$controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());
				$controllerInstance->dispatch('index');

				return true;
				break;

			case Mage::getStoreConfig('arioem/partcenter/shortname'):
				$request->setRouteName('arioem');
				$request->setModuleName('Vikont_ARIOEM');
				$request->setControllerModule('Vikont_ARIOEM');
				$controllerName = 'partcenter';
				$request->setControllerName($controllerName);
				$actionName = empty($pathParts[1]) ? 'index' : $pathParts[1]; //'index';
				$request->setActionName($actionName);
				$request->setDispatched(true);
				$controllerClassName = $this->_validateControllerClassName('Vikont_ARIOEM', $controllerName);
				$controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());
				$controllerInstance->dispatch('index');
				break;
		}

		return parent::match($request);
	}

}