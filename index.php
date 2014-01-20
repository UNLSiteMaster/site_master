<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/init.php");

// Initialize App, and construct everything
$app = new \SiteMaster\Core\Controller($_GET);

//Render Away
$savvy = new \SiteMaster\Core\OutputController($app->options);
$savvy->setTheme(\SiteMaster\Core\Config::get('THEME'));
$savvy->initialize();
$savvy->addGlobal('app', $app);

//TODO: implement users
//$savvy->addGlobal('user', \SiteMaster\User\Service::getCurrentUser());

echo $savvy->render($app);