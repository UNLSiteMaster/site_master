<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$example = new \SiteMaster\Core\ExampleEmail();

$emailer = new \SiteMaster\Core\Emailer($example);
$result = $emailer->send();

var_dump($result);