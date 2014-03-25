<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

if (!isset($argv[1])) {
    echo "usage: php sendChangeEmail.php http://reportsite.unl.edu/" . PHP_EOL;
    exit();
}

if (!$site = \SiteMaster\Core\Registry\Site::getByBaseURL($argv[1])) {
    echo "unable to find site" . PHP_EOL;
    exit();
}

if (!$scan = $site->getLatestScan()) {
    echo "unable to find a scan for that site" . PHP_EOL;
    exit();
}

$result = $scan->sendChangedScanEmail();

var_dump($result);