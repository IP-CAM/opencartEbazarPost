<?php

class ModelExtensionShippingFlat extends Model {

	function getQuote($address) {

		$this->load->language('extension/shipping/flat');



		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('flat_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");



		if (!$this->config->get('flat_geo_zone_id')) {

			$status = true;

		} elseif ($query->num_rows) {

			$status = true;

		} else {

			$status = false;

		}

		$weight = $this->cart->getWeight();
		$price = $this->cart->getSubTotal();

		function correctPersian($t)
	{
		$nt = "";
		$nt = str_replace("ی","ي",$t);
		$nt = str_replace("ک","ك",$nt);
		return $nt;
	}
    $User_Name = '';
    $Password = '';
    $Connection = new SoapClient('http://svc.ebazaar-post.ir/EshopService.svc?Wsdl');
	$Result = $Connection -> GetStates([
		'username' => $User_Name,
        'password' => $Password,
	]);
	$statecode=1;
	$states = $Result->GetStatesResult->State;
	foreach($states as $state){
		if($state->Name == correctPersian($address['zone']))
			$statecode=$state->Code;
	}
	$Result = $Connection -> GetCities([
		'username' => $User_Name,
        'password' => $Password,
		'stateId' => $statecode,
	]);	
	$cities = $Result->GetCitiesResult->City;
	$citycode=$cities[0]->Code;
	foreach($cities as $res){
		if($res->Name==correctPersian($address['city']))
			$citycode = $res->Code;
		
	}
    $Result = $Connection -> GetDeliveryPrice ([
        'username' => $User_Name,
        'password' => $Password,
        'cityCode' => $citycode,
        'serviceType' => 1, // pishtaz
        'payType' => 1, //COD
		'Weight' => $weight, //Gram
		'Price'=> $price*10, //Rial

    ]);
	$postCost = round(($Result->GetDeliveryPriceResult->PostDeliveryPrice+$Result->GetDeliveryPriceResult->VatTax)/10,-3);
		

		$method_data = array();



		if ($status) {

			$quote_data = array();



			$quote_data['flat'] = array(

				'code'         => 'flat.flat',

				'title'        => $this->language->get('text_description'),

				'cost'         => $postCost,

				'tax_class_id' => $this->config->get('flat_tax_class_id'),

				'text'         => $this->currency->format($this->tax->calculate($postCost, $this->config->get('flat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])

			);



			$method_data = array(

				'code'       => 'flat',

				'title'      => $this->language->get('text_title'),

				'quote'      => $quote_data,

				'sort_order' => $this->config->get('flat_sort_order'),

				'error'      => false

			);

		}



		return $method_data;

	}

}