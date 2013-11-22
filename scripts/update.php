<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

//1.  install all the default stuff.

$pluginManager = \SiteMaster\Plugin\PluginManager::getManager();

//2.  trigger install on internal plugins
$internalPlugins = $pluginManager->getInternalPlugins();
foreach ($internalPlugins as $name=>$options) {
    $plugin = $pluginManager->getPluginInfo($name);
    if ($method = $plugin->getUpdateMethod()) {
        echo 'Preforming ' . $method . ' on internal plugin: ' . $name . PHP_EOL;
        $result = $plugin->preformUpdate();
    }
}

//2.  trigger install on external plugins
$externalPlugins = $pluginManager->getExternalPlugins();
foreach ($externalPlugins as $name=>$options) {
    $plugin = $pluginManager->getPluginInfo($name);
    if ($method = $plugin->getUpdateMethod()) {
        echo 'Preforming ' . $method . ' on external plugin: ' . $name . PHP_EOL;
        $result = $plugin->preformUpdate();
    }
}

//3. TODO: check for plugins that need to be uninstalled.
