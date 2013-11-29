<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

function preformPluginUpdate(\SiteMaster\Plugin\PluginInterface $plugin)
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

function updateComposerJSON(\SiteMaster\Plugin\PluginManager $pluginManager)
{
    $plugins = $pluginManager->getAllPlugins();
    $combinedJson = array();

    $files = array();

    foreach ($plugins as $pluginName) {
        $plugin = $pluginManager->getPluginInfo($pluginName);

        $files[] = $plugin->getRootDirectory() . '/' . $pluginName . '_composer.json';
    }

    $files[] = \SiteMaster\Util::getRootDir() . '/base_composer.json';

    foreach ($files as $filename) {
        if (!file_exists($filename)) {
            continue;
        }

        if (!$contents = file_get_contents($filename)) {
            echo "ERROR: unable to get " . $filename . PHP_EOL;
            continue;
        }

        if (!$json = json_decode($contents, true)) {
            echo "ERROR: unable to json_decode contents of " . $filename . PHP_EOL;
            continue;
        }

        $combinedJson = array_replace_recursive($combinedJson, $json);
    }

    $combinedJson = array_replace_recursive($combinedJson, $json);

    return file_put_contents(
        \SiteMaster\Util::getRootDir() . '/composer.json',
        json_encode($combinedJson, JSON_PRETTY_PRINT)
    );
}


//1.  install all the default stuff.
$pluginManager = \SiteMaster\Plugin\PluginManager::getManager();

echo '===== Generating composer.json and updating libraries. =====' . PHP_EOL;
echo 'This may take some time...' . PHP_EOL;

//Update base_composer.json
updateComposerJSON($pluginManager);

//Update composer
echo shell_exec('php ' . \SiteMaster\Util::getRootDir() . '/composer.phar update');

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