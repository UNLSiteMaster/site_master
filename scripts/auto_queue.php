<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$queued = new \SiteMaster\Core\Auditor\Scans\Queued();
//var_dump($queued);exit();
if (count($queued) > 10) {
    echo 'Too many queued sites ('.count($queued).')... waiting to queue more.' . PHP_EOL;
    exit();
}

$production_sites = new \SiteMaster\Core\Registry\Sites\ByProductionStatus(array(
    'production_status' => \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_PRODUCTION
));
$development_sites = new \SiteMaster\Core\Registry\Sites\ByProductionStatus(array(
    'production_status' => \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_DEVELOPMENT
));

//Figure the number of sites to queue each day in order to scan them all once a month (total sites/31 days) 
$production_limit  = round($production_sites->count() / \SiteMaster\Core\Config::get('AUTO_QUEUE_RESCAN_PRODUCTION'));  //at least Once every x days
$development_limit = round($development_sites->count() / \SiteMaster\Core\Config::get('AUTO_QUEUE_RESCAN_DEVELOPMENT'));  //at least Once every x days

//Assume that this script will be ran every hour
$production_limit = ceil($production_limit / 24);
$development_limit = ceil($development_limit / 24);

//Schedule production sites

$queue = new \SiteMaster\Core\Registry\Sites\AutoQueue(array(
    'queue_limit' => $production_limit,
    'production_status' => \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_PRODUCTION
));

foreach ($queue as $site) {
    $site->scheduleScan();
}

//Schedule development sites

$queue = new \SiteMaster\Core\Registry\Sites\AutoQueue(array(
    'queue_limit' => $development_limit,
    'production_status' => \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_DEVELOPMENT
));

foreach ($queue as $site) {
    $site->scheduleScan();
}
