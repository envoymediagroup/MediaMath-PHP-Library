<?php

class MediaMathAPI_Creatives extends MediaMathAPI {
    public $method = 'atomic_creatives';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}