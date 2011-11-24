<?php

class MediaMathAPI_Concepts extends MediaMathAPI {
    public $method = 'concepts';
    public $method_full = 'concept';
    public $parent = 'advertiser';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}