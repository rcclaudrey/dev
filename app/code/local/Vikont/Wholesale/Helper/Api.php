<?php

class Vikont_Wholesale_Helper_Api extends Mage_Core_Helper_Abstract
{

	public function request($requestType, $data = null)
	{
		$requester = new Vikont_ARIOEM();

		switch($requestType) {
			case 'part':
				$params = array(
					'key' => 'part',
					'search' => $data,
				);
				$result = $requester->getSearchData($params);
				break;

			default:
				$result = false;
		}
		return $result;
	}

}