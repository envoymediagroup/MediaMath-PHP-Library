# MediaMath PHP Library

The goal for this project is to proved a versatile, easy to use PHP library for
anyone wishing to integrate with the MediaMath API.

# Requirements

PHP 5.2+
PHP Curl
MediaMath API Credentials

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
    print_r($API->Creatives->create($creative));
    ?>