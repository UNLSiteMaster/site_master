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
        \SiteMaster\Util::getRootDir() . '/composer.json',
        json_encode($combinedJson, JSON_PRETTY_PRINT)
    );
}

function exec_sql($db, $sql, $message, $fail_ok = false)
{
    echo $message.PHP_EOL;

    //Replace all instances of DEFAULTDATABASENAME with the config db name.
    $sql = str_replace('DEFAULTDATABASENAME', \SiteMaster\Config::get("DB_NAME"), $sql);

    try {
        $result = true;
        if ($db->multi_query($sql)) {
            do {
                /* store first result set */
                if ($result = $db->store_result()) {
                    $result->free();
                }

                if (!$db->more_results()) {
                    break;
                }
            } while ($db->next_result());
        } else {
            echo "Query Failed: " . $db->error . PHP_EOL;
        }
    } catch (Exception $e) {
        $result = false;
        if (!$fail_ok) {
            echo 'The query failed:'.$result->errorInfo();
            exit();
        }
    }
    echo 'finished.'.PHP_EOL;
    echo '------------------------------------------'.PHP_EOL;
    return $result;
}

$db = \SiteMaster\Util::getDB();

$sql = "";

$sql .= file_get_contents(dirname(__DIR__) . "/data/database.sql");

exec_sql($db, $sql, 'updatating core database');

//Install/update the database



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