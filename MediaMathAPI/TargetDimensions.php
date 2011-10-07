<?php

class MediaMathAPI_TargetDimensions extends MediaMathAPI {

    public $method = 'target_dimensions';

    public function fetchAll($type) {
	if (is_numeric($type)) {
	    $type_code = $type;
	} else {
	    $lower_type = strtolower($type);
	    $map = Array(
		'dma' => 1,
		'connection speed' => 2,
		'isp' => 3,
		'browser' => 4,
		'os' => 5,
		'geography' => 7,
		'mathselect250' => 8
	    );
	    $type_code = $map[$lower_type] ? $map[$lower_type] : 0;
	}
	return $this->cleanResponse(parent::call($this->method . '/'.$type_code.'?with=target_values', Array('xml_priority' => 'attribute')));
    }

    public function fetchAllDetail($type) {
	return $this->fetchAll($type);
    }
    
    public function cleanResponse($response) {

	$ret = Array(
	    'status' => Array(
		'code' => $response['status']['attr']['code'],
		'detail' => ucwords($response['status']['attr']['code'])
	    )
	);
	$entities = Array();
	if ($ret['status']['code'] == 'ok') {
	    foreach ($response['entity']['entity'] as $ekey => $es) {
		$my_key = $es['attr']['id'];
		$entities[$my_key]['id'] = $my_key;
		if (isset($es['prop']) && $my_key) {
		    foreach ($es['prop'] as $pkey => $p) {
			$entities[$my_key][$p['attr']['name']] = $p['attr']['value'];
		    }
		}
	    }
	}
	$ret['entities'] = $entities;
	return $ret;
    }

}