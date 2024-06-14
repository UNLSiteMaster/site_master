<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//1.  install all the default stuff.
$pluginManager = \SiteMaster\Core\Plugin\PluginManager::getManager();

echo '===== Installing libraries. =====' . PHP_EOL;
echo 'This may take some time...' . PHP_EOL;

//Update composer
passthru('php ' . \SiteMaster\Core\Util::getRootDir() . '/composer.phar install -n');

$plugins = $pluginManager->getAllPlugins();

foreach ($plugins as $pluginName) {
    $plugin = $pluginManager->getPluginInfo($pluginName);
    
    $command = 'cd ' .  $plugin->getRootDirectory() . ' && ' . \SiteMaster\Core\Util::getRootDir() . '/composer.phar install -n';
    passthru($command);
}
