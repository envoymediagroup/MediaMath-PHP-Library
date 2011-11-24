<?php

class MediaMathAPI_Pixels extends MediaMathAPI {
    public $method = 'pixel_bundles';
    public $method_full = 'pixel_bundle';
    public $parent = 'advertiser';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}