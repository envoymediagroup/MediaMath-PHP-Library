<?php

class MediaMathAPI_Campaigns extends MediaMathAPI {
    public $method = 'campaigns';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}