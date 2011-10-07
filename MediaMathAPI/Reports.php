<?php

class MediaMathAPI_Reports extends MediaMathAPI {
    
    function fetchMargins($campaign_id,$start_date) {
	$method = 'campaigns/'.$campaign_id.'/margins.csv?start='.date('Y-m-d', strtotime($start_date));
	$response = Array();
	$response = parent::call($method, Array());
	$ret = Array();
	$ret = parent::csvArray($response['status']);
	return $ret;
    }
    
    function fetchGoalMonitoring($start_date,$end_date) {
	$method = 'reports/goal_monitoring.csv?start='.date('Y-m-d', strtotime($start_date)).'&end='.date('Y-m-d', strtotime($start_date));
	$response = Array();
	$response = parent::call($method, Array());
	return $response;
    }
    
    function fetchReach($start_date,$end_date) {
	$method = 'reports/reach.csv?start='.date('Y-m-d', strtotime($start_date)).'&end='.date('Y-m-d', strtotime($start_date));
	$response = Array();
	$response = parent::call($method, Array());
	return $response;
    }
    
    function fetchFrequency($start_date,$end_date) {
	$method = 'reports/frequency.csv?start='.date('Y-m-d', strtotime($start_date)).'&end='.date('Y-m-d', strtotime($start_date));
	$response = Array();
	$response = parent::call($method, Array());
	return $response;
    }
    
    function fetchCampaignPlacement($start_date,$end_date) {
	$method = 'reports/campaign_placement.csv?start='.date('Y-m-d', strtotime($start_date)).'&end='.date('Y-m-d', strtotime($start_date));
	$response = Array();
	$response = parent::call($method, Array());
	return $response;
    }
}