<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

$pluginManager = \SiteMaster\Core\Plugin\PluginManager::getManager();

//3. check for plugins that need to be uninstalled.
$installedPlugins = $pluginManager->getInstalledPlugins();
foreach ($installedPlugins as $name=>$plugin) {
    $plugin->uninstall();
}
