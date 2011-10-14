<?php

class MediaMathAPI_Strategy_SupplySources extends MediaMathAPI {
    public $method = 'supply_sources';
    public $method_full = 'supply_source';
    
    public function fetchAll($strategy_id=0) {
	$old_method = $this->method;
	if ($strategy_id) {
	    $this->method = $this->method.'/limit/strategy='.$strategy_id;
	}
	$response = Array();
	$response = parent::fetchAll();
	if ($strategy_id) {
	    $this->method = $old_method;
	}
	return $response;
    }
    
    public function fetchAllDetail($strategy_id=0) {
	$old_method = $this->method;
	if ($strategy_id) {
	    $this->method = $this->method.'/limit/strategy='.$strategy_id;
	}
	$response = Array();
	$response = parent::fetchAllDetail();
	if ($strategy_id) {
	    $this->method = $old_method;
	}
	return $response;
    }
}

