<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$queue = new \SiteMaster\Core\Registry\Sites\AutoQueue(array('only_not_scanned' => true, 'queue_limit' => 9999));

foreach ($queue as $site) {
    $site->scheduleScan();
}
