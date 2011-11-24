<?php

class MediaMathAPI_Agencies extends MediaMathAPI {
    public $method = 'agencies';
    public $method_full = 'agency';
    public $parent = 'organization';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}