<?php

class MediaMathAPI_Advertisers extends MediaMathAPI {
    public $method = 'advertisers';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}