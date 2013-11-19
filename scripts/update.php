<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//1.  install all the default stuff.

//2.  trigger install on internal plugins
$internalPlugins = \SiteMaster\Plugin\PluginManager::getManager()->getInternalPlugins();
foreach ($internalPlugins as $name=>$options) {
    echo 'Updating internal plugin: ' . $name . PHP_EOL;
    $plugin = \SiteMaster\Plugin\PluginManager::getManager()->getPluginInfo($name);
    $result = $plugin->update();
    var_dump($result);
    //plugin->update checks to see if it is already installed, checks version, updates/install if needed.
}

//2.  trigger install on external plugins
$externalPlugins = \SiteMaster\Plugin\PluginManager::getManager()->getExternalPlugins();
foreach ($externalPlugins as $plugin) {
    $result = $plugin->update();
    echo 'Updating external plugin: ' . $plugin->getName() . PHP_EOL;
    var_dump($result);
    //plugin->update checks to see if it is already installed, checks version, updates/install if needed.
}