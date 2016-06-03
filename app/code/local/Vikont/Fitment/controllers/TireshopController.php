<?php

class Vikont_Fitment_TireshopController extends Mage_Core_Controller_Front_Action
{

	public function requestAction()
	{
//Mage::register('vd', 1);
		$params = $this->getRequest()->getParams();

		$subject = $params['subject'];
		unset($params['subject']);

		$tmsActivityId = (int)$params['activity'];
		unset($params['activity']);

		$res = array();

		$options = $params;
		$options['includeFacets'] = 'true';
		$options['take'] = '0';

		try {
			switch($subject) {
				case 'size':
					$res = Mage::helper('fitment')->getTireSizes($tmsActivityId);
					break;

				case 'brand':
					$ariActivityId = Vikont_Fitment_Helper_Data::getTireshopActivity($tmsActivityId, 'ari_activity');
					$options = Vikont_Fitment_Helper_Data::applyExtraFilter($options, $tmsActivityId);
					$data = Mage::helper('fitment/api')->request('search', array($ariActivityId), $options);
					if(!$data) {
						throw new Exception('Error getting remote data for ' . $subject);
					}

					foreach($data['Facets'] as $facet) {
						if($facet['Field'] == 'brandId') {
							foreach($facet['Values'] as $facetValue) {
								$res[] = array(
									'Name' => $facetValue['Name'],
									'Value' => $facetValue['Value'],
								);
							}
							$res = Vikont_Fitment_Helper_Data::sortOptions($res, 'Name');
							break;
						}
					}
					break;

				case 'price': /**
					$activityId = Vikont_Fitment_Helper_Data::getTireshopActivity($activityIndex);
					$options = Vikont_Fitment_Helper_Data::applyExtraFilter($options, $activityIndex);
					$data = Mage::helper('fitment/api')->request('search', array($activityId), $options);
					if(!$data) {
						throw new Exception('Error getting remote data for' . $subject);
					} /**/
					$res = array(
						array(
							'From' => 0,
							'To' => 1000000,
							'Value' => '0-0',
							'Name' => $this->__('Any price'),
						),
					);
					break;
			}

			$errorMessage = '';
		} catch (Exception $e) {
			$errorMessage = Vikont_Fitment_Helper_Data::reportError($e->getMessage());
		}

		$response = array(
			'subject' => $subject,
			'params' => $this->getRequest()->getParams(),
			'data' => $res,
			'errorMessage' => $errorMessage,
		);

		echo json_encode($response);
		die;
	}



	public function clearCacheAction()
	{
		$tableName = Vikont_Fitment_Helper_Db::getDbResource()->getTableName('core/config_data');
		$sql = 'DELETE FROM ' . $tableName  . ' WHERE path LIKE "fitment/cache/%"';
		Vikont_Fitment_Helper_Db::executeQuery($sql);
		die;
	}

}