<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/init.php");

\SiteMaster\Core\User\Session::start();

// Initialize App, and construct everything
$app = new \SiteMaster\Core\Controller($_GET);

//Render Away
$savvy = new \SiteMaster\Core\OutputController($app->options);
$savvy->setTheme(\SiteMaster\Core\Config::get('THEME'));
$savvy->initialize();

$savvy->addGlobal('app', $app);
$savvy->addGlobal('plugin_manager', \SiteMaster\Core\Plugin\PluginManager::getManager());
$savvy->addGlobal('user', \SiteMaster\Core\User\Session::getCurrentUser());
$savvy->addGlobal('grading_helper', new \SiteMaster\Core\Auditor\GradingHelper());
$savvy->addGlobal('base_url', \SiteMaster\Core\Config::get('BASE_URL'));
$savvy->addGlobal('theme_helper', new \SiteMaster\Core\ThemeHelper());

echo $savvy->render($app);