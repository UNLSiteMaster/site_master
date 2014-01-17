<?php
ini_set('display_errors', true);

//Initialize all settings and autoloaders
require_once(__DIR__ . "/../init.php");

function updateComposerJSON(\SiteMaster\Core\Plugin\PluginManager $pluginManager)
{
    $plugins = $pluginManager->getAllPlugins();
    $combinedJson = array();

    $files = array();

    foreach ($plugins as $pluginName) {
        $plugin = $pluginManager->getPluginInfo($pluginName);

        $files[] = $plugin->getRootDirectory() . '/' . $pluginName . '_composer.json';
    }

    $files[] = \SiteMaster\Core\Util::getRootDir() . '/base_composer.json';

    $repositories = array();
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

        if (isset($json['repositories'])) {
            /* array_replace_recursive will replace values of an array by int indexes,
             * which we do not want to do in the case of repositories.  We want ALL of the unique
             * repositories, so create an array of them and add them back later.
             */
            $repositories = array_merge($repositories, $json['repositories']);
        }

        $combinedJson = array_replace_recursive($combinedJson, $json);
    }

    if (isset($combinedJson['repositories'])) {
        $combinedJson['repositories'] = array_unique($repositories, SORT_REGULAR);
    }

    return file_put_contents(
        \SiteMaster\Core\Util::getRootDir() . '/composer.json',
        json_encode($combinedJson, JSON_PRETTY_PRINT)
    );
}

//1.  install all the default stuff.
$pluginManager = \SiteMaster\Core\Plugin\PluginManager::getManager();

echo '===== Generating composer.json and updating libraries. =====' . PHP_EOL;
echo 'This may take some time...' . PHP_EOL;

//Update base_composer.json
updateComposerJSON($pluginManager);

//Update composer
echo shell_exec('php ' . \SiteMaster\Core\Util::getRootDir() . '/composer.phar update');
