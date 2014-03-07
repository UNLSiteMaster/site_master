<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$queue = new \SiteMaster\Core\Registry\Sites\AutoQueue();

foreach ($queue as $site) {
    $site->scheduleScan();
}
