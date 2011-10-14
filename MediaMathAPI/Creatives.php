<?php

class MediaMathAPI_Creatives extends MediaMathAPI {
    public $method = 'atomic_creatives';
    public $method_full = 'atomic_creative';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}