<?php

class MediaMathAPI_Agencies extends MediaMathAPI {
    public $method = 'agencies';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}