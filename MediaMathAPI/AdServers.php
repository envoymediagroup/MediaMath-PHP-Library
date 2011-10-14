<?php

class MediaMathAPI_AdServers extends MediaMathAPI {

    public $method = 'ad_servers';
    public $method_full = 'ad_server';
    
    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}
