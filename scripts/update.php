<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

function preformPluginUpdate(\SiteMaster\Core\Plugin\PluginInterface $plugin)
{
    static $checked;

    if (!isset($checked)) {
        $checked = array();
    }

    $name = $plugin->getMachineName();

    //Have we already checked this plugin?
    if (in_array($name, $checked)) {
        return true;
    }

    $checked[] = $name;

    if ($method = $plugin->getUpdateMethod()) {
        echo 'Preforming ' . $method . ' on ' . $plugin->getPluginType() . ' plugin: ' . $name . PHP_EOL;
        return $plugin->preformUpdate();
    }

    return true;
}

//1.  install all the default stuff.
$pluginManager = \SiteMaster\Core\Plugin\PluginManager::getManager();

echo '===== Updating Plugins =====' . PHP_EOL;

//2.  trigger install on internal plugins
$internalPlugins = $pluginManager->getInternalPlugins();
foreach ($internalPlugins as $name=>$options) {
    preformPluginUpdate($pluginManager->getPluginInfo($name));
}

//2.  trigger install on external plugins
$externalPlugins = $pluginManager->getExternalPlugins();
foreach ($externalPlugins as $name=>$options) {
    preformPluginUpdate($pluginManager->getPluginInfo($name));
}

//3. check for plugins that need to be uninstalled.
$installedPlugins = $pluginManager->getInstalledPlugins();
foreach ($installedPlugins as $name=>$plugin) {
    preformPluginUpdate($plugin);
}

echo '===== Finished! =====' . PHP_EOL;