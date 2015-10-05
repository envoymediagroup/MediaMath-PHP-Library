<?php

/**
 * An easy to use MediaMath PHP Library
 *
 * @name      MediaMath PHP Library
 * @author    Michael Taggart <mtaggart@envoymediagroup.com>
 * @copyright (c) 2011 Envoy Media Group
 * @link      http://www.envoymediagroup.com
 * @license   MIT
 * @version   $Rev$
 * @internal  $Id: api.php 8 2011-10-06 08:38:50Z mtaggart $
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */
class MediaMathAPI {
    /* CONFIG - BEGIN */

    //The base url for the MediaMath API
    //[SANDBOX]
    //private static $_base = 'https://t1sandbox.mediamath.com/api/v1/'; //remember trailing slash
    //[PRODUCTION]
    private static $_base = 'https://api.mediamath.com/api/v1/'; //remember the trailing slash
    //Folder where authentication cookies are stored
    private static $_cookie_folder = '/tmp/'; //remember trailing slash
    //If $_auto_version is set to true then the system will automatically
    //do an extra call to retrieve the "version" field when attempting to
    //modify a particular item IF version is not passed in $args
    private static $_auto_version = true;
    //If $_auto_version_conflict is set to true then the system will automatically
    //do an extra call to retrieve the "version" field when attempting to
    //modify a particular item IF an attept at modification returned a Version Conflict message
    //Some could argue this defeats the purpose of having version at all, but if you have
    //a system where you know there is only 1 way a modification can be made and you want to
    //be lazy about your version checking this variable will be one of your favorites.
    private static $_auto_version_conflict = true;

    /* CONFIG - END */
    protected static $_debug_level = -1;
    protected static $_auth = Array();
    protected static $_ch;
    protected static $_filter = Array('type' => '', 'id' => '');
    public $AdServers;

    public function __construct($debug_level=0) {
	if (self::$_debug_level < 0) {
	    self::$_debug_level = $debug_level;

	    self::$_ch = curl_init();
	    curl_setopt(self::$_ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt(self::$_ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt(self::$_ch, CURLOPT_USERAGENT, 'MediaMath PHP Library 1.0');
	    if ($method == 'login') {
		$username = $args['name'];
	    } else {
		$username = self::$_auth['name'];
	    }
	    curl_setopt(self::$_ch, CURLOPT_COOKIEFILE, self::$_cookie_folder . "mediamath_cookie-" . mt_rand() . ".file");
	    curl_setopt(self::$_ch, CURLOPT_AUTOREFERER, 1);
	    if (self::$_debug_level >= 2) {
		curl_setopt(self::$_ch, CURLOPT_VERBOSE, 1);
	    }

	    $this->AdServers = new MediaMathAPI_AdServers();
	    $this->Advertisers = new MediaMathAPI_Advertisers();
	    $this->Agencies = new MediaMathAPI_Agencies();
	    $this->Campaigns = new MediaMathAPI_Campaigns();
	    $this->Campaign_Watermarks = new MediaMathAPI_Campaign_Watermarks();
	    $this->Concepts = new MediaMathAPI_Concepts();
	    $this->Creatives = new MediaMathAPI_Creatives();
	    $this->Organizations = new MediaMathAPI_Organizations();
	    $this->Pixels = new MediaMathAPI_Pixels();
	    $this->Reports = new MediaMathAPI_Reports();
	    $this->Segments = new MediaMathAPI_Segments();
	    $this->Strategies = new MediaMathAPI_Strategies();
	    $this->Strategies->Concepts = new MediaMathAPI_Strategy_Concepts();
	    $this->Strategies->DayParts = new MediaMathAPI_Strategy_DayParts();
	    $this->Strategies->SupplySources = new MediaMathAPI_Strategy_SupplySources();
	    $this->TargetDimensions = new MediaMathAPI_TargetDimensions();
	    $this->Verticals = new MediaMathAPI_Verticals();
	}
    }

    public function setDebugLevel($debug_level=0) {
	self::$_debug_level = $debug_level;
    }

    public function setFilter($type, $id, $persist=false) {
	//This will filter all multi result calls to just the advertiser_id specified
	self::$_filter = Array('type' => $type, 'id' => $id, 'persist' => $persist);
    }

    public function getFilter() {
	//This will filter all multi result calls to just the advertiser_id specified
	return self::$_filter;
    }

    public function getFilterPrefix($start, $end) {
	//this will give us the appropriate filter for a limit lookup
	$hierarchy = Array(
	    'strategy' => Array(
		'campaign', 'advertiser', 'agency', 'organization'
	    ),
	    'pixel_bundle' => Array(
		'advertiser', 'agency', 'organization'
	    ),
	    'concept' => Array(
		'advertiser', 'agency', 'organization'
	    ),
	    'campaign' => Array(
		'advertiser', 'agency', 'organization'
	    ),
	    'advertiser' => Array(
		'agency', 'organization'
	    ),
	    'agency' => Array(
		'organization'
	    )
	);

	if (!isset($hierarchy[$start])) {
	    return '';
	}

	$prefix = $start . '.';
	foreach ($hierarchy[$start] as $h) {
	    if ($h == $end) {
		break;
	    }
	    $prefix .= $h . '.';
	}

	return $prefix;
    }

    public function login($username, $password, $api_key='') {
	$args = Array('user' => $username, 'password' => $password);
	if ($api_key) {
	    //The api_key variable is only required for production accounts
	    //For the sandbox this is not necessary.
	    $args['api_key'] = $api_key;
	}
	$response = Array();
	$response = $this->call('login', $args);
	if ($response['status_attr']['code'] != 'ok') {
	    if (self::$_debug_level >= 1) {
		print "API->login = FALSE\n";
	    }
	    die("ERROR: Login FAILED!\n");
	} else {
	    if (self::$_debug_level >= 1) {
		print "API->login = TRUE\n";
	    }
	    self::$_auth = Array(
		'name' => $username,
		'password' => $password
	    );
	    return true;
	}
    }

    public function call($method, $args=Array()) {
	if ($method != 'login' && empty(self::$_auth)) {
	    return Array('status' => "You must call login before $method");
	}
	$url = self::$_base . $method;

	if (self::$_debug_level >= 1) {
	    print "##############################################################\n";
	    print "URL: $url\n";
	}

	$xml_priority = 'tag';
	if ($args['xml_priority']) {
	    $xml_priority = $args['xml_priority'];
	    unset($args['xml_priority']);
	}

	if (!empty($args)) {
	    $fields = '';
	    foreach ($args as $key => $val) {
		$fields .= $key . '=' . urlencode($val) . '&';
	    }
	    $fields = trim($fields, '&');


	    if (self::$_debug_level >= 1) {
		print "POST: $fields\n";
	    }
	    curl_setopt(self::$_ch, CURLOPT_POST, 1);
	    curl_setopt(self::$_ch, CURLOPT_POSTFIELDS, $fields);
	} else {
	    curl_setopt(self::$_ch, CURLOPT_POST, 0);
	}
	curl_setopt(self::$_ch, CURLOPT_URL, $url);
	$xml = curl_exec(self::$_ch);

	if (self::$_debug_level >= 1) {
	    print "RESPONSE (XML):\n$xml\n";
	}

	$response = Array();
	$response = $this->xml2array($xml, 1, $xml_priority);
	$response = $response['result'];
	if (empty($response) && strpos($xml, ' not implemented for ') !== 'false') {
	    $response['status'] = trim($xml);
	    $response['status_attr']['code'] = 'not_implemented';
	}
	if (self::$_debug_level >= 1) {
	    print "RESPONSE (ARRAY):\n" . print_r($response, true) . "\n";
	    print "##############################################################\n";
	}
	if ($method != 'login' && $response['status'] == 'Authentication Required' && !empty(self::$_auth)) {
	    if ($this->call('login', self::$_auth)) {
		return $this->call($method, $args);
	    }
	}
	return $response;
    }

    //Global functions available to all methods
    public function fetch($id) {
	$response = Array();
	$response = $this->call($this->method . '/' . $id, Array());
	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->fetch = " . print_r($response, true) . "\n";
	}
	return $this->prepareResponseSingle($response);
    }

    public function fetchAll($args=Array()) {
	if (!$args['page_limit'] || $args['page_limit'] > 100) {
	    $args['page_limit'] = 100;
	}
	$all_items = false;
	if (!isset($args['page_offset'])) {
	    $args['page_offset'] = 0;
	    $all_items = true;
	}

	if (!isset($args['sort_by'])) {
	    $args['sort_by'] = 'id';
	}

	$args_str = '';
	foreach ($args as $key => $val) {
	    $args_str .= '&' . $key . '=' . urlencode($val);
	}

	$args_str = trim($args_str, '&');

	$response = Array();
	if (self::$_filter) {
	    $filter = Array();
	    $filter = self::$_filter;
	    $filter_prefix = '';
	    if ($this->parent && $filter['type'] != $this->parent) {
		$filter_prefix = $this->getFilterPrefix($this->parent, $filter['type']);
	    }
	    $response = $this->call($this->method . '/limit/' . $filter_prefix . $filter['type'] . $filter['id'] . '?' . $args_str, Array());
	    if (!$filter['persist']) {
		self::$_filter = Array();
	    }
	} else {
	    $response = $this->call($this->method . '?' . $args_str, Array());
	}

	$full_count = $response['entities_attr']['count'];

	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->fetchAll = " . print_r($response, true) . "\n";
	}

	$ret = Array();
	$ret = $this->prepareResponseMultiple($response);

	if ($all_items && count($ret['entities']) == $args['page_limit']) {
	    do {
		$ret2 = Array();
		$args['page_offset'] += $args['page_limit'];
		$ret2 = $this->fetchAll($args);
		foreach ($ret2['entities'] as $eid => $e) {
		    $ret['entities'][$eid] = $e;
		}
	    } while (count($ret['entities']) < $full_count);
	}

	return $ret;
    }

    public function fetchAllDetail($args=Array()) {
	if (!$args['page_limit'] || $args['page_limit'] > 100) {
	    $args['page_limit'] = 100;
	}
	$all_items = false;
	if (!isset($args['page_offset'])) {
	    $args['page_offset'] = 0;
	    $all_items = true;
	}

	if (!isset($args['sort_by'])) {
	    $args['sort_by'] = 'id';
	}

	$args_str = '';
	foreach ($args as $key => $val) {
	    $args_str .= '&' . $key . '=' . urlencode($val);
	}

	$response = Array();
	if (self::$_filter) {
	    $filter = Array();
	    $filter = self::$_filter;
	    $filter_prefix = '';
	    if ($this->parent && $filter['type'] != $this->parent) {
		$filter_prefix = $this->getFilterPrefix($this->parent, $filter['type']);
	    }
	    $response = $this->call($this->method . '/limit/' . $filter_prefix . $filter['type'] . '=' . $filter['id'] . '?full=' . $this->method_full . $args_str, Array());
	    if (!$filter['persist']) {
		self::$_filter = Array();
	    }
	} else {
	    $response = $this->call($this->method . '?full=' . $this->method_full . $args_str, Array());
	}


	$full_count = $response['entities_attr']['count'];

	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->fetchAllDetail = " . print_r($response, true) . "\n";
	}

	$ret = Array();
	$ret = $this->prepareResponseMultiple($response);

	if ($all_items && count($ret['entities']) == $args['page_limit']) {
	    do {
		$ret2 = Array();
		$args['page_offset'] += $args['page_limit'];
		$ret2 = $this->fetchAllDetail($args);
		foreach ($ret2['entities'] as $eid => $e) {
		    $ret['entities'][$eid] = $e;
		}
	    } while (count($ret['entities']) < $full_count);
	}

	return $ret;
    }

    public function create($args) {
	$response = Array();
	$response = $this->call($this->method, $args);
	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->create = " . print_r($response, true) . "\n";
	}
	return $this->prepareResponseSingle($response);
    }

    public function modify($args) {
	if (!isset($args['version']) && self::$_auto_version) {
	    //get the current version
	    $temp = Array();
	    $temp = $this->fetch($args['id']);
	    if (isset($temp['entity']['version'])) {
		$args['version'] = $temp['entity']['version'];
	    }
	}

	$response = Array();
	$response = $this->call($this->method . '/' . $args['id'], $args);
	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->modify = " . print_r($response, true) . "\n";
	}

	$ret = Array();
	$ret = $this->prepareResponseSingle($response);

	if (self::$_auto_version_conflict && $ret['status']['code'] == 'conflict' && strpos($ret['status']['detail'], 'Version') !== false) {
	    $temp = Array();
	    $temp = $this->fetch($args['id']);
	    if (isset($temp['entity']['version'])) {
		$args['version'] = $temp['entity']['version'];
	    }
	    $response = Array();
	    $response = $this->call($this->method . '/' . $args['id'], $args);
	    if (self::$_debug_level >= 1) {
		print "API->" . get_class($this) . "->modify = " . print_r($response, true) . "\n";
	    }
	    $ret = Array();
	    $ret = $this->prepareResponseSingle($response);
	}

	return $ret;
    }

    public function delete($args) {
	$response = Array();
	$response = $this->call($this->method . '/' . $args['id'] . '/delete', $args);
	if (self::$_debug_level >= 1) {
	    print "API->" . get_class($this) . "->delete = " . print_r($response, true) . "\n";
	}

	return $this->prepareResponseSingle($response);
    }

    public function prepareResponseSingle($response) {
	$ret = Array(
	    'status' => Array(
		'code' => $response['status_attr']['code'],
		'detail' => $response['status'] ? $response['status'] : ucwords($response['status_attr']['code'])
	    )
	);

	//build entity
	$entity = Array();
	if ($response['status_attr']['code'] == 'ok') {
	    //loop through all the attributes and build a nice array
	    foreach ($response['entity']['prop'] as $pkey => $e) {
		if (strpos($pkey, '_attr') !== false) {
		    $entity[$e['name']] = $e['value'];
		}
	    }
	    if (!isset($entity['id']) && isset($response['entity_attr']['id'])) {
		$entity['id'] = $response['entity_attr']['id'];
	    }
	    if (!isset($entity['version']) && isset($response['entity_attr']['version'])) {
		$entity['version'] = $response['entity_attr']['version'];
	    }
	} else {
	    $entity = Array();
	    $ret['errors'] = $this->prepareErrors($response);
	}
	$ret['entity'] = $entity;
	return $ret;
    }

    public function prepareResponseMultiple($response) {
	//this function will take an array of entities and remap it to be
	//an associative array by id
	$ret = Array(
	    'status' => Array(
		'code' => $response['status_attr']['code'],
		'detail' => $response['status'] ? $response['status'] : ucwords($response['status_attr']['code'])
	    )
	);
	$new = Array();
	//I hate how XML feeds always do this
	//if you are going to return an array then do it everytime not just if there is
	//more than 1 item!!
	//Here we make sure that everything always goes into nice associative array with id as the key
	if ($ret['status']['code'] == 'ok' && $response['entities_attr']['count'] > 0) {
	    if (isset($response['entities']['entity'][0])) {
		$prop_flag = false;
		foreach ($response['entities']['entity'] as $key => $es) {
		    if (isset($es['prop'])) {
			$prop_flag = true;
			$attr = Array();
			$attr = $response['entities']['entity'][$key . '_attr'];
			$my_id = $attr['id'];
			$new[$my_id] = $attr;
			foreach ($es['prop'] as $key2 => $e) {
			    if (strpos($key2, '_attr') !== false) {
				$new[$my_id][$e['name']] = $e['value'];
			    }
			}
		    } elseif (!$prop_flag) {
			if (strpos($key, '_attr') !== false) {
			    $new[$es['id']] = $es;
			}
		    }
		}
	    } else {
		//single entity
		if (isset($response['entities']['entity']['prop'])) {
		    $attr = Array();
		    $attr = $response['entities']['entity_attr'];
		    $my_id = $attr['id'];
		    $new[$my_id] = $attr;
		    foreach ($response['entities']['entity']['prop'] as $key => $e) {
			if (strpos($key, '_attr') !== false) {
			    $new[$my_id][$e['name']] = $e['value'];
			}
		    }
		} else {
		    $new[$response['entities']['entity_attr']['id']] = $response['entities']['entity_attr'];
		}
	    }
	} else {
	    $new = Array();
	}
	$ret['entities'] = $new;
	return $ret;
    }

    public function prepareErrors($response) {
	$ret = Array();
	if (isset($response['errors'][0])) {
	    foreach ($response['errors'] as $key => $es) {
		if (strpos($key, '_attr') !== false) {
		    $ret[$es['code']] = $es;
		}
	    }
	} else {
	    $ret[$response['errors']['field-error_attr']['code']] = $response['errors']['field-error_attr'];
	}
	return $ret;
    }

    /**
     * xml2array() will convert the given XML text to an array in the XML structure.
     * Link: http://www.bin-co.com/php/scripts/xml2array/
     * Arguments : $contents - The XML text
     *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
     *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
     * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
     * Examples: $array =  xml2array(file_get_contents('feed.xml'));
     *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
     */
    private function xml2array($contents, $get_attributes=1, $priority = 'tag') {
	if (!$contents)
	    return array();

	if (!function_exists('xml_parser_create')) {
	    //print "'xml_parser_create()' function not found!";
	    return array();
	}

	//Get the XML parser of PHP - PHP must have this module for the parser to work
	$parser = xml_parser_create('');
	// http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);

	if (!$xml_values) {
	    return; //Hmm...
	}

	//Initializations
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();

	$current = &$xml_array; //Reference
	//Go through the tags.
	//print_r($xml_values);

	$repeated_tag_index = array(); //Multiple tags with same name will be turned into an array
	foreach ($xml_values as $data) {
	    unset($attributes, $value); //Remove existing values, or there will be trouble
	    //This command will extract these variables into the foreach scope
	    // tag(string), type(string), level(int), attributes(array).
	    extract($data); //We could use the array by itself, but this cooler.

	    $result = array();
	    $attributes_data = array();

	    if (isset($value)) {
		if ($priority == 'tag')
		    $result = $value;
		else
		    $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
	    }

	    //Set the attributes too.
	    if (isset($attributes) and $get_attributes) {
		foreach ($attributes as $attr => $val) {
		    if ($priority == 'tag')
			$attributes_data[$attr] = $val;
		    else
			$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
		}
	    }

	    $stop_flag = false;
	    if ($attributes_data['id'] == 102625) {
		print_r($data);
		print_r($attributes_data);
		$stop_flag = true;
	    }

	    //See tag status and do the needed.
	    if ($type == "open") {//The starting of the tag '<tag>'
		$parent[$level - 1] = &$current;

		if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
		    $current[$tag] = $result;
		    if ($attributes_data)
			$current[$tag . '_attr'] = $attributes_data;
		    $repeated_tag_index[$tag . '_' . $level] = 1;

		    $current = &$current[$tag];
		} else { //There was another element with the same tag name
		    if (isset($current[$tag][0])) {//If there is a 0th element it is already an array
			$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
			if ($attributes_data) {
			    //put attributes in 1_attr
			    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
			}
			$repeated_tag_index[$tag . '_' . $level]++;
		    } else {//This section will make the value an array if multiple tags with the same name appear together
			$current[$tag] = array($current[$tag], $result); //This will combine the existing item and the new item together to make an array
			$repeated_tag_index[$tag . '_' . $level] = 2;

			if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
			    $current[$tag]['0_attr'] = $current[$tag . '_attr'];
			    unset($current[$tag . '_attr']);
			}

			if ($attributes_data) {
			    //put attributes in 1_attr
			    $current[$tag]['1_attr'] = $attributes_data;
			}
		    }
		    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
		    $current = &$current[$tag][$last_item_index];
		}
	    } elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
		//See if the key is already taken.
		if (!isset($current[$tag])) { //New Key
		    $current[$tag] = $result;
		    $repeated_tag_index[$tag . '_' . $level] = 1;
		    if ($priority == 'tag' and $attributes_data)
			$current[$tag . '_attr'] = $attributes_data;
		} else { //If taken, put all things inside a list(array)
		    if (isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...
			// ...push the new element into that array.
			$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;

			if ($priority == 'tag' and $get_attributes and $attributes_data) {
			    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
			}
			$repeated_tag_index[$tag . '_' . $level]++;
		    } else { //If it is not an array...
			$current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
			$repeated_tag_index[$tag . '_' . $level] = 1;
			if ($priority == 'tag' and $get_attributes) {
			    if (isset($current[$tag . '_attr'])) { //The attribute of the last(0th) tag must be moved as well
				$current[$tag]['0_attr'] = $current[$tag . '_attr'];
				unset($current[$tag . '_attr']);
			    }

			    if ($attributes_data) {
				$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
			    }
			}
			$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
		    }
		}
	    } elseif ($type == 'close') { //End of tag '</tag>'
		$current = &$parent[$level - 1];
	    }
	}
	return($xml_array);
    }

    public function csvArray($input, $headers=true, $delimiter=',', $enclosure='"', $escape='\\') {
	$lines = Array();
	$lines = explode("\n", $input);

	$ret = Array();
	if ($headers) {
	    //parse first line
	    $headers = $this->csvArrayLine($lines[0], $delimiter, $enclosure, $escape);
	    unset($lines[0]);
	    $line_count = 0;
	    foreach ($lines as $line) {
		$fields = $this->csvArrayLine($line, $delimiter, $enclosure, $escape);
		foreach ($fields as $key => $f) {
		    $ret[$line_count][$headers[$key]] = $f;
		}
		$line_count++;
	    }
	} else {
	    $line_count = 0;
	    foreach ($lines as $line) {
		$ret[$line_count] = $fields = $this->csvArrayLine($line, $delimiter, $enclosure, $escape);
		$line_count++;
	    }
	}

	return $ret;
    }

    public function csvArrayLine($input, $delimiter=',', $enclosure='"', $escape='\\') {
	$fields = explode($enclosure . $delimiter . $enclosure, substr($input, 1, -1));
	foreach ($fields as $key => $value)
	    $fields[$key] = str_replace($escape . $enclosure, $enclosure, $value);
	return($fields);
    }

}

function MediaMath_Autoloader($class_name) {
    $path = str_replace('_', '/', $class_name);
    $file = dirname(__FILE__) . "/{$path}.php";
    if (file_exists($file) && include($file)) {
	return true;
    } else {
	trigger_error("The MediaMath_Autoloader could not include $file", E_USER_WARNING);
	return false;
    }
}

spl_autoload_register('MediaMath_Autoloader');
