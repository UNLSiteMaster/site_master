<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$sites = new \SiteMaster\Core\Registry\Sites\All();

//Figure the number of sites to queue each day in order to scan them all once a month (total sites/31 days) 
$limit = round($sites->count() / 31);

$queue = new \SiteMaster\Core\Registry\Sites\AutoQueue(array('queue_limit' => $limit));

foreach ($queue as $site) {
    $site->scheduleScan();
}
