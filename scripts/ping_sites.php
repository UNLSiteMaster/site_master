<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$sites = new \SiteMaster\Core\Registry\Sites\All();

foreach ($sites as $site) {
    $http_info = \SiteMaster\Core\Util::getHTTPInfo($site->base_url);
    $site->http_code = $http_info['http_code'];
    $site->curl_code = $http_info['curl_code'];
    
    if ($site->http_code == 200) {
        $site->last_connection_success = \SiteMaster\Core\Util::epochToDateTime();
    } else {
        $site->last_connection_error = \SiteMaster\Core\Util::epochToDateTime();
    }
    $site->save();
    sleep(1);
}
