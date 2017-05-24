<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$overrides = new \SiteMaster\Core\Auditor\Site\Overrides\Expired();

foreach ($overrides as $override) {
    $override->delete();
}
