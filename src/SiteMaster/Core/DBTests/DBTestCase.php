<?php
namespace SiteMaster\Core\DBTests;

use SiteMaster\Core\Config;
use SiteMaster\Core\Plugin\PluginManager;
use SiteMaster\Core\Util;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class DBTestCase
 * @package SiteMaster
 */
class DBTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        try {
            Util::connectTestDB();
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database is not available, database tests were skipped: ' . $e->getMessage());
        }

        $pluginManager = \SiteMaster\Core\Plugin\PluginManager::getManager();
        foreach ($pluginManager->getAllPlugins() as $plugin_name) {
            \SiteMaster\Core\Util::connectTestDB();
            $plugin = $pluginManager->getPluginInfo($plugin_name);
            $plugin->performUpdate();
        }
        
        $pluginManager->initializeMetrics();
    }

    public function cleanDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getAllPlugins();

        //Clear the database
        foreach ($installedPlugins as $name=>$plugin) {
            $plugin = PluginManager::getManager()->getPluginInfo($plugin);
            //Don't actually uninstall, just perform uninstall logic defined by the plugin (which should remove all SQL)
            $plugin->uninstall();
        }
    }

    public function installBaseDB()
    {
        $pluginManager = PluginManager::getManager();
        $installedPlugins = $pluginManager->getAllPlugins();

        //Reset database to a fresh install
        foreach ($installedPlugins as $name=>$plugin) {
            $plugin = PluginManager::getManager()->getPluginInfo($plugin);
            if (!$plugin->isInstalled()) {
                //Run the install process
                $plugin->performUpdate();

                //Re-register any new plugins
                PluginManager::initialize(
                    new EventDispatcher(),
                    array(
                        'internal_plugins' => array(
                            'Core' => array(),
                        ),
                        'external_plugins' => Config::get('PLUGINS')
                    ),
                    Config::get('GROUPS'),
                    true //force re-initialize
                );
            }
        }
    }

    public function setUpDB()
    {
        $this->cleanDB();
        $this->installBaseDB();
        $this->installMockData(new BaseTestDataInstaller());
    }

    public function installMockData(MockTestDataInstallerInterface $installer)
    {
        $installer->install();
    }
}