# MediaMath PHP Library

The goal for this project is to provide a versatile, easy to use PHP library for
anyone wishing to integrate with the MediaMath API.

# Requirements

* PHP 5.2+
* PHP Curl
* MediaMath API Credentials

# Overview

Every class in this library is under the MediaMathAPI folder. Every class immediately inherits the following public methods:

### Get Methods

* fetch - Fetch the details for a specific record as specified by id
* fetchAll - Fetch an associative array keyed by id of all the records available
* fetchAllDetail - Fetch an associative array keyed by id of all the records available and do additional calls to fetch the full details for each record

### Set Methods

* create - Create a new record
* modify - Modify an existing record. The MediaMath API makes use of a field named version to protect against simultaneous overwrite. You can pass the version field in with the array to this method, or if you have auto_version enabled in MediaMathAPI.php and you leave the version field out of the array you pass this method, the library will automatically fetch the most recent version for you.
* delete - Delete a record. Almost everything in the API is not deletable and will give an error if you try. Therefore, in many cases, if you try to delete an object that is not deletable the library will set status=off for you. The exception is subobjects for Strategies. The relationship of things like Concepts and DayParts to strategies are deletable.

### I/O

The input and output for all of these methods is associative arrays. The exception being fetch which requires an integer value for the id to be passed in.

### Debugging

This library has two modes for debugging: 1 or 2. You set the debugging flag when you construct the MediaMathAPI object like this:

    $API = new MediaMathAPI(1);

Additionally, you may set the debugging flag by calling the setDebugLevel method like this:

    $API->setDebugLevel(2);

Here is some more info on what the debug levels do:

* Debug Level 1: This level will print out extra detail for what is going on in all the methods
* Debug Level 2: This level gives you all the output of level 1 and additionally outputs the CURL headers and MediaMath raw responses involved.

# Examples

List all the current advertisers
--------------------------------

    <?php
    $API = new MediaMathAPI();
    $API->login('mm_api_username','mm_api_password');
    print_r($API->Advertisers->fetchAll());
    ?>

List all the current advertisers with full details for each
-----------------------------------------------------------

    <?php
    $API = new MediaMathAPI();
    $API->login('mm_api_username','mm_api_password');
    print_r($API->Advertisers->fetchAllDetail());
    ?>

Create a creative
-----------------

Note: All of the objects follow the same format where you pass in an array that you
would like sent to the API. Please visit: https://kb.mediamath.com/wiki/display/APID/API+Documentation+Home
to learn more about all the objects and methods available.

    <?php
    $API = new MediaMathAPI();
    $API->login('mm_api_username','mm_api_password');
    $creative = Array(
        'status' => 'on',
        'name' => 'Test Creative 1',
        'advertiser_id' => 12345,
        'concept_id' => 67890,
        'external_identifier' => 111111,
        'file_type' =>  'gif',
        'tag_type' => 'NOSCRIPT',
        'width' => 300,
        'height' => 250',
        'is_https' => 'off',
        'has_sound' => 'off',
        'is_multi_creative' => 'off',
        'adserver_type' => 'OTHER',
        'tag' => '<a href="[UNENCODED_CLICK_REDIRECT]http://www.blah.com/"><img src="http://www.blah.com/hoohaa.gif" /></a>',
        'tpas_ad_tag_name' => 'Not Applicable'
    );
    print_r($API->Creatives->create($creative));
    ?>

Modify a creative
-----------------

Note: If you leave out version and have auto_version on then the library will automatically fetch
the correct version number for you.

    <?php
    $API = new MediaMathAPI();
    $API->login('mm_api_username','mm_api_password');
    $creative = Array(
        'id' => 12345
        'status' => 'off',
        'version' => 1
    );
    print_r($API->Creatives->modify($creative));
    ?>

