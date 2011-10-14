<?php

class MediaMathAPI_Strategies extends MediaMathAPI {

    public $method = 'strategies';
    public $method_full = 'strategy';
    
    public function create($args) {
	$flags = Array(
	    'concepts' => false,
	    'supply_sources' => false,
	    'day_parts' => false,
	    'target_dimensions' => false
	);
	if (isset($args['concepts'])) {
	    $concepts = Array();
	    $concepts = $args['concepts'];
	    unset($args['concepts']);
	    $flags['concepts'] = true;
	}
	$supply_sources = Array();
	if (isset($args['supply_sources'])) {
	    $supply_sources = $args['supply_sources'];
	    unset($args['supply_sources']);
	    $flags['supply_sources'] = true;
	}
	$day_parts = Array();
	if (isset($args['day_parts'])) {
	    $day_parts = $args['day_parts'];
	    unset($args['day_parts']);
	    $flags['day_parts'] = true;
	}
	$target_dimensions = Array();
	if (isset($args['target_dimensions'])) {
	    $target_dimensions = $args['target_dimensions'];
	    unset($args['target_dimensions']);
	    $flags['target_dimensions'] = true;
	}
	$response = Array();
	$response = parent::create($args);
	if ($orig_response['status']['code'] == 'ok' && $orig_response['entity']['id']) {
	    $strategy_id = $orig_response['entity']['id'];

	    //Assign/unassign any concepts
	    if ($flags['concepts']) {
		$C = new MediaMathAPI_Strategy_Concepts();
		if (!empty($concepts)) {
		    foreach ($concepts as $id => $c) {
			$c['strategy_id'] = $strategy_id;
			$response['concepts'][$id] = $C->create($c);
		    }
		} else {
		    //delete existing
		    $existing = Array();
		    $existing = $C->fetchAll();
		    foreach ($existing['entities'] as $id => $e) {
			$C->delete(Array('id' => $id, 'version' => $e['entity']['detail']['version']));
		    }
		}
	    }
	    
	    //Assign/unassign any supply_sources
	    if ($flags['supply_sources']) {
		$C = new MediaMathAPI_Strategy_SupplySources();
		if (!empty($supply_sources)) {
		    foreach ($supply_sources as $id => $ss) {
			$ss['strategy_id'] = $strategy_id;
			$response['supply_sources'][$id] = $C->create($ss);
		    }
		} else {
		    //delete existing
		    $existing = Array();
		    $existing = $C->fetchAll();
		    foreach ($existing['entities'] as $id => $e) {
			$C->delete(Array('id' => $id, 'version' => $e['entity']['detail']['version']));
		    }
		}
	    }
	    
	    //Assign/unassign any day_parts
	    if ($flags['day_parts']) {
		$C = new MediaMathAPI_Strategy_DayParts();
		if (!empty($day_parts)) {
		    foreach ($day_parts as $id => $dp) {
			$dp['strategy_id'] = $strategy_id;
			$response['day_parts'][$id] = $C->create($dp);
		    }
		} else {
		    //delete existing
		    $existing = Array();
		    $existing = $C->fetchAll();
		    foreach ($existing['entities'] as $id => $e) {
			$C->delete(Array('id' => $id, 'version' => $e['entity']['detail']['version']));
		    }
		}
	    }
	    
	    //Assign/unassign any target_dimensions
	    if ($flags['target_dimensions']) {
		$C = new MediaMathAPI_TargetDimensions();
		if (!empty($concepts)) {
		    foreach ($concepts as $id => $c) {
			$c['strategy_id'] = $strategy_id;
			$response['concepts'][$id] = $C->create($c);
		    }
		} else {
		    //delete existing
		    $existing = Array();
		    $existing = $C->fetchAll();
		    foreach ($existing['entities'] as $id => $e) {
			$C->delete(Array('id' => $id, 'version' => $e['entity']['detail']['version']));
		    }
		}
	    }
	    
	    
	}
	return $orig_response;
    }
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }

}