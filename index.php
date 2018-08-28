<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/init.php");

\SiteMaster\Core\User\Session::start();

// Initialize App, and construct everything
$app = new \SiteMaster\Core\Controller($_GET);


// Set a few caching options
$options = array(
    'cacheDir' => \SiteMaster\Core\Config::get('CACHE_DIR'),
    'lifeTime' => 60*60*24*7 //cache for 7 days
);

// Create a Cache_Lite object
$cache = new \Savvy_Turbo_CacheInterface_UNLCacheLite($options);

//Render Away
$savvy = new \SiteMaster\Core\OutputController($app->options);
$savvy->setCacheInterface($cache);
$savvy->setTheme(\SiteMaster\Core\Config::get('THEME'));
$savvy->initialize();

$savvy->addGlobal('app', $app);
$savvy->addGlobal('plugin_manager', \SiteMaster\Core\Plugin\PluginManager::getManager());
$savvy->addGlobal('user', \SiteMaster\Core\User\Session::getCurrentUser());
$savvy->addGlobal('grading_helper', new \SiteMaster\Core\Auditor\GradingHelper());
$savvy->addGlobal('base_url', \SiteMaster\Core\Util::getAbsoluteBaseURL());
$savvy->addGlobal('theme_helper', new \SiteMaster\Core\ThemeHelper());
$savvy->addGlobal('csrf_helper', \SiteMaster\Core\Controller::getCSRFHelper());

$savvy->addGlobal('scan', false);
if (isset($app->options['scans_id']) && $scan = \SiteMaster\Core\Auditor\Scan::getById($app->options['scans_id'])) {
    $savvy->addGlobal('scan', $scan);
}

echo $savvy->render($app);
