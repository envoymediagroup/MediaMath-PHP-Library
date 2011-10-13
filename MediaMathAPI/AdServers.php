<?php

class MediaMathAPI_AdServers extends MediaMathAPI {

    public $method = 'ad_servers';

    public function delete($args) {
	$args['status'] = 'off';
	return parent::modify($args);
    }
}
