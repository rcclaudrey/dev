<?php

class Vikont_Fitment_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$this->loadLayout()->renderLayout();
	}



	protected function _prepareParams($params)
	{
		$pageMode = isset($params['pageMode']) ? $params['pageMode'] : '';
		unset($params['pageMode']);

		$tmsActivityId = isset($params['activity']) ? $params['activity'] : null;
		Mage::register('current_activity', $tmsActivityId);
		if(null === $tmsActivityId) {
			$tmsActivityId = Vikont_Fitment_Helper_Data::getActivityId();
		}
		unset($params['activity']); // TMS activity

		$fitmentId = isset($params['fitment']) ? $params['fitment'] : null;
		unset($params['fitment']);

		$vehicleName = isset($params['vehicle']) ? $params['vehicle'] : '';
		unset($params['vehicle']);

		$brandId = isset($params['brand']) ? $params['brand'] : '';
		unset($params['brand']);

		$categoryId = isset($params['category']) ? $params['category'] : (
				(	('tireBySize' == $pageMode)
				||	('tireByRide' == $pageMode)	)
				?	Mage::helper('fitment')->getTiresCategoryId()
				:	''
			);
		unset($params['category']);

		$subCategoryId = isset($params['subCategory']) ? $params['subCategory'] : '';
		unset($params['subCategory']);

		$viewMode = isset($params['viewMode']) ? $params['viewMode'] : Vikont_Fitment_Block_Fitment_Toolbar::getViewMode();
		unset($params['viewMode']);

		$options = array(
			'sort' => Vikont_Fitment_Block_Fitment_Toolbar::getDefaultSort(),
			'skip' => 0,
			'take' => (int)Vikont_Fitment_Block_Fitment_Pager::getDefaultPageSize(),
			'term' => '',
			'includeFacets' => 'true',
			'minPrice' => null,
			'maxPrice' => null,
		);

		$options = array_merge($options, $params);

		if($brandId)	{	$options['brandId'] = array($brandId);	}
		if($categoryId)	{	$options['categoryId'] = array($categoryId);	}
		if($subCategoryId)	{	$options['subCategoryId'] = array($subCategoryId);	}

		$rideRequired = true;

		if('tireBySize' == $pageMode) {
			$fitmentId = null;
			$rideRequired = false;
		} else if($fitmentId) {
			$rideRequired = false;
			$options['fitmentId'] = $fitmentId;
			$ride = Mage::helper('fitment')->setCurrentRide($tmsActivityId, $fitmentId, $vehicleName);
			$vehicleName = $ride['name'];
		} else {
			$ride = Mage::helper('fitment')->getCurrentRide($tmsActivityId);
			$fitmentId = $ride['id'];
			$vehicleName = $ride['name'];
			$options['fitmentId'] = $fitmentId;
			$rideRequired = !$fitmentId;
		}

		$pageHeader = $pageMode
				?	$this->__('Tireshop')
				:	$this->__('Shop by Fitment');

		return array(
			'params' => array(
				'activity' => $tmsActivityId,
			),
			'options' => $options,
			'viewMode' => $viewMode,
			'activityId' => $tmsActivityId, // TMS activity
			'ariActivityId' => Vikont_Fitment_Helper_Data::getTmsActivity($tmsActivityId, 'ari_activity'),
			'fitmentId' => $fitmentId,
			'vehicle' => $vehicleName,
			'pageMode' => $pageMode,
			'rideRequired' => $rideRequired,
			'pageHeader' => $pageHeader,
		);
	}



	public function initAction()
	{
//Mage::register('vd', 1);
		$params = array();
		parse_str($this->getRequest()->getParam('hash'), $params);
		$params = $this->_prepareParams($params);
		$response = array(
			'config' => array(
				'baseURL' => rtrim(Mage::getUrl('fitment/index/page'), '/'),
				'addToCartURL' => Mage::getUrl('fitment/index/addToCart'),
				'viewProductURL' => Mage::getUrl('fitment/index/viewProduct'),
				'params' => $params['params'],
				'options' => $params['options'],
				'viewMode' => $params['viewMode'],
				'pageMode' => $params['pageMode'],
				'filterValuesShrinkerText' => array(
					'more' => $this->__('+ Show more'),
					'less' => $this->__('- Show less'),
				),
				'redirectMessage' => $this->__('You need to select the options of this product before adding it to the Cart. You\'ll be redirected to the product page now.'),
			),
			'rideSelectorConfig' => array(
				'baseURL' => rtrim(Mage::getUrl('fitment/index/fitment'), '/'),
				'activity' => $params['activityId'], // TMS activity
				'emptyText' => array(
					'makeSelect' => $this->__('-- Select make --'),
					'yearSelect' => $this->__('-- Select year --'),
					'modelSelect' => $this->__('-- Select model --'),
					'rideName' => $this->__('Not selected'),
				),
				'fitment' => array(
					'id' => $params['fitmentId'],
					'name' => $params['vehicle'] ? $params['vehicle'] : $this->__('Not selected'),
				),
			),
			'viewPopupConfig' => array(
				'requestURL' => Mage::getUrl('fitment/index/viewProduct'),
				'requestRatingURL' => Mage::getUrl('fitment/index/viewProductRating'),
				'requestFitmentURL' => Mage::getUrl('fitment/index/viewProductFitment'),
				'emptyText' => array(
					'noResultMessage' => $this->__('No tire options available for this configuration'),
				),
				'errorMessage' => $this->__('Some error occurred, please contact site admin'),
			),
			'pageHeader' => $params['pageHeader'],
			'blocks' => array(),
			'errorMessage' => '',
		);

		Mage::register('ari_params', array(
			'caller' => '*',
			'params' => $response['config']['params'],
			'options' => $response['config']['options'],
			'viewMode' => $response['config']['viewMode'],
			'pageMode' => $response['config']['pageMode'],
			'rideRequired' => $params['rideRequired'],
			'vehicle' => $params['vehicle'],
		));

		try {
			$blockScope = $params['rideRequired']
				?	'init'
				:	'*';

			$remoteData = $params['rideRequired']
				?	null
				:	Mage::helper('fitment/api')->request(
						'search',
						array($params['ariActivityId']),
						$response['config']['options']
					);

			Mage::register('ari_data', $remoteData);


			foreach(Vikont_Fitment_Helper_Data::getBlockDependency($blockScope) as $blockName) {
				$response['blocks'][$blockName] = $this->getLayout()->createBlock('fitment/fitment_' . $blockName)->toHtml();
			}

		} catch (Exception $e) {
			$response['errorMessage'] = Vikont_Fitment_Helper_Data::reportError($e->getMessage());
		}

		echo json_encode($response);
		die;
	}



	public function pageAction()
	{
//Mage::register('vd', 1);
		$params = $this->getRequest()->getParams();
		$responseData = array(
			'params' => $params,
			'update' => array(),
		);

		try {
			if(!isset($params['options'])) { $params['options'] = array(); }
			$params['options'] = array_merge(Vikont_Fitment_Helper_Data::collectCommonParams(), $params['options']);

			$tmsActivityId = isset($params['params']['activity']) ? $params['params']['activity'] : null;
			Mage::register('current_activity', $tmsActivityId);
			$ariActivityId = Vikont_Fitment_Helper_Data::getTmsActivity($tmsActivityId, 'ari_activity');
			$pageMode = isset($params['pageMode']) ? $params['pageMode'] : null;

			$fitmentId = null;
			$params['options']['fitmentId'] = null;

			if('tireBySize' == $pageMode) {
//				$fitmentId = null;
//				$params['options']['fitmentId'] = null;
				Mage::helper('fitment')->setCurrentRide($tmsActivityId, null);
			} else {
				$fitmentId = isset($params['options']['fitmentId'])
					?	$params['options']['fitmentId']
					:	null;

				if(!$fitmentId) {
					$ride = Mage::helper('fitment')->getCurrentRide($tmsActivityId);
					$fitmentId = $ride['id'];
					if($fitmentId) {
						$responseData['update'] = array(
							'rideSelector' => array('config' => array('fitment' => array(
								'id' => $fitmentId,
								'name' => $ride['name'],
							))),
							'options' => array('fitmentId' => $fitmentId),
						);
					}
					$params['options']['fitmentId'] = $fitmentId;
					$params['vehicle'] = $ride['name'];
//					Mage::helper('fitment')->setCurrentRide($tmsActivityId, $fitmentId); // vehicle name is empty so it is to be gotten from ARI request
				}
			}


			$rideIsRequired = !($fitmentId || ('tireBySize' == $pageMode));
			$params['rideRequired'] = $rideIsRequired;
			$blockScope = $rideIsRequired
				?	'rideRequired'
				:	$params['caller'];

			Mage::register('ari_params', $params);

			$remoteData = $params['rideRequired']
				?	null
				:	Mage::helper('fitment/api')->request('search', array($ariActivityId), $params['options']);

			Mage::register('ari_data', $remoteData);

			$blockList = Vikont_Fitment_Helper_Data::getBlockDependency($blockScope);
			$blocks = array();

			foreach($blockList as $blockName) {
				$blocks[$blockName] = $this->getLayout()->createBlock('fitment/fitment_' . $blockName)->toHtml();
			}

			$responseData['blocks'] = $blocks;

			$errorMessage = '';
		} catch(Exception $e) {
			$errorMessage = Vikont_Fitment_Helper_Data::reportError($e->getMessage());
		}

		$responseData['error_message'] = $errorMessage;

		$responseAjax = new Varien_Object($responseData);
		$this->getResponse()->setBody($responseAjax->toJson());
	}



	/*
	 * Product details page fitment requests receiver & responder
	 */
	public function fitmentRequestAction()
	{
//Mage::register('vd', 1);
		$params = $this->getRequest()->getParams();

		if(isset($params['activity'])) {
			$params['activity'] = Vikont_Fitment_Helper_Data::getTmsActivity($params['activity'], 'ari_activity');
		}

		try {
			$remoteData = Mage::helper('fitment')->getFitmentValues($params);
			$errorMessage = '';
		} catch(Exception $e) {
			$errorMessage = Vikont_Fitment_Helper_Data::reportError($e->getMessage());
		}

		$response = array(
			'subject' => $this->getRequest()->getParam('subject'),
			'data' => $remoteData,
			'error' => (bool)$errorMessage,
			'errorMessage' => $errorMessage,
		);

		echo json_encode($response);
		die;
	}



	/*
	 * Product details page fitment saver
	 */
	public function fitmentSaveAction()
	{
//Mage::register('vd', 1);
		$fitmentId = $this->getRequest()->getParam('fitment');
		$vehicleName = $this->getRequest()->getParam('vehicle');
		$tmsActivityId = $this->getRequest()->getParam('activity');
		$ride = Mage::helper('fitment')->setCurrentRide($tmsActivityId, $fitmentId, $vehicleName);

		if($ride) {
			$response = array(
				'message' => $this->__('Current fitment selection has been set as default'),
				'errorMessage' => '',
			);
		} else {
			$response = array(
				'errorMessage' => $this->__('Error saving fitment'),
			);
		}

		echo json_encode($response);
		die;
	}



	/*
	 * Product details page fitment reset;
	 * Reserved as it currently doesn't have any frontend control element to be assigned
	 */
	public function fitmentResetAction()
	{
		Mage::getSingleton('core/session')->unsRide();
		setcookie(Vikont_Fitment_Helper_Data::FITMENT_ID_COOKIE_NAME, '', time() - 3600*24*30);

		$response = array(
			'errorMessage' => ''
		);

		echo json_encode($response);
		die;
	}



	public function viewProductAction()
	{
//Mage::register('vd', 1);
		$params = $this->getRequest()->getParams();

		$ariActivityId = Vikont_Fitment_Helper_Data::getTmsActivity($params['activity'], 'ari_activity');

		$tmsProductIds = Mage::helper('fitment')->detectProductIdInfo(
				$params['product'],
				$ariActivityId,
				$params['fitment']
			);

		$params['productIds'] = $tmsProductIds;

		$response = array(
			'params' => $params,
			'errorMessage' => '',
		);

		try {
			$response['html'] = $this->getLayout()
					->createBlock('fitment/fitment_view')
						->setTmsActivity($params['activity'])
						->setAriActivity($ariActivityId)
						->setAriProductId($params['product'])
						->setFitmentId($params['fitment'])
						->setTmsProductIds($tmsProductIds)
						->setVehicle($params['vehicle'])
						->setElements($params['options']['elements'])
						->toHtml();
		} catch (Exception $e) {
			$response['errorMessage'] = $e->getMessage();
			Mage::logException($e);
		}

		echo json_encode($response);
		die;
	}



	public function viewAction()
	{
		$skuId = $this->getRequest()->getParam('sku');
		if(!$skuId) {
			$this->_redirectReferer();
		}

		try {
			$productId = Vikont_Fitment_Helper_Db::getTableValue(
				Vikont_Fitment_Helper_Db::getTableName('catalog/product'),
				'entity_id',
				'type_id="' . Mage_Catalog_Model_Product_Type::TYPE_SIMPLE . '" AND sku="' . addslashes($skuId) . '"'
			);

			$product = Mage::getModel('catalog/product')->load($productId);

			if($product->getId()) {
				$url = $product->getProductUrl();

				if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
					$params = $this->getRequest()->getParams();
					unset($params['sku']);
					$params['from'] = 'fitment';
					$url .= '?' . http_build_query($params);
				}

				$this->getResponse()->setRedirect($url);

				return;
			}
		} catch (Exception $e) {
			Mage::logException($e);
		}
		$this->_redirectReferer();
	}

}