<?php

class MediaMathAPI_Pixels extends MediaMathAPI {
    public $method = 'pixel_bundles';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}