<?php

class MediaMathAPI_Advertisers extends MediaMathAPI {
    public $method = 'advertisers';
    public $method_full = 'advertiser';
    public $parent = 'agency';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}