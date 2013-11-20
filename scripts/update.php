<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//1.  install all the default stuff.

//2.  trigger install on internal plugins
$internalPlugins = \SiteMaster\Plugin\PluginManager::getManager()->getInternalPlugins();
foreach ($internalPlugins as $name=>$options) {
    $plugin = \SiteMaster\Plugin\PluginManager::getManager()->getPluginInfo($name);
    if ($method = $plugin->getUpdateMethod()) {
        echo 'Preforming ' . $method . ' on internal plugin: ' . $name . PHP_EOL;
        $result = $plugin->preformUpdate();
    }
}

//2.  trigger install on external plugins
$externalPlugins = \SiteMaster\Plugin\PluginManager::getManager()->getExternalPlugins();
foreach ($externalPlugins as $plugin) {
    $plugin = \SiteMaster\Plugin\PluginManager::getManager()->getPluginInfo($name);
    if ($method = $plugin->getUpdateMethod()) {
        echo 'Preforming ' . $method . ' on external plugin: ' . $name . PHP_EOL;
        $result = $plugin->preformUpdate();
    }
}

//3. TODO: check for plugins that need to be uninstalled.